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
 * Exporter model
 * 
 * @category   Professio
 * @package    Professio_BudgetMailer
 */
class Professio_BudgetMailer_Model_Exporter
{
    /**
     * Customers collection
     * @var Mage_Customer_Model_Resource_Customer_Collection
     */
    protected $_customersCollection;
    
    /**
     * Subscribers collection
     * @var Mage_Newsletter_Model_Resource_Subscriber_Collection 
     */
    protected $_subscribersCollection;
    
    /**
     * Unregistered customer orders collection
     * @var Mage_Sales_Model_Resource_Order_Collection
     */
    protected $_unregisteredCollection;
    
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
     * Get contact model
     * 
     * @return Professio_BudgetMailer_Model_Contact
     */
    protected function getContact()
    {
        return Mage::getModel('budgetmailer/contact');
    }
    
    /**
     * Prepare and get customers collection
     * 
     * @return Mage_Customer_Model_Resource_Customer_Collection
     */
    protected function getCustomersCollection()
    {
        if (!isset($this->_customersCollection)) {
            $this->_customersCollection = Mage::getModel('customer/customer')
                ->getCollection();
            $this->_customersCollection
                ->addAttributeToSelect('*')
                ->setPageSize(Professio_BudgetMailer_Model_Client::LIMIT);
        }
        
        return $this->_customersCollection;
    }
    
    /**
     * Prepare and get subscribers collection
     * 
     * @return Mage_Newsletter_Model_Resource_Subscriber_Collection
     */
    protected function getSubscribersCollection()
    {
        if (!isset($this->_subscribersCollection)) {
            $this->_subscribersCollection = 
                Mage::getModel('newsletter/subscriber')->getCollection();
            $this->_subscribersCollection
                ->setPageSize(Professio_BudgetMailer_Model_Client::LIMIT);
        }
        
        return $this->_subscribersCollection;
    }
    
    /**
     * Prepare and get collection of orders from unregistered customers
     * 
     * @return Mage_Sales_Model_Resource_Order_Collection
     */
    protected function getUnregisteredCollection()
    {
        if (!isset($this->_unregisteredCollection)) {
            $this->_unregisteredCollection = 
                Mage::getModel('sales/order')->getCollection();
            $this->_unregisteredCollection
                ->setPageSize(Professio_BudgetMailer_Model_Client::LIMIT);
            $this->_unregisteredCollection
                ->addAttributeToFilter('customer_id', array('null' => true));
        }
        
        return $this->_unregisteredCollection;
    }
    
    /**
     * Get mapper helper
     * @return Professio_BudgetMailer_Helper_Mapper
     */
    protected function getMapper()
    {
        return Mage::helper('budgetmailer/mapper');
    }
    
    /**
     * Get budgetmailer helper
     * @return Professio_BudgetMailer_Helper_Data
     */
    protected function getHelper()
    {
        return Mage::helper('budgetmailer');
    }
    
    /**
     * Export all customers to BudgetMailer API
     * 
     * @return array
     */
    public function exportCustomers()
    {
        $this->log('budgetmailer/exporter::exportCustomers() start');
        
        $totals = array('total' => 0, 'fail' => 0, 'success' => 0);
        
        try {
            $total = $this->getCustomersCollection()->getSize();

            if ($total > 0) {
                
                $page = 1;
                $pages = ceil(
                    $total / Professio_BudgetMailer_Model_Client::LIMIT
                );

                $this->log(
                    'budgetmailer/exporter::exportCustomers() customers: ' 
                    . $total . ', customer pages: ' . $pages
                );

                do {
                    $this->getCustomersCollection()->clear();
                    $this->getCustomersCollection()->setCurPage($page);
                    $this->getCustomersCollection()->load();

                    list($total, $fail, $success) = 
                        $this->exportCustomersPage();
                    
                    $totals['total'] += $total;
                    $totals['fail'] += $fail;
                    $totals['success'] += $success;
                    
                    $page++;
                } while ($page <= $pages);
            } else {
                $this->log(
                    'budgetmailer/exporter::exportCustomers() no customers'
                );
            }
        } catch(Exception $e) {
            $this->log(
                'budgetmailer/exporter::exportCustomers() failed '
                . 'with exception: ' . $e->getMessage()
            );
            
            Mage::logException($e);
        }
        
        $this->log('budgetmailer/exporter::exportCustomers() end');
        
        return $totals;
    }
    
