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
 * Exporter model
 * 
 * @category   Professio
 * @package    Professio_BudgetMailer
 */
class Professio_BudgetMailer_Model_Exporter
{
    /**
     * List of store ids
     * @var array
     */
    protected $_storeIds;
    
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
     * Get list of store ids
     * @return array
     */
    protected function getStoreIds()
    {
        if (!isset($this->_storeIds)) {
            $stores = Mage::app()->getStores();
            $this->_storeIds = array();

            foreach ($stores as $store) {
                $this->_storeIds[] = $store->getId();
            }
        }
        
        return $this->_storeIds;
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
        
        $storeIds = $this->getStoreIds();
        $totals = array('total' => 0, 'fail' => 0, 'success' => 0);
        
        foreach ($storeIds as $storeId) {
            $this->exportCustomersStore($storeId, $totals);
        }
        
        $this->log('budgetmailer/exporter::exportCustomers() end');
        
        return $totals;
    }
    
    /**
     * Export customes from store
     * @param integer $storeId
     * @param array $totals
     */
    public function exportCustomersStore($storeId, &$totals)
    {
        $this->log(
            'budgetmailer/exporter::exportCustomersStore() start (store id: ' 
            . $storeId . ').'
        );

        try {
            $client = Mage::getSingleton('budgetmailer/client')
                ->getStoreClient($storeId);
            
            $collection = Mage::getModel('customer/customer')
                ->getCollection();
            $collection
                ->addAttributeToSelect('*')
                ->addFieldToFilter('store_id', $storeId)
                ->setPageSize(\BudgetMailer\Api\Client::LIMIT);

            $total = $collection->getSize();
            
            if ($total > 0) {
                $page = 1;
                $pages = ceil($total / \BudgetMailer\Api\Client::LIMIT);

                $this->log(
                    'budgetmailer/exporter::exportCustomersStore() customers: ' 
                    . $total . ', customer pages: ' . $pages
                );
                
                do {
                    $collection->clear();
                    $collection->setCurPage($page);
                    $collection->load();

                    $this->exportCustomersStorePage(
                        $client, $collection, $totals
                    );
                    
                    $page++;
                } while ($page <= $pages);
            } else {
                $this->log(
                    'budgetmailer/exporter::exportCustomersStore() no customers'
                );
            }
        } catch (Exception $e) {
            $this->log(
                'budgetmailer/exporter::exportCustomersStore() failed '
                . 'with exception: ' . $e->getMessage()
            );
            
            Mage::logException($e);
        }
        
        $this->log('budgetmailer/exporter::exportCustomersStore() end');
    }
    
    /**
     * Export one page from store customers
     * @param \BudgetMailer\Api\Client $client
     * @param Mage_Customer_Model_Resource_Customer_Collection $collection
     * @param array $totals
     */
    public function exportCustomersStorePage($client, $collection, &$totals)
    {
        $this->log('budgetmailer/exporter::exportCustomersStorePage() start');

        try {
            $contacts = array();

            foreach (
                $collection->getIterator() as $customer
            ) {
                $contact = new stdClass();
                $this->getMapper()->customerToContact($customer, $contact);
                
                $contact->unsubscribed = false;
                $contact->subscribe = true;
                
                $tags = $this->getHelper()
                    ->getCategoryNamesOfOrderedProducts($customer);
                
                if ($tags) {
                    $contact->tags = $tags;
                }
                
                $contacts[] = $contact;
            }

            if (count($contacts)) {
                list($total, $fail, $success) = 
                    $client->postContactsBulk($contacts);
                
                $totals['total'] += $total;
                $totals['fail'] += $fail;
                $totals['success'] += $success;
            }
        } catch (Exception $e) {
            $this->log(
                'budgetmailer/exporter::exportCustomersStorePage() failed '
                . 'with exception: ' . $e->getMessage()
            );
            
            Mage::logException($e);
        }
        
        $this->log('budgetmailer/exporter::exportCustomersStorePage() end');
    }
    
    /**
     * Export newsletter subscribers
     * @return array
     */
    public function exportSubscribers()
    {
        $this->log('budgetmailer/exporter::exportSubscribers() start');
        
        $storeIds = $this->getStoreIds();
        $totals = array('total' => 0, 'fail' => 0, 'success' => 0);
        
        foreach ($storeIds as $storeId) {
            $this->exportSubscribersStore($storeId, $totals);
        }
        
        $this->log('budgetmailer/exporter::exportSubscribers() end');
        
        return $totals;
    }
    
    /**
     * Export subscribers from store
     * @param integer $storeId
     * @param array $totals
     */
    public function exportSubscribersStore($storeId, &$totals)
    {
        $this->log('budgetmailer/exporter::exportSubscribersStore() start');

        try {
            $client = Mage::getSingleton('budgetmailer/client')
                ->getStoreClient($storeId);
            
            $collection = Mage::getModel('newsletter/subscriber')
                ->getCollection();
            $collection
                ->addFieldToFilter('store_id', $storeId)
                ->setPageSize(\BudgetMailer\Api\Client::LIMIT);

            $total = $collection->getSize();
            
            if ($total > 0) {
                $page = 1;
                $pages = ceil($total / \BudgetMailer\Api\Client::LIMIT);

                $this->log(
                    'budgetmailer/exporter::exportSubscribersStore() '
                    . 'subscribers: ' . $total . ', subscriber pages: ' . $pages
                );
                
                do {
                    $collection->clear();
                    $collection->setCurPage($page);
                    $collection->load();

                    $this->exportSubscribersStorePage(
                        $client, $collection, $totals
                    );
                    
                    $page++;
                } while ($page <= $pages);
            } else {
                $this->log(
                    'budgetmailer/exporter::exportSubscribersStore() no subscr.'
                );
            }
        } catch (Exception $e) {
            $this->log(
                'budgetmailer/exporter::exportSubscribersStore() failed '
                . 'with exception: ' . $e->getMessage()
            );
            
            Mage::logException($e);
        }
        
        $this->log('budgetmailer/exporter::exportSubscribersStore() end');
    }
    
