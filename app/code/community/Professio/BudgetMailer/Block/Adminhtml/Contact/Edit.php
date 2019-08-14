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
 * Contact edit
 *
 * @category    Professio
 * @package     Professio_BudgetMailer
 */
class Professio_BudgetMailer_Block_Adminhtml_Contact_Edit
extends Mage_Adminhtml_Block_Widget_Form_Container
{
    /**
     * Constructor
     * 
     * @return void
     */
    public function __construct() 
    {
        parent::__construct();
        
        $this->_blockGroup = 'budgetmailer';
        $this->_controller = 'adminhtml_contact';
        
        $this->_updateButton(
            'save', 
            'label', 
            Mage::helper('budgetmailer')->__('Save Contact')
        );
        
        $this->_updateButton(
            'delete', 
            'label', Mage::helper('budgetmailer')->__('Delete Contact')
        );
        
        $this->_addButton(
            'saveandcontinue',
            array(
                'label' => Mage::helper('budgetmailer')
                    ->__('Save And Continue Edit'),
                'onclick' => 'saveAndContinueEdit()',
                'class' => 'save',
            ), 
            -100
        );
        
        $this->_formScripts[] = "
            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    /**
     * Get the edit form header
     * 
     * @return string
     */
    public function getHeaderText()
    {
        if (Mage::registry('current_contact') 
            && Mage::registry('current_contact')->getId()) {
            return Mage::helper('budgetmailer')
                ->__(
                    "Edit Contact '%s'",
                    $this->escapeHtml(
                        Mage::registry('current_contact')->getEmail()
                    )
                );
        } else {
            return Mage::helper('budgetmailer')->__('Add Contact');
        }
    }
}
