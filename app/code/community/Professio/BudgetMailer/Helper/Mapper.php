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
 * Helper mapper 
 * 
 * @category   Professio
 * @package    Professio_BudgetMailer
 */
class Professio_BudgetMailer_Helper_Mapper extends Mage_Core_Helper_Abstract
{
    /**
     * Address model to contact model
     * @var array 
     */
    protected $_addressToContact = array(
        'companyName' => 'company',
        // INFO for now not replacing multiple to single line
        'address' => 'street', 
        'postalCode' => 'postcode',
        'city' => 'city',
        'countryCode' => 'country_id',
        'telephone' => 'telephone',
    );
    
    /**
     * INFO not mapping names, because they are coming from customer model
     * 
     * Address contact to model
     * @var array
     */
    protected $_addressToModel = array( 
//        'firstname' => 'first_name',
//        'lastname' => 'last_name',
        'company' => 'company_name',
        'city' => 'city',
        'postcode' => 'postal_code',
        'street' => 'address',
        'telephone' => 'telephone',
        'country_id' => 'country_code'
    );
    
    /**
     * Contact to model
     * @var array
     */
    protected $_contactToModel = array(
        'id' => 'budgetmailer_id',
        'list' => 'list_id',
        
        'email' => 'email',
        // INFO not mapping company name, because the editable one is in address
        // kind of need this when saving customer address (on front-end)
        'companyName' => 'company_name', 
        'firstName' => 'first_name',
        'insertion' => 'insertion',
        'lastName' => 'last_name',
        'sex' => 'sex',
        'address' => 'address',
        'postalCode' => 'postal_code',
        'city' => 'city',
        'countryCode' => 'country_code',
        'telephone' => 'telephone',
        'mobile' => 'mobile',
        'remarks' => 'remarks',
        'tags' => 'tags',
        'unsubscribed' => 'unsubscribed',
        'subscribe' => 'subscribe'
    );
    
    /**
     * List of countries by id
     * @var array
     */
    protected $_countriesIdIso3 = array();
    /**
     * List of countries by ISO3 code
     * @var array
     */
    protected $_countriesIso3Id = array();
    
    /**
     * Customer model to API object
     * @var array
     */
    protected $_customerToApi = array(
        'email' => 'email',
        'firstname' => 'firstName',
        'lastname' => 'lastName',
        'sex' => 'gender',
    );
    
    /**
     * Customer to contact model
     * @var array
     */
    protected $_customerToModel = array(
        'email' => 'email',
        'firstname' => 'first_name',
        'lastname' => 'last_name',
        'gender' => 'sex'
    );
    
    /**
     * Map list ids to names
     * @var array
     */
    protected $_listIdToName;
    
    /**
     * Map list names to ids
     * @var array
     */
    protected $_listNameToId;
    
    /**
     * Map list ids to budgetmailer ids
     * @var array
     */
    protected $_listIdToBudgetmailerId;
    
    /**
     * Map list budgetmailer ids to ids
     * @var array
     */
    protected $_listBudgetmailerIdToId;
    
    /**
     * INFO list->primary is not mapped
     * List to model
     * @var array
     */
    protected $_listToModel = array( 
        'id' => 'budgetmailer_id',
        'list' => 'name',
    );
    
    /**
     * Subscriber to model
     * @var array
     */
    protected $_subscriberToModel = array(
        'subscriber_email' => 'email',
        'subscriber_status' => 'unsubscribed',
    );

    /**
     * Map address to API contact object
     * 
     * @param Mage_Customer_Model_Address $address
     * @param stdClass $contactApi
     */
    public function addressToContact(
        Mage_Customer_Model_Address $address, $contactApi
    )
    {
        if (!is_object($contactApi)) {
            $contactApi = new stdClass();
        }
        
        foreach ($this->_addressToContact as $keyApi => $keyModel) {
            $contactApi->{$keyApi} = $address->getData($keyModel);
        }
        
        $contactApi->countryCode = 
            $this->countryIdToCountryCode($contactApi->countryCode);
        
        return $contactApi;
    }
    
    /**
     * Map address model to contact model
     * 
     * @param Mage_Customer_Model_Address $address
     * @param Professio_BudgetMailer_Model_Contact $contact
     */
    public function addressToModel(
        Mage_Customer_Model_Address $address, 
        Professio_BudgetMailer_Model_Contact $contact
    )
    {
        foreach ($this->_addressToModel as $keyAddress => $keyModel) {
            $contact->setData($keyModel, $address->getData($keyAddress));
        }
        
        $contact->setData(
            'country_code', 
            $this->countryIdToCountryCode($contact->getData('country_code'))
        );
    }
    
