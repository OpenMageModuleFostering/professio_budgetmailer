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
 * Importer model
 * 
 * @category   Professio
 * @package    Professio_BudgetMailer
 */
class Professio_BudgetMailer_Model_Importer
{
    const ACTION_INSERT = 'insert';
    const ACTION_UPDATE = 'update';
    const ACTION_DELETE = 'delete';
    
    /**
     * Cache of all budgetmailer ids in API
     * @var array
     */
    protected $_budgetmailerIds;
    /**
     * Contacts collection
     * @var Professio_BudgetMailer_Model_Resource_Contact_Collection
     */
    protected $_contactsCollection;
    
    /**
     * Get API client
     * 
     * @return Professio_BudgetMailer_Model_Client
     */
    protected function getClient()
    {
        return Mage::getSingleton('budgetmailer/client');
    }
    
    /**
     * Get contact
     * 
     * @return Professio_BudgetMailer_Model_Contact
     */
    protected function getContact()
    {
        return Mage::getModel('budgetmailer/contact');
    }
    
    /**
     * Get contacts collection
     * 
     * @return array
     */
    protected function getContactsCollection()
    {
        if (!isset($this->_contactsCollection)) {
            $this->_contactsCollection = 
                Mage::getModel('budgetmailer/contact')->getCollection();
            $this->_contactsCollection
                ->setPageSize(Professio_BudgetMailer_Model_Client::LIMIT);
            $this->_contactsCollection->load();
        }
        
        return $this->_contactsCollection;
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
     * Get all budgetmailer ids from API
     * 
     * @return array
     */
    protected function getBudgetmailerIds()
    {
        if (!isset($this->_budgetmailerIds)) {
            $this->log('budgetmailer/importer::getBudgetmailerIds() start');

            $this->_budgetmailerIds = array();
            $total = $this->getClient()->getContactsCount();

            if ($total > 0) {
                $page = 0;
                $pages = 
                    ceil($total / Professio_BudgetMailer_Model_Client::LIMIT);

                $this->log(
                    'budgetmailer/importer::deleteOrphans() contact pages: ' 
                    . $pages
                );

                do {
                    try {
                        $contactsApi = $this->getClient()->getContacts(
                            $page * Professio_BudgetMailer_Model_Client::LIMIT, 
                            Professio_BudgetMailer_Model_Client::LIMIT, 
                            'ASC', 
                            null
                        );

                        foreach ($contactsApi as $contactApi) {
                            $this->_budgetmailerIds[$contactApi->id] = true;
                        }

                        $page++;
                    } catch (Exception $e) {
                        Mage::logException($e);
                        $this->log(
                            'budgetmailer/importer::getBudgetmailerIds() page: '
                            . $page . ', failed with exception: ' 
                            . $e->getMessage()
                        );
                    }
                } while ($page < $pages);
            }

            $this->log(
                'budgetmailer/importer::getBudgetmailerIds() end, data: '
                . json_encode(array_keys($this->_budgetmailerIds))
            );
        }
        
        return $this->_budgetmailerIds;
    }
    
    /**
     * Delete single orphans page
     * 
     * @param integer $page
     * 
     * @return array
     */
    protected function deleteOrphansPage()
    {
        $this->log('budgetmailer/importer::deleteOrphansPage() start');
        
        $totals = array(
            'completed' => 0, 'deleted' => 0, 'failed' => 0, 'skipped' => 0
        );
        
        foreach ($this->getContactsCollection()->getIterator() as $contact) {
            try {
                $this->log(
                    'budgetmailer/importer::deleteOrphansPage() '
                    . 'checking budgetmailer id: ' 
                    . $contact->getBudgetmailerId() 
                    . ', has: ' 
                    . ( 
                        $this->hasBudgetmailerId($contact->getBudgetmailerId())
                        ? 'yes' : 'no'
                        )
                );
                
                if (!$this->hasBudgetmailerId($contact->getBudgetmailerId())) {
                    $contact->delete(false);

                    $totals['deleted']++;
                    $this->log(
                        'budgetmailer/importer::deleteOrphansPage() '
                        . 'deleted: ' . $contact->getEntityId()
                    );
                } else {
                    $totals['skipped']++;
                    $this->log(
                        'budgetmailer/importer::deleteOrphansPage() '
                        . 'skipping: ' . $contact->getEntityId()
                    );
                }

                $totals['completed']++;
            } catch(Exception $e) {
                $totals['failed']++;
                $this->log(
                    'budgetmailer/importer::deleteOrphansPage() '
                    . 'failed: ' . $contact->getEntityId() 
                    . ', with exception: ' . $e->getMessage()
                );
                Mage::logException($e);
            }
        }
        
        $this->log('budgetmailer/importer::deleteOrphansPage() start');
        
        return $totals;
    }
    
    /**
     * Delete all orphan models from database
     * 
     * @return array
     */
    public function deleteOrphans()
    {
        $this->log('budgetmailer/importer::deleteOrphans() start');
        
        $total = $this->getContactsCollection()->getSize();
        $totals = array(
            'completed' => 0, 'deleted' => 0, 'failed' => 0, 'skipped' => 0
        );

        if ($total > 0) {
            $page = 0;
            $pages = ceil($total / Professio_BudgetMailer_Model_Client::LIMIT);

            $this->log(
                'budgetmailer/importer::deleteOrphans() local contact pages: ' 
                . $pages
            );
            
            do {
                try {
                    $this->getContactsCollection()->clear();
                    $this->getContactsCollection()->setCurPage($page);
                    $this->getContactsCollection()->load();

                    $rs = $this->deleteOrphansPage();

                    $totals['completed'] += $rs['completed'];
                    $totals['deleted'] += $rs['deleted'];
                    $totals['failed'] += $rs['failed'];
                    $totals['skipped'] += $rs['skipped'];

                    $page++;
                } catch(Exception $e) {
                    $this->log(
                        'budgetmailer/importer::deleteOrphans() failed page: ' 
                        . $page . ', with exception: ' . $e->getMessage()
                    );
                    Mage::logException($e);
                }
            } while ($page <= $pages);
            
        } else {
            $this->log(
                'budgetmailer/importer::deleteOrphans() no local contacts.'
            );
        }
        
        return $totals;
    }
    
    /**
     * Check if budgetmailer id exists
     * 
     * @param string $id
     * 
     * @return boolean
     */
    protected function hasBudgetmailerId($id)
    {
        $this->getBudgetmailerIds();
        
        return isset($this->_budgetmailerIds[$id]);
    }
    
    /**
     * Web hook implementation
     * 
     * @param array $actions actions to process
     * 
     * @return void
     */
    public function hook($actions)
    {
        foreach ($actions as $action) {
            if (is_object($action) && isset($action->action)) {
                $contact = $this->getContact();
                
                switch($action->action) {
                    case self::ACTION_DELETE:
                        $contact->loadByBudgetmailerId($action->contact->id);
                        $contact->delete(false);
                        break;
                    case self::ACTION_INSERT:
                        $this->getMapper()
                            ->contactToModel($action->contact, $contact);
                        $contact->save(false);
                        break;
                    case self::ACTION_UPDATE:
                        $contact->loadByBudgetmailerId($action->contact->id);
                        $this->getMapper()
                            ->contactToModel($action->contact, $contact);
                        $contact->save(false);
                        break;
                    default:
                        throw new Professio_BudgetMailer_Exception(
                            'Unexpected hook action.'
                        );
                        break;
                }
            } else {
                throw new Professio_BudgetMailer_Exception(
                    'Unexpected hook action.'
                );
            }
        }
    }
    
    /**
     * Import single contact from API
     * 
     * @param object $contactApi
     * 
     * @return boolean
     */
    public function importContact($contactApi)
    {
        $this->log('budgetmailer/importer::importContact() start');
        
        // INFO we assume there is only one website
        $websiteId = Mage::helper('budgetmailer')->getDefaultWebsiteId();
        
        $contact = $this->getContact();
        
        $this->log(
            'budgetmailer/importer::importContact() id: ' . $contactApi->id
            . ' email: ' . $contactApi->email
        );
        
        if ($contactApi->email) {
            $contact->loadByEmail($contactApi->email, false);
        }
        
        if (!$contact->getEntityId() && $contactApi->id) {
            $contact->loadByBudgetMailerId($contactApi->id, false);
        }
        
        $this->getMapper()->contactToModel($contactApi, $contact);

        if (!$contact->getCustomerId()) {
            $customer = Mage::getModel('customer/customer')
                ->setWebsiteId($websiteId)->loadByEmail($contactApi->email);

            $this->log(
                'budgetmailer/importer::importContact() email: ' 
                . $contactApi->email 
                . ', customer id: ' 
                . $customer->getEntityId()
            );

            if ($customer->getEntityId()) {
                $contact->setCustomerId($customer->getEntityId());
            }
        }

        $contact->setIsMassupdate(true);
        $contact->save(false);
        
        $this->log('budgetmailer/importer::importContact() end');
        
        return true;
    }
    
    /**
     * Import single page of contacts
     * 
     * @param integer $page
     * 
     * @return array
     */
    protected function importContactsPage($page)
    {
        $this->log(
            'budgetmailer/importer::importContactsPage() start (' 
            . $page . ')'
        );
        
        $completed = $failed = 0;
        
        $contactsApi = $this->getClient()->getContacts(
            $page * Professio_BudgetMailer_Model_Client::LIMIT, 
            Professio_BudgetMailer_Model_Client::LIMIT, 
            'ASC', 
            null
        );
        
        if (is_array($contactsApi) && count($contactsApi)) {
            foreach ($contactsApi as $contactApi) {
                try {
                    $this->importContact($contactApi);
                    
                    $this->log(
                        'budgetmailer/importer::importContactsPage() '
                        . 'saved contact: ' . json_encode($contactApi)
                    );
                } catch(Exception $e) {
                    Mage::logException($e);
                    $this->log(
                        'budgetmailer/importer::importContactsPage() '
                        . 'failed contact: ' . json_encode($contactApi) 
                        . ', with exception: ' . $e->getMessage()
                    );
                    $failed++;
                }

                $completed++;
            }
        }
        
        $this->log(
            'budgetmailer/importer::importContactsPage() end, completed: ' 
            . $completed . ', failed: '.  $failed
        );
        
        return array('completed' => $completed, 'failed' => $failed);
    }
    
    /**
     * Import all contacts from budgetmailer list
     * 
     * @return array
     */
    public function importContacts()
    {
        $this->log('budgetmailer/importer::importContacts() start');
        
        $total = $this->getClient()->getContactsCount();
        $totals = array('completed' => 0, 'failed' => 0);
        
        if ($total > 0) {
            $page = 0;
            $pages = ceil($total / Professio_BudgetMailer_Model_Client::LIMIT);
        
            $this->log(
                'budgetmailer/importer::importContacts() subscriber pages: ' 
                . $pages
            );
        
            do {
                $rs = $this->importContactsPage($page);
                
                $totals['completed'] += $rs['completed'];
                $totals['failed'] += $rs['failed'];
                
                $page++;
            } while ($page < $pages);
        }
        
        $this->log(
            'budgetmailer/importer::importContacts() end: ' 
            . json_encode($totals)
        );
        
        return $totals;
    }
    
    public function deleteContact($contactApi)
    {
        $this->log('budgetmailer/importer::deleteContact() start');
        
        $contact = Mage::getModel('budgetmailer/contact');
        
        if ($contactApi->email) {
            $contact->loadByEmail($contactApi->email, false);
        }
        
        if (!$contact->getEntityId() && $contactApi->id) {
            $contact->loadByBudgetMailerId($contactApi->id, false);
        }
        
        if ($contact->getEntityId()) {
            $contact->delete();
            
            return true;
        }
        
        $this->log('budgetmailer/importer::deleteContact() end');
        
        return false;
    }
    
    /**
     * Custom logging method - logging only if in developer mode
     * 
     * @param string $message
     * 
     * @return void
     */
    protected function log($message)
    {
        if (Mage::getIsDeveloperMode()) {
            Mage::log($message);
        }
    }
}
