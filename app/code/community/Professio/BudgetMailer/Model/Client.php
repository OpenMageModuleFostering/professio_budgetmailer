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
 * API REST-JSON client
 * 
 * @category   Professio
 * @package    Professio_BudgetMailer
 */
class Professio_BudgetMailer_Model_Client extends Zend_Rest_Client
{
    const LIMIT = 1000;

    /**
     * Caching of API contacts
     * 
     * @var boolean
     */
    protected $_cachingEnabled = true;
    
    /**
     * Contacts memory cache
     * 
     * @var array 
     */
    protected $_contacts = array();
    
    /**
     * Contacts count in last result
     * 
     * @var integer
     */
    protected $_contactsCount;
    
    protected $_totalCount;
    protected $_totalFail;
    protected $_totalSuccess;

    /**
     * API salt
     * @var string 
     */
    protected $_salt;
    
    /**
     * API signature
     * @var string
     */
    protected $_signature;
    
    /**
     * API signature (encoded)
     * @var string
     */
    protected $_signatureEncoded;
    
    /**
     * Constructor
     *
     * @param string|Zend_Uri_Http $uri URI for the web service
     * 
     * @return void
     */
    public function __construct($uri = null)
    {
        if (!empty($uri)) {
            $this->setUri($uri);
        } else {
            $this->setUri($this->getConfigHelper()->getApiEndpoint());
        }
        
        //$this->init();
    }
    
    /**
     * Override original Zend_Rest_Client::_performPost()... 
     * force content-type to application/json.
     *
     * Performs a POST or PUT request. Any data provided is set in the HTTP
     * client. String data is pushed in as raw POST data; array or object data
     * is pushed in as POST parameters.
     *
     * @param mixed $method
     * @param mixed $data
     * 
     * @return Zend_Http_Response
     */
    protected function _performPost($method, $data = null)
    {
        $client = self::getHttpClient();
        
        if (is_string($data)) {
            $client->setRawData($data);
        } elseif (is_array($data) || is_object($data)) {
            $client->setParameterPost((array) $data);
        }
        
        $client->setHeaders('content-type', 'application/json');
        
        return $client->request($method);
    }
    
    /**
     * Translation wrapper
     * 
     * @param string $s
     * 
     * @return string
     */
    protected function __($s)
    {
        return Mage::helper('budgetmailer')->__($s);
    }
    
    /**
     * Get cached record
     * 
     * @param string $id
     * 
     * @return mixed
     */
    protected function getCache($id)
    {
        /*if (isset($this->contacts[$id]) && is_string($this->contacts[$id])) {
            throw new Exception('cache fail');
        }*/
        
        return $this->isCachingEnabled() 
            && isset($this->_contacts[$id]) 
            ? $this->_contacts[$id] : null;
    }
    
    /**
     * Get caching enabled
     * 
     * @return boolean
     */
    protected function isCachingEnabled()
    {
        return $this->_cachingEnabled;
    }
    
    /**
     * Get config helper
     * 
     * @return Professio_BudgetMailer_Helper_Config
     */
    protected function getConfigHelper()
    {
        return Mage::helper('budgetmailer/config');
    }
    
    /**
     * Get default list
     * 
     * @return string
     */
    protected function getList()
    {
        return $this->getConfigHelper()->getGeneralList();
    }
    
    /**
     * Get / generate salt
     * 
     * @return string
     */
    protected function getSalt($reinit = false)
    {
        if (!isset($this->salt) || $reinit) {
            $this->salt = md5(microtime(true));//mt_rand();
        }
        
        return $this->salt;
    }
    
    /**
     * Get / generate signature
     * 
     * @return string
     */
    protected function getSignature($reinit = false)
    {
        if (!isset($this->_signature) || $reinit) {
            $this->_signature = hash_hmac(
                'sha256',
                $this->getSalt($reinit),
                $this->getConfigHelper()->getApiSecret(), 
                true
            );
        }
        
        return $this->_signature;
    }
    
