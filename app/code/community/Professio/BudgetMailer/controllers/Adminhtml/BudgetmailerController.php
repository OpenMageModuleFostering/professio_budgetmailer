<?php
/**
 * Professio_BudgetMailer extension
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the MIT License
 * that is bundled with this package in the file LICENSE.
 * It is also available through the world-wide-web at this URL:
 * https://gitlab.com/budgetmailer/budgetmailer-mag1/blob/master/LICENSE
 * 
 * @category       Professio
 * @package        Professio_BudgetMailer
 * @copyright      Copyright (c) 2015 - 2017
 * @license        https://gitlab.com/budgetmailer/budgetmailer-mag1/blob/master/LICENSE
 */

/**
 * Back-end controller for customer and newsletter subscriber mass actions
 *
 * @category    Professio
 * @package     Professio_BudgetMailer
 */
class Professio_BudgetMailer_Adminhtml_BudgetmailerController
extends Mage_Adminhtml_Controller_Action
{
    /**
     * Check if current user is allowed to use this controller.
     * @return boolean
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')
            ->isAllowed('admin/system/convert/professio_budgetmailer');
    }

    /**
     * Get exporter model
     * @return Professio_BudgetMailer_Model_Exporter
     */
    protected function getExporter()
    {
        return Mage::getSingleton('budgetmailer/exporter');
    }
    
    /**
     * Get session
     * @return Mage_Adminhtml_Model_Session
     */
    protected function getSession()
    {
        return Mage::getSingleton('adminhtml/session');
    }
    
    public function indexAction()
    {
        $this->loadLayout();
        
        $this->_title(Mage::helper('budgetmailer')->__('BudgetMailer'))
                ->_title(Mage::helper('budgetmailer')->__('BudgetMailer'));
        
        $this->renderLayout();
    }
    
    /**
     * Export customers action
     */
    public function exportcustomersAction()
    {
        try {
            $rs = $this->getExporter()->exportCustomers();
            $this->getSession()->addSuccess(
                sprintf(
                    $this->__(
                        'Customers export finished (completed: %d, fail: %d, '
                        . 'success: %d).'
                    ), 
                    $rs['total'], $rs['fail'], $rs['success']
                )
            );
        } catch (Exception $e) {
            $this->getSession()
                ->addError($this->__('Customers export failed.'));
            Mage::logException($e);
        }
        
        $this->_redirect('*/budgetmailer/index');
    }
    
    /**
     * Export subscribers action
     */
    public function exportsubscribersAction()
    {
        try {
            $rs = $this->getExporter()->exportSubscribers();
            $this->getSession()
                ->addSuccess(
                    sprintf(
                        $this->__(
                            'Newsletter Subscribers export finished '
                            . '(completed: %d, fail: %d, success: %d).'
                        ),
                        $rs['total'], $rs['fail'], $rs['success']
                    )
                );
        } catch (Exception $e) {
            $this->getSession()
                ->addError($this->__('Subscribers export failed.'));
            Mage::logException($e);
        }
        
        $this->_redirect('*/budgetmailer/index');
    }
    
    /**
     * Export unregistered customers action
     */
    public function exportunregisteredAction()
    {
        try {
            $rs = $this->getExporter()->exportUnregistered();
            $this->getSession()
                ->addSuccess(
                    sprintf(
                        $this->__(
                            'Unregistered customers export finished '
                            . '(completed: %d, fail: %d, success: %d).'
                        ),
                        $rs['total'], $rs['fail'], $rs['success']
                    )
                );
        } catch (Exception $e) {
            $this->getSession()
                ->addError($this->__('Unregistered customers export failed.'));
            Mage::logException($e);
        }
        
        $this->_redirect('*/budgetmailer/index');
    }
}
