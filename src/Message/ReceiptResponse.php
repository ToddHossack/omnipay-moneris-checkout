<?php
namespace Omnipay\Moneris\Message;

use Omnipay\Moneris\Helper;
use Omnipay\Moneris\Message\AbstractResponse;

/**
 * Moneris Complete Purchase Response
 */
class ReceiptResponse extends AbstractResponse
{

    public function isSuccessful()
    {
        $success = Helper::data_get($this->data,'response.success');
        return (in_array($success,[1,'1','true',true],true));
    }
    
    public function getError()
    {
        return empty($this->data) ? 'Response has no data' : null;
    }
    
    public function getTransactionReference()
    {
        return Helper::data_get($this->data,'receipt.cc.reference_no');
    }

    public function getCode()
    {
        return Helper::data_get($this->data,'receipt.cc.response_code');
    }
}
