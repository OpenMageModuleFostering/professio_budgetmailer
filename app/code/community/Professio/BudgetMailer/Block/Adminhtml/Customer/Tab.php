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
     * @var type Professio_BudgetMailer_Model_Contact
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
     * Before to HTML
     * 
     * @return void
     */
    protected function _beforeToHtml() 
    {
        parent::_beforeToHtml();
        
        Mage::register('current_contact', $this->getContact());
        
        $block = $this->getLayout()
            ->createBlock(
                'budgetmailer/adminhtml_contact_edit_tab_form', 
                'budgetmailer_customer_tab_edit'
            );
        $this->append($block, 'budgetmailer_customer_tab_edit');
    }
    
    /**
     * Get current contact
     * 
     * @return Professio_BudgetMailer_Model_Contact
     */
    protected function getContact()
    {
        if (!isset($this->_contact)) {
            $this->_contact = Mage::getModel('budgetmailer/contact');
            $this->_contact->loadByCustomer($this->getCustomer());
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
        return !$this->getContact()->getUnsubscribed();
    }
    
    /**
     * Get current contact tags
     * 
     * @return array
     */
    public function getTags()
    {
        return $this->getContact()->getTags();
    }
}
