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
class Professio_BudgetMailer_Model_Contact extends Mage_Core_Model_Abstract
{
    /**
     * Entity code.
     * Can be used as part of method name for entity processing
     */
    const ENTITY = 'budgetmailer_contact';
    const CACHE_TAG = 'budgetmailer_contact';

    /**
     * Prefix of model events names
     * @var string
     */
    protected $_eventPrefix = 'budgetmailer_contact';

    /**
     * Parameter name in event
     * @var string
     */
    protected $_eventObject = 'contact';

    /**
     * Current customer
     * @var Mage_Customer_Model_Customer
     */
    protected $_customer;
    
    /**
     * Constructor
     */
    public function _construct()
    {
        parent::_construct();
        
        $this->_init('budgetmailer/contact');
    }

    /**
     * Before save contact
     * 
     * @return Professio_BudgetMailer_Model_Contact
     */
    protected function _beforeSave()
    {
        parent::_beforeSave();
        
        $now = Mage::getSingleton('core/date')->gmtDate();
        
        if ($this->isObjectNew()) {
            $this->setCreatedAt($now);
        }
        
        $this->setUpdatedAt($now);
        
        if (!is_array($this->getTags())) {
            $this->setTags(array());
        }
        
        $this->setTags(json_encode($this->getTags()));
        
        return $this;
    }

    /**
     * After load  - decode json encoded tags to array
     * 
     * @return Professio_BudgetMailer_Model_Contact
     */
    protected function _afterLoad()
    {
        parent::_afterLoad();
        
        $this->setTags($this->decodeTags($this->getTags()));
        
        return $this;
    }

    /**
     * Retrieve parent 
     * 
     * @return null|Professio_BudgetMailer_Model_List
     */
    public function getParentList()
    {
        if (!$this->hasData('_parent_list')) {
            if (!$this->getListId()) {
                return null;
            } else {
                $list = Mage::getModel('budgetmailer/list')
                    ->load($this->getListId());
                
                if ($list->getId()) {
                    $this->setData('_parent_list', $list);
                } else {
                    $this->setData('_parent_list', null);
                }
            }
        }
        
        return $this->getData('_parent_list');
    }

    /**
     * Get default values
     * 
     * @return array
     */
    public function getDefaultValues()
    {
        $values = array();
        $values['status'] = 1;
        
        return $values;
    }
    
    /**
     * Delete override, allows delete contact from API
     * 
     * @param boolean $fromApi true = delete from API , false = do nothing
     * 
     * @return Professio_BudgetMailer_Model_Contact
     */
    public function delete($fromApi = true)
    {
        // INFO ensure we have loaded email... 
        if (empty($this->getEmail())) { 
            $this->load($this->getEntityId());
        }
        
        // INFO deleting local before API
        parent::delete();
        
        if ($fromApi) {
            $this->deleteFromApi();
        }
        
        return $this;
    }
    
    /**
     * Load method override ... automatically loading record from API 
     * if doesn't exist locally 
     * 
     * @param integer $id 
     * @param mixed $field
     */
    public function load($id, $field = null)
    {
        Mage::log('budgetmailer/contact::load()');
        
        parent::load($id, $field);
        
        Mage::log(
            'budgetmailer/contact::load() orig data: ' 
            . json_encode($this->getOrigData())
        );
        
        if (is_null($this->getOrigData())) {
            Mage::log('budgetmailer/contact::load() setting original data.');
            $this->setOrigData();
        }
        
        $ttl = time() - Mage::helper('budgetmailer/config')->getSyncTtl();
        $updatedAt = strtotime($this->getData('updated_at'));
        
        if (!$updatedAt ||  $updatedAt < $ttl) {
            $this->loadFromApi();
            
            Mage::log(
                'budgetmailer/contact::load() after load from api: ' 
                . json_encode($this->getData())
            );
            
            if ($this->getBudgetmailerId()) {
                $this->save(false);
            }
        } else {
            Mage::log('budgetmailer/contact::load() no api load');
        }
    }
    
