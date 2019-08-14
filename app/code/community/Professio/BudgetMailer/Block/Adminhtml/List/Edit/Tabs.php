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
 * List admin edit tabs
 *
 * @category    Professio
 * @package     Professio_BudgetMailer
 */
class Professio_BudgetMailer_Block_Adminhtml_List_Edit_Tabs
extends Mage_Adminhtml_Block_Widget_Tabs
{
    /**
     * Constructor
     */
    public function __construct() 
    {
        parent::__construct();
        
        $this->setId('list_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('budgetmailer')->__('List'));
    }
    
    /**
     * Before render html
     * 
     * @return Professio_BudgetMailer_Block_Adminhtml_List_Edit_Tabs
     */
    protected function _beforeToHtml()
    {
        $this->addTab(
            'form_list', array(
            'label'        => Mage::helper('budgetmailer')->__('List'),
            'title'        => Mage::helper('budgetmailer')->__('List'),
            'content'     => $this->getLayout()
                ->createBlock('budgetmailer/adminhtml_list_edit_tab_form')
                ->toHtml(),
            )
        );
        
        return parent::_beforeToHtml();
    }
    
    /**
     * Retrieve list entity
     * 
     * @return Professio_BudgetMailer_Model_List
     */
    public function getList()
    {
        return Mage::registry('current_list');
    }
}