    /**
     * Export single customers page
     * 
     * @param boolean $subscribe (un)subscribe
     * @return array
     */
    protected function exportCustomersPage($subscribe = true)
    {
        $this->log('budgetmailer/exporter::exportCustomersPage() start');

        try {
            $contacts = $emails = array();
            $total = $fail = $success = 0;

            foreach (
                $this->getCustomersCollection()->getIterator() as $customer
            ) {
                $contact = $this->getContact();
                $contact->loadByCustomer($customer, false);

                if (!$contact->getEntityId()) {
                    $this->getMapper()->customerToModel($customer, $contact);

                    $address = $this->getHelper()
                        ->getCustomersPrimaryAddress($customer);

                    if ($address && $address->getEntityId()) {
                        $this->getMapper()->addressToModel($address, $contact);
                    }

                    $contact->setCustomerId($customer->getEntityId());
                    $contact->setEmail($customer->getEmail());
                    
                    $contact->setUnsubscribed(!$subscribe);
                    $contact->setSubscribe($subscribe);
                    $contact->collectTags();
                    //$contact->setIsMassupdate(true);
                    //$contact->save(false);
                    
                    $emails[] = $contact->getEmail();
                } else {
                    $contact->setUnsubscribed(!$subscribe);
                    $contact->setSubscribe($subscribe);
                }

                $contacts[] = $this->getMapper()->contactToApi($contact);
            }

            if (count($contacts)) {
                list($total, $fail, $success, $contactsNew) = 
                    $this->getClient()->postContacts(
                        $contacts, Professio_BudgetMailer_Model_Client::BULK_INS
                    );
                
                if (isset($contactsNew->Success) && is_array($contactsNew->Success)) {
                    foreach ($contactsNew->Success as $contact) {
                        Mage::getModel('budgetmailer/importer')
                            ->importContact($contact);
                    }
                }
            }
        } catch (Exception $e) {
            $this->log(
                'budgetmailer/exporter::exportCustomersPage() failed '
                . 'with exception: ' . $e->getMessage()
            );
            
            Mage::logException($e);
        }
        
        $this->log(
            'budgetmailer/exporter::exportCustomersPage() end (total: ' 
            . $total . ', fail: ' . $fail . ', success: ' . $success
        );
        
        return array($total, $fail, $success);
    }
    
    /**
     * Export all subscribers to BudgetMailer API
     * 
     * @return array
     */
    public function exportSubscribers()
    {
        $this->log('budgetmailer/exporter::exportSubscribers() start');
        
        $totals = array('total' => 0, 'fail' => 0, 'success' => 0);
        
        try {
            $total = $this->getSubscribersCollection()->getSize();

            if ($total > 0) {
                $page = 1;
                $pages = ceil(
                    $total / Professio_BudgetMailer_Model_Client::LIMIT
                );

                $this->log(
                    'budgetmailer/exporter::exportSubscribers() '
                    . 'subscribers: ' . $total . ', subscriber pages: ' 
                    . $pages
                );

                do {
                    $this->getSubscribersCollection()->clear();
                    $this->getSubscribersCollection()->setCurPage($page);
                    $this->getSubscribersCollection()->load();
        
                    list($total, $fail, $success) = 
                        $this->exportSubscribersPage();
                    
                    $totals['total'] += $total;
                    $totals['fail'] += $fail;
                    $totals['success'] += $success;
                    
                    $page++;
                } while ($page <= $pages);
            } else {
                $this->log(
                    'budgetmailer/exporter::exportSubscribers() no subscribers'
                );
            }
        } catch(Exception $e) {
            $this->log(
                'budgetmailer/exporter::exportSubscribers() failed '
                . 'with exception: ' . $e->getMessage()
            );
            
            Mage::logException($e);
        }
        
        $this->log('budgetmailer/exporter::exportSubscribers() end');
        
        return $totals;
    }
    
    /**
     * Export single subscribers page
     * 
     * @param boolean $subscribe (un)subscribe
     * @return array
     */
    protected function exportSubscribersPage($subscribe = true)
    {
        $this->log('budgetmailer/exporter::exportSubscribersPage() start');
        
        try {
            $contacts = array();
            $total = $fail = $success = 0;

            foreach (
                $this->getSubscribersCollection()->getIterator() as $subscriber
            ) {
                $contact = $this->getContact();
                $contact->loadBySubscriber($subscriber, false);

                if (!$contact->getEntityId()) {
                    if ($subscriber->getCustomer()) {
                        $this->getMapper()
                            ->customerToModel(
                                $subscriber->getCustomer(), 
                                $contact
                            );

                        $address = $this->getHelper()
                            ->getCustomersPrimaryAddress(
                                $subscriber->getCustomer()
                            );

                        if ($address && $address->getEntityId()) {
                            $this->getMapper()
                                ->addressToModel($address, $contact);
                        }

                        $contact->setCustomerId(
                            $subscriber->getCustomer()->getEntityId()
                        );
                    }

                    $contact->setEmail($subscriber->getEmail());
                    
                    $contact->setUnsubscribed(!$subscribe);
                    $contact->setSubscribe($subscribe);
                    //$contact->setIsMassupdate(true);
                    //$contact->save(false);
                } else {
                    $contact->setUnsubscribed(!$subscribe);
                    $contact->setSubscribe($subscribe);
                }

                $contacts[] = $this->getMapper()->contactToApi($contact);
            }

            if (count($contacts)) {
                list($total, $fail, $success, $contactsNew) = 
                    $this->getClient()->postContacts(
                        $contacts, Professio_BudgetMailer_Model_Client::BULK_INS
                    );
                
                if (isset($contactsNew->Success) && is_array($contactsNew->Success)) {
                    foreach ($contactsNew->Success as $contact) {
                        Mage::getModel('budgetmailer/importer')
                            ->importContact($contact);
                    }
                }
            }
        } catch (Exception $e) {
            $this->log(
                'budgetmailer/exporter::exportSubscribersPage() failed '
                . 'with exception: ' . $e->getMessage()
            );
            
            Mage::logException($e);
        }
        
        $this->log(
            'budgetmailer/exporter::exportSubscribersPage() end (total: ' 
            . $total . ', fail: ' . $fail . ', success: ' . $success
        );
        
        return array($total, $fail, $success);
    }
    
