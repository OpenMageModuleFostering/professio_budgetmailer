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
 * Implementation of front-end subscriber
 *
 * @category    Professio
 * @package     Professio_BudgetMailer
 */
class Professio_BudgetMailer_SubscriberController
extends Mage_Core_Controller_Front_Action
{
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
     * Handle single subscription
     * INFO this doesn't allow to have single customer with multiple emails
     * 
     * @return null
     */
    protected function subscribe()
    {
        $this->log('budgetmailer/subscriber::subscribe() start');
        
        $client = Mage::getSingleton('budgetmailer/client')->getClient();
        $email = $this->getEmail();
        $subscribe = true;
        
        $contact = $client->getContact($email);
        
        if (!$contact) {
            $contact = new stdClass();

            if ($this->getCustomer() && $this->getCustomer()->getId()) {
                Mage::helper('budgetmailer/mapper')->customerToContact(
                    $this->getCustomer(), $contact
                );
            } else {
                $contact->email = $email;
            }
            
            $new = true;
        } else {
            $new = false;
        }

        $contact->subscribe = $subscribe;
        $contact->unsubscribed = !$subscribe;

        if ($new) {
            $client->postContact($contact);
        } else {
            $client->putContact($contact->email, $contact);
        }
        
        $this->log('budgetmailer/subscriber::subscribe() end');
    }
    
    /**
     * Single subscription action
     * 
     * @return null
     */
    public function subscribeAction()
    {
        if ($this->getRequest()->isPost() && $this->getEmail()) {
            try {
                $this->subscribe();
                $this->getSession()->addSuccess(
                    $this->__('The subscription has been saved.')
                );
            } catch(Exception $e) {
                Mage::logException($e);
                
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