    /**
     * Save method override - allows save contact to API
     * 
     * @param boolean $useApi true = save to API, false = don't save to API
     */
    public function save($useApi = true)
    {
        $changed = false;
        $hasId = $this->getEntityId();
        
        if (!is_null($this->getOrigData())) {
            foreach ($this->getData() as $k => $v) {
                $changed = $this->dataHasChangedFor($k);

                // decode orig value of tags field
                if ($changed && 'tags' == $k) {
                    $ov = $this->getOrigData($k);
                    $ov = $this->decodeTags($ov);
                    $v = $this->decodeTags($v);
                    
                    $changed = $ov != $v;
                }
                
                if ($changed) {
                    Mage::log(
                        'budgetmailer/contact::save() changed key: ' 
                        . $k . ' orig: ' . json_encode($this->getOrigData($k)) 
                        . ', now: ' . json_encode($v)
                    );
                    break;
                }
            }
        } else {
            Mage::log('budgetmailer/contact::save() no orig data...');
            // INFO i guess it's new then?!?!?!
            $changed = true;
        }
        
        Mage::log(
            'budgetmailer/contact::save() changed: ' 
            . ($changed ? 'yes' : 'no') . ', with data: ' 
            . json_encode($this->getData())
        );
        
        if (!$hasId && $this->getCustomerId()) {
            Mage::log('budgetmailer/contact::save() no id.');
            
            $customer = Mage::getModel('customer/customer')
                ->load($this->getCustomerId());
            $tags = Mage::helper('budgetmailer')
                ->getCategoryNamesOfOrderedProducts($customer);
            
            Mage::log('budgetmailer/contact::save() tags: ' . json_encode($tags));
            
            if (count($tags)) {
                $this->setTags($tags);
            }
        } else {
            Mage::log('budgetmailer/contact::save() has id.');
        }
        
        if ($useApi && $changed) {
            $this->saveToApi();
        }
        
        parent::save();
    }
    
    /**
     * Get BudgetMailer client
     * 
     * @return Professio_BudgetMailer_Model_Client
     */
    protected function getClient()
    {
        return Mage::getSingleton('budgetmailer/client');
    }
    
