<?php
/**
 * Professio_BudgetMailer extension
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the MIT License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/mit-license.php
 * 
 * @category       Professio
 * @package        Professio_BudgetMailer
 * @copyright      Copyright (c) 2015
 * @license        http://opensource.org/licenses/mit-license.php MIT License
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
    
    const CONFIG_PATH_SYNC_CRON = 'budgetmailer/sync/cron';
    const CONFIG_PATH_SYNC_WEBHOOK = 'budgetmailer/sync/webhook';
    const CONFIG_PATH_SYNC_TTL = 'budgetmailer/sync/ttl';
    
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
    public function getAdvancedCreateAccount() {
        return Mage::getStoreConfig(self::CONFIG_PATH_ADVANCED_ON_CREATE_ACCOUNT);
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
     * @return string
     * @throws Professio_BudgetMailer_Exception
     */
    public function getGeneralList()
    {
        $generalList = Mage::getStoreConfig(self::CONFIG_PATH_GENERAL_LIST);
        
        if (empty($generalList)) {
            throw new Professio_BudgetMailer_Exception(
                $this->__('BudgetMailer list is not set.')
            );
        }
        
        return $generalList;
    }
    
    /**
     * Get sync cron
     * 
     * @return boolean
     */
    public function isSyncCronEnabled()
    {
        return Mage::getStoreConfig(self::CONFIG_PATH_SYNC_CRON);
    }
    
    /**
     * Get sync webhook
     * 
     * @return boolean
     */
    public function isSyncWebhookEnabled()
    {
        return Mage::getStoreConfig(self::CONFIG_PATH_SYNC_WEBHOOK);
    }
    
    /**
     * Get sync ttl
     * 
     * @return integer
     */
    public function getSyncTtl()
    {
        return Mage::getStoreConfig(self::CONFIG_PATH_SYNC_TTL);
    }
}
