<?php
namespace Omnipay\Moneris\Message;

use Omnipay\Moneris\Helper;
use Omnipay\Moneris\Config;
use Omnipay\Common\Exception\RuntimeException;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request as HttpRequest;
use Guzzle\Http\ClientInterface;

/**
 * Moneris Abstract Request
 */
abstract class AbstractRequest extends \Omnipay\Common\Message\AbstractRequest
{
    
    protected $gatewayParameters = [];
    
    protected $responseClass;
    
    protected $jsonBody = true;
    
    /**
     * Indicates whether responses should be mocked
     * @var bool
     */
    protected $mock;
    
    /**
     * Mock response data
     * @var array 
     */
    protected static $mockResponseData;
    
    
    /* ------------------------------------------------------------------------ 
     * Init
     * ------------------------------------------------------------------------    
     */
    
        /**
         * Create a new Request
         * NB: Overrides parent because of double call to initialize:
         *  - parent calls initialize in constructor, 
         *  - the gateway calls initialize in createRequest()
         *
         * @param ClientInterface $httpClient  A Guzzle client to make API calls with
         * @param HttpRequest     $httpRequest A Symfony HTTP request object
         */
        public function __construct(ClientInterface $httpClient, HttpRequest $httpRequest)
        {
            $this->httpClient = $httpClient;
            $this->httpRequest = $httpRequest;
        }
        
    /**
     * Initializes request parameters.
     * As opposed to the parent class, which filters parameters based on available 
     * setters, this method filters the parameters based on hard-coded configuration
     * matching Moneris Checkout parameter configuration.
     * @see https://developer.moneris.com/livedemo/checkout/overview/guide/php
     * @param array $parameters
     * @return $this
     * @throws RuntimeException
     */
    public function initialize(array $parameters = array())
    {
        if (null !== $this->response) {
            throw new RuntimeException('Request cannot be modified after it has been sent!');
        }

        $parameterCfg = array_merge(
            static::requiredParameterConfig(),
            static::optionalParameterConfig(),
            static::optionalParameterObjectConfig()
        );
        
        $this->parameters = new ParameterBag;

        if(is_array($parameters)) {
            foreach($parameters as $key => $value) {
                // Use setter if available
                $method = 'set'.ucfirst(Helper::camelCase($key));
                
                if($key === 'mock') {
                    $this->mock = in_array($value,[1,'1','true',true],true);
                }
                elseif(method_exists($this, $method)) {
                    $this->$method($value);
                }
                // Set if configured
                elseif(array_key_exists($key,$parameterCfg)) {
                    $this->parameters->set($key,$value);
                }
            }
        }

        return $this;
    }
    
    /* ------------------------------------------------------------------------ 
     * Getters / setters
     * ------------------------------------------------------------------------    
     */
    
    /**
     * Gets gateway parameters
     * @return array
     */
    public function getGatewayParameters()
    {
        return $this->gatewayParameters;
    }
    
    /**
     * Sets gateway parameters
     * @param array $parameters
     */
    public function setGatewayParameters($parameters)
    {
        $this->gatewayParameters = $parameters;
    }
    
    /**
     * Gets endpoint
     * @return string
     */
    public function getEndpoint()
    {
        return $this->isTest() ? Config::getTestEndpoint() : Config::getLiveEndpoint();
    }
    
    /**
     * Gets the class to use for the request response
     * @return string
     */
    public function getResponseClass()
    {
        return $this->responseClass;
    }
    
    /**
     * Sets the class to use for the request response
     * @return string
     */
    public function setResponseClass($class)
    {
        $this->responseClass = $class;
    }
    
    /**
     * Sets mock as enabled (true) or disabled (false)
     * @param bool $mode
     */
    public function setMock($mode)
    {
        $this->mock = (bool) $mode;
    }
    
    /**
     * Gets mock setting
     * @return bool
     */
    public function getMock()
    {
        return $this->mock;
    }
    
    /* ------------------------------------------------------------------------ 
     * Parameter config methods
     * ------------------------------------------------------------------------    
     */
    
    /**
     * Returns configuration for required parameters.
     * @return array
     */
    abstract static public function requiredParameterConfig();
  
    /**
     * Returns configuration for optional parameters.
     * Overridden in subclasses as needed.
     * @return array
     */
    public static function optionalParameterConfig()
    {
        return [];
    }
    
    /**
     * Returns configuration for optional parameter objects.
     * Overridden in subclasses as needed.
     * @return array
     */
    public static function optionalParameterObjectConfig()
    {
        return [];
    }
    
    public static function condensedParameterConfig()
    {
        $condensed = [];
        
        $all = array_merge(
            static::requiredParameterConfig(),
            static::optionalParameterConfig(),
            static::optionalParameterObjectConfig()
        );
        
        return static::flattenConfig($all);
    }
    
    protected static function flattenConfig($parameterCfg)
    {
        $condensed = [];
        if(is_array($parameterCfg)) {
            foreach($parameterCfg as $key => $cfg) {
                $type = isset($cfg['type']) ? $cfg['type'] : 'string';
                // Arrays / objects
                if(!empty($cfg['variables']) && is_array($cfg['variables'])) {
                    $condensed[$key] = static::flattenConfig($cfg['variables']);
                }
                // Scalar
                else {
                    $condensed[$key] = $cfg;
                }
            }
        }
        
        return $condensed;
    }
    
    
    /* ------------------------------------------------------------------------ 
     * Parameter data methods
     * ------------------------------------------------------------------------    
     */
    
