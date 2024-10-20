<?php
namespace Omnipay\Moneris\Message;

use \Omnipay\Moneris\Message\AbstractRequest;
use Omnipay\Moneris\Helper;

abstract class AbstractResponse extends \Omnipay\Common\Message\AbstractResponse
{
    abstract public function isSuccessful();
    
    abstract public function getError();
    
    public function isTest()
    {
        return $this->getRequest()->isTest();
    }
    
}
