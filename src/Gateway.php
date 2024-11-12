<?php
namespace Omnipay\Moneris;

use Omnipay\Common\AbstractGateway;
use Omnipay\Moneris\Helper;
use Omnipay\Common\Exception\RuntimeException;
use Symfony\Component\HttpFoundation\ParameterBag;

class Gateway extends AbstractGateway
{
    /**
     * Indicates whether responses should be mocked
     * @var bool
     */
    protected $mock;
    
    /**
     * Initialize this gateway with default parameters
     *
     * @param  array $parameters
     * @return $this
     */
    public function initialize(array $parameters = array())
    {
        $this->parameters = new ParameterBag;

        $config = static::parameterConfig();
        
        if(is_array($parameters)) {
            foreach($parameters as $key => $value) {
                // Use setter if available
                $method = 'set'.ucfirst(Helper::camelCase($key));
                if(method_exists($this, $method)) {
                    $this->$method($value);
                }
                // Set if key is configured, formatting and checking as config
                elseif(array_key_exists($key,$config)) {
                    $this->parameters->set($key,Helper::formatAndValidateParameterValue($key,$value,$config[$key]));
                }
            }
        };

        return $this;
    }
    
    /* ------------------------------------------------------------------------ 
     * Getters / setters
     * ------------------------------------------------------------------------    
     */
    public function getName()
    {
        return 'Moneris';
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
     * Returns configuration for required gateway parameters.
     * @return array
     */
    public static function parameterConfig() 
    {
        return [
            'store_id' => [
                'type' => 'string',
                'limit' => 10,
                'required' => true
            ],
            'api_token' => [
                'type' => 'string',
                'limit' => 20,
                'required' => true
            ],
            'checkout_id' => [
                'type' => 'string',
                'limit' => 30,
                'required' => true
            ],
            'environment' => [
                'type' => 'string',
                'options' => [
                    'qa', 'prod'
                ],
                'required' => true
            ]
        ];
    }
    
    /* ------------------------------------------------------------------------ 
     * Flow methods
     * ------------------------------------------------------------------------    
     */
    
    /**
     * Creates preload request for purchase.
     * Note: Gateway parameters are added by the createRequest method
     * @param array $parameters
     * @return \Omnipay\Moneris\Message\PreloadRequest
     */
    public function purchase(array $parameters = array())
    {
        $request = $this->createRequest('\Omnipay\Moneris\Message\PreloadRequest', $parameters);
        $request->setGatewayParameters($this->prepareParameterDataArray());
        $request->setMock($this->mock);
        return $request;
    }

    /**
     * Creates receipt request - after purchase response.
     * Note: Gateway parameters are added by the createRequest method
     * @param array $parameters
     * @return \Omnipay\Moneris\Message\ReceiptRequest
     */
    public function completePurchase(array $parameters = array())
    {
        $request = $this->createRequest('\Omnipay\Moneris\Message\ReceiptRequest', $parameters);
        $request->setGatewayParameters($this->prepareParameterDataArray());
        $request->setMock($this->mock);
        return $request;
    }

    /**
     * Prepares, formats and validates parameter data according to configuration
     * @return array
     * @throws RuntimeException
     */
    public function prepareParameterDataArray()
    {
        $data = [];
        
        $config = static::parameterConfig();
        
        if(empty($config)) {
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
    
}
