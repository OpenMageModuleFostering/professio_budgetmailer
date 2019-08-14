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
 * Admin contact block
 * 
 * @category   Professio
 * @package    Professio_BudgetMailer
 */
class Professio_BudgetMailer_Block_Adminhtml_Contact
extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    /**
     * Contact - prepare widget
     */
    public function __construct()
    {
        $this->_controller = 'adminhtml_contact';
        $this->_blockGroup = 'budgetmailer';
        
        parent::__construct();
        
        $this->_headerText = Mage::helper('budgetmailer')->__('Contact');
        
        unset($this->_buttons[0]['add']);
        
        $this->_addButton(
            'delete_orphans', 
            array(
                'label' => Mage::helper('budgetmailer')
                    ->__('Delete Orphans'),
                'onclick' => 'setLocation(\'' 
                    . $this->getDeleteOrphansUrl() .'\')',
                'class' => '',
            )
        );
        
        $this->_addButton(
            'export_customers', 
            array(
                'label' => Mage::helper('budgetmailer')
                    ->__('Export Magento Customers'),
                'onclick' => 'setLocation(\'' 
                    . $this->getCustomersExportUrl() .'\')',
                'class' => '',
            )
        );
        
        $this->_addButton(
            'export_subscribers', 
            array(
                'label' => Mage::helper('budgetmailer')
                    ->__('Export Magento Newsletter Subscribers'),
                'onclick' => 'setLocation(\'' 
                    . $this->getSubscribersExportUrl() .'\')',
                'class' => '',
            )
        );
        
        $this->_addButton(
            'import_budgetmailer', 
            array(
                'label'     => Mage::helper('budgetmailer')
                    ->__('Import BudgetMailer Contacts'),
                'onclick'   => 'setLocation(\'' 
                    . $this->getBudgetmailerImportUrl() .'\')',
                'class'     => '',
            )
        );
    }
    
    /**
     * Get budgetmailer import url
     * @return string
     */
    protected function getBudgetmailerImportUrl()
    {
        return $this->getUrl('*/budgetmailer/importbudgetmailer');
    }
    
    /**
     * Get customers export url
     * 
     * @return string
     */
    protected function getCustomersExportUrl()
    {
        return $this->getUrl('*/budgetmailer/exportcustomers');
    }
    
    /**
     * Get delete orphans url
     * 
     * @return string
     */
    protected function getDeleteOrphansUrl()
    {
        return $this->getUrl('*/budgetmailer/deleteorphans');
    }
    
    /**
     * Get subscribers export url
     * 
     * @return string
     */
    protected function getSubscribersExportUrl()
    {
        return $this->getUrl('*/budgetmailer/exportsubscribers');
    }
}
