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
 * Subscription management controller 
 *
 * @category    Professio
 * @package     Professio_BudgetMailer
 */
class Professio_BudgetMailer_ManageController 
extends Mage_Core_Controller_Front_Action
{
    /**
     * Don't dispatch if there is no customer session
     */
    public function preDispatch()
    {
        parent::preDispatch();
        
        if (!$this->getCustomerSession()->authenticate($this)) {
            $this->setFlag('', 'no-dispatch', true);
        }
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
     * Display subscription status
     * 
     * @return null
     */
    public function indexAction()
    {
        $this->loadLayout();
        
        $this->_initLayoutMessages('customer/session');
        $this->_initLayoutMessages('catalog/session');

        if ($block = $this->getLayout()->getBlock('budgetmailer_newsletter')) {
            $block->setRefererUrl($this->_getRefererUrl());
        }
        
        $this->getLayout()
            ->getBlock('head')
            ->setTitle($this->__('Newsletter Subscription'));
        
        $this->renderLayout();
    }

    /**
     * Save single subscription
     * 
     * @return null
     */
    public function saveAction()
    {
        if (!$this->_validateFormKey()) {
            return $this->_redirect('customer/account/');
        }

        try {
            $subscribe = $this->getRequest()
                ->getParam('budgetmailer_subscribe', false);
            
            $client = Mage::getSingleton('budgetmailer/client')->getClient();
            $contact = $client->getContact($this->getCustomer()->getEmail());
            
            if (!$contact) {
                $contact = new stdClass();
                
                Mage::helper('budgetmailer/mapper')->customerToContact(
                    $this->getCustomer(), $contact
                );
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
            
            $this->getCustomerSession()->addSuccess(
                $subscribe ? 
                $this->__('The subscription has been saved.') :
                $this->__('The subscription has been removed.')
            );
        } catch (Exception $e) {
            $this->getCustomerSession()
                ->addError(
                    $this->__(
                        'An error occurred while saving your subscription.'
                    )
                );
            
            Mage::logException($e);
        }
        
        $this->_redirect('customer/account/');
    }
}
