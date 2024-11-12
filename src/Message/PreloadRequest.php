<?php
namespace Omnipay\Moneris\Message;

use Guzzle\Http\Client;
use Guzzle\Http\Message\Request;

/**
 * Moneris Preload Request (for purchases)
 */
class PreloadRequest extends AbstractRequest
{

    protected $responseClass = \Omnipay\Moneris\Message\PreloadResponse::class;
    
    /* ------------------------------------------------------------------------ 
     * Required Parameter Config
     * ------------------------------------------------------------------------    
     */
    
    public static function requiredParameterConfig() 
    {
        return [
            'txn_total' => [
                'type' => 'float',
                'decimals' => 2,
                'limit' => 10,
                'required' => true
            ],
            'action' => [
                'type' => 'string',
                'default' => 'preload',
                'required' => true
            ]
        ];
    }
    
    /* ------------------------------------------------------------------------ 
     * Optional Parameter Config
     * ------------------------------------------------------------------------    
     */
    
    public static function optionalParameterConfig() 
    {
        return [
            'order_no' => [
                'type' => 'string',
                'limit' => 50,
                'replace' => [' ','<','>','$','%','=','\\','?','^','{','}','[',']','"']
            ],
            'cust_id' => [
                'type' => 'string',
                'limit' => 50,
                'replace' => [' ','<','>','$','%','=','\\','?','^','{','}','[',']','"']
            ],
            'dynamic_descriptor' => [
                'type' => 'string',
                'limit' => 20,
                'replace' => [' ','<','>','$','%','=','\\','?','^','{','}','[',']','"']
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
    public static function optionalParameterObjectConfig() 
    {
        return [
            'recur' => static::recurConfig(),
            'cart' => static::cartConfig(),
            'contact_details' => static::contactDetailsConfig(),
            'shipping_details' => static::shippingDetailsConfig(),
            'billing_details' => static::billingDetailsConfig()
        ];
    }
    
    /**
     * Recur object configuration.
     * Available variables contained under the "variables" key
     * @return array
     */
    public static function recurConfig()
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
    public static function cartConfig()
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
                            'replace' => ['<','>','$','%','=','\\','?','^','{','}','[',']','"']
                        ],
                        'product_code' => [
                            'type' => 'string',
                            'limit' => 50,
                            'replace' => ['<','>','$','%','=','\\','?','^','{','}','[',']','"']
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
                'tax' => static::taxConfig()
            ]
            
        ];
        
    }

    /**
     * Tax object configuration.
     * Available variables contained under the "variables" key
     * @return array
     */
    public static function taxConfig()
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
                    'replace' => ['<','>','$','%','=','\\','?','^','{','}','[',']','"']
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
    public static function contactDetailsConfig()
    {
        return [
            'type' => 'object', // Associative array
            'variables' => [
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
            ]
        ];
    }
    
    /**
     * Shipping details configuration.
     * @return array
     */
    public static function shippingDetailsConfig()
    {
        return [
            'type' => 'object', // Associative array
            'variables' => [
                'address_1' => [
                    'type' => 'string',
                    'limit' => 50,
                    'replace' => ['<','>','$','%','=','\\','?','^','{','}','[',']','"']
                ],
                'address_2' => [
                    'type' => 'string',
                    'limit' => 50,
                    'replace' => ['<','>','$','%','=','\\','?','^','{','}','[',']','"']
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
                    'replace' => ['<','>','$','%','=','\\','?','^','{','}','[',']','"']
                ]
            ]
        ];
    }
    
    /**
     * Billing details configuration.
     * (Same as shipping details configuration).
     * @return array
     */
    public static function billingDetailsConfig()
    {
        return static::shippingDetailsConfig();
    }
    
}