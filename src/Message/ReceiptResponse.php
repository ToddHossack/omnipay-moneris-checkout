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
        $result = Helper::data_get($this->data,'receipt.result');
        return ($result === 'a');
    }
    
    public function getError()
    {
        $success = Helper::data_get($this->data,'response.success');
        $unsuccessful = (in_array($success,[0,'0','false',false],true));
        return (empty($this->data) ? 'Response has no data' : 
            (($unsuccessful) ? 'Error or could not process' : null));
    }
    
    public function getTransactionReference()
    {
        return Helper::data_get($this->data,'response.receipt.cc.reference_no');
    }

    public function getCode()
    {
        return Helper::data_get($this->data,'response.receipt.cc.response_code');
    }
}
