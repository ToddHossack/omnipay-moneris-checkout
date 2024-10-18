<?php
namespace Omnipay\Moneris\Message;

use \Omnipay\Moneris\Message\AbstractRequest;
use Omnipay\Moneris\Helper;

class AbstractResponse extends \Omnipay\Common\Message\AbstractResponse
{
    
    public function isTest()
    {
        return $this->getRequest()->isTest();
    }
    
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
    
}
