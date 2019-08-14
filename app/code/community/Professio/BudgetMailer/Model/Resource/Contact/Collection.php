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
class Professio_BudgetMailer_Model_Resource_Contact_Collection
extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    /**
     * Something
     * @var array
     */
    protected $_joinedFields = array();

    /**
     * Constructor
     * 
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        
        $this->_init('budgetmailer/contact');
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
    
    
    public function idsToBudgetmailerIds($ids)
    {
        $this->clear();
        //$this->addFieldToSelect('budgetmailer_id');
        $this->addFieldToFilter(
            'entity_id',
            array(
                'in' => $ids
            )
        );
        $this->load();
        
        $budgetmailerIds = array();
        
        foreach ($this->getIterator() as $contact) {
            $budgetmailerIds[] = $contact->getBudgetmailerId();
        }
        
        return $budgetmailerIds;
    }
}
