<?php
namespace Omnipay\Moneris\Message;

/**
 * Moneris Receipt Request
 */
class ReceiptRequest extends AbstractRequest
{
    
    protected $responseClass = \Omnipay\Moneris\Message\ReceiptResponse::class;
    
    /* ------------------------------------------------------------------------ 
     * Parameter config methods
     * ------------------------------------------------------------------------    
     */
    
    public static function requiredParameterConfig() 
    {
        return [
            'ticket' => [
                'type' => 'string',
                'limit' => 50,
                'required' => true
            ],
            'action' => [
                'type' => 'string',
                'default' => 'receipt',
                'required' => true
            ]
        ];
    }
    
}
