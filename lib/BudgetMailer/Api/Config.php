<?php

/**
 * BudgetMailer PHP API (https://www.budgetmailer.nl/index.php)
 *
 * @author    BudgetMailer <info@budgetmailer.nl>
 * @copyright (c) 2015 - 2017 - BudgetMailer
 * @license   https://gitlab.com/budgetmailer/budgetmailer-php-api/blob/master/LICENSE.txt
 * @package   BudgetMailer\API\Client
 * @version   1.0.2
 */

/**
 * Namespace
 *
 * @package BudgetMailer\Api
 */
namespace BudgetMailer\Api;

/**
 * BudgetMailer API Client Config Wrapper
 *
 * This Class provides simple Interface for BudgetMailer API Client Configuration.
 * You can use either access Configuration Values as Object Properties ($o->cache)
 * thanks to Magic Functions __get() and __set(). Or with getters, listed below.
 *
 * @method  boolean getCache()
 * @method  string getCacheDir()
 * @method  string getEndPoint()
 * @method  string getKey()
 * @method  string getList()
 * @method  string getSecret()
 * @method  integer getTtl()
 * @method  integer getTimeOutSocket()
 * @method  integer getTimeOutStream()
 * @package BudgetMailer\Api
 */
class Config
{
    /**
     * @var array Associative Array of the Configuration Values
     */
    protected $config;

    /**
     * Create new instance of Config
     *
     * @param array $config Configuration as an Associative Array
     */
    public function __construct(array $config)
    {
        $this->setConfig($config);
    }
    
    /**
     * Set Configuration
     *
     * @param array $config Configuration as an Associative Array
     */
    public function setConfig(array $config)
    {
        $this->config = $config;
    }
    
    /**
     * Magic Method Implementation
     *
     * Converts Part of called Method after "get" to Configuration Key,
     * e.g. "getCache" to "cache", and returns the Value or null.
     *
     * @param  string $method Method Name
     * @param  array  $args   Method Arguments
     * @return mixed Configuration Value or null
     * @see    \BudgetMailer\Api\Config::__get()
     * @throws \BadMethodCallException
     */
    public function __call($method, $args)
    {
        $get = 'get';
        
        if (preg_match('/^' . $get . '/i', $method)) {
            $property = lcfirst(str_replace($get, '', $method));
            
            return $this->$property;
        }
        
        throw new \BadMethodCallException('Call to undefined method ' . __CLASS__ . '::' . $method . '().');
    }
    
    /**
     * Magic Method Implementation - get Object Property.
     *
     * @param  string $key requested Property Name
     * @return mixed Property Value or null
     */
    public function __get($key)
    {
        return isset($this->config[$key]) ? $this->config[$key] : null;
    }
    
    /**
     * Magic Method Implementation - set Object Property.
     *
     * @param string $key Property Name
     * @param mixed  $val Property Value
     */
    public function __set($key, $val)
    {
        $this->config[$key] = $val;
    }
}
