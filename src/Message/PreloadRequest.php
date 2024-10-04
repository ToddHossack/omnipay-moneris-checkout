<?php
namespace Omnipay\Moneris\Message;

/**
 * Moneris Preload Request (for purchases)
 */
class PreloadRequest extends AbstractRequest
{

    /* ------------------------------------------------------------------------ 
     * Required Parameter Config
     * ------------------------------------------------------------------------    
     */
    
    public function requiredParameterConfig() 
    {
        return [
            'txn_total' => [
                'type' => 'float',
                'decimals' => 2,
                'limit' => 10
            ],
            'action' => [
                'type' => 'string',
                'default' => 'preload'
            ]
        ];
    }
    
    /* ------------------------------------------------------------------------ 
     * Optional Parameter Config
     * ------------------------------------------------------------------------    
     */
    public function optionalParameterConfig() 
    {
        return [
            'order_no' => [
                'type' => 'string',
                'limit' => 50,
                'replace' => [' ','<','>','$'.'%','=','\\','?','^','{','}','[',']','"']
            ],
            'cust_id' => [
                'type' => 'string',
                'limit' => 50,
                'replace' => [' ','<','>','$'.'%','=','\\','?','^','{','}','[',']','"']
            ],
            'dynamic_descriptor' => [
                'type' => 'string',
                'limit' => 20,
                'replace' => [' ','<','>','$'.'%','=','\\','?','^','{','}','[',']','"']
            ],
            'language' => [
                'type' => 'string',
                'limit' => 2,
                'options' => [
                    'en', 'fr'
                ],
                'default' => 'en'
            ],
            'data_key' => [
                'type' => 'alnum',
                'limit' => 25
            ],
            'ask_cvv' => [
                'type' => 'alpha',
                'limit' => 1,
                'options' => ['Y','N']
            ],
            'shipping_amount' => [
                'type' => 'float',
                'decimals' => 2,
                'limit' => 10,
            ],
        ];
    }
    
    /* ------------------------------------------------------------------------ 
     * Optional Parameter Object Config
     * ------------------------------------------------------------------------    
     */
    /**
     * Returns associative array of optional parameters which are conveyed as objects,
     * with allowed sub-keys specified.
     * @return array
     */
    public function optionalParameterObjectConfig() 
    {
        return [
            'recur' => $this->recurConfig(),
            'cart' => $this->cartConfig(),
            'contact_details' => $this->contactDetailsConfig(),
            'shipping_details' => $this->shippingDetailsConfig(),
            'billing_details' => $this->billingDetailsConfig()
        ];
    }
    
    /**
     * Recur object configuration.
     * Available variables contained under the "variables" key
     * @return array
     */
    protected function recurConfig()
    {
        return [
            'type' => 'object', // Associative array
            'variables' => [
                'number_of_recurs' => [
                    'type' => 'int',
                    'min' => 1,
                    'max' => 999
                ],
                'recur_period' => [
                    'type' => 'int',
                    'min' => 1,
                    'max' => 999
                ],
                'recur_amount' => [
                    'type' => 'float',
                    'min' => 0,
                    'decimals' => 2,
                    'limit' => 10,
                ],
                'recur_unit' => [
                    'type' => 'string',
                    'options' => [
                        'day','week','month','enom'
                    ]
                ],
                'start_date' => [
                    'type' => 'date',
                    'format' => 'Y/m/d'
                ],
                'bill_now' => [
                    'type' => 'string',
                    'options' => [
                        'true', 'false'
                    ]
                ]
            ]
        ];
        
    }
    
    
    /**
     * Cart object configuration.
     * Available variables contained under the "variables" key
     * @return array
     */
    protected function cartConfig()
    {
        return [
            'type' => 'object', // Associative array
            'variables' => [
                'items' => [
                    'type' => 'array', // Numeric array
                    'variables' => [
                        'url' => [
                            'type' => 'string',
                            'limit' => 20
                        ],
                        'description' => [
                            'type' => 'string',
                            'limit' => 200,
                            'replace' => ['<','>','$'.'%','=','\\','?','^','{','}','[',']','"']
                        ],
                        'product_code' => [
                            'type' => 'string',
                            'limit' => 50,
                            'replace' => ['<','>','$'.'%','=','\\','?','^','{','}','[',']','"']
                        ],
                        'unit_cost' => [
                            'type' => 'float',
                            'min' => 0,
                            'decimals' => 2,
                            'limit' => 10,
                        ],
                        'quantity' => [
                            'type' => 'int',
                            'min' => 0,
                            'limit' => 6,
                        ],
                    ]

                ],
                'subtotal' => [
                    'type' => 'float',
                    'min' => 0,
                    'decimals' => 2,
                    'limit' => 10,
                ], 
                'tax' => $this->taxConfig()
            ]
            
        ];
        
    }

    /**
     * Tax object configuration.
     * Available variables contained under the "variables" key
     * @return array
     */
    protected function taxConfig()
    {
        return [
            'type' => 'object', // Associative array
            'variables' => [
                'amount' => [
                    'type' => 'float',
                    'min' => 0,
                    'decimals' => 2,
                    'limit' => 10,
                ],
                'description' => [
                    'type' => 'string',
                    'limit' => 50,
                    'replace' => ['<','>','$'.'%','=','\\','?','^','{','}','[',']','"']
                ],
                'rate' => [
                    'type' => 'float',
                    'min' => 0,
                    'decimals' => 3,
                    'limit' => 7,
                ],
            ]
        ];
    }
    
