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
 * Contact edit tabs
 *
 * @category    Professio
 * @package     Professio_BudgetMailer
 */
class Professio_BudgetMailer_Block_Adminhtml_Contact_Edit_Tabs
extends Mage_Adminhtml_Block_Widget_Tabs
{
    /**
     * Initialize Tabs
     * 
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        
        $this->setId('contact_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('budgetmailer')->__('Contact'));
    }

    /**
     * Before render html
     * 
     * @return Professio_BudgetMailer_Block_Adminhtml_Contact_Edit_Tabs
     */
    protected function _beforeToHtml()
    {
        $this->addTab(
            'form_contact', array(
                'label' => Mage::helper('budgetmailer')->__('Contact'),
                'title' => Mage::helper('budgetmailer')->__('Contact'),
                'content' => $this->getLayout()
                    ->createBlock(
                        'budgetmailer/adminhtml_contact_edit_tab_form'
                    )->toHtml(),
            )
        );
        
        return parent::_beforeToHtml();
    }

    /**
     * Retrieve contact entity
     * 
     * @return Professio_BudgetMailer_Model_Contact
     */
    public function getContact()
    {
        return Mage::registry('current_contact');
    }
}
