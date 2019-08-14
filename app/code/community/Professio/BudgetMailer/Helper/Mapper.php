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
        'company' => 'companyName',
        // INFO for now not replacing multiple to single line
        'street' => 'address', 
        'postcode' => 'postalCode',
        'city' => 'city',
        'country_id' => 'countryCode',
        'telephone' => 'telephone',
    );
    
    /**
     * List of countries by id
     * @var array
     */
    protected $_countriesIdIso = array();
    /**
     * List of countries by ISO3 code
     * @var array
     */
    protected $_countriesIsoId = array();
    
    /**
     * Customer model to API object
     * @var array
     */
    protected $_customerToContact = array(
        'email' => 'email',
        'firstname' => 'firstName',
        'middlename' => 'insertion',
        'lastname' => 'lastName',
        'sex' => 'gender',
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
     * Order to model
     * @var array
     */
    protected $_orderToContact = array(
        'customer_email' => 'email',
        'customer_firstname' => 'firstName',
        'customer_middlename' => 'insertion',
        'customer_lastname' => 'lastName',
    );
    
    /**
     * Map address to API contact object
     * 
     * @param Mage_Customer_Model_Address_Abstract $address
     * @param stdClass $contactApi
     */
    public function addressToContact(
        Mage_Customer_Model_Address_Abstract $address, $contactApi
    )
    {
        if (!is_object($contactApi)) {
            $contactApi = new stdClass();
        }
        
        foreach ($this->_addressToContact as $keyModel => $keyApi) {
            $contactApi->{$keyApi} = $address->getData($keyModel);
        }
        
        $contactApi->countryCode = 
            $this->countryIdToCountryCode($contactApi->countryCode);
        
        return $contactApi;
    }
    
    /**
     * Map country code to country id
     * 
     * @param string $iso
     * 
     * @return string
     */
    public function countryCodeToCountryId($iso)
    {
        if (!isset($this->_countriesIsoId[$iso])) {
            $country = Mage::getModel('directory/country')
                ->loadByCode($iso);
            $this->_countriesIsoId[$iso] = $country->getCountryId();
        }
        
        return $this->_countriesIsoId[$iso];
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
        if (!isset($this->_countriesIdIso[$countryId])) {
            $country = Mage::getModel('directory/country')->load($countryId);
            $this->_countriesIdIso[$countryId] = $country->getIso3Code();
        }
        
        return $this->_countriesIdIso[$countryId];
    }
    
    /**
     * Map customer model to api contact
     * 
     * @param Mage_Customer_Model_Customer $customer
     * @param stdClass $contactApi
     */
    public function customerToContact(
        Mage_Customer_Model_Customer $customer, 
        $contactApi = null
    )
    {
        if (!is_object($contactApi)) {
            $contactApi = new stdClass();
        }
        
        foreach ($this->_customerToContact as $keyModel => $keyApi) {
            $contactApi->{$keyApi} = $customer->getData($keyModel);
        }
        
        if (Mage::helper('budgetmailer/config')
            ->isAdvancedOnAddressUpdateEnabled()) {
            if (Professio_BudgetMailer_Model_Config_Source_Address::BILLING 
                == Mage::helper('budgetmailer/config')->getAdvancedAddressType()
            ) {
                $address = $customer->getDefaultBillingAddress();
            } elseif (
                Professio_BudgetMailer_Model_Config_Source_Address::SHIPPING 
                == Mage::helper('budgetmailer/config')->getAdvancedAddressType()
            ) {
                $address = $customer->getDefaultShippingAddress();
            } else {
                $address = false;
            }

            if ($address && $address->getId()) {
                $this->addressToContact($address, $contactApi);
            }
        }
        
        return $contactApi;
    }
    
    /**
     * Map order to contact
     * @param Mage_Sales_Model_Order $order
     * @param stdClass $contactApi
     * @return \stdClass
     */
    public function orderToContact(
        Mage_Sales_Model_Order $order,
        $contactApi = null
    )
    {
        if (!is_object($contactApi)) {
            $contactApi = new stdClass();
        }
        
        foreach ($this->_orderToContact as $keyOrder => $keyApi) {
            $contactApi->{$keyApi} = $order->getData($keyOrder);
        }
        
        if (Mage::helper('budgetmailer/config')
            ->isAdvancedOnAddressUpdateEnabled()) {
            if (Professio_BudgetMailer_Model_Config_Source_Address::BILLING 
                == Mage::helper('budgetmailer/config')->getAdvancedAddressType()
            ) {
                $address = $order->getBillingAddress();
            } elseif (
                Professio_BudgetMailer_Model_Config_Source_Address::SHIPPING 
                == Mage::helper('budgetmailer/config')->getAdvancedAddressType()
            ) {
                $address = $order->getShippingAddress();
            } else {
                $address = false;
            }

            if ($address && $address->getId()) {
                $this->addressToContact($address, $contactApi);
            }
        }
        
        return $contactApi;
    }
    
    /**
     * Map subscriber to contact
     * @param Mage_Newsletter_Model_Subscriber $subscriber
     * @param stdClass $contactApi
     * @return \stdClass
     */
    public function subscriberToContact(
        Mage_Newsletter_Model_Subscriber $subscriber,
        $contactApi = null
    )
    {
        if (is_null($contactApi)) {
            $contactApi = new stdClass();
        }
        
        $contactApi->email = $subscriber->getSubscriberEmail();
        $contactApi->subscribe = ($subscriber->getSubscriberStatus() == 1);
        $contactApi->unsubscribed = !($subscriber->getSubscriberStatus() == 1);
        
        return $contactApi;
    }
}
