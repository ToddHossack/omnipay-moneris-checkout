<?php
namespace Omnipay\Moneris\Message;

use Omnipay\Moneris\Helper;
use Omnipay\Moneris\Message\AbstractResponse;
use Omnipay\Moneris\Config;

/**
 * Moneris Complete Purchase Response
 * 
 * Response parameters and values found at:
 * https://developer.moneris.com/sitecore/media%20library/Hidden/MCO/Receipt%20Request?sc_lang=en
 */
class ReceiptResponse extends AbstractResponse
{

    /**
     * Whether the response process had an error.
     * ie. error in receiving/processing the data
     * @return bool|null
     */
    public function getError()
    {
        $success = Helper::data_get($this->data,'response.success');
        $unsuccessful = (in_array($success,[0,'0','false',false],true));
        return (empty($this->data) ? 'Response has no data' : 
            (($unsuccessful) ? 'Error or could not process' : null));
    }
    
    /**
     * Gets credit card reference number.
     * @return string
     */
    public function getTransactionReference()
    {
        return Helper::data_get($this->data,'response.receipt.cc.reference_no');
    }

    /**
     * Gets credit card response code
     * @return string
     */
    public function getCode()
    {
        return Helper::data_get($this->data,'response.receipt.cc.response_code');
    }
    
    /**
     * Gets receipt result
     *  "a" - approved
     *  "d" - declined
     * @return string
     */
    public function getReceiptResult()
    {
        return Helper::data_get($this->data,'response.receipt.result');
    }
    
    /**
     * Response Message
     *
     * @return null|string A response message from the payment gateway
     */
    public function getMessage()
    {
        $messages = $this->messagesForResponseCode($this->getCode());
        
        // No message - try fraud prevention messages
        if(empty($messages) && !$this->isApproved()) {
            
            $fraudResponses = $this->findFailedOrErrorFraudToolResponses();
            
            foreach($fraudResponses as $fraudTool => $fraudResponse) {
                // Find message
                $msg = $this->getMessageFromFraudToolData($fraudResponse);
                
                if(!empty($msg)) {
                    if(stripos($msg,'declined') === false) {
                        $msg = 'DECLINED - '. $msg .' ('. strtoupper($fraudTool) .')';
                    }
                    
                    $messages = [$msg];
                    break;
                }
            }
        }
        
        return (is_array($messages)) ? implode(' / ', $messages) : '';
    }
    
    public function findFailedOrErrorFraudToolResponses()
    {
        $strategies = ['cvd','avs','3d_secure','kount'];
        $filtered = [];
        
        foreach($strategies as $strategy) {
            $strategyResponse = Helper::data_get($this->data,['response','receipt','cc','fraud',$strategy]);
            // Skip not found
            if(empty($strategyResponse)) {
                continue;
            }
            // Skip successful or disabled detected by status
            $status = Helper::data_get($strategyResponse,'status');
            if(in_array($status,['success','disabled'],true)) {
                continue;
            }
            // Skip successful or disabled detected by result
            $result = Helper::data_get($strategyResponse,'result');
            if(in_array(strval($result),['1','001','3'],true)) {
                continue;
            }
            
            $filtered[$strategy] = $strategyResponse;
        }
        
        return $filtered;
    }
    
    protected function getMessageFromFraudToolData($data)
    {
        // Message in details (Kount / 3-D Secure)
        $msg = Helper::data_get((array) $data,'details.message');
        if(!empty(trim($msg))) {
            return ucfirst($msg);
        }
        
        // Message by result value
        $result = Helper::data_get($data,'result');
        $resultMessage = Helper::data_get(Config::getFraudToolResultValues(),[strval($result),'message']);
        
        if($resultMessage) {
            return $resultMessage;
        }
        
        // Message from status (fallback)
        $status = strtolower(Helper::data_get((array) $data,'status',''));
        if($status && $status !== 'success' && $status !== 'disabled') {
            $msg = ucfirst(str_replace('_',' ',$status)); // Format as user friendly text
        }
        return $msg;
    }
    
    /**
     * Determines whether the credit card transaction was approved, based on response code.
     * @return bool
     */
    public function isApproved()
    {
        $code = $this->getCode();
        return (!in_array($code,[null,'NULL','null'],true) && intval($code) < 50);
    }
    
    /**
     * Uses both the receipt result and the credit card response code to determine
     * whether transaction was successful
     * @return bool
     */
    public function isSuccessful()
    {
        return ($this->getReceiptResult() === 'a' && $this->isApproved());
    }
    
    /**
     * Finds English and French message which correlate with given code.
     * @param string|int $code
     * @todo handle more codes
     * @return array|null
     */
    public function messagesForResponseCode($code)
    {
        /*
         * No code
         */
        if(is_null($code)) {
            return null;
        }
        $code = (int) $code;
        
        /*
         * Approved
         */
        // Approval
        if($code >= 23 && $code <= 26) {
            return ['APPROVED APPROVAL','APPROUVÉE'];
        }
        // Authorized
        elseif($code >= 27 && $code <= 29) {
            return ['APPROVED AUTHORIZED','APPROUVÉE'];
        }
        // General
        elseif($code < 50) {
            return ['APPROVED','APPROUVÉE'];
        }
        /*
         * Declined
         */
        elseif($code > 50) {
            return ['DECLINED','REFUSÉE'];
        }
        
        return null;
    }
    
}