    /**
     * Get encoded signature
     * 
     * @return string
     */
    protected function getSignatureEncoded($reinit = false)
    {
        if (!isset($this->_signatureEncoded) || $reinit) {
            $this->_signatureEncoded = rawurlencode(
                base64_encode($this->getSignature($reinit))
            );
        }
        
        return $this->_signatureEncoded;
    }
    
    /**
     * Initiate the http client
     * 
     * @param boolean $reinit regenerate signature
     * 
     * @return void
     */
    protected function init($reinit = false)
    {
        $headers = array(
            'Accept' => 'application/json',
            'apikey' => $this->getConfigHelper()->getApiKey(),
            'apisecret' => $this->getConfigHelper()->getApiSecret(),
            'Content-Type' => 'application/json',
            // INFO this will regenerate salt if needed
            'signature' => $this->getSignatureEncoded($reinit),
            'salt' => $this->getSalt(),
        );
        
        $this->getHttpClient()->setHeaders($headers);
        
        $this->log(
            'budgetmailer/client::init() re: ' . ($reinit ? 'yes' : 'no')
            . ' headers: ' . json_encode($headers)
        );
    }
    
    /**
     * Check if memory cache is not too large
     */
    protected function limitCache()
    {
        if (count($this->_contacts) > self::LIMIT) {
            $this->_contacts = array();
        }
    }
    
    /**
     * Custom log wrapper - log only if developer mode
     * 
     * @param string $message
     */
    protected function log($message)
    {
        if (Mage::getIsDeveloperMode()) {
            Mage::log($message);
        }
    }
    
    /**
     * Set cache 
     * 
     * @param mixed $data
     * @param string $id
     * 
     * @return Professio_BudgetMailer_Model_Client
     */
    protected function setCache($data, $id = null)
    {
        if (!$this->isCachingEnabled()) {
            return $this;
        }
        
        if (!$id) {
            $id = $data && $data->id ? $data->id : null;
        }
        
        if ($id) {
            $this->limitCache();
            
            /*if (is_string($data)) {
                throw new Exception('set cache failed.');
            }*/
            
            $this->_contacts[$id] = $data;
        }
        
        return $this;
    }
    
    /**
     * Delete contact API call
     * 
     * @param string $id email or budgetmailer id
     * @param null|string $list list name or id or null for default 
     * 
     * @return boolean
     * @throws Professio_BudgetMailer_Exception
     */
    public function deleteContact($id, $list = null)
    {
        $this->init(true);
        
        if (is_null($list)) {
            $list = $this->getList();
        }
        
        $path = '/contacts/' . rawurlencode($list) . '/' . rawurlencode($id);
        
        $this->log(
            'budgetmailer/client::deleteContact() path: ' 
            . $path . ', list: ' . $list . ', id: ' . $id
        );
        
        $rs = $this->restDelete($path);
        
        $this->log(
            'budgetmailer/client::deleteContact() result: ' 
            . $rs->getStatus() . ', body: ' . $rs->getBody()
        );
        
        if ($rs->isError()) {
            throw new Professio_BudgetMailer_Exception(
                Mage::helper('budgetmailer')
                ->__('Couldn\'t delete contact from BudgetMailer API.')
            );
        } else {
            if (isset($this->_contacts[$id])) {
                unset($this->_contacts[$id]);
            }
        }
        
        return true;
    }
    
