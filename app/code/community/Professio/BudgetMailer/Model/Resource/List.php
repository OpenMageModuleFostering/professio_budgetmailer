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
 * Resource for list
 * 
 * @category   Professio
 * @package    Professio_BudgetMailer
 */
class Professio_BudgetMailer_Model_Resource_List
extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Constructor
     * 
     * @return void
     */
    public function _construct()
    {
        $this->_init('budgetmailer/list', 'entity_id');
    }
    
    /**
     * Load list by budgetmailer id
     *
     * @param Professio_BudgetMailer_Model_List $list
     * @param string $budgetmailerId
     * 
     * @return Mage_Customer_Model_Resource_Customer
     * @throws Mage_Core_Exception
     */
    public function loadByBudgetMailerId(
        Professio_BudgetMailer_Model_List $list, 
        $budgetmailerId
    )
    {
        $adapter = $this->_getReadAdapter();
        $bind = array('budgetmailer_id' => $budgetmailerId);
        
        $select = $adapter->select()
            ->from(
                Mage::getSingleton('core/resource')
                ->getTableName('budgetmailer/list'), array('entity_id')
            )
            ->where('budgetmailer_id = :budgetmailer_id');
        
        $listId = $adapter->fetchOne($select, $bind);
        
        if ($listId) {
            $this->load($list, $listId);
            $list->setOrigData();
        } else {
            $list->setData(array());
        }

        return $this;
    }
}
