<?php
namespace Omnipay\Moneris;

use Omnipay\Common\AbstractGateway;
use Omnipay\Common\Exception\RuntimeException;
use Symfony\Component\HttpFoundation\ParameterBag;

class Config 
{
    // Server-to-server request end points
    protected static $liveEndpoint = 'https://gateway.moneris.com/chktv2/request/request.php';
    protected static $testEndpoint = 'https://gatewayt.moneris.com/chktv2/request/request.php';
    
    // JavaScript Libs
    protected static $liveJsSrc = 'https://gateway.moneris.com/chktv2/js/chkt_v2.00.js';
    protected static $testJsSrc = 'https://gatewayt.moneris.com/chktv2/js/chkt_v2.00.js';
    
    protected static $callbackResponseCodes = [
        '001'   => [
            'message' => 'Success',
        ],
        '902'   => [
            'error' => true,
            'message' => '3-D Secure failed on response',
        ],
        '2001'  => [
            'error' => true,
            'message' => 'Invalid ticket',
        ],
        '2002'  => [
            'error' => true,
            'message' => 'Ticket re-use',
        ],
        '2003'  => [
            'error' => true,
            'message' => 'Ticket expired',
        ]     
    ];
    
    protected static $fraudToolResultValues = [
        // CVD / AVS / 3-D Secure
        '1'   => [
            'message' => 'Success',
        ],
        '2'   => [
            'error' => true,
            'message' => 'Failed',
        ],
        '3'   => [
            'error' => true,
            'message' => 'Not performed',
        ],
        '4'   => [
            'error' => true,
            'message' => 'Card not eligible',
        ],
        // Kount
        '001'   => [
            'message' => 'Success',
        ],
        '973'   => [
            'error' => true,
            'message' => 'Unable to locate merchant Kount details',
        ],
        '984'  => [
            'error' => true,
            'message' => 'Data error',
        ],
        '987'  => [
            'error' => true,
            'message' => 'Invalid transaction',
        ]   
    ];
    
    /**
     * Set test end point - allow for local development / mock gateways
     * @param string $url
     */
    public function setTestEndpoint($url)
    {
        static::$testEndpoint = $url;
    }
    
    /**
     * Get test end point 
     * @return string $url
     */
    public static function getTestEndpoint()
    {
        return static::$testEndpoint;
    }
    
    
    /**
     * Get live end point 
     * @return string $url
     */
    public static function getLiveEndpoint()
    {
        return static::$liveEndpoint;
    }
    
    /**
     * Get live JS source
     * @return string
     */
    public static function getLiveJsSrc()
    {
        return static::$liveJsSrc;
    }
    
    /**
     * Get test JS source
     * @return string
     */
    public static function getTestJsSrc()
    {
        return static::$testJsSrc;
    }
    
    public static function getJsSrcByEnvironment($env)
    {
        return ($env === 'prod') ? static::$liveJsSrc : static::$testJsSrc;
    }
    
    
    public static function getCallbackResponseCodes()
    {
        return static::$callbackResponseCodes;
    }
    
    public static function getFraudToolResultValues()
    {
        return static::$fraudToolResultValues;
    }
    
}
