<?php
namespace Omnipay\Moneris;

use Omnipay\Common\AbstractGateway;
use Omnipay\Common\Exception\RuntimeException;
use Symfony\Component\HttpFoundation\ParameterBag;

class Helper extends Omnipay\Common\Helper
{
   
    /**
     * Formats value of parameter, based on configuration provided.
     * Recursive - to handle parameter objects.
     * All scalar values cast to string after processing, unless
     * configuration key "send_type" is set to another data type.
     * @param string|int|float|array $val
     * @param array $cfg
     * @return string|int|float|array
     */
    public static function formatParameterValue($key,$val,$cfg=[])
    {
        // Configured cast types
        $type = isset($cfg['type']) ? $cfg['type'] : 'string';
        
        // Numeric array (JS array)
        if($type === 'array' && is_array($val)) {
            // Config for sub variables
            $variablesCfg = isset($cfg['variables']) ? $cfg['variables'] : [];
            // Iterate items
            foreach($val as &$v) {
                if(is_array($v)) {
                    // Iterate variables and format values
                    static::formatParameterSubElements($v,$variablesCfg);
                }
            }
        }
        
        // Associative array (JS object)
        elseif($type === 'object' && is_array($val)) {
            // Config for sub variables
            $variablesCfg = isset($cfg['variables']) ? $cfg['variables'] : [];
            // Iterate variables and format values
            static::formatParameterSubElements($val,$variablesCfg);
        }
        
        // Scalar
        else {
            $val = $this->formatScalarValue($key,$val,$cfg);
        }
        
        return $val;
    }
    
    /**
     * 
     * @param string $key
     * @param string|int|float $val
     * @param array $cfg
     * @return string|int|float
     * @throws RuntimeException
     */
    protected function formatScalarValue($key,$val,$cfg=[])
    {
        $type = isset($cfg['type']) ? $cfg['type'] : 'string';
        $sendType = isset($cfg['send_type']) ? $cfg['send_type'] : 'string';
        
        // Trim
        if(is_string($val)) {
            $val = trim($val);
        }
        // Initial cast
        $val = static::castTo($val,$type);

        // Check integer range
        if($type === 'int') {
            // Min
            if(isset($cfg['min']) && $val < intval($cfg['min'])) {
                throw new RuntimeException(sprintf('Parameter %s must be greater than %d.',$key,intval($cfg['min'])));
            }
            // Max
            if(isset($cfg['max']) && $val > intval($cfg['max'])) {
                throw new RuntimeException(sprintf('Parameter %s must be less than %d.',$key,intval($cfg['max'])));
            }
        }

        // Not in allowed options
        if(!empty($cfg['options']) && is_array($cfg['options'])) {
            if(!in_array($val,$cfg['options'],true)) {
                throw new RuntimeException(sprintf('Allowed values for parameter %s are %s.',$key,'['.implode('|',$cfg['options']).']'));
            }
        }

        // Final cast
        if($sendType !== $type) {
            $val = static::castTo($val,$sendType);
        }

        // Format / check string
        if($sendType === 'string') {
            // Truncate length
            if(isset($cfg['limit']) && strlen($val) > intval($cfg['limit'])) {
                $val = substr($val,0,intval($cfg['limit']));
            }
            // Replace disallowed characters
            if(!empty($cfg['replace'])) {
                $val = str_replace($cfg['replace'],'',$val);
            }
        }
        
        return $val;
    }
    
    /**
     * Iterates sub-elements and formats values, based on config
     * @param array $elements
     * @param array $cfg
     */
    protected function formatParameterSubElements(&$elements,$cfg=[])
    {
        foreach((array) $elements as $key => &$v) {
            $varCfg = isset($cfg[$key]) ? $cfg[$key] : [];
            $v = self::formatParameterValue($key,$v,$varCfg);
        }
    }
    
    /**
     * Simple function casting value to given type.
     * @param mixed $val
     * @param string $type
     * @return mixed
     */
    public static function castTo($val,$type)
    {
        switch($type) {
            case 'float':
                return (float) $val;
            break;
            case 'int':
            case 'integer':
                return (int) $val;
                break;
            case 'boolean':
            case 'bool':
                return (bool) $val;
                break;
            case 'array':
                return (array) $val;
                break;
            case 'object':
                return (object) $val;
                break;
            case 'string':
            default:
                return (string) $val;
                break;
        };
        
    }
    
    public static function truncate($str,$limit)
    {
        return substr(trim(strval($str)),0,intval($limit));
    }
    
    
}
