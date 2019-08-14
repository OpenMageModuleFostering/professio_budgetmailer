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
 * Implementation of front-end subscriber
 *
 * @category    Professio
 * @package     Professio_BudgetMailer
 */
class Professio_BudgetMailer_SubscriberController
extends Mage_Core_Controller_Front_Action
{
    /**
     * Current contact
     * @var Professio_BudgetMailer_Model_Contact  
     */
    protected $_contact;
    
    /**
     * Get current contact
     * 
     * @return Professio_BudgetMailer_Model_Contact
     */
    protected function getContact()
    {
        if (!isset($this->_contact)) {
            $this->_contact = Mage::getModel('budgetmailer/contact');
//            $this->contact->loadByEmail($this->getEmail());
        }
        
        return $this->_contact;
    }
    
    /**
     * Get current customer
     * 
     * @return Mage_Customer_Model_Customer
     */
    protected function getCustomer()
    {
        return $this->getCustomerSession()->getCustomer();
    }
    
    /**
     * Get current customer session
     * 
     * @return Mage_Customer_Model_Session
     */
    protected function getCustomerSession()
    {
        return Mage::getSingleton('customer/session');
    }
    
    /**
     * Get email input from request
     * 
     * @return string
     */
    protected function getEmail()
    {
        return (string) $this->getRequest()->getPost('email');
    }
    
    /**
     * Get mapper
     * 
     * @return Professio_BudgetMailer_Helper_Mapper
     */
    protected function getMapper()
    {
        return Mage::helper('budgetmailer/mapper');
    }
    
    /**
     * Get core session
     * 
     * @return Mage_Core_Model_Session
     */
    protected function getSession()
    {
        return Mage::getSingleton('core/session');
    }
    
    /**
     * Custom log function, logs only in developer mode.
     * 
     * @param string $message message to log
     * @return null
     */
    protected function log($message)
    {
        if (Mage::getIsDeveloperMode()) {
            Mage::log($message);
        }
    }
    
    /**
     * INFO this doesn't allow the have single customer with multiple emails
     * 
     * Handle single subscription
     * 
     * @return null
     */
    protected function subscribe()
    {
        $this->log('budgetmailer/subscriber::subscribe() start');
        
        if ($this->getCustomer()->getId()) {
            $this->log('budgetmailer/subscriber::subscribe() customer');
            
            $this->getContact()->loadByCustomer($this->getCustomer());
            
            if (!$this->getContact()->getId()) {
                $this->log(
                    'budgetmailer/subscriber::subscribe() customer no contact'
                );
                
                $this->getContact()->setCustomerId(
                    $this->getCustomer()->getId()
                );
                
                $this->getContact()->setEmail($this->getEmail());
            }
            
            $this->getMapper()->customerToModel(
                $this->getCustomer(), $this->getContact()
            );
        } else {
            $this->log('budgetmailer/subscriber::subscribe() no customer');
            
            $this->getContact()->loadByEmail($this->getEmail());
            
            if (!$this->getContact()->getId()) {
                $this->log(
                    'budgetmailer/subscriber::subscribe() no customer no '
                    . 'contact'
                );
                
                $this->getContact()->setEmail($this->getEmail());
            }
        }

        $this->getContact()->setUnsubscribed(false);
        $this->getContact()->setSubscribe(true);
        
        $this->getContact()->save();
    }
    
    /**
     * Single subscription action
     * 
     * @return null
     */
    public function subscribeAction()
    {
        if ($this->getRequest()->isPost() 
            && $this->getRequest()->getPost('email')) {
            try {
                $this->subscribe();
                $this->getSession()->addSuccess(
                    $this->__('The subscription has been saved.')
                );
            } catch(Exception $e) {
                $this->getSession()->addError(
                    $this->__(
                        'An error occurred while saving your subscription.'
                    )
                );
            }
        }
        
        $this->_redirectReferer();
    }
}
