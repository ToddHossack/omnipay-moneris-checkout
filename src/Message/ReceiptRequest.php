<?php
namespace Omnipay\Moneris\Message;

/**
 * Moneris Receipt Request
 */
class ReceiptRequest extends AbstractRequest
{
    
    /* ------------------------------------------------------------------------ 
     * Parameter config methods
     * ------------------------------------------------------------------------    
     */
    
    public function requiredParameterConfig() 
    {
        $config = parent::requiredParameterConfig();
        
        $config['ticket'] = [
            'type' => 'string',
            'limit' => 50
        ];
        
        $config['action'] = [
            'type' => 'string',
            'value' => 'receipt'
        ];
    }
    
    /*
     * 
     
    public function getData()
    {
        $requestMethod = $this->httpRequest->server->get('REQUEST_METHOD');
        if($requestMethod === 'POST') {
            return $this->httpRequest->request->all();
        } else {
            return $this->httpRequest->query->all();
        }
    }

    public function sendData($data)
    {
        $this->response = new CompletePurchaseResponse($this, $data);
        return $this->response;
    }
     * 
     */
}
