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
 * Customer BudgetMailer tab
 *
 * @category    Professio
 * @package     Professio_BudgetMailer
 */
class Professio_BudgetMailer_Block_Adminhtml_Customer_Tab
extends Mage_Adminhtml_Block_Template
implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    /**
     * Current contact 
     * @var null|stdClass
     */
    protected $_contact;
    
    /**
     * Set the template for the block
     * 
     * @return void
     */
    public function _construct()
    {
        parent::_construct();
        
        $this->setTemplate('budgetmailer/customer/tab.phtml');
    }
    
    /**
     * Retrieve the label used for the tab relating to this block
     *
     * @return string
     */
    public function getTabLabel()
    {
        return $this->__('BudgetMailer Newsletter');
    }
    
    /**
     * Retrieve the title used by this tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return $this->__(
            'Click here to view Budgetmailer subscription and contact '
            . 'information for this customer.'
        );
    }
    
    /**
     * Determines whether to display the tab
     * Add logic here to decide whether you want the tab to display
     *
     * @return bool
     */
    public function canShowTab()
    {
        $customer = Mage::registry('current_customer');
        
        return $customer->getId() > 0;
    }
    
    /**
     * Stops the tab being hidden
     *
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }
    
    /**
     * Defines after which tab, this tab should be rendered
     *
     * @return string
     */
    public function getAfter()
    {
        return 'tags';
    }
    
    /**
     * Get current contact
     * 
     * @return Professio_BudgetMailer_Model_Contact
     */
    protected function getContact()
    {
        if (!isset($this->_contact)) {
            $client = Mage::getSingleton('budgetmailer/client')
                ->getStoreClient($this->getCustomer()->getStoreId());
            
            $this->_contact = $client->getContact(
                $this->getCustomer()->getEmail()
            );
        }
        
        return $this->_contact;
    }
    
    /**
     * Get current contact
     * 
     * @return Mage_Customer_Model_Customer
     */
    protected function getCustomer()
    {
        return Mage::registry('current_customer');
    }
    
    /**
     * Check if current contact is subscribed
     * 
     * @return boolean
     */
    public function isSubscribed()
    {
        $contact = $this->getContact();
        
        if ($contact) {
            return !$contact->unsubscribed;
        }
        
        return false;
    }
}
