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
 * Observer model
 * 
 * @category   Professio
 * @package    Professio_BudgetMailer
 */

class Professio_BudgetMailer_Model_Observer
{
    /**
     * Get BudgetMailer API client
     * 
     * @return \BudgetMailer\Api\Client
     */
    protected function getClient()
    {
        return Mage::getSingleton('budgetmailer/client')->getClient();
    }
    
    /**
     * Get request
     * 
     * @return Mage_Core_Controller_Request_Http
     */
    protected function getRequest()
    {
        return Mage::app()->getRequest();
    }
    
    /**
     * Get current session
     * 
     * @return Mage_Core_Model_Session_Abstract
     */
    protected function getSession()
    {
        if (!isset($this->session)) {
            if (Mage::app()->getStore()->isAdmin()) {
                $this->session = Mage::getSingleton('adminhtml/session');
            } else if (Mage::getSingleton('customer/session')->isLoggedIn()) {
                $this->session = Mage::getSingleton('customer/session');
            } else {
                $this->session = Mage::getSingleton('core/session');
            }
        }
        
        return $this->session;
    }
    
    /**
     * Log wrapper - loging only when in developer mode
     * 
     * @param type $message
     */
    protected function log($message)
    {
        if (Mage::getIsDeveloperMode()) {
            Mage::log($message);
        }
    }
    
    /**
     * Check if current address is the right type.
     * @param type $address
     * @param type $customer
     */
    protected function isAddressSaveAfter($address, $customer)
    {
        if ($customer->getDefaultBillingAddress()) {
            $billing = $address->getEntityId() == $customer
                ->getDefaultBillingAddress()->getEntityId()
                && Mage::helper('budgetmailer/config')
                    ->isAddressTypeBilling();
        } else {
            $billing = false;
        }

        if ($customer->getDefaultShippingAddress()) {
            $shipping = $address->getEntityId() == $customer
                ->getDefaultShippingAddress()->getEntityId()
                && Mage::helper('budgetmailer/config')
                    ->isAddressTypeShipping();
        } else {
            $shipping = false;
        }
        
        return $billing || $shipping;
    }
    
    /**
     * After address save, check if primary and update contact
     * 
     * @param Varien_Event_Observer $observer
     */
    public function addressSaveAfter($observer)
    {
        $this->log('budgetmailer/observer::addressSaveAfter() start');
        
        $request = $this->getRequest();
        
        if ('customer' == $request->getModuleName() 
            && 'account' == $request->getControllerName() 
            && 'editPost' == $request->getActionName()
        ) {
            // INFO if saving customer don't fire this event
            $this->log(
                'budgetmailer/observer::addressSaveAfter() skip duplic. event'
            );
            
            return;
        }
        
        try {
            if (Mage::helper('budgetmailer/config')
                    ->isAdvancedOnAddressUpdateEnabled()
                ) {
                $address = $observer->getCustomerAddress();
                $customer = $address->getCustomer();
                
                if ($this->isAddressSaveAfter($address, $customer)) {

                    if ($customer && $customer->getEntityId()) {
                        $client = $this->getClient();
                        $contact = $client->getContact($customer->getEmail());

                        if ($contact) {
                            Mage::helper('budgetmailer/mapper')
                                ->addressToContact(
                                    $address, $contact
                                );

                            $client->putContact($contact->email, $contact);
                        }
                    } else {
                        $this->log(
                            'budgetmailer/observer::addressSaveAfter() no cust.'
                        );
                    }
                } else {
                    $this->log(
                        'budgetmailer/observer::addressSaveAfter() wrong type'
                    );
                }
            } else {
                $this->log(
                    'budgetmailer/observer::addressSaveAfter() disabled'
                );
            }
        } catch (Exception $e) {
            $this->getSession()->addError($e->getMessage());
            $this->log(
                'budgetmailer/observer::addressSaveAfter() '
                . 'failed with exception: ' . $e->getMessage()
            );
            Mage::logException($e);
        }
        
        $this->log('budgetmailer/observer::addressSaveAfter() end');
    }
    
