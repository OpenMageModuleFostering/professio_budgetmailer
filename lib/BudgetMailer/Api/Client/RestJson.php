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
 * @package BudgetMailer\Api\Client
 */
namespace BudgetMailer\Api\Client;

use BudgetMailer\Api\Config;

/**
 * Simplified REST-JSON HTTP Client
 *
 * @method  mixed   delete(string $url, array $headers = array, mixed $body = null, integer $expectedCode = Http::OK)
 * @method  mixed   get(string $url, array $headers = array, mixed $body = null, integer $expectedCode = Http::OK)
 * @method  mixed   post(string $url, array $headers = array, mixed $body = null, integer $expectedCode = Http::OK)
 * @method  mixed   put(string $url, array $headers = array, mixed $body = null, integer $expectedCode = Http::OK)
 * @package BudgetMailer\Api\Client
 */
class RestJson
{
    /**
     * @var \BudgetMailer\Api\Client\Http HTTP Client
     */
    protected $http;
    
    /**
     * Create new instance of REST-JSON Client.
     *
     * @param Config                        $config Configuration
     * @param \BudgetMailer\Api\Client\Http $http   HTTP Client or null
     */
    public function __construct(Config $config, Http $http = null)
    {
        if (is_null($http)) {
            $http = new Http($config);
        }
        
        $this->http = $http;
    }
    
    /**
     * Magic Method Implementation.
     *
     * Implements possible HTTP Methods: delete, get, post, and put as
     * virtual methods.
     *
     * @param  string $method Called Method Name
     * @param  array  $args   Called Method Arguments
     * @return mixed Decoded JSON String
     * @throws \RuntimeException In Case the Expected Code is not null and HTTP Response Status Code doesn't match
     * @throws \BadMethodCallException In Case the Method is not HTTP Method
     */
    public function __call($method, $args)
    {
        if ($this->http->isAllowedMethod($method)) {
            $args[0] = isset($args[0]) ? $args[0] : ''; // URL
            $args[1] = isset($args[1]) ? $args[1] : array(); // headers
            $args[2] = isset($args[2]) ? $args[2] : null; // body
            $expectedCode = isset($args[3]) ? $args[3] : null; // expected response code
            
            if ($args[2]) {
                $args[2] = $this->encode($args[2]);
            }
            
            $this->http->request($args[0], $method, $args[1], $args[2]);
            
            if (!is_null($expectedCode) && $expectedCode != $this->http->getResponseCode()) {
                throw new \RuntimeException(
                    sprintf(
                        'REST-JSON Call failed. Expected Response Code %d, got %d - %s. URL: %s',
                        $expectedCode,
                        $this->http->getResponseCode(),
                        $this->http->getResponseMessage(),
                        $args[0]
                    ),
                    $this->http->getResponseCode()
                );
            }
            
            return $this->decode($this->http->getResponseBody());
        }

        throw new \BadMethodCallException('Call to undefined method ' . __CLASS__ . '::' . $method . '().');
    }
    
    /**
     * Decode JSON String
     *
     * @param  string $string JSON encoded String
     * @return mixed Decoded JSON String
     * @throws \RuntimeException In Case the decoding fails
     */
    public function decode($string)
    {
        $result = json_decode($string);
        
        if (false === $result) {
            throw new \RuntimeException('Can\'t decode JSON string.');
        }
        
        return $result;
    }
    
    /**
     * Encode JSON String
     *
     * @param  mixed $data Data to encode as JSON String
     * @return string JSON encoded Data
     * @throws \RuntimeException In case the encoding fails
     */
    public function encode($data)
    {
        $result = json_encode($data);
        
        if (false === $result) {
            throw new \RuntimeException('Can\'t encode data to JSON.');
        }
        
        return $result;
    }
    
    /**
     * Get HTTP client instance.
     *
     * @return Http
     */
    public function getHttp()
    {
        return $this->http;
    }
}
