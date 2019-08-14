<?php
/**
 * Professio_BudgetMailer extension
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the MIT License
 * that is bundled with this package in the file LICENSE.
 * It is also available through the world-wide-web at this URL:
 * https://gitlab.com/budgetmailer/budgetmailer-mag1/blob/master/LICENSE
 * 
 * @category       Professio
 * @package        Professio_BudgetMailer
 * @copyright      Copyright (c) 2015 - 2017
 * @license        https://gitlab.com/budgetmailer/budgetmailer-mag1/blob/master/LICENSE
 */

/**
 * Config helper
 * 
 * @category   Professio
 * @package    Professio_BudgetMailer
 */
class Professio_BudgetMailer_Helper_Config extends Mage_Core_Helper_Abstract
{
    const CONFIG_PATH_ADVANCED_ADDRESS_TYPE = 
        'budgetmailer/advanced/address_type';
    const CONFIG_PATH_ADVANCED_ON_ADDRESS_UPDATE = 
        'budgetmailer/advanced/on_address_update';
    const CONFIG_PATH_ADVANCED_ON_CUSTOMER_DELETE = 
        'budgetmailer/advanced/on_customer_delete';
    const CONFIG_PATH_ADVANCED_ON_CUSTOMER_UPDATE = 
        'budgetmailer/advanced/on_customer_update';
    const CONFIG_PATH_ADVANCED_ON_CREATE_ACCOUNT = 
        'budgetmailer/advanced/on_create_account';
    const CONFIG_PATH_ADVANCED_ON_ORDER = 
        'budgetmailer/advanced/on_order';
    const CONFIG_PATH_ADVANCED_FRONTEND = 
        'budgetmailer/advanced/frontend';
    
    const CONFIG_PATH_API_ENDPOINT = 'budgetmailer/api/endpoint';
    const CONFIG_PATH_API_KEY = 'budgetmailer/api/key';
    const CONFIG_PATH_API_SECRET = 'budgetmailer/api/secret';
    
    const CONFIG_PATH_GENERAL_LIST = 'budgetmailer/general/list';
    
    const CONFIG_PATH_CACHE_ENABLED = 'budgetmailer/cache/enabled';
    const CONFIG_PATH_CACHE_TTL = 'budgetmailer/cache/ttl';
    
    /**
     * Get address type
     * 
     * @return string
     */
    public function getAdvancedAddressType()
    {
        return Mage::getStoreConfig(self::CONFIG_PATH_ADVANCED_ADDRESS_TYPE);
    }
    
    /**
     * Get sign-up configuration while creating an account
     */
    public function getAdvancedCreateAccount()
    {
        return Mage::getStoreConfig(
            self::CONFIG_PATH_ADVANCED_ON_CREATE_ACCOUNT
        );
    }
    
    /**
     * Get if selected address type is billing
     * 
     * @return boolean
     */
    public function isAddressTypeBilling()
    {
        return $this->getAdvancedAddressType() == 
            Professio_BudgetMailer_Model_Config_Source_Address::BILLING;
    }
    
    /**
     * Get if selected address type is shipping
     * 
     * @return boolean
     */
    public function isAddressTypeShipping()
    {
        return $this->getAdvancedAddressType() == 
            Professio_BudgetMailer_Model_Config_Source_Address::SHIPPING;
    }
    
    /**
     * Get enabled front-end
     * 
     * @return boolean
     */
    public function isAdvancedFrontendEnabled()
    {
        return Mage::getStoreConfig(self::CONFIG_PATH_ADVANCED_FRONTEND);
    }
    
    /**
     * Get on address update enabled
     * 
     * @return boolean
     */
    public function isAdvancedOnAddressUpdateEnabled()
    {
        $c = 
            Professio_BudgetMailer_Model_Config_Source_Update::ON_UPDATE_UPDATE;
        
        $v = Mage::getStoreConfig(
            self::CONFIG_PATH_ADVANCED_ON_ADDRESS_UPDATE
        );
        
        return $c == $v;
    }
    
