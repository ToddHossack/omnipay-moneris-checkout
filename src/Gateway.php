<?php
namespace Omnipay\Moneris;

use Omnipay\Common\AbstractGateway;
use Omnipay\Common\Helper;
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

        if(is_array($parameters)) {
            foreach($parameters as $key => $value) {
                // Use setter if available
                $method = 'set'.ucfirst(Helper::camelCase($key));
                if(method_exists($this, $method)) {
                    $this->$method($value);
                }
                // Set if configured
                // @todo check parameters
                elseif(array_key_exists($key,array_merge(
                    $this->requiredParameterConfig(),
                    $this->optionalParameterConfig()
                ))) {
                    $this->parameters->set($key,$value);
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
    public function requiredParameterConfig() 
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
    public function optionalParameterConfig() 
    {
        return [];
    }
    
    /*
    public function getDefaultParameters()
    {
        return array(
            'storeId' => '',
            'apiToken' => '',
            'checkoutId' => '',
            'environment' => ''
        );
    }

    
    public function getStoreId()
    {
        return $this->getParameter('storeId');
    }

    public function setStoreId($value)
    {
        return $this->setParameter('storeId', $value);
    }

    public function getApiToken()
    {
        return $this->getParameter('apiToken');
    }

    public function setApiToken($value)
    {
        return $this->setParameter('apiToken', $value);
    }
    */
    
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
        return $this->createRequest('\Omnipay\Moneris\Message\PreloadRequest', $parameters);
    }

    /**
     * Creates receipt request - after purchase response.
     * Note: Gateway parameters are added by the createRequest method
     * @param array $parameters
     * @return \Omnipay\Moneris\Message\ReceiptRequest
     */
    public function completePurchase(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\Moneris\Message\ReceiptRequest', $parameters);
    }

}