    /**
     * Map API contact object to address model
     * 
     * @param stdClass $contactApi
     * @param Mage_Customer_Model_Address $address
     */
    public function contactToAddress(
        $contactApi, 
        Mage_Customer_Model_Address $address
    )
    {
        foreach ($this->_addressToContact as $keyApi => $keyModel) {
            if (isset($contactApi->{$keyApi})) {
                $address->setData($keyModel, $contactApi->{$keyApi});
            }
        }
        
        $address->setData(
            'country_id', 
            $this->countryCodeToCountryId($contactApi->countryCode)
        );
    }
    
    /**
     * Map API contact object to contact model
     * 
     * @param stdClass $contactApi
     * @param Professio_BudgetMailer_Model_Contact $contact
     */
    public function contactToModel(
        $contactApi, 
        Professio_BudgetMailer_Model_Contact $contact
    )
    {
        foreach ($this->_contactToModel as $keyApi => $keyModel) {
            if (isset($contactApi->{$keyApi})) {
                $contact->setData($keyModel, $contactApi->{$keyApi});
            }
        }
        
        $contact->setData(
            'list_id', 
            $this->listNameToListId($contact->getData('list_id'))
        );
    }
    
    /**
     * Map contact model to API object
     * 
     * @param Professio_BudgetMailer_Model_Contact $contact
     * @param stdClass $contactApi
     * 
     * @return stdClass
     */
    public function contactToApi(
        Professio_BudgetMailer_Model_Contact $contact, 
        $contactApi = null
    )
    {
        if (!is_object($contactApi)) {
            $contactApi = new stdClass();
        }
        
        foreach ($this->_contactToModel as $keyApi => $keyModel) {
            $contactApi->{$keyApi} = $contact->getData($keyModel);
        }
        
        $contactApi->sex = (int)$contactApi->sex;
        $contactApi->unsubscribed = (bool)$contactApi->unsubscribed;
        
        $contact->setData(
            'list', $this->listIdToName($contact->getData('list'))
        );
        
        return $contactApi;
    }
    
    /**
     * Map country code to country id
     * 
     * @param string $iso3
     * 
     * @return string
     */
    public function countryCodeToCountryId($iso3)
    {
        if (!isset($this->_countriesIso3Id[$iso3])) {
            $country = Mage::getModel('directory/country')
                ->loadByCode($iso3, 3);
            $this->_countriesIso3Id[$iso3] = $country->getId();
        }
        
        return $this->_countriesIso3Id[$iso3];
    }
    
    /**
     * Map country id to country code
     * 
     * @param string $countryId
     * 
     * @return string 
     */
    public function countryIdToCountryCode($countryId)
    {
        if (!isset($this->_countriesIdIso3[$countryId])) {
            $country = Mage::getModel('directory/country')->load($countryId);
            $this->_countriesIdIso3[$countryId] = $country->getIso3Code();
        }
        
        return $this->_countriesIdIso3[$countryId];
    }
    
    /**
     * Map customer model to api contact
     * 
     * @param Mage_Customer_Model_Customer $customer
     * @param stdClass $contactApi
     */
    public function customerToApi(
        Mage_Customer_Model_Customer $customer, 
        $contactApi = null
    )
    {
        if (!is_object($contactApi)) {
            $contactApi = new stdClass();
        }
        
        foreach ($this->_customerToApi as $keyModel => $keyApi) {
            $contactApi->{$keyApi} = $customer->getData($keyModel);
        }
        
        if (Professio_BudgetMailer_Model_Config_Source_Address::BILLING 
            == Mage::helper('budgetmailer/config')->getAdvancedAddressType()) {
            $address = $customer->getDefaultBillingAddress();
        } elseif (Professio_BudgetMailer_Model_Config_Source_Address::SHIPPING 
            == Mage::helper('budgetmailer/config')->getAdvancedAddressType()) {
            $address = $customer->getDefaultShippingAddress();
        } else {
            $address = false;
        }
        
        if ($address && $address->getId()) {
            $this->addressToContact($address, $contactApi);
        }
        
        return $contactApi;
    }
    
    /**
     * Map customer model to contact model
     * 
     * @param Mage_Customer_Model_Customer $customer
     * @param Professio_BudgetMailer_Model_Contact $contact
     */
    public function customerToModel(
        Mage_Customer_Model_Customer $customer, 
        Professio_BudgetMailer_Model_Contact $contact
    )
    {
        foreach ($this->_customerToModel as $keyCustomer => $keyModel) {
            $contact->setData($keyModel, $customer->getData($keyCustomer));
        }
    }
    