    /**
     * Get on customer delete enabled
     * 
     * @return boolean
     */
    public function isAdvancedOnCustomerDeleteEnabled()
    {
        $c = 
            Professio_BudgetMailer_Model_Config_Source_Delete::ON_DELETE_DELETE;
        
        $v = Mage::getStoreConfig(
            self::CONFIG_PATH_ADVANCED_ON_CUSTOMER_DELETE
        );
        
        return $c == $v;
    }
    
    /**
     * Get on customer delete and unsubscribe enabled
     * 
     * @return boolean
     */
    public function isAdvancedOnCustomerDeleteUnsubscribeEnabled()
    {
        $c = Professio_BudgetMailer_Model_Config_Source_Delete
            ::ON_DELETE_DEL_UNSUB;
        
        $v = Mage::getStoreConfig(
            self::CONFIG_PATH_ADVANCED_ON_CUSTOMER_DELETE
        );
        
        return $c == $v;
    }
    
    /**
     * Get on customer update enabled
     * 
     * @return boolean
     */
    public function isAdvancedOnCustomerUpdateEnabled()
    {
        $c = 
            Professio_BudgetMailer_Model_Config_Source_Update::ON_UPDATE_UPDATE;
        
        $v = Mage::getStoreConfig(
            self::CONFIG_PATH_ADVANCED_ON_CUSTOMER_UPDATE
        );
        
        return $c == $v;
    }
    
    /**
     * Get on new order
     * 
     * @return boolean
     */
    public function isAdvancedOnOrderEnabled()
    {
        return Mage::getStoreConfig(
            self::CONFIG_PATH_ADVANCED_ON_ORDER
        );
    }
    
    /**
     * Get config for budgetmailer client.
     * 
     * @return array associative array
     */
    public function getApiConfig()
    {
        return array(
            'cache' => $this->isCacheEnabled(),
            'cacheDir' => Mage::getBaseDir('var') . '/cache/bm/',
            'endPoint' => $this->getApiEndpoint(),
            'key' => $this->getApiKey(),
            // name of the budgetmailer list you want to use as a default
            'list' => $this->getGeneralList(false),
            // your API secret
            'secret' => $this->getApiSecret(),
            // advanced: socket timeout
            'timeOutSocket' => 10,
            // advanced: socket stream read timeout
            'timeOutStream' => 10,
            'ttl' => $this->getCacheTtl(),
        );
    }
    
    /**
     * Get API endpoint
     * 
     * @return string
     */
    public function getApiEndpoint()
    {
        return Mage::getStoreConfig(self::CONFIG_PATH_API_ENDPOINT);
    }
    
    /**
     * Get API key
     * 
     * @return string
     */
    public function getApiKey()
    {
        return Mage::getStoreConfig(self::CONFIG_PATH_API_KEY);
    }
    
    /**
     * Get API secret
     * 
     * @return string
     */
    public function getApiSecret()
    {
        return Mage::getStoreConfig(self::CONFIG_PATH_API_SECRET);
    }
    
    /**
     * Get API list
     * 
     * @param boolean $throwException throw exception if list not set
     * @return null|string null (if false = $throwException) or list string
     * @throws Professio_BudgetMailer_Exception
     */
    public function getGeneralList($throwException = true)
    {
        $generalList = Mage::getStoreConfig(self::CONFIG_PATH_GENERAL_LIST);
        
        if (empty($generalList)) {
            if ($throwException) {
                throw new Professio_BudgetMailer_Exception(
                    $this->__('BudgetMailer list is not set.')
                );
            } else {
                $generalList = null;
            }
        }
        
        return $generalList;
    }
    
    /**
     * Get cache enabled
     * 
     * @return integer
     */
    public function isCacheEnabled()
    {
        return Mage::getStoreConfig(self::CONFIG_PATH_CACHE_ENABLED);
    }
    
    /**
     * Get cache ttl
     * 
     * @return integer
     */
    public function getCacheTtl()
    {
        return Mage::getStoreConfig(self::CONFIG_PATH_CACHE_TTL);
    }
}
