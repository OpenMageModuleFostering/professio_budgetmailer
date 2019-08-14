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
 * Client model
 * 
 * @category   Professio
 * @package    Professio_BudgetMailer
 */
class Professio_BudgetMailer_Model_Client
extends Mage_Core_Model_Abstract
{
    /**
     * @var \BudgetMailer\Api\Client
     */
    protected $_client;
    /**
     * Hash of clients by store id
     * @var array
     */
    protected $_storeClients = array();
    
    /**
     * Constructor: load classes and initiate the client
     */
    public function __construct()
    {
        $this->loadClasses();
        $this->init();
    }
    
    /**
     * Load classes of the API client
     */
    protected function loadClasses()
    {
        $lib = Mage::getBaseDir('lib');
        
        // INFO: when using client, load all classes
        require_once $lib . '/BudgetMailer/Api/Client/Http.php';
        require_once $lib . '/BudgetMailer/Api/Client/RestJson.php';
        require_once $lib . '/BudgetMailer/Api/Cache.php';
        require_once $lib . '/BudgetMailer/Api/Client.php';
        require_once $lib . '/BudgetMailer/Api/Config.php';
    }
    
    /**
     * Initiate the client
     */
    protected function init()
    {
        $config = new \BudgetMailer\Api\Config(
            Mage::helper('budgetmailer/config')->getApiConfig()
        );
        $cache = new \BudgetMailer\Api\Cache($config);
        
        $this->_client = new \BudgetMailer\Api\Client($cache, $config);
    }
    
    /**
     * Get API client
     * @return \BudgetMailer\Api\Client
     */
    public function getClient()
    {
        return $this->_client;
    }
    
    /**
     * Get client initialized by configuration for store
     * @param integer $storeId
     * @return \BudgetMailer\Api\Client
     */
    public function getStoreClient($storeId)
    {
        if (!isset($this->_storeClients[$storeId])) {
            // INFO start emulation to get right list (from right store)
            $appEmulation = Mage::getSingleton('core/app_emulation');
            $initialEnvironmentInfo = $appEmulation
                ->startEnvironmentEmulation($storeId);

            $config = new \BudgetMailer\Api\Config(
                Mage::helper('budgetmailer/config')->getApiConfig()
            );
            $cache = new \BudgetMailer\Api\Cache($config);
            $this->_storeClients[$storeId] = new \BudgetMailer\Api\Client(
                $cache, $config
            );

            $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
        }
        
        return $this->_storeClients[$storeId];
    }
}