    /**
     * Delete tag from contact API call
     * 
     * @param string $id email or budgetmailer id
     * @param string $tag tag
     * @param null|string $list list name or id or null for default
     * 
     * @return boolean
     * @throws Professio_BudgetMailer_Exception
     */
    public function deleteTag($id, $tag, $list = null)
    {
        $this->init(true);
        
        if (is_null($list)) {
            $list = $this->getList();
        }
        
        $path = '/contacts/' . rawurlencode($list) . '/' 
            . rawurlencode($id) . '/tags/' . rawurlencode($tag);
        
        $this->log(
            'budgetmailer/client::deleteTag() path: ' 
            . $path . ', list: ' . $list . ', id: ' . $id
        );
        
        $rs = $this->restDelete($path);
        
        $this->log(
            'budgetmailer/client::deleteTag() result: ' 
            . $rs->getStatus() . ', body: ' . $rs->getBody()
        );
        
        if ($rs->isError()) {
            throw new Professio_BudgetMailer_Exception(
                Mage::helper('budgetmailer')
                ->__('Couldn\'t delete tag from contact from BudgetMailer API.')
            );
        }
        
        return true;
    }
    
    /**
     * Get single contact from API
     * 
     * @param string $id email or budgetmailer id
     * @param null|string $list list name or id or null for default
     * 
     * @return boolean|Professio_BudgetMailer_Model_Contact
     * @throws Professio_BudgetMailer_Exception
     */
    public function getContact($id, $list = null)
    {
        $this->init(true);
        
        /*try {
            throw new Exception('trace');
        } catch (Exception $e) {
            Mage::logException($e);
        }*/
        
        $contactCache = $this->getCache($id);
        
        if (!is_null($contactCache)) {
            $this->log(
                'budgetmailer/client::getContact() cache hit, result: ' 
                . json_encode($contactCache)
            );
            
            return $contactCache;
        }
        
        if (is_null($list)) {
            $list = $this->getList();
        }
        
        $path = '/contacts/' . rawurlencode($list) . '/' . rawurlencode($id);
        
        $this->log(
            'budgetmailer/client::getContact() path: ' 
            . $path . ', list: ' . $list . ', id: ' . $id
        );
        
        $rs = $this->restGet($path);
        
        $this->log(
            'budgetmailer/client::getContact() result: ' 
            . $rs->getStatus() . ', body: ' . $rs->getBody()
        );
        
        if ($rs->isError()) {
            if (404 == $rs->getStatus()) {
                $contact = false;
            } else {
                throw new Professio_BudgetMailer_Exception(
                    Mage::helper('budgetmailer')
                    ->__('Couldn\'t get the contact from BudgetMailer API.')
                );
            }
        } else {
            $contact = json_decode($rs->getBody());
        }
        
        $this->setCache($contact, $id);
        
        return $contact;
    }
    
    /**
     * Get multiple contacts from API
     * 
     * @param integer $offset
     * @param integer $limit
     * @param string $sort
     * @param boolean $unsubscribed
     * @param null|string $list list name or id or null for default
     * 
     * @return boolean|array
     * @throws Professio_BudgetMailer_Exception
     */
    public function getContacts(
        $offset = 0, 
        $limit = 20, 
        $sort = 'ASC', 
        $unsubscribed = 'False', 
        $list = null
        )
    {
        $this->init(true);
        
        if (is_null($list)) {
            $list = $this->getList();
        }
        
        $path = '/contacts/' . rawurlencode($list) . '/';
        
        $query = array(
            'offset' => $offset,
            'limit' => $limit,
            'sort' => $sort,
            'unsubscribed' => $unsubscribed
        );
        
        foreach ($query as $k => $v) {
            if (is_null($v)) {
                unset($query[$k]);
            }
        }
        
        $this->log(
            'budgetmailer/client::getContacts() path: ' 
            . $path . ', list: ' . $list . ', offset: ' . $offset 
            . ', limit: ' . $limit . ', sort: ' . $sort . ', unsubscribed: ' 
            . ($unsubscribed ? 'yes' : 'no')
        );
        
        $rs = $this->restGet($path, $query);
        
        $this->log(
            'budgetmailer/client::getContacts() result: ' . $rs->getStatus() 
            . ',  headers: ' . json_encode($rs->getHeaders()) 
            . ', body: ' . $rs->getBody()
        );
        
        $this->_contactsCount = $rs->getHeader('X-total-count');
        
        if ($rs->isError()) {
            throw new Professio_BudgetMailer_Exception(
                Mage::helper('budgetmailer')
                ->__('Couldn\'t get contacts from BudgetMailer API.')
            );
        }
        
        $contacts = json_decode($rs->getBody());
        
        return $contacts;
    }
    