    /**
     * Get customer for this contact
     * 
     * @return Mage_Customer_Model_Customer
     */
    protected function getCustomer()
    {
        if (!isset($this->_customer)) {
            if (isset($this->_data['customer_id'])) {
                $this->_customer = Mage::getModel('customer/customer')
                    ->load($this->_data['customer_id']);
            } elseif (isset($this->_data['email'])) {
                $this->_customer = Mage::getModel('customer/customer')
                    ->loadByEmail($this->_data['email']);
            }
        }
        
        return $this->_customer;
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
     * Load contact by budgetmailer id
     * 
     * @param string $budgetmailerId 
     * @param boolean $useApi true = try to load from API
     * 
     * @return Professio_BudgetMailer_Model_Contact
     */
    public function loadByBudgetMailerId($budgetmailerId, $useApi = true)
    {
        $this->_getResource()->loadByBudgetMailerId($this, $budgetmailerId);
        
        $ttl = time() - Mage::helper('budgetmailer/config')->getSyncTtl();
        
        // not found try to load from API
        if ($useApi && (
                    !$this->getData('updated_at') 
                    || $this->getData('updated_at') < $ttl
                )
            ) {
            $this->setBudgetmailerId($budgetmailerId);
            $this->load($this->getId());
        }
        
        return $this;
    }
    
    /**
     * Load by customer
     * 
     * @param Mage_Customer_Model_Customer $customer
     * @param boolean $useApi true = try to load from API
     * 
     * @return Professio_BudgetMailer_Model_Contact
     */
    public function loadByCustomer($customer, $useApi = true)
    {
        if (!$customer->getEntityId()) {
            $this->setData(array());
            
            return $this;
        }
        
        $this->_getResource()->loadByCustomer($this, $customer);
        
        $ttl = time() - Mage::helper('budgetmailer/config')->getSyncTtl();
        
        // not found try to load from API
        if ($useApi && (
                    !$this->getData('updated_at') 
                    || $this->getData('updated_at') < $ttl
                )
            ) {
            $this->setEmail($customer->getEmail());
            $this->setCustomerId($customer->getId());
            $this->load($this->getId());
        }
        
        return $this;
    }

    /**
     * Load by email
     * @param string $email
     * @param boolean $useApi true = try to load from API
     * @return Professio_BudgetMailer_Model_Contact
     */
    public function loadByEmail($email, $useApi = true)
    {
        $this->_getResource()->loadByEmail($this, $email);
        
        $ttl = time() - Mage::helper('budgetmailer/config')->getSyncTtl();
        
        // not found try to load from API
        if ($useApi && (
                    !$this->getData('updated_at') 
                    || $this->getData('updated_at') < $ttl
                )
            ) {
            $this->setEmail($email);
            $this->load($this->getId());
        }
        
        return $this;
    }

    /**
     * Load by subscriber
     * 
     * @param Mage_Newsletter_Model_Subscriber $subscriber
     * @param boolean $useApi true = try to load from API
     * 
     * @return Professio_BudgetMailer_Model_Contact
     */
    public function loadBySubscriber($subscriber, $useApi = true)
    {
        if ($subscriber->getCustomer() && $subscriber->getCustomer()->getId()) {
            $this->_getResource()
                ->loadByCustomer($this, $subscriber->getCustomer());
        } else {
            $this->_getResource()
                ->loadByEmail($this, $subscriber->getEmail());
        }
        
        $ttl = time() - Mage::helper('budgetmailer/config')->getSyncTtl();
        
        // not found try to load from API
        if ($useApi && (
                    !$this->getData('updated_at') 
                    || $this->getData('updated_at') < $ttl
                )
            ) {
            $this->setEmail($subscriber->getEmail());
            $this->load($this->getId());
        }
        
        return $this;
    }

    /**
     * Delete contact from API
     * 
     * @param null|string $email email or use current contact email
     * 
     * @return boolean
     */
    public function deleteFromApi($email = null)
    {
        $email = is_null($email) ? $this->getEmail() : $email;
        $list = $this->getList() 
            ? $this->getMapper()->listIdToBudgetmailerId($this->getList()) 
            : null;
        
        return $this->getClient()->deleteContact($email, $list);
    }
    
    /**
     * Load contact from API - will use either budgetmailer id or email
     * 
     * @return boolean
     */
    public function loadFromApi()
    {
        if ($this->getBudgetmailerId()) {
            $id = $this->getBudgetmailerId();
        } else if ($this->getEmail()) {
            $id = $this->getEmail();
        } else {
            throw new Exception('Don\'t know how to load contact from API.');
        }
        
        if (!$id) {
            return false;
        }
        
        $contact = $this->getClient()->getContact($id);
        $this->getMapper()->contactToModel($contact, $this);
        
        return true;
    }
    
    /**
     * INFO this method expected subscribe/unsubscribed to be set from outside 
     * 
     * Save contact to API
     * @return boolean
     */
    public function saveToApi()
    {
        Mage::log('budgetmailer/contact::saveToApi() start');
        
        if ($this->getBudgetmailerId()) {
            $id = $this->getBudgetmailerId();
        } else {
            $id = $this->getEmail();
        }
        
        if (!$id) {
            return false;
        }
        
        $contactApi = $this->getClient()->getContact($id);
        $contactApiNew = $this->getMapper()->contactToApi($this, $contactApi);
        
        if (!is_object($contactApi) 
                || !isset($contactApi->id) || !$contactApi->id
            ) {
            Mage::log('budgetmailer/contact::saveToApi(): post');
            
            $this->getMapper()->prepareContactApi($contactApiNew);
            
            Mage::log(
                'budgetmailer/contact::saveToApi(): ' 
                . json_encode($contactApiNew)
            );
            
            $contactApiNew = $this->getClient()->postContact($contactApiNew);
            $this->getMapper()->contactToModel($contactApiNew, $this);
            
            $this->save(false);
        } else {
            Mage::log('budgetmailer/contact::saveToApi(): put');
            
            $list = $this->getList() 
                ? $this->getMapper()->listIdToBudgetmailerId($this->getList()) 
                : null;
        
            $this->getClient()->putContact($id, $contactApiNew, $list);
        }
        
        Mage::log('budgetmailer/contact::saveToApi() end');
    }
    
    /**
     * Add tags to this contact, and save to API
     * 
     * @param array $orderTags
     * @param boolean $useApi true = save to API
     * 
     * @return void
     */
    public function addTags($orderTags, $useApi = true)
    {
        if (!is_array($orderTags) || !count($orderTags)) {
            return;
        }
        
        $newTags = array();
        $tags = $this->getTags();
        
        if (is_array($tags) && count($tags)) {
            foreach ($orderTags as $tag) {
                if (!in_array($tag, $tags)) {
                    $newTags = $orderTags;
                }
            }
        } else {
            $newTags = $orderTags;
        }
        
        if (count($newTags)) {
            $this->setTags(array_merge($tags, $newTags));
            $this->save($useApi);
            
            $this->getClient()->postTags($this->getBudgetmailerId(), $newTags);
        }
    }
    
    /**
     * Make sure tags are decoded
     * @param mixed $tags string or array
     * @return array
     */
    protected function decodeTags($tags)
    {
        if (is_string($tags)) {
            $tags = json_decode($tags);
        } else if (!is_array($tags)) {
            $tags = array();
        }
        
        return $tags;
    }
    
    /**
     * Collect all category names of the previously ordered customer products
     */
    public function collectTags()
    {
        if ($this->getCustomerId()) {
            $customer = Mage::getModel('customer/customer')->load(
                $this->getCustomerId()
            );
            
            $tags = Mage::helper('budgetmailer')
                ->getCategoryNamesOfOrderedProducts($customer);
            
            if (count($tags)) {
                $this->setTags($tags);
            }
        }
    }
}
