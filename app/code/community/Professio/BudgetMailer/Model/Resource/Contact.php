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
 * Contact model
 * 
 * @category   Professio
 * @package    Professio_BudgetMailer
 */
class Professio_BudgetMailer_Model_Resource_Contact 
extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Constructor
     */
    public function _construct()
    {
        $this->_init('budgetmailer/contact', 'entity_id');
    }
    
    /**
     * Load contact by budgetmailer id
     *
     * @param Professio_BudgetMailer_Model_Contact $contact
     * @param string $budgetmailerId
     * 
     * @return Mage_Customer_Model_Resource_Customer
     * @throws Mage_Core_Exception
     */
    public function loadByBudgetMailerId(
        Professio_BudgetMailer_Model_Contact $contact, 
        $budgetmailerId
    )
    {
        $adapter = $this->_getReadAdapter();
        $bind = array('budgetmailer_id' => $budgetmailerId);
        
        $select = $adapter->select()
            ->from(
                Mage::getSingleton('core/resource')
                ->getTableName('budgetmailer/contact'), array('entity_id')
            )
            ->where('budgetmailer_id = :budgetmailer_id');
        
        $contactId = $adapter->fetchOne($select, $bind);
        
        if ($contactId) {
            $this->load($contact, $contactId);
            $contact->setOrigData();
        } else {
            $contact->setData(array());
        }

        return $this;
    }
    
    /**
     * Load contact by customer
     *
     * @param Professio_BudgetMailer_Model_Contact $contact
     * @param Mage_Customer_Model_Customer $customer
     * 
     * @return Mage_Customer_Model_Resource_Customer
     * @throws Mage_Core_Exception
     */
    public function loadByCustomer(
        Professio_BudgetMailer_Model_Contact $contact, 
        Mage_Customer_Model_Customer $customer
    )
    {
        $adapter = $this->_getReadAdapter();
        $bind = array(
            'customer_id' => $customer->getEntityId(),
            'list_id' => Mage::helper('budgetmailer/mapper')
                ->listBudgetmailerIdToListId(
                    Mage::helper('budgetmailer/config')->getGeneralList()
                )
        );
        
        $select = $adapter->select()
            ->from(
                Mage::getSingleton('core/resource')
                ->getTableName('budgetmailer/contact'), array('entity_id')
            )
            ->where('customer_id = :customer_id AND list_id = :list_id');
        
        $contactId = $adapter->fetchOne($select, $bind);
        
        if ($contactId) {
            Mage::log('resource loadByCustomer() loading');
            $this->load($contact, $contactId);
            $contact->setOrigData();
        } else {
            Mage::log('resource loadByCustomer() NOT loading');
            $contact->setData(array());
        }

        return $this;
    }
    
    /**
     * Load contact by email
     *
     * @param Professio_BudgetMailer_Model_Contact $contact
     * @param string $email
     * 
     * @return Mage_Customer_Model_Resource_Customer
     * @throws Mage_Core_Exception
     */
    public function loadByEmail(
        Professio_BudgetMailer_Model_Contact $contact, 
        $email
    )
    {
        $adapter = $this->_getReadAdapter();
        $bind = array(
            'email' => $email,
            'list_id' => Mage::helper('budgetmailer/mapper')
                ->listBudgetmailerIdToListId(
                    Mage::helper('budgetmailer/config')->getGeneralList()
                )
        );
        
        $select = $adapter->select()
            ->from(
                Mage::getSingleton('core/resource')
                ->getTableName('budgetmailer/contact'), array('entity_id')
            )
            ->where('email = :email AND list_id = :list_id');
        
        $contactId = $adapter->fetchOne($select, $bind);
        
        if ($contactId) {
            $this->load($contact, $contactId);
            $contact->setOrigData();
        } else {
            $contact->setData(array());
        }

        return $this;
    }
}