    /**
     * Get total contacts count
     * 
     * @return integer
     */
    public function getContactsCount()
    {
        if (!isset($this->_contactsCount)) {
            $this->getContacts(0, 1);
        }
        
        return $this->_contactsCount;
    }
    
    /**
     * Get budgetmailer lists API call
     * 
     * @return boolean|array
     * @throws Professio_BudgetMailer_Exception
     */
    public function getLists()
    {
        $this->init(true);
        
        $path = '/lists';
        
        $this->log('budgetmailer/client::getLists() path: ' . $path);
        
        $rs = $this->restGet($path);
        
        $this->log(
            'budgetmailer/client::getLists() result: ' 
            . $rs->getStatus() . ', body: ' . $rs->getBody()
        );
        
        if ($rs->isError()) {
            throw new Professio_BudgetMailer_Exception(
                Mage::helper('budgetmailer')
                ->__('Couldn\'t get the lists from BudgetMailer API.')
            );
        } else {
            $lists = json_decode($rs->getBody());
        }
        
        return $lists;
    }
    
    /**
     * Get tags for contact API call
     * 
     * @param string $id email or budgetmailer id
     * @param null|string $list list name, id, or null for default
     * 
     * @return boolean|array
     * @throws Professio_BudgetMailer_Exception
     */
    public function getTags($id, $list = null)
    {
        $this->init(true);
        
        if (is_null($list)) {
            $list = $this->getList();
        }
        
        $path = '/contacts/' . rawurlencode($list) 
            . '/' . rawurlencode($id) . '/tags';
        
        $this->log(
            'budgetmailer/client::getTags() path: ' 
            . $path . ', list: ' . $list . ', id: ' . $id
        );
        
        $rs = $this->restGet($path);
        
        $this->log(
            'budgetmailer/client::getTags() result: ' 
            . $rs->getStatus() . ', body: ' . $rs->getBody()
        );
        
        if ($rs->isError()) {
            throw new Professio_BudgetMailer_Exception(
                Mage::helper('budgetmailer')
                ->__('Couldn\'t get tags from BudgetMailer API.')
            );
        }
        
        return json_decode($rs->getBody());
    }
    
    /**
     * Insert new contact ot API
     * 
     * @param object $contact contact to save
     * @param null|string $list list name, id, or null for default
     * 
     * @return boolean|object false or returned record from API
     * @throws Professio_BudgetMailer_Exception
     */
    public function postContact($contact, $list = null)
    {
        $this->init(true);
        
        if (is_null($list)) {
            $list = $this->getList();
        }
        
        $path = '/contacts/' . rawurlencode($list);
        $data = json_encode($contact);
        
        $this->log(
            'budgetmailer/client::postContact() path: ' 
            . $path . ', list: ' . $list . ', contact: ' . $data
        );
        
        $rs = $this->restPost($path, $data);
        
        $this->log(
            'budgetmailer/client::postContact() result: ' 
            . $rs->getStatus() . ', body: ' . $rs->getBody()
        );
        
        if ($rs->isError()) {
            throw new Professio_BudgetMailer_Exception(
                Mage::helper('budgetmailer')
                ->__('Couldn\'t subscribe contact to BudgetMailer API.')
            );
        }
        
        $contact = json_decode($rs->getBody());
        
        if ($contact) {
            $this->setCache($contact);
        }
        
        return $contact;
    }
    
