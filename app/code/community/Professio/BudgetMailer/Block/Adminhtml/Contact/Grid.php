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
 * Conctact edit grid
 *
 * @category    Professio
 * @package     Professio_BudgetMailer
 */
class Professio_BudgetMailer_Block_Adminhtml_Contact_Grid 
extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * @var array columns definition 
     */
    protected $_columnsDefinition = array(
        array(
            'entity_id',
            array(
                'header' => 'Id',
                'index' => 'entity_id',
                'type' => 'number'
            )
        ),
        array(
            'list_id',
            array(
                'header'    => 'List',
                'index'     => 'list_id',
                'type'      => 'options',
                'options'   => array(),
                'renderer'  => 
                    'budgetmailer/adminhtml_helper_column_renderer_parent',
                'params' => array(
                    'id' => 'getListId'
                ),
                'base_link' => 'adminhtml/budgetmailer_list/edit'
            )
        ),
        array(
            'budgetmailer_id', 
            array(
                'header' => 'Budgetmailer ID',
                'index' => 'budgetmailer_id',
                'type' => 'text',
            )
        ),
        array(
            'email', 
            array(
                'header' => 'Email',
                'align' => 'left',
                'index' => 'email',
            )
        ),
        array(
            'company_name', 
            array(
                'header' => 'Company Name',
                'index' => 'company_name',
                'type' => 'text',
            )
        ),
        array(
            'first_name', 
            array(
                'header' => 'First Name',
                'index' => 'first_name',
                'type' => 'text',
            )
        ),
        array(
            'last_name', 
            array(
                'header' => 'Last Name',
                'index' => 'last_name',
                'type' => 'text',
            )
        ),
        array(
            'city', 
            array(
                'header' => 'City',
                'index' => 'city',
                'type' => 'text',
            )
        ),
        array(
            'country_code', 
            array(
                'header' => 'Country Code',
                'index' => 'country_code',
                'type' => 'text',
            )
        ),
        array(
            'unsubscribed',
            array(
                'header' => 'Unsubscribed',
                'index' => 'unsubscribed',
                'type' => 'options',
                'options' => array(
                    '1' => 'Yes',
                    '0' => 'No',
                )
            )
        ),
        array(
            'action', 
            array(
                'header' => 'Action',
                'width' => '100',
                'type' => 'action',
                'getter' => 'getId',
                'actions' => array(
                    array(
                        'caption' => 'Edit',
                        'url' => array('base' => '*/*/edit'),
                        'field' => 'id'
                    )
                ),
                'filter' => false,
                'is_system' => true,
                'sortable' => false,
            )
        )
    );
    
    /**
     * Constructor
     * 
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        
        $this->setId('contactGrid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    /**
     * Prepare collection
     * 
     * @return Professio_BudgetMailer_Block_Adminhtml_Contact_Grid
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getModel('budgetmailer/contact')->getCollection();
        $this->setCollection($collection);
        
        return parent::_prepareCollection();
    }

    /**
     * Prepare grid collection
     * 
     * @return Professio_BudgetMailer_Block_Adminhtml_Contact_Grid
     */
    protected function _prepareColumns()
    {
        foreach ($this->_columnsDefinition as $column) {
            if (isset($column[1]['header'])) {
                $column[1]['header'] = Mage::helper('budgetmailer')
                    ->__($column[1]['header']);
            }
            
            $this->addColumn($column[0], $column[1]);
        }
        
        $this->getColumn('list_id')->setData(
            'options',
            Mage::getResourceModel('budgetmailer/list_collection')
                ->toOptionHash()
        );
        
        return parent::_prepareColumns();
    }

    /**
     * Prepare mass action
     * 
     * @return Professio_BudgetMailer_Block_Adminhtml_Contact_Grid
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('contact');
        
        $this->getMassactionBlock()->addItem(
            'delete', 
            array(
                'label' => Mage::helper('budgetmailer')->__('Delete'),
                'url' => $this->getUrl('*/*/massDelete'),
                'confirm' => Mage::helper('budgetmailer')->__('Are you sure?')
            )
        );
        
        $this->getMassactionBlock()->addItem(
            'unsubscribed', array(
            'label' => Mage::helper('budgetmailer')->__('Change Unsubscribed'),
            'url' => $this->getUrl(
                '*/*/massUnsubscribed', 
                array('_current' => true)
            ),
            'additional' => array(
                'flag_unsubscribed' => array(
                    'name' => 'flag_unsubscribed',
                    'type' => 'select',
                    'class' => 'required-entry',
                    'label' => Mage::helper('budgetmailer')->__('Unsubscribed'),
                    'values' => array(
                        '1' => Mage::helper('budgetmailer')->__('Yes'),
                        '0' => Mage::helper('budgetmailer')->__('No'),
                    )
                )
            )
            )
        );
        
        $values = Mage::getResourceModel('budgetmailer/list_collection')
            ->toOptionHash();
        $values = array_reverse($values, true);
        $values[''] = '';
        $values = array_reverse($values, true);
        
        return $this;
    }

    /**
     * Get the row url
     * 
     * @param Professio_BudgetMailer_Model_Contact
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
        return $this->getUrl('*/*/grid', array('_current' => true));
    }

    /**
     * After collection load
     * 
     * @return Professio_BudgetMailer_Block_Adminhtml_Contact_Grid
     */
    protected function _afterLoadCollection()
    {
        $this->getCollection()->walk('afterLoad');
        
        parent::_afterLoadCollection();
    }
}
