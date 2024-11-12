<?php
namespace Omnipay\Moneris;

use Omnipay\Common\AbstractGateway;
use Omnipay\Common\Exception\RuntimeException;
use Symfony\Component\HttpFoundation\ParameterBag;

class Helper extends \Omnipay\Common\Helper
{
   
    protected static $defaultPrecision = 2;
    
    
    public static function getDefaultPrecision()
    {
        return static::$defaultPrecision;
    }
    
    public static function setDefaultPrecision($digits)
    {
        static::$defaultPrecision = (int) $digits;
    }
    
    /* ------------------------------------------------------------------------ 
     * Parameter data handling
     * ------------------------------------------------------------------------    
     */
    
    public static function prepareParameterData($key,$val,$cfg) 
    {
        // Configured data types
        $type = isset($cfg['type']) ? $cfg['type'] : 'string';
        $sendType = isset($cfg['send_type']) ? $cfg['send_type'] : 'string';
        
        // Config for sub variables
        $variablesCfg = isset($cfg['variables']) ? $cfg['variables'] : [];
        
        // Numeric array (JS array)
        if($type === 'array') {
            if(is_array($val)) {
                // Iterate items, which are associative arrays
                foreach($val as &$v) {
                    if(is_array($v)) {
                        // Iterate variables and format values
                        static::prepareParameterSubElements($v,$variablesCfg);
                    }
                }
            }
        }

        // Associative array (JS object)
        elseif($type === 'object') {
            if(is_array($val)) {
                // Iterate variables and format values
                static::prepareParameterSubElements($val,$variablesCfg);
            }
        }

        // Scalar value
        else {
            $val = static::formatAndValidateParameterValue($key,$val,$cfg);
        }
        
        return $val;
    }
    
    /**
     * Iterates sub-elements and formats values, based on config
     * @param array $elements
     * @param array $cfg
     */
    protected static function prepareParameterSubElements(&$elements,$cfg=[])
    {
        foreach((array) $elements as $key => &$v) {
            $varCfg = isset($cfg[$key]) ? $cfg[$key] : [];
            $v = static::prepareParameterData($key,$v,$varCfg);
            // Unset empty parameters
            if(static::isEmpty($v)) {
                unset($elements[$key]);
            }
        }
    }
    
    /* ------------------------------------------------------------------------ 
     * Formatting and validation
     * ------------------------------------------------------------------------    
     */
    /**
     * 
     * @param string $key
     * @param string|int|float $val
     * @param array $cfg
     * @return string|int|float
     * @throws RuntimeException
     */
    public static function formatAndValidateParameterValue($key,$val,$cfg)
    {
        $type = isset($cfg['type']) ? $cfg['type'] : 'string';
        $sendType = isset($cfg['send_type']) ? $cfg['send_type'] : 'string';
        
        // Trim
        if(is_string($val)) {
            $val = trim($val);
        }
        
        // Check required
        static::validateRequired($key,$val,$cfg);
        
        // Initial cast
        $val = static::castTo($val,$type);

        // Check range
        if(in_array($type,['int','float'])) {
            // Min
            static::validateMin($key,$val,$cfg);
            // Max
            static::validateMax($key,$val,$cfg);
        }

        // Not in allowed options
        if(isset($cfg['options'])) {
            static::validateInOptions($key,$val,$cfg);
        }
        
        // Final cast
        if($sendType !== gettype($val)) {
            $val = static::castTo($val,$sendType,$cfg);
        }

        // Format / check string
        if($sendType === 'string') {
            
            // String length
            static::validateLimit($key,$val,$cfg);
            
            // Invalid characters
            $val = static::validateCharacters($key,$val,$cfg,true);
            
        }
        
        return $val;
    }
    
    /**
     * Checks length of value
     * @param string $key
     * @param mixed $val
     * @param array $cfg
     * @param bool $replace
     * @throws RuntimeException
     */
    public static function validateCharacters($key,$val,$cfg=[],$replace=false)
    {
        // Replace disallowed characters
        if(!empty($cfg['replace'])) {
            // Don't replace, just warn
            if(!$replace) {
                $found = [];
                foreach((array) $cfg['replace'] as $char) {
                    if(strpos($val,$char) !== false && !in_array($char,$found)) {
                        $found[] = $char;
                    }
                }
                
                if(count($found)) {
                    throw new RuntimeException(sprintf('%s has invalid character(s): %s.',$key,implode(' ',$found)));
                }
            }
            // Just replace, without a warning
            else {
                $val = str_replace($cfg['replace'],'',$val,$count);
            }
        }
        
        return $val;
    }
    
