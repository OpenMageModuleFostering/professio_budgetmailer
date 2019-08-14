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
 * List collection resource
 * 
 * @category   Professio
 * @package    Professio_BudgetMailer
 */
class Professio_BudgetMailer_Model_Resource_List_Collection
extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    /**
     * Something?
     * @var array
     */
    protected $_joinedFields = array();
    /**
     * Load from API flag
     * @var boolean
     */
    protected $_loadingFromApi;

    /**
     * Constructor
     * 
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        
        $this->_init('budgetmailer/list');
    }

    /**
     * Get SQL for get record count. Extra GROUP BY strip added.
     * 
     * @return Varien_Db_Select
     */
    public function getSelectCountSql()
    {
        $countSelect = parent::getSelectCountSql();
        $countSelect->reset(Zend_Db_Select::GROUP);
        
        return $countSelect;
    }
    
    /**
     * Get BudgetMailer client
     * @return Professio_BudgetMailer_Model_Client
     */
    protected function getClient()
    {
        return Mage::getSingleton('budgetmailer/client');
    }
    
    /**
     * Get mapper
     * @return Professio_BudgetMailer_Helper_Mapper
     */
    protected function getMapper()
    {
        return Mage::helper('budgetmailer/mapper');
    }
    
    /**
     * Load collection
     * 
     * @param boolean $printQuery
     * @param boolean $logQuery
     * @param boolean $loadFromApi if true and no records -> load from api
     * 
     * @return \Professio_BudgetMailer_Model_Resource_List_Collection
     */
    public function load(
        $printQuery = false, $logQuery = false, $loadFromApi = true,
        $forceLoadFromApi = false
    )
    {
        parent::load($printQuery, $logQuery);
        
        if ( ( !count($this->_items) && $loadFromApi ) || $forceLoadFromApi) {
            $this->loadFromApi();
        }
        
        return $this;
    }
    
    /**
     * Load list collection from API
     * 
     * @return void
     */
    public function loadFromApi()
    {
        if ($this->isLoadingFromApi()) {
            return;
        }
        
        $this->setLoadingFromApi(true);
        // INFO this prevents cycling out (see mapper)
        Mage::register('budgetmailer_list_initiation', true);
        
        $lists = $this->getClient()->getLists();
        
        foreach ($lists as $list) {
            $model = Mage::getModel('budgetmailer/list');
            $model->loadByBudgetmailerId($list->id, false);
            $this->getMapper()->listToModel($list, $model);
            $model->setIsMassupdate(true);
            $model->save();
        }
        
        $this->clear();
        //$this->_setIsLoaded(false);
        //$this->load(false, false, false);
        parent::load(false, false);
        
        $this->setLoadingFromApi(false);
        Mage::unregister('budgetmailer_list_initiation');
    }
    
    /**
     * Check if loading from api is in progress.
     * Avoid double API loads
     * 
     * @return boolean
     */
    protected function isLoadingFromApi()
    {
        return $this->_loadingFromApi;
    }
    
    /**
     * Set load from api flag value
     * 
     * @param boolean $v
     * 
     * @return Professio_BudgetMailer_Model_Resource_List_Collection
     */
    protected function setLoadingFromApi($v)
    {
        $this->_loadingFromApi = $v;
        
        return $this;
    }
    
    /**
     * Override to option hash
     * @return array
     */
    public function toOptionHash()
    {
        $h = array();
        
        foreach ($this as $item) {
            $h[$item->getEntityId()] = $item->getName();
        }
        
        return $h;
    }
}
