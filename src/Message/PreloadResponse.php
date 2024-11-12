<?php
namespace Omnipay\Moneris\Message;

use Omnipay\Moneris\Helper;
use Omnipay\Moneris\Message\AbstractResponse;

/**
 * Moneris Purchase Response
 */
class PreloadResponse extends AbstractResponse
{
    
    
    public function isSuccessful()
    {
        $success = Helper::data_get($this->data,'response.success');
        $error = $this->getError();
        return (in_array($success,[1,'1','true',true],true) && empty($error));
    }
    
    public function getError()
    {
        return Helper::data_get($this->data,'response.error');
    }
    
    public function isPending()
    {
        return true;
    }
    
    /**
     * Response code
     *
     * @return null|string A response code from the payment gateway
     */
    public function getCode()
    {
        return Helper::data_get($this->data,'response.response_code');
    }

    
}