    /**
     * After customer delete - if enabled by config delete contact after 
     * deleting customer
     * 
     * @param Varien_Event_Observer $observer
     */
    public function customerDeleteAfter($observer)
    {
        $this->log('budgetmailer/observer::customerDeleteAfter() start');
        
        try {
            if (Mage::helper('budgetmailer/config')
                    ->isAdvancedOnCustomerDeleteEnabled()
                || Mage::helper('budgetmailer/config')
                    ->isAdvancedOnCustomerDeleteUnsubscribeEnabled()
                ) {
                $customer = $observer->getCustomer();
                $client = Mage::getSingleton('budgetmailer/client')
                    ->getStoreClient($customer->getStoreId());
                $email = $customer->getEmail();
                $contact = $client->getContact($email);
                
                if ($contact) {
                    if (Mage::helper('budgetmailer/config')
                        ->isAdvancedOnCustomerDeleteUnsubscribeEnabled()) {
                        $contact->subscribe = false;
                        $contact->unsubscribed = true;
                        
                        $client->putContact($contact->email, $contact);
                        
                        $this->log(
                            'budgetmailer/observer::customerDeleteAfter() '
                            . 'unsubscribe / delete contact for customer id: '
                            . $customer->getEntityId()
                        );
                    }
                    
                    $client->deleteContact($email);
                    
                    $this->log(
                        'budgetmailer/observer::customerDeleteAfter() '
                        . 'deleted contact for customer id: '
                        . $customer->getEntityId()
                    );
                } else {
                    $this->log(
                        'budgetmailer/observer::customerDeleteAfter() '
                        . 'contact not found for customer id: '
                        . $customer->getEntityId()
                    );
                }
            } else {
                $this->log(
                    'budgetmailer/observer::customerDeleteAfter() disabled'
                );
            }
        } catch (Exception $e) {
            $this->getSession()->addError($e->getMessage());
            $this->log(
                'budgetmailer/observer::customerDeleteAfter() '
                . 'failed with exception: ' . $e->getMessage()
            );
            Mage::logException($e);
        }
        
        $this->log('budgetmailer/observer::customerDeleteAfter() end');
    }
    
    /**
     * Admin - Customer save after - if there are post data for contact 
     * update contact. If update customer contact config is enabled,
     * update contact by customer data
     * 
     * @param Varien_Event_Observer $observer
     */
    public function customerSaveAfterAdmin($observer)
    {
        $this->log('budgetmailer/observer::customerSaveAfterAdmin() start');
        
        try {
            if (!Mage::helper('budgetmailer/config')
                ->isAdvancedOnCustomerUpdateEnabled() ) {
                // nothing to do
                $this->log(
                    'budgetmailer/observer::customerSaveAfterAdmin() disabled'
                );
                return;
            }
            
            $customer = $observer->getEvent()->getCustomer();
            $client = Mage::getSingleton('budgetmailer/client')
                ->getStoreClient($customer->getStoreId());
            
            $email = $customer->getOrigData('email') 
                ? $customer->getOrigData('email')
                : $customer->getData('email');

            $contact = $client->getContact($email);
            $subscribe = $this->getRequest()->getPost('budgetmailer_subscribe');
            
            if (!$contact && !$subscribe) {
                // nothing to do 2
                return;
            }
            
            if (!$contact) {
                $contact = new stdClass();
                $new = true;
            } else {
                // unsub only if contact
                $new = false;
            }

            Mage::helper('budgetmailer/mapper')
                ->customerToContact($customer, $contact);
            
            $contact->subscribe = $subscribe;
            $contact->unsubscribed = !$subscribe;
            
            if ($new) {
                $client->postContact($contact);
            } else {
                $client->putContact($email, $contact);
            }
            
        } catch (Exception $e) {
            $this->getSession()->addError($e->getMessage());
            $this->log(
                'budgetmailer/observer::customerSaveAfterAdmin() '
                . 'failed with exception: ' . $e->getMessage()
            );
            Mage::logException($e);
        }
        
        $this->log('budgetmailer/observer::customerSaveAfterAdmin() end');
    }
    