    /**
     * Export subscribers one page from store subscribers
     * @param \BudgetMailer\Api\Client $client
     * @param Mage_Newsletter_Model_Resource_Subscriber_Collection $collection
     * @param array $totals
     */
    public function exportSubscribersStorePage($client, $collection, &$totals)
    {
        $this->log('budgetmailer/exporter::exportSubscribersStorePage() start');
        
        try {
            $contacts = array();

            foreach (
                $collection->getIterator() as $subscriber
            ) {
                $contact = new stdClass();
                $this->getMapper()->subscriberToContact($subscriber, $contact);
                
                $contact->unsubscribed = false;
                $contact->subscribe = true;
                
                $customer = $subscriber->getCustomer();
                
                if ($customer && $customer->getEntityId()) {
                    $this->getMapper()->customerToContact($customer, $contact);
                    
                    $tags = $this->getHelper()
                        ->getCategoryNamesOfOrderedProducts($customer);
                    
                    if ($tags) {
                        $contact->tags = $tags;
                    }
                }
                
                $contacts[] = $contact;
            }

            if (count($contacts)) {
                list($total, $fail, $success) = 
                    $client->postContactsBulk($contacts);
                
                $totals['total'] += $total;
                $totals['fail'] += $fail;
                $totals['success'] += $success;
            }
        } catch (Exception $e) {
            $this->log(
                'budgetmailer/exporter::exportCustomersStorePage() failed '
                . 'with exception: ' . $e->getMessage()
            );
            
            Mage::logException($e);
        }
        
        $this->log('budgetmailer/exporter::exportSubscribersStorePage() end');
    }
    
    /**
     * Export unregistered customers
     * @return array
     */
    public function exportUnregistered()
    {
        $this->log('budgetmailer/exporter::exportUnregistered() start');
        
        $storeIds = $this->getStoreIds();
        $totals = array('total' => 0, 'fail' => 0, 'success' => 0);
        
        foreach ($storeIds as $storeId) {
            $this->exportUnregisteredStore($storeId, $totals);
        }
        
        $this->log('budgetmailer/exporter::exportUnregistered() end');
        
        return $totals;
    }
    
    /**
     * Export unregistered customers from one store
     * @param integer $storeId
     * @param array $totals
     */
    public function exportUnregisteredStore($storeId, &$totals)
    {
        $this->log('budgetmailer/exporter::exportUnregisteredStore() start');

        try {
            $client = Mage::getSingleton('budgetmailer/client')
                ->getStoreClient($storeId);

            $collection = Mage::getModel('sales/order')
                ->getCollection();
            $collection
                ->addAttributeToFilter('customer_id', array('null' => true))
                ->addFieldToFilter('store_id', $storeId)
                ->setPageSize(\BudgetMailer\Api\Client::LIMIT);
            
            // avoid duplicates
            $collection->getSelect()->group('main_table.customer_email');

            $total = $collection->getSize();
            
            if ($total > 0) {
                $page = 1;
                $pages = ceil($total / \BudgetMailer\Api\Client::LIMIT);

                $this->log(
                    'budgetmailer/exporter::exportUnregisteredStore() '
                    . 'orders: ' . $total . ', order pages: ' . $pages
                );
                
                do {
                    $collection->clear();
                    $collection->setCurPage($page);
                    $collection->load();

                    $this->exportUnregisteredStorePage(
                        $client, $collection, $totals
                    );
                    
                    $page++;
                } while ($page <= $pages);
            } else {
                $this->log(
                    'budgetmailer/exporter::exportUnregisteredStore() no orders'
                );
            }
        } catch (Exception $e) {
            $this->log(
                'budgetmailer/exporter::exportUnregisteredStore() failed '
                . 'with exception: ' . $e->getMessage()
            );
            
            Mage::logException($e);
        }
        
        $this->log('budgetmailer/exporter::exportUnregisteredStore() end');
    }
    
    /**
     * Export one page of unregistered customers from one store
     * @param \BudgetMailer\Api\Client $client
     * @param Mage_Sales_Model_Resource_Order_Collection $collection
     * @param array $totals
     */
    public function exportUnregisteredStorePage($client, $collection, &$totals)
    {
        $this->log(
            'budgetmailer/exporter::exportUnregisteredStorePage() start'
        );
        
        try {
            $contacts = array();

            foreach (
                $collection->getIterator() as $order
            ) {
                $contact = new stdClass();
                $this->getMapper()->orderToContact($order, $contact);
                
                $contact->unsubscribed = false;
                $contact->subscribe = true;
                
                $tags = $this->getHelper()->getOrderTags($order);

                if ($tags) {
                    $contact->tags = $tags;
                }
                
                $contacts[] = $contact;
            }

            if (count($contacts)) {
                list($total, $fail, $success) = 
                    $client->postContactsBulk($contacts);
                
                $totals['total'] += $total;
                $totals['fail'] += $fail;
                $totals['success'] += $success;
            }
        } catch (Exception $e) {
            $this->log(
                'budgetmailer/exporter::exportUnregisteredStorePage() failed '
                . 'with exception: ' . $e->getMessage()
            );
            
            Mage::logException($e);
        }
        
        $this->log('budgetmailer/exporter::exportSubscribersStorePage() end');
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
