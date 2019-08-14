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
 * List edit form tab
 *
 * @category    Professio
 * @package     Professio_BudgetMailer
 */
class Professio_BudgetMailer_Block_Adminhtml_List_Edit_Tab_Form
extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * Prepare the form
     * 
     * @return Professio_BudgetMailer_Block_Adminhtml_List_Edit_Tab_Form
     */
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        
        $form->setHtmlIdPrefix('list_');
        $form->setFieldNameSuffix('list');
        
        $this->setForm($form);
        
        $fieldset = $form->addFieldset(
            'list_form', 
            array('legend' => Mage::helper('budgetmailer')->__('List'))
        );

        $fieldset->addField(
            'budgetmailer_id',
            'text',
            array(
                'label' => Mage::helper('budgetmailer')->__('Budgetmailer ID'),
                'name'  => 'budgetmailer_id',
                'required'  => true,
                'class' => 'required-entry',
            )
        );

        $fieldset->addField(
            'name',
            'text',
            array(
                'label' => Mage::helper('budgetmailer')->__('Name'),
                'name'  => 'name',
                'required'  => true,
                'class' => 'required-entry',
                )
        );
        
        $fieldset->addField(
            'status', 
            'select', 
            array(
                'label' => Mage::helper('budgetmailer')->__('Status'),
                'name'  => 'status',
                'values'=> array(
                    array(
                        'value' => 1,
                        'label' => Mage::helper('budgetmailer')->__('Enabled'),
                    ),
                    array(
                        'value' => 0,
                        'label' => Mage::helper('budgetmailer')->__('Disabled'),
                    ),
                ),
            )
        );
        
        $formValues = Mage::registry('current_list')->getDefaultValues();
        
        if (!is_array($formValues)) {
            $formValues = array();
        }
        
        if (Mage::getSingleton('adminhtml/session')->getListData()) {
            $formValues = array_merge(
                $formValues, 
                Mage::getSingleton('adminhtml/session')->getListData()
            );
            Mage::getSingleton('adminhtml/session')->setListData(null);
        } elseif (Mage::registry('current_list')) {
            $formValues = array_merge(
                $formValues, 
                Mage::registry('current_list')->getData()
            );
        }
        
        $form->setValues($formValues);
        
        return parent::_prepareForm();
    }
}