    /**
     * Front - customer save after. If update of contact by customer is enabled 
     * update contact. If there is subscribed parameter after registering new 
     * customer, sign up the customer.
     * 
     * @param Varien_Event_Observer $observer
     */
    public function customerSaveAfterFront($observer)
    {
        $this->log('budgetmailer/observer::customerSaveAfterFront() start');
        
        try {
            if (!Mage::helper('budgetmailer/config')
                ->isAdvancedOnCustomerUpdateEnabled() ) {
                // nothing to do
                $this->log(
                    'budgetmailer/observer::customerSaveAfterFront() disabled'
                );
                return;
            }
            
            $client = $this->getClient();
            $customer = $observer->getEvent()->getCustomer();
            
            $email = $customer->getOrigData('email') 
                ? $customer->getOrigData('email')
                : $customer->getData('email');

            $contact = $client->getContact($email);
            
            $subscribe = Mage::app()->getRequest()->getPost('bm_is_subscribed');
            
            if ($contact) {
                Mage::helper('budgetmailer/mapper')->customerToContact(
                    $customer, $contact
                );
                
                $client->putContact($email, $contact);
            } else if ($subscribe) {
                $contact = new stdClass();
                
                Mage::helper('budgetmailer/mapper')->customerToContact(
                    $customer, $contact
                );
                
                $contact->subscribe = $subscribe;
                $contact->unsubscribed = !$subscribe;
                
                $client->postContact($contact);
            }
        } catch (Exception $e) {
            $this->getSession()->addError($e->getMessage());
            $this->log(
                'budgetmailer/observer::customerSaveAfterFront() '
                . 'failed with exception: ' . $e->getMessage()
            );
            Mage::logException($e);
        }
        
        $this->log('budgetmailer/observer::customerSaveAfterFront() end');
    }
    
    /**
     * After place order - if enabled, update contact by tags (ordered product 
     * category names)
     * 
     * @param Varien_Event_Observer $observer
     */
    public function salesOrderPlaceAfter($observer)
    {
        $this->log('budgetmailer/observer::salesOrderPlaceAfter() start');
        
        try {
            $subscribe = Mage::app()->getRequest()
                ->getPost('bm_is_subscribed');
            
            $client = Mage::getSingleton('budgetmailer/client')->getClient();
            
            $order = $observer->getEvent()->getOrder();
            $email = $order->getCustomerEmail();
            $contact = $client->getContact($email);
            
            if ($subscribe) {
                if (!$contact) {
                    $contact = new stdClass();

                    Mage::helper('budgetmailer/mapper')
                        ->orderToContact($order, $contact);
                    
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
            }

            if (Mage::helper('budgetmailer/config')
                ->isAdvancedOnOrderEnabled()) {

                if ($contact) {
                    $orderTags = Mage::helper('budgetmailer')
                        ->getOrderTags($order);
                    
                    $client->postTags($email, $orderTags);
                }
            } else {
                $this->log(
                    'budgetmailer/observer::salesOrderPlaceAfter() '
                    . 'tagging disabled'
                );
            }
        } catch(Exception $e) {
            $this->getSession()->addError($e->getMessage());
            $this->log(
                'budgetmailer/observer::salesOrderPlaceAfter() '
                . 'failed with exception: ' . $e->getMessage()
            );
            Mage::logException($e);
        }
        
        $this->log('budgetmailer/observer::salesOrderPlaceAfter() end');
    }
    
    /**
     * After saving configuration of BudgetMailer - validate API key 
     * and secret. Optionally flush cache (if disabled).
     * 
     * @param Varien_Event_Observer $observer
     */
    public function afterConfigChange()
    {
        $this->log('budgetmailer/observer::afterConfigChange() start');
        
        try {
            if (!$this->getClient()->isConnected()) {
                $this->getSession()->addError(
                    Mage::helper('budgetmailer')
                    ->__('Invalid API endpoint, key and secret combination.')
                );
            } else {
                $this->getSession()->addSuccess(
                    Mage::helper('budgetmailer')
                        ->__(
                            'API endpoint, key and secret combination is valid.'
                        )
                );
            }
            
            if (!Mage::helper('budgetmailer/config')->isCacheEnabled()) {
                $this->getClient()->getCache()->purge();
            }
        } catch (Exception $e) {
                $this->getSession()->addError($e->getMessage());
                $this->log(
                    'budgetmailer/observer::afterConfigChange() failed '
                    . 'with exception: ' . $e->getMessage()
                );
                Mage::logException($e);
        }
        
        $this->log('budgetmailer/observer::afterConfigChange() end');
    }
}
