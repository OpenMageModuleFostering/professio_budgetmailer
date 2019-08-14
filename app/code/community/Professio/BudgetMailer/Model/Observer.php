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
     * @return Professio_BudgetMailer_Model_Client
     */
    protected function getClient()
    {
        return Mage::getSingleton('budgetmailer/client');
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
     * Add mass actions to customer grid
     * 
     * @param Varien_Event_Observer $observer
     */
    public function addMassAction($observer)
    {
        //$this->log('budgetmailer/observer::addMassAction() start');
        
        try {
            $block = $observer->getEvent()->getBlock();
            $controller = $block->getRequest()->getControllerName();

            if ('Mage_Adminhtml_Block_Widget_Grid_Massaction' 
                    == get_class($block)
                    && in_array(
                        $controller, 
                        array('customer', 'newsletter_subscriber')
                    )
                ) {
                $url = '*/budgetmailer/';

                $block->addItem(
                    'budgetmailer_subscribe',
                    array(
                        'label' => Mage::helper('budgetmailer')
                            ->__('Subscribe to a BudgetMailer List'),
                        'url' => Mage::helper('adminhtml')
                            ->getUrl($url . 'masssubscribe')
                    )
                );

                $block->addItem(
                    'budgetmailer_unsubscribe', 
                    array(
                        'label' => Mage::helper('budgetmailer')
                            ->__('Unsubscribe from a BudgetMailer List'),
                        'url' => Mage::helper('adminhtml')
                            ->getUrl($url . 'massunsubscribe')
                    )
                );
            }
        } catch (Exception $e) {
            $this->log('budgetmailer/observer::addMassAction() failed');
            Mage::logException($e);
        }
        
        //$this->log('budgetmailer/observer::addMassAction() end');
    }
    
    /**
     * After address delete - try to use new primary address, and update contact
     * 
     * @param Varien_Event_Observer $observer
     */
    public function addressDeleteAfter($observer)
    {
        $this->log('budgetmailer/observer::addressDeleteAfter() start');
        
        try {
            if (Mage::helper('budgetmailer/config')
                    ->isAdvancedOnAddressDeleteEnabled()
                ) {
                $address = $observer->getCustomerAddress();
                $customer = $address->getCustomer();
            
                if ($customer && $customer->getEntityId()) {
                    $addressPrimary = Mage::helper('budgetmailer')
                        ->getCustomersPrimaryAddress($customer);
                    
                    // check if primary address / map / save
                    if ($address && $addressPrimary
                        && $address->getEntityId() 
                        == $addressPrimary->getEntityId()
                    ) {
                        $contact = Mage::getModel('budgetmailer/contact')
                            ->loadByCustomer($customer);
                        Mage::helper('budgetmailer/mapper')
                            ->addressToModel($address, $contact);
                        $contact->save();
                    }
                } else {
                    $this->log(
                        'budgetmailer/observer::addressDeleteAfter() '
                        . 'no customer'
                    );
                }
            } else {
                $this->log(
                    'budgetmailer/observer::addressDeleteAfter() disabled'
                );
            }
        } catch (Exception $e) {
            $this->getSession()->addError($e->getMessage());
            $this->log(
                'budgetmailer/observer::addressDeleteAfter() '
                . 'failed with exception: ' . $e->getMessage()
            );
            Mage::logException($e);
        }
        
        $this->log('budgetmailer/observer::addressDeleteAfter() end');
    }
    
    /**
     * After address save, check if primary and update contact
     * 
     * @param Varien_Event_Observer $observer
     */
    public function addressSaveAfter($observer)
    {
        $this->log('budgetmailer/observer::addressSaveAfter() start');
        
        try {
            if (Mage::helper('budgetmailer/config')
                    ->isAdvancedOnAddressUpdateEnabled()
                ) {                
                $address = $observer->getCustomerAddress();
                $customer = $address->getCustomer();
                
                $this->log(
                    'budgetmailer/observer::addressSaveAfter address id: ' 
                    . $address->getEntityId()
                );
                
                if ($customer && $customer->getEntityId()) {
                    $contact = Mage::getModel('budgetmailer/contact')
                        ->loadByCustomer($customer);
                    
                    if ($contact && $contact->getEntityId()) {
                        $addressPrimary = Mage::helper('budgetmailer')
                            ->getCustomersPrimaryAddress($customer);
                        
                        // check if primary address
                        if (!$addressPrimary 
                            || ($addressPrimary->getEntityId() 
                            == $address->getEntityId())
                        ) {
                            Mage::helper('budgetmailer/mapper')
                                ->addressToModel($address, $contact);
                            
                            $contact->save();
                            
                            $this->log(
                                'budgetmailer/observer::addressSaveAfter() '
                                . 'updated contact'
                            );
                        } else {
                            $this->log(
                                'budgetmailer/observer::addressSaveAfter() '
                                . 'DIDN\'T update contact'
                            );
                        }
                    } else {
                        $this->log(
                            'budgetmailer/observer::addressSaveAfter() '
                            . 'no contact'
                        );
                    }
                } else {
                    $this->log(
                        'budgetmailer/observer::addressSaveAfter() no customer'
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
                ) {
                $customer = $observer->getCustomer();
                
                $contact = Mage::getModel('budgetmailer/contact');
                $contact->loadByCustomer($customer);
                
                if ($contact->getEntityId()) {
                    $contact->delete();
                    
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
            $contactData = $this->getRequest()->getPost('contact');

            $customer = $observer->getEvent()->getCustomer();
            $contact = Mage::getModel('budgetmailer/contact')
                ->loadByCustomer($customer);
            
            $this->log(
                'budgetmailer/observer::customerSaveAfterAdmin() contact: ' 
                . $contact->getEntityId() . ', customer: ' 
                . $customer->getEntityId()
            );

            // MAP CONTACT DATA
            $hasContactData = is_array($contactData) && count($contactData);
            
            if ($hasContactData) {
                $contactData['unsubscribed'] = 
                    !isset($contactData['subscribe']);
                $contact->addData($contactData);

                $this->log(
                    'budgetmailer/observer::customerSaveAfterAdmin() '
                    . 'mapped contact data to contact: '
                    . json_encode($contactData) . '.'
                );
            } else {
                $this->log(
                    'budgetmailer/observer::customerSaveAfterAdmin() '
                    . 'no contact data.'
                );
            }
            
            // MAP CUSTOMER
            if (Mage::helper('budgetmailer/config')
                    ->isAdvancedOnCustomerUpdateEnabled()
                ) {
                Mage::helper('budgetmailer/mapper')
                    ->customerToModel($customer, $contact);
                
                $this->log(
                    'budgetmailer/observer::customerSaveAfterAdmin() '
                    . 'mapped customer to contact.'
                );
            } else {
                $this->log(
                    'budgetmailer/observer::customerSaveAfterAdmin() '
                    . 'customer update disabled.'
                );
            }

            $this->customerSaveAfterAdminAddress($contact, $customer);
            
            if ($contact->getEntityId() 
                || ( $hasContactData && isset($contactData['subscribe']) ) 
            ) {
                $this->log(
                    'budgetmailer/observer::customerSaveAfterAdmin() saving.'
                );

                $contact->save();
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
    
    protected function customerSaveAfterAdminAddress($contact, $customer)
    {
        // MAP ADDRESS 
        if (Mage::helper('budgetmailer/config')
                ->isAdvancedOnAddressUpdateEnabled()
            && $customer && $customer->getEntityId()
        ) {
            $address = Mage::helper('budgetmailer')
                ->getCustomersPrimaryAddress($customer);

            if ($address && $address->getEntityId()) {
                $this->log(
                    'budgetmailer/observer::customerSaveAfterAdmin()'
                    . ' mapping address: ' . $address->getEntityId() 
                );

                Mage::helper('budgetmailer/mapper')
                    ->addressToModel($address, $contact);
            } else {
                $this->log(
                    'budgetmailer/observer::customerSaveAfterAdmin() '
                    . 'no address'
                );
            }
        } else {
            $this->log(
                'budgetmailer/observer::customerSaveAfterAdmin() '
                . 'not mapping address'
            );
        }
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
            $bmIsSubscribed = $this->getRequest()->get('bm_is_subscribed');
            $changed = false;
            
            $customer = $observer->getEvent()->getCustomer();
            $contact = Mage::getModel('budgetmailer/contact')
                ->loadByCustomer($customer);
            
            $this->log(
                'budgetmailer/observer::customerSaveAfterFront() contact: ' 
                . $contact->getEntityId() . ', customer: ' 
                . $customer->getEntityId()
            );
            
            if ($bmIsSubscribed) {
                $changed = true;
                
                $contact->setSubscribe(true);
                $contact->setUnsubscribed(false);
                
                $this->log(
                    'budgetmailer/observer::customerSaveAfterFront() '
                    . 'subscribing.'
                );
            } else {
                $this->log(
                    'budgetmailer/observer::customerSaveAfterFront() '
                    . 'not subscribing.'
                );
            }
            
            if (Mage::helper('budgetmailer/config')
                    ->isAdvancedOnCustomerUpdateEnabled()
            ) {
                if ($contact->getEntityId() || $bmIsSubscribed) {
                    $changed = true;
                    
                    Mage::helper('budgetmailer/mapper')
                        ->customerToModel($customer, $contact);
                    
                    $this->log(
                        'budgetmailer/observer::customerSaveAfterFront() '
                        . 'mapped customer to contact.'
                    );
                } else {
                    // INFO this was creating contacts without subscribing
                    $this->log(
                        'budgetmailer/observer::customerSaveAfterFront() '
                        . 'DIDN\'T mapped customer to contact, because '
                        . 'contact doesnt\'t exist yet..'
                    );
                }
            } else {
                $this->log(
                    'budgetmailer/observer::customerSaveAfterFront() '
                    . 'customer update disabled.'
                );
            }
            
            if ($changed) {
                $this->log(
                    'budgetmailer/observer::customerSaveAfterFront() saving.'
                );
                $contact->save();
            } else {
                $this->log(
                    'budgetmailer/observer::customerSaveAfterFront() not saved.'
                );
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
            if (Mage::helper('budgetmailer/config')
                ->isAdvancedOnOrderEnabled()) {
                $order = $observer->getEvent()->getOrder();
                $contact = Mage::getModel('budgetmailer/contact');
                $customer = $order->getCustomer();
                
                $contact->loadByCustomer($customer);
                
                $this->log(
                    'budgetmailer/observer::salesOrderPlaceAfter() order: ' 
                    . $order->getEntityId() . ', customer: ' 
                    . $customer->getEntityId() . ', contact: ' 
                    . $contact->getEntityId()
                );
                
                if ($contact->getEntityId()) {
                    $orderTags = Mage::helper('budgetmailer')
                        ->getOrderTags($order);
                    
                    $this->log(
                        'budgetmailer/observer::salesOrderPlaceAfter() '
                        . 'adding tags: ' . json_encode($orderTags)
                    );
                    $contact->addTags($orderTags, false);
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
     * Cron method. Periodically delete orphans, and import new contacts
     */
    public function cron()
    {
        Mage::log('budgetmailer/observer::cron() start');
        
        if (Mage::helper('budgetmailer/config')->isSyncCronEnabled()) {
            try {
                $rs = Mage::getSingleton('budgetmailer/importer')
                    ->deleteOrphans();
                $rs = Mage::getSingleton('budgetmailer/importer')
                    ->importContacts();

                Mage::log(
                    ($rs
                        ? 'budgetmailer/observer::cron() no contacts' 
                        : 'budgetmailer/observer::cron() completed: ' 
                            . $rs['completed'] . ', failed: ' . $rs['failed']
                    )
                );
            } catch (Exception $e) {
                $this->getSession()->addError($e->getMessage());
                Mage::log(
                    'budgetmailer/observer::cron() failed with exception: ' 
                    . $e->getMessage()
                );
                Mage::logException($e);
            }
        }
        
        Mage::log('budgetmailer/observer::cron() end');
    }
    
    /**
     * After saving configuration of BudgetMailer - validate API key 
     * and secret. Initiate lists collection.
     * 
     * @param Varien_Event_Observer $observer
     */
    public function afterConfigChange()
    {
        $this->log('budgetmailer/observer::afterConfigChange() start');
        
        try {
            $p = $this->getRequest()->getPost();
            $t = $this->getClient()
                ->testApiCredentials($p['groups']['api']['fields']);

            if (!$t) {
                $this->getSession()->addError(
                    Mage::helper('budgetmailer')
                    ->__('Invalid API endpoint, key and secret combination.')
                );
            } else {
                // INFO this block could be in testApiCredentials
                $list = Mage::getModel('budgetmailer/list');
                $collection = $list->getCollection();
                $collection->load(false, false, true, true);
            
                $this->getSession()->addSuccess(
                    Mage::helper('budgetmailer')
                        ->__(
                            'API endpoint, key and secret combination is valid.'
                        )
                );
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