    public function exportUnregistered($subscribe = true)
    {
        $this->log('budgetmailer/exporter::exportUnregistered() start');
        
        $totals = array('total' => 0, 'fail' => 0, 'success' => 0);
        
        try {
            $total = $this->getUnregisteredCollection()->getSize();

            if ($total > 0) {
                $page = 1;
                $pages = ceil(
                    $total / Professio_BudgetMailer_Model_Client::LIMIT
                );

                $this->log(
                    'budgetmailer/exporter::exportUnregistered() '
                    . 'total: ' . $total . ', pages: ' 
                    . $pages
                );

                do {
                    $this->getUnregisteredCollection()->clear();
                    $this->getUnregisteredCollection()->setCurPage($page);
                    $this->getUnregisteredCollection()->load();
        
                    list($total, $fail, $success) = 
                        $this->exportUnregisteredPage($subscribe);
                    
                    $totals['total'] += $total;
                    $totals['fail'] += $fail;
                    $totals['success'] += $success;
                    
                    $page++;
                } while ($page <= $pages);
            } else {
                $this->log(
                    'budgetmailer/exporter::exportUnregistered() no unregistered orders'
                );
            }
        } catch(Exception $e) {
            $this->log(
                'budgetmailer/exporter::exportUnregistered() failed '
                . 'with exception: ' . $e->getMessage()
            );
            
            Mage::logException($e);
        }
        
        $this->log('budgetmailer/exporter::exportUnregistered() end');
        
        return $totals;
    }
    
    public function exportUnregisteredPage($subscribe = true)
    {
        $this->log('budgetmailer/exporter::exportUnregisteredPage() start');

        try {
            $contacts = $emails = array();
            $total = $fail = $success = 0;

            foreach (
                $this->getUnregisteredCollection()->getIterator() as $order
            ) {
                $contact = $this->getContact();
                $contact->loadByEmail($order->getCustomerOrder(), false);

                if (!$contact->getEntityId()) {
                    $this->getMapper()->orderToModel($order, $contact);

                    $address = $order->getBillingAddress();

                    if ($address && $address->getEntityId()) {
                        $this->getMapper()->addressToModel($address, $contact);
                    }

                    $contact->setUnsubscribed(!$subscribe);
                    $contact->setSubscribe($subscribe);
                    
                    if (Mage::helper('budgetmailer/config')->isAdvancedOnOrderEnabled()) {
                        $tags = $this->getHelper()->getOrderTags($order);
                        $contact->setTags($tags);
                    }
                    
                    $emails[] = $contact->getEmail();
                } else {
                    $contact->setUnsubscribed(!$subscribe);
                    $contact->setSubscribe($subscribe);
                }

                $contacts[] = $this->getMapper()->contactToApi($contact);
            }

            if (count($contacts)) {
                list($total, $fail, $success, $contactsNew) = 
                    $this->getClient()->postContacts(
                        $contacts, Professio_BudgetMailer_Model_Client::BULK_INS
                    );
                
                if (isset($contactsNew->Success) && is_array($contactsNew->Success)) {
                    foreach ($contactsNew->Success as $contact) {
                        Mage::getModel('budgetmailer/importer')
                            ->importContact($contact);
                    }
                }
            }
        } catch (Exception $e) {
            $this->log(
                'budgetmailer/exporter::exportUnregisteredPage() failed '
                . 'with exception: ' . $e->getMessage()
            );
            
            Mage::logException($e);
        }
        
        $this->log(
            'budgetmailer/exporter::exportUnregisteredPage() end (total: ' 
            . $total . ', fail: ' . $fail . ', success: ' . $success
        );
        
        return array($total, $fail, $success);
    }
    
    /**
     * Custom log wrapper - log only in developer mode
     * 
     * @param string $message
     */
    protected function log($message)
    {
        if (Mage::getIsDeveloperMode()) {
            Mage::log($message);
        }
    }
}
