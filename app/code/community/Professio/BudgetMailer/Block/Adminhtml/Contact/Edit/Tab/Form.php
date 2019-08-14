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
 * Contact edit tab form
 *
 * @category    Professio
 * @package     Professio_BudgetMailer
 */
class Professio_BudgetMailer_Block_Adminhtml_Contact_Edit_Tab_Form
extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * @var array form fields definitions 
     */
    protected $_fields = array(
        array(
            'budgetmailer_id', 
            'text', 
            array(
                'label' => 'Budgetmailer ID',
                'name'  => 'budgetmailer_id',
                //'required'  => true,
                //'class' => 'required-entry',
                'readonly' => true,
            )
        ),
        array(
            'email', 
            'text', 
            array(
                'label' => 'Email',
                'name'  => 'email',
                'required'  => true,
                'class' => 'required-entry',
            )
        ),
        array(
            'company_name', 
            'text', 
            array(
                'label' => 'Company',
                'name'  => 'company_name',
                'required'  => false,
            )
        ),
        array(
            'first_name',
            'text',
            array(
                'label' => 'First Name',
                'name'  => 'first_name',
                'required'  => false,
            )
        ),
        array(
            'insertion', 
            'text', 
            array(
                'label' => 'Insertion',
                'name'  => 'insertion',
                'required'  => false,
            )
        ),
        array(
            'last_name',
            'text',
            array(
                'label' => 'Last Name',
                'name'  => 'last_name',
                'required'  => false,
            )
        ),
        array(
            'sex',
            'select',
            array(
                'label'     => 'Sex',
                'name'      => 'sex',
                'required'  => false,
                'values'    => array(
                    array(
                        'value' => 0, 
                        'label' => ''
                    ),
                    array(
                        'value' => 1, 
                        'label' => 'Male'
                    ),
                    array(
                        'value' => 2, 
                        'label' => 'Female'
                    ),
                ),
            )
        ),
        array(
            'address', 
            'text', 
            array(
                'label' => 'Address',
                'name'  => 'address',
                'required'  => false,
            )
        ),
        array(
            'postal_code', 
            'text',
            array(
                'label' => 'Postal Code',
                'name'  => 'postal_code',
                'required'  => false,
            )
        ),
        array(
            'city',
            'text',
            array(
                'label' => 'City',
                'name'  => 'city',
                'required'  => false,
            )
        ),
        array(
            'country_code',
            'text',
            array(
                'label' => 'Country Code',
                'name'  => 'country_code',
                'required'  => false,
            )
        ),
        array(
            'telephone',
            'text',
            array(
                'label' => 'Telephone',
                'name'  => 'telephone',
                'required'  => false,
            )
        ),
        array(
            'mobile', 
            'text', 
            array(
                'label' => 'Mobile',
                'name'  => 'mobile',
                'required'  => false,
            )
        ),
        array(
            'remarks', 
            'textarea', 
            array(
                'label' => 'Remarks',
                'name'  => 'remarks',
                'required'  => false,
            )
        ),
        array(
            'tags', 
            'budgetmailer_tags', 
            array(
                'label' => 'Tags',
                'name'  => 'tags',
                'required'  => false,
            )
        ),
//        array(
//            'unsubscribed', 
//            'select', 
//            array(
//                'label' => 'Unsubscribed',
//                'name'  => 'unsubscribed',
//                'readonly' => true,
//                'required'  => false,
//                'values'=> array(
//                    array(
//                        'value' => 1,
//                        'label' => 'Yes',
//                    ),
//                    array(
//                        'value' => 0,
//                        'label' => 'No',
//                    ),
//                ),
//            )
//        )
    );
    
    /**
     * Prepare the form
     * 
     * @return Professio_BudgetMailer_Block_Adminhtml_Contact_Edit_Tab_Form
     */
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        
        $form->setHtmlIdPrefix('contact_');
        $form->setFieldNameSuffix('contact');
        $this->setForm($form);
        
        $fieldset = $form->addFieldset(
            'contact_form', 
            array(
                'legend' => Mage::helper('budgetmailer')->__('Contact')
            )
        );
        
        $fieldset->addType(
            'budgetmailer_tags', 
            'Professio_BudgetMailer_Form_Element_Tags'
        );
        
        $values = Mage::getResourceModel('budgetmailer/list_collection')
            ->toOptionArray();
        array_unshift($values, array('label'=>'', 'value'=>''));

        $fieldset->addField(
            'list_id', 
            'hidden', 
            array(
                'name' => 'list_id',
                //'required' => true,
                )
        );

        $subscribe = Mage::registry('current_contact') 
            && Mage::registry('current_contact')->getEntityId() 
            ? !Mage::registry('current_contact')->getUnsubscribed() 
            : false;
        
        $fieldset->addField(
            'subscribe',
            'checkbox',
            array(
                'label' => Mage::helper('budgetmailer')->__('Subscribe'),
                'name' => 'subscribe',
                'value' => 1,
                'checked' => $subscribe
            )
        );
        
        $this->addFields($fieldset);
        
        $formValues = Mage::registry('current_contact')->getDefaultValues();
        
        if (!is_array($formValues)) {
            $formValues = array();
        }
        
        if (Mage::getSingleton('adminhtml/session')->getContactData()) {
            $formValues = array_merge(
                $formValues, 
                Mage::getSingleton('adminhtml/session')->getContactData()
            );
            Mage::getSingleton('adminhtml/session')->setContactData(null);
        } elseif (Mage::registry('current_contact')) {
            $formValues = array_merge(
                $formValues, 
                Mage::registry('current_contact')->getData()
            );
        }
        
        $form->setValues($formValues);
        
        return parent::_prepareForm();
    }
    
    /**
     * Add fields from definition to fieldset
     * @param Varien_Data_Form_Element_Fieldset $fieldset
     */
    protected function addFields($fieldset)
    {
        foreach ($this->_fields as $k => $field) {
            if (isset($field[2]['label'])) {
                $field[2]['label'] = Mage::helper('budgetmailer')
                    ->__($field[2]['label']);
            }
            
            if (isset($field[2]['values'])) {
                foreach ($field[2]['values'] as $k => $v) {
                    if (isset($v['label'])) {
                        $field[2]['values'][$k]['label'] = 
                            Mage::helper('budgetmailer')
                                ->__($v['label']);
                    }
                }
            }
            
            $fieldset->addField($field[0], $field[1], $field[2]);
        }
    }
}
