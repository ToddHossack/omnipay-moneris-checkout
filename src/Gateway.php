<?php
namespace Omnipay\Moneris;

use Omnipay\Common\AbstractGateway;
use Omnipay\Moneris\Helper;
use Omnipay\Common\Exception\RuntimeException;
use Symfony\Component\HttpFoundation\ParameterBag;

class Gateway extends AbstractGateway
{
    /**
     * Initialize this gateway with default parameters
     *
     * @param  array $parameters
     * @return $this
     */
    public function initialize(array $parameters = array())
    {
        $this->parameters = new ParameterBag;

        $config = array_merge(
            static::requiredParameterConfig(),
            static::optionalParameterConfig()
        );
        
        if(is_array($parameters)) {
            foreach($parameters as $key => $value) {
                // Use setter if available
                $method = 'set'.ucfirst(Helper::camelCase($key));
                if(method_exists($this, $method)) {
                    $this->$method($value);
                }
                // Set if configured, formatting and checking as per config
                elseif(array_key_exists($key,$config)) {
                    $this->parameters->set($key,Helper::formatParameterValue($key,$value,$config[$key]));
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

    
    /* ------------------------------------------------------------------------ 
     * Parameter config methods
     * ------------------------------------------------------------------------    
     */
    
    /**
     * Returns configuration for required gateway parameters.
     * @return array
     */
    public static function requiredParameterConfig() 
    {
        return [
            'store_id' => [
                'type' => 'string',
                'limit' => 10,
            ],
            'api_token' => [
                'type' => 'string',
                'limit' => 20,
            ],
            'checkout_id' => [
                'type' => 'string',
                'limit' => 30,
            ],
            'environment' => [
                'type' => 'string',
                'options' => [
                    'qa', 'prod'
                ]
            ]
        ];
    }
    
    /**
     * Returns configuration for optional gateway parameters.
     * @return array
     */
    public static function optionalParameterConfig() 
    {
        return [];
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
        $request->setGatewayParameters($this->parameters->all());
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
        $request->setGatewayParameters($this->parameters->all());
        return $request;
    }

}