    /**
     * Prepare list maps
     */
    public function initListsMap()
    {
        // INFO in case of initiation of lists collection,
        // we don't want to try load list collection again
        if (Mage::registry('budgetmailer_list_initiation')) {
            return;
        }
        
        if (!isset($this->_listIdToName)) {
            $list = Mage::getModel('budgetmailer/list');
            $collection = $list->getCollection();
            $collection->load();
            
            foreach ($collection->getIterator() as $list) {
                $this->_listIdToName[$list->getEntityId()] = $list->getName();
                $this->_listNameToId[$list->getName()] = $list->getEntityId();
                
                $this->_listBudgetmailerIdToId[$list->getBudgetmailerId()] = 
                    $list->getEntityId();
                $this->_listIdToBudgetmailerId[$list->getEntityId()] = 
                    $list->getBudgetmailerId();
            }
        }
    }
    
    /**
     * Map list API object to list model
     * @param stdClass $listApi
     * @param Professio_BudgetMailer_Model_List $list
     */
    public function listToModel(
        $listApi, Professio_BudgetMailer_Model_List $list
    )
    {
        foreach ($this->_listToModel as $keyApi => $keyModel) {
            if (isset($listApi->{$keyApi})) {
                $list->setData($keyModel, $listApi->{$keyApi});
            }
        }
        
        if (0 == (int)$list->getEntityId()) {
            $list->setData(
                'entity_id', 
                $this->listBudgetmailerIdToListId($list->getEntityId())
            );
        }
    }
    
    /**
     * Get list id by name
     * 
     * @param string $name
     * 
     * @return integer
     */
    public function listNameToListId($name)
    {
        $this->initListsMap();
        
        return isset($this->_listNameToId[$name]) 
            ? $this->_listNameToId[$name] : null;
    }
    
    /**
     * Get list id by budgetmailer id
     * 
     * @param string $budgetmailerId
     * 
     * @return integer
     */
    public function listBudgetmailerIdToListId($budgetmailerId)
    {
        $this->initListsMap();
        
        return isset($this->_listBudgetmailerIdToId[$budgetmailerId]) 
            ? $this->_listBudgetmailerIdToId[$budgetmailerId] : null;
    }
    
    /**
     * Get list budgetmailer id by id
     * @param integer $listId
     */
    public function listIdToBudgetmailerId($listId)
    {
        $this->initListsMap();
        
        return isset($this->_listIdToBudgetmailerId[$listId]) 
            ? $this->_listIdToBudgetmailerId[$listId] : null;
    }
    
    /**
     * Get list name by id
     * 
     * @param integer $listId
     * 
     * @return string
     */
    public function listIdToName($listId)
    {
        $this->initListsMap();
        
        return isset($this->_listIdToName[$listId]) 
            ? $this->_listIdToName[$listId] : null;
    }
    
    /**
     * Map list model to API object
     * @param Professio_BudgetMailer_Model_List $list
     * @param stdClass $listApi
     */
    public function listToApi(
        Professio_BudgetMailer_Model_List $list, 
        $listApi = null
    )
    {
        if (!is_object($listApi)) {
            $listApi = new stdClass();
        }
        
        foreach ($this->_listToModel as $keyApi => $keyModel) {
            $listApi->{$keyApi} = $list->getData($keyModel);
        }
        
        $list->setData('id', $this->listIdToBudgetmailerId($listApi->id));
    }
    
    /**
     * Map subscriber model to contact model
     * 
     * @param Mage_Newsletter_Model_Subscriber $subscriber
     * @param Professio_BudgetMailer_Model_Contact $contact
     */
    public function subscriberToModel(
        Mage_Newsletter_Model_Subscriber $subscriber, 
        Professio_BudgetMailer_Model_Contact $contact
    )
    {
        foreach ($this->_subscriberToModel as $keySubscriber => $keyModel) {
            $contact->setData(
                $keyModel, $subscriber->getData($keySubscriber)
            );
            
            $contact->status = !$contact->status;
        }
    }
    
    public function prepareContactApi($contactApiNew)
    {
        foreach ($contactApiNew as $k => $v) {
            if (is_null($v)) {
                unset($contactApiNew->{$k});
            }
        }

        if (isset($contactApiNew->tags) && ( 
                !is_array($contactApiNew->tags) || !count($contactApiNew->tags) 
                || $contactApiNew->tags == '[]'
            ) 
        ) {
            Mage::log('prepare unset tags');
            unset($contactApiNew->tags);
        }

        if (empty($contactApiNew->address)) {
            unset($contactApiNew->address);
        }
    }
}