    public function getData()
    {
        $data = [];
        
        // Gateway parameter data
        $data += (array) $this->getGatewayData();
        
        // Required parameter dasta
        $data += (array) $this->prepareParameterDataArray(static::requiredParameterConfig(),true);
  
        // Optional parameter data
        $data += (array) $this->prepareParameterDataArray(array_merge(
            (array) static::optionalParameterConfig(), 
            (array) static::optionalParameterObjectConfig()
        ));
        
        return $data;
    }
    
    // Assumed formatted and validated by gateway
    public function getGatewayData() 
    {
        return $this->gatewayParameters;
    }
    
    public function prepareParameterDataArray($config,$required=false)
    {
        $data = [];
        
        if(empty($config) && $required) {
            throw new RuntimeException('Configuration not found for required parameters.');
        }
        
        foreach($config as $key => $cfg) {
            $val = Helper::prepareParameterData($key,$this->getParameterData($key,$cfg),$cfg);
            // Ignore keys without corresponding configuration
            // or values without any data
            if(empty($cfg) || Helper::isEmpty($val)) {
                continue;
            }
            
            $data[$key] = $val;
        }
        
        return $data;
    }
    
    /**
     * Gets data for given parameter key, trying getters first, 
     * before using the standard parameter bag "get".
     * @param string $key
     * @param array $cfg
     * @return mixed
     */
    protected function getParameterData($key,$cfg=null)
    {
        // Use getter if available
        $method = 'get'.ucfirst(Helper::camelCase($key));
        if(method_exists($this, $method)) {
            return $this->$method($value);
        }
        
        $type = isset($cfg['type']) ? $cfg['type'] : 'string';
        $default = isset($cfg['default']) ? $cfg['default'] : null;

        // Get data from parameter bag, using getter, if available
        $method = 'get'.ucfirst($type);
        if(method_exists($this->parameters, $method)) {
            return $this->parameters->$method($key,$default);
        } else {
            return $this->parameters->get($key,$default);
        }
    }
  
    public function isTest()
    {
        $mode = Helper::data_get($this->gatewayParameters,'environment');
        return ($mode && $mode === 'qa');
    }
    
    /* ------------------------------------------------------------------------ 
     * Flow methods
     * ------------------------------------------------------------------------    
     */
    
    /**
     * 
     * @param array $data
     * @return object
     */
    public function sendData($data)
    { 
        try {
            
            $body = ($this->jsonBody) 
                ? json_encode($data,JSON_HEX_APOS | JSON_HEX_QUOT)
                : $data;
            
            /*
             * HTTP response
             */
            $responseClass = $this->getResponseClass();

            // Mock response
            if($this->isMock()) {
                $responseData = static::getMockResponseData();
            }
            // Normal response
            else {
                $response = $this->sendHttpRequest($body);
                $responseData = ($this->jsonBody) 
                    ? json_decode($response->getBody(),true)
                    : $response->getBody();
            }
            // Response
            $this->response = new $responseClass($this, $responseData);
        
        } catch (\Exception $ex) {
            throw new RuntimeException($ex->getMessage(),$ex->getCode());
        }

        return $this->response;
    }
    
    /**
     * 
     * @param array $data
     * @return \Guzzle\Http\Message\Response
     * @throws RuntimeException
     */
    protected function sendHttpRequest($body)
    {
         /*
         * Set up request
         */
        $requestOptions = [
            'allow_redirects' => false,
            'timeout' => 5
        ];
        
        // Test requests may require following setting, to "supply the path
        // to a CA bundle to enable verification using a custom certificate" such 
        // as a self-signed certificate  
        if($this->isTest()) {
            $requestOptions['verify'] = \Composer\CaBundle\CaBundle::getSystemCaRootBundlePath();
        }
        
        // JSON
        if($this->jsonBody) {
            $headers['Accept'] = 'application/json';
        }
        /*
         * Request / response
         */
        try {
            // HTTP request
            $request = $this->httpClient->createRequest(
                'POST',
                $this->getEndpoint(),
                $headers,
                $body,
                $requestOptions
            );

            // HTTP response
            return $this->httpClient->send($request);
        } catch (\Exception $ex) {
            throw new RuntimeException($ex->getMessage(),$ex->getCode());
        }
    }
    
    /* ------------------------------------------------------------------------ 
     * Mock methods
     * ------------------------------------------------------------------------    
     */
    
    /**
     * Whether the request is mock, based on the boolean gateway parameter 'mock'
     * @return bool
     */
    public function isMock()
    {
        return (bool) $this->mock;
    }
    
    /**
     * Sets mock response data
     * @param array $data
     */
    public static function setMockResponseData($data)
    {
        static::$mockResponseData = $data;
    }
    
    /**
     * Gets mock response data
     * @return array
     */
    public static function getMockResponseData()
    {
        return static::$mockResponseData;
    }
    
}
