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
 * Newsletter management block
 *
 * @category    Professio
 * @package     Professio_BudgetMailer
 */
class Professio_BudgetMailer_Block_Newsletter extends Mage_Core_Block_Template
{
    /**
     * Currenct contact
     * 
     * @var Professio_BudgetMailer_Model_Contact 
     */
    protected $_contact;
    
    /**
     * Constructor... set template
     */
    public function __construct()
    {
        parent::__construct();
        
        $this->setTemplate('budgetmailer/newsletter.phtml');
    }

    /**
     * Get URL for the form
     * 
     * @return string
     */
    public function getAction()
    {
        return $this->getUrl('*/*/save');
    }
    
    /**
     * Get config helper
     * 
     * @return Professio_BudgetMailer_Helper_Config
     */
    public function getConfigHelper()
    {
        return Mage::helper('budgetmailer/config');
    }
    
    /**
     * Get current customer 
     * 
     * @return Mage_Customer_Model_Customer
     */
    protected function getCustomer()
    {
        return Mage::getSingleton('customer/session')->getCustomer();
    }
    
    /**
     * Check if current contact is subscribed
     * 
     * @return boolean
     */
    public function isSubscribed()
    {
        if (!isset($this->isSubscribed)) {
            $contact = Mage::getSingleton('budgetmailer/client')->getClient()
                ->getContact(
                    $this->getCustomer()->getEmail(),
                    $this->getConfigHelper()->getGeneralList()
                );
            
            $this->isSubscribed = $contact ? !$contact->unsubscribed : false;
        }
        
        return $this->isSubscribed;
    }
}
