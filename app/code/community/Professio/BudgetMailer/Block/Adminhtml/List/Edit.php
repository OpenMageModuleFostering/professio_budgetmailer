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
 * List admin edit form
 *
 * @category    Professio
 * @package     Professio_BudgetMailer
 */
class Professio_BudgetMailer_Block_Adminhtml_List_Edit
extends Mage_Adminhtml_Block_Widget_Form_Container
{
    /**
     * Constructor
     * 
     * @access public
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        
        $this->_blockGroup = 'budgetmailer';
        $this->_controller = 'adminhtml_list';
        
        $this->_updateButton(
            'save', 'label', Mage::helper('budgetmailer')->__('Save List')
        );
        $this->_updateButton(
            'delete', 'label', Mage::helper('budgetmailer')->__('Delete List')
        );
        
        $this->_addButton(
            'saveandcontinue', array(
                'label'        => Mage::helper('budgetmailer')
                                    ->__('Save And Continue Edit'),
                'onclick'    => 'saveAndContinueEdit()',
                'class'        => 'save',
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
     * get the edit form header
     * 
     * @return string
     */
    public function getHeaderText()
    {
        if (Mage::registry('current_list') 
            && Mage::registry('current_list')->getId()) {
            return Mage::helper('budgetmailer')
                ->__(
                    "Edit List '%s'", 
                    $this->escapeHtml(Mage::registry('current_list')->getName())
                );
        } else {
            return Mage::helper('budgetmailer')->__('Add List');
        }
    }
}