    /**
     * Insert multiple contacts to API
     * 
     * @param array $contacts array of contact objects
     * @param null|string $list list name, id, or null for default
     * 
     * @return boolean
     * @throws Professio_BudgetMailer_Exception
     */
    public function postContacts($contacts, $list = null)
    {
        $this->init(true);
        
        if (is_null($list)) {
            $list = $this->getList();
        }
        
        $path = '/contacts/' . rawurlencode($list) . '/bulk';
        $data = json_encode($contacts);
        
        $this->log(
            'budgetmailer/client::postContacts() path: ' 
            . $path . ', list: ' . $list . ', contact: ' . $data
        );
        
        $rs = $this->restPost($path, $data);
        
        $this->log(
            'budgetmailer/client::postContacts() result: ' . $rs->getStatus() 
            . ',  headers: ' . json_encode($rs->getHeaders()) 
            . ', body: ' . $rs->getBody()
        );
        
        $this->_totalCount = $rs->getHeader('X-Total-Count');
        $this->_totalFail = $rs->getHeader('X-Total-Fail');
        $this->_totalSuccess = $rs->getHeader('X-Total-Success');
        
        if ($rs->isError()) {
            throw new Professio_BudgetMailer_Exception(
                Mage::helper('budgetmailer')
                ->__('Couldn\'t bulk subscribe contacts to BudgetMailer API.')
            );
        }
        
        return array(
            $this->_totalCount, $this->_totalFail, $this->_totalSuccess, 
            json_decode($rs->getBody())
        );
    }
    
    /**
     * Add tags to contact
     * 
     * @param string $id email or budgetamiler id
     * @param array $tags tags
     * @param null|string $list list name, id, or null for default
     * 
     * @return boolean
     * @throws Professio_BudgetMailer_Exception
     */
    public function postTags($id, $tags, $list = null)
    {
        $this->init(true);
        
        if (is_null($list)) {
            $list = $this->getList();
        }
        
        $path = '/contacts/' . rawurlencode($list) . '/' 
            . rawurlencode($id) . '/tags';
        $data = json_encode($tags);
        
        $this->log(
            'budgetmailer/client::postTags() path: ' 
            . $path . ', list: ' . $list . ', id: ' . $id . ', tags: ' . $data
        );
        
        $rs = $this->restPost($path, $data);
        
        $this->log(
            'budgetmailer/client::postTags() result: ' 
            . $rs->getStatus() . ', body: ' . $rs->getBody()
        );
        
        if ($rs->isError()) {
            throw new Professio_BudgetMailer_Exception(
                Mage::helper('budgetmailer')
                ->__('Couldn\'t post tags to BudgetMailer API.')
            );
        }
        
        return true;
    }
    
    /**
     * Update contact in API
     * 
     * @param string $id email or budgetamiler id
     * @param object $contact contact object
     * @param null|string $list list name, id, or null for default
     * 
     * @return boolean|string
     * @throws Professio_BudgetMailer_Exception
     */
    public function putContact($id, $contact, $list = null)
    {
        $this->init(true);
        
        if (is_null($list)) {
            $list = $this->getList();
        }
        
        $path = '/contacts/' . rawurlencode($list) . '/' . rawurlencode($id);
        $data = json_encode($contact);
        
        $this->log(
            'budgetmailer/client::putContact() path: ' 
            . $path . ', list: ' . $list . ', id: ' . $id . ', contact: ' 
            . $data
        );
        
        $rs = $this->restPut($path, $data);
        
        $this->log(
            'budgetmailer/client::putContact() result: ' 
            . $rs->getStatus() . ', body: ' . $rs->getBody()
        );
        
        if ($rs->isError()) {
            throw new Professio_BudgetMailer_Exception(
                Mage::helper('budgetmailer')
                ->__('Couldn\'t update contact in BudgetMailer API.')
            );
        } else {
            $this->setCache(json_decode($data), $id);
        }
        
        return json_decode($rs->getBody());
    }
    
    /**
     * Test current API credentials
     * @return boolean
     */
    public function testApiCredentials()
    {
        try {
            return $this->getLists();
        } catch (Exception $e) {
            Mage::logException($e);
            
            $this->log(
                'budgetmailer/client::testApiCredentials() failed '
                . 'with exception: ' . $e->getMessage()
            );
        
            return false;
        }
    }
}