    /**
     * Contact details configuration.
     * @return array
     */
    protected function contactDetailsConfig()
    {
        return [
            'first_name' => [
                'type' => 'string',
                'limit' => 30
            ],
            'last_name' => [
                'type' => 'string',
                'limit' => 30
            ],
            'email' => [
                'type' => 'string',
                'limit' => 255
            ],
            'phone' => [
                'type' => 'string',
                'limit' => 30
            ]
        ];
    }
    
    /**
     * Shipping details configuration.
     * @return array
     */
    protected function shippingDetailsConfig()
    {
        return [
            'address_1' => [
                'type' => 'string',
                'limit' => 50,
                'replace' => ['<','>','$'.'%','=','\\','?','^','{','}','[',']','"']
            ],
            'address_2' => [
                'type' => 'string',
                'limit' => 50,
                'replace' => ['<','>','$'.'%','=','\\','?','^','{','}','[',']','"']
            ],
            'city' => [
                'type' => 'string',
                'limit' => 50
            ],
            'province' => [
                'type' => 'string',
                'limit' => 2  // Country subdivision ISO 3166-2
            ],
            'country' => [
                'type' => 'string',
                'limit' => 2  // Country ISO 3166-1 alpha-2
            ],
            'postal_code' => [
                'type' => 'string',
                'limit' => 20,
                'replace' => ['<','>','$'.'%','=','\\','?','^','{','}','[',']','"']
            ],
        ];
    }
    
    /**
     * Billing details configuration.
     * (Same as shipping details configuration).
     * @return array
     */
    protected function billingDetailsConfig()
    {
        return $this->shippingDetailsConfig();
    }
    
    /* ------------------------------------------------------------------------ 
     * Parameter data methods
     * ------------------------------------------------------------------------    
     */
    
    /**
     * 
     * @param array $data
     * @return type
     */
    public function sendData($data)
    {
        return $this->response = new PreloadResponse($this, $data);
    }
    
    /*
    public function xgetData()
    {
        // Required data
        $data = $this->getRequiredData();
        
        
        // Billing data
        $this->addBillingData($data);
        
        // Shipping data
        $this->addShippingData($data);
        
        // Optional data
        $this->addOptionalData($data);
        
        // Rvar data
        $this->addRvarData($data);

        return $data;
    }
    */
    /**
     * Gather basic data required by Moneris
     * @return array
     
    protected function getRequiredData()
    {
        $data = [];
        foreach($this->requiredParameters() as $key) {
            $data[$key] = $this->getParameterData($key);
        }
        return $data;
    }
    */
    /**
     * Add optional data
     * @param array $data
     
    protected function getOptionalData(&$data)
    {
        // Order ID
        $data['order_no'] = $this->getTransactionId();
        
        // Email
        $card = $this->getCard();
        if($card && $card->getEmail()) {
            $data['email'] = $card->getEmail();
        }
        // Customer ID
        if($this->getCustId() !== null) {
            $data['cust_id'] = $this->getCustId();
        }
        // Note
        if($this->getNote() !== null) {
            $data['note'] = $this->getNote();
        }
    }
    */
    /**
     * Add billing details
     * @param array $data
     
    protected function addBillingData(&$data)
    {
        $card = $this->getCard();
        if(!is_object($card)) {
            return;
        }
        
        if(null !== ( $card->getBillingCompany() ) ) {
            $data['bill_company_name'] = $card->getBillingCompany();
        }
                
        $data['bill_first_name'] = $card->getFirstname();
        $data['bill_last_name'] = $card->getLastname();
        $data['bill_address_one'] = $card->getAddress1();
        $data['bill_city'] = $card->getCity();
        $data['bill_postal_code'] = $card->getPostcode();
        $data['bill_state_or_province'] = $card->getState();
        $data['bill_country'] = $card->getCountry();
        $data['bill_phone'] = $card->getPhone();

    }
    */
    /**
     * Add shipping details
     * @param array $data
     
    protected function addShippingData(&$data)
    {
        $card = $this->getCard();
        if(!is_object($card)) {
            return;
        }

        if(null !== ( $card->getShippingCompany() ) ) {
            $data['ship_company_name'] = $card->getShippingCompany();
        }

        $data['ship_first_name'] = $card->getShippingFirstname();
        $data['ship_last_name'] = $card->getShippingLastname();
        $data['ship_address_one'] = $card->getShippingAddress1();
        $data['ship_city'] = $card->getShippingCity();
        $data['ship_postal_code'] = $card->getShippingPostcode();
        $data['ship_state_or_province'] = $card->getShippingState();
        $data['ship_country'] = $card->getShippingCountry();
        $data['ship_phone'] = $card->getShippingPhone();

    }
    */
    /**
     * Add custom response variables
     * @param array $data
     
    protected function addRvarData(&$data)
    {
        $rvar = $this->getRvar();

        if(!is_array($rvar)) {
            return;
        }
        
        foreach($rvar as $key => $value) {
            $data['rvar'.$key] = $value;
        }
    
    }
    */
        
    
}