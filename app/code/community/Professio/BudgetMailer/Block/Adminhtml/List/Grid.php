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
 * List admin grid block
 *
 * @category    Professio
 * @package     Professio_BudgetMailer
 */
class Professio_BudgetMailer_Block_Adminhtml_List_Grid
extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Constructor
     * 
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        
        $this->setId('listGrid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }
    
    /**
     * Prepare collection
     * 
     * @return Professio_BudgetMailer_Block_Adminhtml_List_Grid
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getModel('budgetmailer/list')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }
    
    /**
     * Prepare grid collection
     * 
     * @return Professio_BudgetMailer_Block_Adminhtml_List_Grid
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'entity_id', 
            array(
                'header'    => Mage::helper('budgetmailer')->__('Id'),
                'index'        => 'entity_id',
                'type'        => 'number'
            )
        );
        
        $this->addColumn(
            'name', 
            array(
                'header'    => Mage::helper('budgetmailer')->__('Name'),
                'align'     => 'left',
                'index'     => 'name',
            )
        );
        
        $this->addColumn(
            'status', 
            array(
                'header'    => Mage::helper('budgetmailer')->__('Status'),
                'index'        => 'status',
                'type'        => 'options',
                'options'    => array(
                    '1' => Mage::helper('budgetmailer')->__('Enabled'),
                    '0' => Mage::helper('budgetmailer')->__('Disabled'),
                )
            )
        );
        
        $this->addColumn(
            'budgetmailer_id', array(
            'header'=> Mage::helper('budgetmailer')->__('Budgetmailer ID'),
            'index' => 'budgetmailer_id',
            'type'=> 'text',

            )
        );
        
        $this->addColumn(
            'created_at',
            array(
                'header'    => Mage::helper('budgetmailer')->__('Created at'),
                'index'     => 'created_at',
                'width'     => '120px',
                'type'      => 'datetime',
            )
        );
        
        $this->addColumn(
            'updated_at', 
            array(
                'header'    => Mage::helper('budgetmailer')->__('Updated at'),
                'index'     => 'updated_at',
                'width'     => '120px',
                'type'      => 'datetime',
            )
        );
        
        $this->addColumn(
            'action',
            array(
                'header'=>  Mage::helper('budgetmailer')->__('Action'),
                'width' => '100',
                'type'  => 'action',
                'getter'=> 'getId',
                'actions'   => array(
                    array(
                        'caption'   => Mage::helper('budgetmailer')->__('Edit'),
                        'url'   => array('base'=> '*/*/edit'),
                        'field' => 'id'
                    )
                ),
                'filter'=> false,
                'is_system'    => true,
                'sortable'  => false,
            )
        );
        
        return parent::_prepareColumns();
    }
    
    /**
     * Prepare mass action
     * 
     * @return Professio_BudgetMailer_Block_Adminhtml_List_Grid
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('list');
        
        $this->getMassactionBlock()->addItem(
            'delete', 
            array(
                'label'=> Mage::helper('budgetmailer')->__('Delete'),
                'url'  => $this->getUrl('*/*/massDelete'),
                'confirm'  => Mage::helper('budgetmailer')->__('Are you sure?')
            )
        );
        
        $this->getMassactionBlock()->addItem(
            'status', 
            array(
                'label'=> Mage::helper('budgetmailer')->__('Change status'),
                'url'  => $this->getUrl(
                    '*/*/massStatus', 
                    array('_current'=>true)
                ),
                'additional' => array(
                    'status' => array(
                        'name' => 'status',
                        'type' => 'select',
                        'class' => 'required-entry',
                        'label' => Mage::helper('budgetmailer')->__('Status'),
                        'values' => array(
                            '1' => Mage::helper('budgetmailer')->__('Enabled'),
                            '0' => Mage::helper('budgetmailer')->__('Disabled'),
                        )
                    )
                )
            )
        );
        
        return $this;
    }
    
    /**
     * Get the row url
     * 
     * @param Professio_BudgetMailer_Model_List
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }
    
    /**
     * Get the grid url
     * 
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current'=>true));
    }
    
    /**
     * After collection load
     * 
     * @return Professio_BudgetMailer_Block_Adminhtml_List_Grid
     */
    protected function _afterLoadCollection()
    {
        $this->getCollection()->walk('afterLoad');
        
        parent::_afterLoadCollection();
    }
}