    /**
     * Checks length of value
     * @param string $key
     * @param mixed $val
     * @param array $cfg
     * @throws RuntimeException
     */
    public static function validateLimit($key,$val,$cfg=[])
    {
        // String length
        if(isset($cfg['limit']) && strlen($val) > intval($cfg['limit'])) {
            throw new RuntimeException(sprintf('%s has a limit of %d characters.',$key,intval($cfg['limit'])));
        }
    }
    
    /**
     * Checks value is not less than configured minimum
     * @param string $key
     * @param mixed $val
     * @param array $cfg
     * @throws RuntimeException
     */
    public static function validateMin($key,$val,$cfg=[])
    {
        if(!isset($cfg['min']) || !is_numeric($cfg['min'])) {
            return;
        }
            
        if(!is_numeric($val) || bccomp(strval(floatval($val)),strval(floatval($cfg['min'])),2) === -1) {
            throw new RuntimeException(sprintf('%s cannot be less than %s.',$key,$cfg['min']));
        }
    }
    
    /**
     * Checks value is not greater than configured maximum
     * @param string $key
     * @param mixed $val
     * @param array $cfg
     * @throws RuntimeException
     */
    public static function validateMax($key,$val,$cfg=[])
    {
        if(!isset($cfg['max']) || !is_numeric($cfg['max'])) {
            return;
        }
            
        if(!is_numeric($val) || bccomp(strval(floatval($val)),strval(floatval($cfg['max'])),2) === 1) {
            throw new RuntimeException(sprintf('%s cannot be more than %s.',$key,$cfg['max']));
        }
    }
    
    /**
     * Checks value is not greater than configured maximum
     * @param string $key
     * @param mixed $val
     * @param array $cfg
     * @throws RuntimeException
     */
    public static function validateInOptions($key,$val,$cfg=[])
    {
        if(empty($cfg['options']) || !is_array($cfg['options'])) {
            return;
        }
        
        // Not in allowed options
        if(!static::isEmpty($val) && !in_array($val,$cfg['options'],true)) {
            throw new RuntimeException(sprintf('Invalid option selected for %s.',$key));
        }
    }
    
    /**
     * Checks value is not empty if it is required
     * @param string $key
     * @param mixed $val
     * @param array $cfg
     * @throws RuntimeException
     */
    public static function validateRequired($key,$val,$cfg=[])
    {
        if(!empty($cfg['required']) && static::isEmpty($val)) {
            throw new RuntimeException(sprintf('A value is required for %s.',$key));
        }
    }
    
    /**
     * Checks if value has input
     * @param mixed $val
     * @return bool
     */
    public static function isEmpty($val)
    {
        return (
            (is_null($val) || $val === '')
            || (is_array($val) && !count($val))
        );
    }
    
    /**
     * Simple function casting value to given type.
     * @param mixed $val
     * @param string $type
     * @return mixed
     */
    public static function castTo($val,$type,$options=[])
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
                if(is_float($val)) {
                    $decimals = isset($options['decimals']) ? (int) $options['decimals'] : static::defaultPrecision;
                    return number_format($val,$decimals);
                }
                return (string) $val;
                break;
        };
        
    }
    
    /**
     * Get an item from an array or object using "dot" notation.
     *
     * @param  object|array $data
     * @param  string|int $key  (path with dot notation, or array path)
     * @param  mixed $default
     *
     * @return mixed
     */
    public static function data_get($data, $key, $default = null) {
        if (empty($data) || $key === '' || $key === null)
            return self::value($default);
        $object_get = function ($obj, $k) {
            return isset($obj->{$k}) ? $obj->{$k} : null;
        };
        $array_get = function ($arr, $k) {
            return (isset($arr[$k])) ? $arr[$k] : null;
        };

        $result = $data;
        $keys = is_array($key) ? $key : explode('.', $key);
        $numKeys = count($keys);
        $i = 1;
        foreach ($keys as $k) {
            if (is_object($result)) {
                $result = $object_get($result, $k);
            } elseif (is_array($result)) {
                $result = $array_get($result, $k);
            } else {
                $result = null;
                if ($i < $numKeys)
                    break;
            }
            ++$i;
        }
        return ($result !== null) ? $result : self::value($default);
    }
    
    public static function value($value) {
        return ($value instanceof Closure) ? $value() : $value;
    }
    
    public static function truncate($str,$limit)
    {
        return substr(trim(strval($str)),0,intval($limit));
    }
    
    public static function debug($data)
    {
        echo '<pre>';
        print_r($data);
        echo '</pre>';
    }
}
