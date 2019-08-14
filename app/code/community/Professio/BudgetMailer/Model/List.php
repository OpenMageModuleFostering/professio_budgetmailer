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
 * List model
 * 
 * @category   Professio
 * @package    Professio_BudgetMailer
 */
class Professio_BudgetMailer_Model_List extends Mage_Core_Model_Abstract
{
    /**
     * Entity code.
     * Can be used as part of method name for entity processing
     */
    const ENTITY = 'budgetmailer_list';
    const CACHE_TAG = 'budgetmailer_list';

    /**
     * Prefix of model events names
     * @var string
     */
    protected $_eventPrefix = 'budgetmailer_list';

    /**
     * Parameter name in event
     * @var string
     */
    protected $_eventObject = 'list';

    /**
     * Constructor
     * 
     * @return void
     */
    public function _construct()
    {
        parent::_construct();
        
        $this->_init('budgetmailer/list');
    }

    /**
     * Before save list
     * 
     * @return Professio_BudgetMailer_Model_List
     */
    protected function _beforeSave()
    {
        parent::_beforeSave();
        
        $now = Mage::getSingleton('core/date')->gmtDate();
        
        if ($this->isObjectNew()) {
            $this->setCreatedAt($now);
        }
        
        $this->setUpdatedAt($now);
        
        return $this;
    }

    /**
     * Retrieve collection
     * 
     * @return Professio_BudgetMailer_Model_Contact_Collection
     */
    public function getSelectedContactsCollection()
    {
        if (!$this->hasData('_contact_collection')) {
            if (!$this->getId()) {
                return new Varien_Data_Collection();
            } else {
                $collection = Mage::getResourceModel(
                    'budgetmailer/contact_collection'
                )->addFieldToFilter('list_id', $this->getId());
                $this->setData('_contact_collection', $collection);
            }
        }
        
        return $this->getData('_contact_collection');
    }

    /**
     * Get default values
     * 
     * @return array
     */
    public function getDefaultValues()
    {
        $values = array();
        $values['status'] = 1;
        
        return $values;
    }
    
    /**
     * Load list by budgetmailer id
     * 
     * @param string $budgetmailerId 
     * @param boolean $useApi true = try to load from API
     * 
     * @return Professio_BudgetMailer_Model_Contact
     */
    public function loadByBudgetMailerId($budgetmailerId, $useApi = true)
    {
        $this->_getResource()->loadByBudgetMailerId($this, $budgetmailerId);
        
        $ttl = time() - Mage::helper('budgetmailer/config')->getSyncTtl();
        
        // not found try to load from API
        if ($useApi && (
                    !$this->getData('updated_at') 
                    || $this->getData('updated_at') < $ttl
                )
            ) {
            $this->setBudgetmailerId($budgetmailerId);
            $this->load($this->getId());
        }
        
        return $this;
    }
    
}
