<?php
namespace Omnipay\Moneris\Message;

use Omnipay\Moneris\Helper;
use Omnipay\Common\Exception\RuntimeException;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * Moneris Abstract Request
 */
abstract class AbstractRequest extends \Omnipay\Common\Message\AbstractRequest
{
    protected $liveEndpoint = 'https://gateway.moneris.com/chktv2/request/request.php';
    protected $testEndpoint = 'https://gatewayt.moneris.com/chktv2/request/request.php';
    
    /* ------------------------------------------------------------------------ 
     * Init
     * ------------------------------------------------------------------------    
     */
    
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

        $parameterCfg = $this->requiredParameterConfig();
        
        $this->parameters = new ParameterBag;

        if(is_array($parameters)) {
            foreach($parameters as $key => $value) {
                // Use setter if available
                $method = 'set'.ucfirst(Helper::camelCase($key));
                if(method_exists($this, $method)) {
                    $this->$method($value);
                }
                // Set if configured
                elseif(array_key_exists($key,array_merge(
                    $this->requiredParameterConfig(),
                    $this->optionalParameterConfig(),
                    $this->optionalParameterObjectConfig()
                ))) {
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
    
    public function getEndpoint()
    {
        return $this->getTestMode() ? $this->testEndpoint : $this->liveEndpoint;
    }
    
    /**
     * Set test end point - allow for local development / mock gateways
     * @param string $url
     */
    public function setTestEndpoint($url)
    {
        $this->testEndpoint = $url;
    }
    
    /* ------------------------------------------------------------------------ 
     * Parameter config methods
     * ------------------------------------------------------------------------    
     */
    
    /**
     * Returns configuration for required parameters.
     * @return array
     */
    abstract public function requiredParameterConfig();
  
    /**
     * Returns configuration for optional parameters.
     * Overridden in subclasses as needed.
     * @return array
     */
    public function optionalParameterConfig()
    {
        return [];
    }
    
    /**
     * Returns configuration for optional parameter objects.
     * Overridden in subclasses as needed.
     * @return array
     */
    public function optionalParameterObjectConfig()
    {
        return [];
    }
    
    /* ------------------------------------------------------------------------ 
     * Parameter data methods
     * ------------------------------------------------------------------------    
     */
    
    public function getData()
    {
        $data = [];
        
        // Required
        $data += (array) $this->getRequiredData();
        
        // Optional
        $data += (array) $this->getOptionalData();
        
        
        return $data;
    }
    
    /**
     * Gets the required parameter data
     * @return array
     * @throws RuntimeException
     */
    public function getRequiredData() 
    {
        $data = [];
        
        $config = $this->requiredParameterConfig();
        
        if(is_array($config)) {
            foreach($config as $key => $cfg) {
                $val = $this->getParameterData($key,$cfg);
                // Check required
                if(is_null($val)) {
                    throw new RuntimeException(sprintf('Required parameter %s is missing for request.',$key));
                }
                // Format and check value
                $data[$key] = Helper::formatParameterValue($key,$val,$cfg);
            }
        } else {
            throw new RuntimeException('Configuration not found for required parameters.');
        }
        
        return $data;
    }
    
    public function getOptionalData()
    {
        $data = [];
        
        $config = array_merge($this->optionalParameterConfig(),$this->optionalParameterObjectConfig());
        
        if(!empty($config)) {
            foreach($config as $key => $cfg) {
                $val = $this->getParameterData($key,$cfg);
                if(!is_null($val)) {
                    $data[$key] = Helper::formatParameterValue($key,$val,$cfg);
                }
            }
        }
        
        return $data;
    }
    
    
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
    
    /*
    public function getStoreId()
    {
        return $this->getParameter('store_id');
    }

    public function setStoreId($value)
    {
        return $this->setParameter('store_id', $value);
    }

    public function getApiToken()
    {
        return $this->getParameter('api_token');
    }

    public function setApiToken($value)
    {
        return $this->setParameter('api_token', $value);
    }

    public function getCheckoutId()
    {
        return $this->getParameter('checkout_id');
    }

    public function setCheckoutId($value)
    {
        return $this->setParameter('checkout_id', $value);
    }

    public function getCustId()
    {
        return $this->getParameter('custId');
    }

    public function setCustId($value)
    {
        return $this->setParameter('custId', $value);
    }
    
    public function getNote()
    {
        return $this->getParameter('note');
    }

    public function setNote($value)
    {
        return $this->setParameter('note', $value);
    }
    
    public function getLang()
    {
        return $this->getParameter('lang');
    }

    public function setLang($value)
    {
        return $this->setParameter('lang', $value);
    }
    
    public function getRvar()
    {
        return $this->getParameter('rvar');
    }

    public function setRvar($arr)
    {
        return $this->setParameter('rvar', $arr);
    }
    */
    
    
}
