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
 * Subscription management controller 
 *
 * @category    Professio
 * @package     Professio_BudgetMailer
 */
class Professio_BudgetMailer_ManageController 
extends Mage_Core_Controller_Front_Action
{
    /**
     * Current contact
     * 
     * @var Professio_BudgetMailer_Model_Contact 
     */
    protected $_contact;
    
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
     * Get current contact
     * 
     * @return Professio_BudgetMailer_Model_Contact
     */
    protected function getContact()
    {
        if (!isset($this->_contact)) {
            $this->_contact = Mage::getModel('budgetmailer/contact');
            $this->_contact->loadByCustomer($this->getCustomer());
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
            
            if (!$this->getContact()->getEntityId()) {
                Mage::helper('budgetmailer/mapper')->customerToModel(
                    $this->getCustomer(), $this->getContact()
                );
                
                $address = Mage::helper('budgetmailer')
                    ->getCustomersPrimaryAddress($this->getCustomer());
                
                if ($address && $address->getEntityId()) {
                    Mage::helper('budgetmailer/mapper')->addressToModel(
                        $address, $this->getContact()
                    );
                }
            }
            
            $this->getContact()->setUnsubscribed(!$subscribe);
            $this->getContact()->setSubscribe($subscribe);
        
            $this->getContact()->save();
            
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
