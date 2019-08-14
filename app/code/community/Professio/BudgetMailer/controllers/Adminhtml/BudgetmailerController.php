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
 * Back-end controller for customer and newsletter subscriber mass actions
 *
 * @category    Professio
 * @package     Professio_BudgetMailer
 */
class Professio_BudgetMailer_Adminhtml_BudgetmailerController
extends Mage_Adminhtml_Controller_Action
{
    /**
     * Check the permission to run it
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')
            ->isAllowed('budgetmailer/manage');
    }

    /**
     * Get importer model
     * @return Professio_BudgetMailer_Model_Importer
     */
    protected function getImporter()
    {
        return Mage::getSingleton('budgetmailer/importer');
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
    
    /**
     * Delete orphans action
     */
    public function deleteorphansAction()
    {
        try {
            $rs = $this->getImporter()->deleteOrphans();
            $this->getSession()->addSuccess(
                sprintf(
                    $this->__(
                        'Delete orphan contacts finished (completed: %d, '
                        . 'deleted: %d, skipped: %d).'
                    ),
                    $rs['completed'], $rs['deleted'], $rs['skipped']
                )
            );
        } catch (Exception $e) {
            $this->getSession()->addError(
                $this->__('Deleting of orphan contacts failed.')
            );
            Mage::logException($e);
        }
        
        $this->_redirect('*/budgetmailer_contact/index');
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
        
        $this->_redirect('*/budgetmailer_contact/index');
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
                ->addError($this->__('Newsletter export import failed.'));
            Mage::logException($e);
        }
        
        $this->_redirect('*/budgetmailer_contact/index');
    }
    
    /**
     * Import budgetmailer contacts action
     */
    public function importbudgetmailerAction()
    {
        try {
            $rs = $this->getImporter()->importContacts();
            $this->getSession()
                ->addSuccess(
                    sprintf(
                        $this->__(
                            'BudgetMailer contacts import finished (completed: '
                            . '%d, failed: %d).'
                        ), 
                        $rs['completed'], $rs['failed']
                    )
                );
        } catch (Exception $e) {
            $this->getSession()
                ->addError($this->__('BudgetMailer contacts import failed.'));
            Mage::logException($e);
        }
        
        $this->_redirect('*/budgetmailer_contact/index');
    }
    
    /**
     * Mas subscribe customers or subscribers
     */
    public function masssubscribeAction()
    {
        try {
            if ($this->getRequest()->getParam('customer')) {
                $redirect = '*/customer/index';
                $rs = $this->getExporter()->massSubscribeCustomers(
                    $this->getRequest()->getParam('customer'), true
                );
                $message = Mage::helper('budgetmailer')->__(
                    sprintf(
                        'Subscribed %d customer(s) (fail: %d, success: %d).', 
                        $rs['total'], $rs['fail'], $rs['success']
                    )
                );
            } elseif ($this->getRequest()->getParam('subscriber')) {
                $redirect = '*/newsletter_subscriber/index';
                $rs = $this->getExporter()->massSubscribeSubscribers(
                    $this->getRequest()->getParam('subscriber'), true
                );
                $message = Mage::helper('budgetmailer')->__(
                    sprintf(
                        'Subscribed %d subscriber(s) (fail: %d, success: %d).', 
                        $rs['total'], $rs['fail'], $rs['success']
                    )
                );
            } else {
                $rs = false;
                $message = Mage::helper('adminhtml')
                        ->__('Please select some item(s).');
            }
            
            if ($rs) {
                Mage::getSingleton('adminhtml/session')->addSuccess($message);
            } else {
                Mage::getSingleton('adminhtml/session')->addError($message);
            }
            
            if (isset($redirect)) {
                $this->_redirect($redirect);
            }
        } catch(Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }
    }
    
    /**
     * Mass unsubscribe customers or subscribers
     */
    public function massunsubscribeAction()
    {
        try {
            if ($this->getRequest()->getParam('customer')) {
                $redirect = '*/customer/index';
                $rs = $this->getExporter()->massSubscribeCustomers(
                    $this->getRequest()->getParam('customer'), false
                );
                $message = Mage::helper('budgetmailer')->__(
                    sprintf(
                        'Unsubscribed %d customer(s) (fail: %d, success: %d).', 
                        $rs['total'], $rs['fail'], $rs['success']
                    )
                );
            } elseif ($this->getRequest()->getParam('subscriber')) {
                $redirect = '*/newsletter_subscriber/index';
                $rs = $this->getExporter()->massSubscribeSubscribers(
                    $this->getRequest()->getParam('subscriber'), false
                );
                $message = Mage::helper('budgetmailer')->__(
                    sprintf(
                        'Unsubscribed %d subscriber(s) '
                        . '(fail: %d, success: %d).', 
                        $rs['total'], $rs['fail'], $rs['success']
                    )
                );
            } else {
                $rs = false;
                $message = Mage::helper('adminhtml')
                        ->__('Please select some item(s).');
            }
        } catch(Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }
    }
}
