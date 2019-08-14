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
 * Implementation of backend-end contact controller (CRUD + mass actions)
 *
 * @category    Professio
 * @package     Professio_BudgetMailer
 */
class Professio_BudgetMailer_Adminhtml_Budgetmailer_ContactController
extends Professio_BudgetMailer_Controller_Adminhtml_BudgetMailer
{

    /**
     * Intiate new contact and try to load it by requested id
     * 
     * @return Professio_BudgetMailer_Model_Contact
     */
    protected function _initContact()
    {
        $contactId = (int) $this->getRequest()->getParam('id');
        $contact = Mage::getModel('budgetmailer/contact');
        
        if ($contactId) {
            $contact->load($contactId);
        }
        
        Mage::register('current_contact', $contact);
        
        return $contact;
    }

    /**
     * Displays list of contacts
     * 
     * @return null
     */
    public function indexAction()
    {
        $this->loadLayout();
        
        $this->_title(Mage::helper('budgetmailer')->__('BudgetMailer'))
                ->_title(Mage::helper('budgetmailer')->__('Contacts'));
        
        $this->renderLayout();
    }

    /**
     * Grid action
     * 
     * @return null
     */
    public function gridAction()
    {
        $this->loadLayout()->renderLayout();
    }

    /**
     * Edit single contact, if doesn't exist redirect to index
     * 
     * @return null
     */
    public function editAction()
    {
        $contactId = $this->getRequest()->getParam('id');
        $contact = $this->_initContact();
        
        if ($contactId && !$contact->getId()) {
            $this->_getSession()->addError(
                Mage::helper('budgetmailer')
                    ->__('This contact no longer exists.')
            );
            $this->_redirect('*/*/');
            
            return;
        }
        
        $data = Mage::getSingleton('adminhtml/session')->getContactData(true);
        
        if (!empty($data)) {
            $contact->setData($data);
        }
        
        Mage::register('contact_data', $contact);
        
        $this->loadLayout();
        $this->_title(Mage::helper('budgetmailer')->__('BudgetMailer'))
                ->_title(Mage::helper('budgetmailer')->__('Contacts'));
        
        if ($contact->getId()) {
            $this->_title($contact->getEmail());
        } else {
            $this->_title(Mage::helper('budgetmailer')->__('Add contact'));
        }
        
        if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled()) {
            $this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
        }
        
        $this->renderLayout();
    }

    /**
     * Create new contact (same as edit)
     */
    public function newAction()
    {
        $this->_forward('edit');
    }

    /**
     * Handle new/edit POST data and save the model
     * 
     * @return null
     */
    public function saveAction()
    {
        if ($data = $this->getRequest()->getPost('contact')) {
            try {
                $data['tags'] = isset($data['tags']) && is_array($data['tags']) 
                    && count($data['tags']) ? $data['tags'] : array();
                $data['unsubscribed'] = !isset($data['subscribe']);
                
                $contact = $this->_initContact();
                $contact->addData($data);
                $contact->save();
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('budgetmailer')->__(
                        'Contact was successfully saved'
                    )
                );
                Mage::getSingleton('adminhtml/session')->setFormData(false);
                
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect(
                        '*/*/edit', array('id' => $contact->getId())
                    );
                    
                    return;
                }
                
                $this->_redirect('*/*/');
                
                return;
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')
                    ->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')
                    ->setContactData($data);
                
                $this->_redirect(
                    '*/*/edit', 
                    array('id' => $this->getRequest()->getParam('id'))
                );
                
                return;
            } catch (Exception $e) {
                Mage::logException($e);
                Mage::getSingleton('adminhtml/session')
                    ->addError(
                        Mage::helper('budgetmailer')
                        ->__('There was a problem saving the contact.')
                    );
                Mage::getSingleton('adminhtml/session')->setContactData($data);
                
                $this->_redirect(
                    '*/*/edit', 
                    array('id' => $this->getRequest()->getParam('id'))
                );
                
                return;
            }
        }
        
        Mage::getSingleton('adminhtml/session')
            ->addError(
                Mage::helper('budgetmailer')
                ->__('Unable to find contact to save.')
            );
        
        $this->_redirect('*/*/');
    }

    /**
     * Delete single contact 
     * 
     * @return null
     */
    public function deleteAction()
    {
        if ($this->getRequest()->getParam('id') > 0) {
            try {
                $contact = Mage::getModel('budgetmailer/contact');
                $contact->setId($this->getRequest()->getParam('id'))->delete();
                
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('budgetmailer')
                    ->__('Contact was successfully deleted.')
                );
                
                $this->_redirect('*/*/');
                
                return;
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError(
                    $e->getMessage()
                );
                $this->_redirect(
                    '*/*/edit', 
                    array('id' => $this->getRequest()->getParam('id'))
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')
                    ->addError(
                        Mage::helper('budgetmailer')
                        ->__('There was an error deleting contact.')
                    );
                $this->_redirect(
                    '*/*/edit', 
                    array('id' => $this->getRequest()->getParam('id'))
                );
                Mage::logException($e);
                
                return;
            }
        }
        
        Mage::getSingleton('adminhtml/session')->addError(
            Mage::helper('budgetmailer')
            ->__('Could not find contact to delete.')
        );
        
        $this->_redirect('*/*/');
    }
    
    /**
     * Delete multiple contacts as mass action
     * 
     * @return null
     */
    public function massDeleteAction()
    {
        $contactIds = $this->getRequest()->getParam('contact');
        
        if (!is_array($contactIds)) {
            Mage::getSingleton('adminhtml/session')
                ->addError(
                    Mage::helper('budgetmailer')
                    ->__('Please select contacts to delete.')
                );
        } else {
            try {
                foreach ($contactIds as $contactId) {
                    $contact = Mage::getModel('budgetmailer/contact');
                    
                    try {
                        $contact->setId($contactId)->delete();
                    } catch(Professio_BudgetMailer_Exception $e) {
                        Mage::getSingleton('adminhtml/session')->addError(
                            Mage::helper('budgetmailer')
                            ->__(
                                'There was an error deleting contacts '
                                . 'from BudgetMailer API.'
                            )
                        );
                        Mage::logException($e);
                    }
                }
                
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('budgetmailer')
                    ->__(
                        'Total of %d contacts were successfully deleted.', 
                        count($contactIds)
                    )
                );
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')
                    ->addError($e->getMessage());
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')
                    ->addError(
                        Mage::helper('budgetmailer')
                        ->__('There was an error deleting contacts.')
                    );
                Mage::logException($e);
            }
        }
        
        $this->_redirect('*/*/index');
    }

    /**
     * Not used... 
     */
    public function massStatusAction()
    {
        $contactIds = $this->getRequest()->getParam('contact');
        
        if (!is_array($contactIds)) {
            Mage::getSingleton('adminhtml/session')
                ->addError(
                    Mage::helper('budgetmailer')
                    ->__('Please select contacts.')
                );
        } else {
            try {
                foreach ($contactIds as $contactId) {
                    $contact = Mage::getSingleton('budgetmailer/contact');
                    $contact->load($contactId)
                        ->setStatus($this->getRequest()->getParam('status'))
                        ->setIsMassupdate(true)
                        ->save();
                }
                
                $this->_getSession()->addSuccess(
                    $this->__(
                        'Total of %d contacts were successfully updated.', 
                        count($contactIds)
                    )
                );
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')
                    ->addError($e->getMessage());
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')
                    ->addError(
                        Mage::helper('budgetmailer')
                        ->__('There was an error updating contacts.')
                    );
                Mage::logException($e);
            }
        }
        
        $this->_redirect('*/*/index');
    }

    /**
     * Mass unsubscribe contacts
     * 
     * @return null
     */
    public function massUnsubscribedAction()
    {
        $contactIds = $this->getRequest()->getParam('contact');
        
        if (!is_array($contactIds)) {
            Mage::getSingleton('adminhtml/session')
                ->addError(
                    Mage::helper('budgetmailer')
                    ->__('Please select contacts.')
                );
        } else {
            try {
                foreach ($contactIds as $contactId) {
                    $subscribe = !$this->getRequest()
                        ->getParam('flag_unsubscribed');
                    
                    $contact = Mage::getSingleton('budgetmailer/contact');
                    $contact->load($contactId);
                    
                    $contact->setUnsubscribed(!$subscribe);
                    $contact->setSubscribe($subscribe);
                    
                    $contact->setIsMassupdate(true);
                    $contact->save();
                }
                
                $this->_getSession()->addSuccess(
                    $this->__(
                        'Total of %d contacts were successfully updated.', 
                        count($contactIds)
                    )
                );
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')
                    ->addError($e->getMessage());
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')
                    ->addError(
                        Mage::helper('budgetmailer')
                        ->__('There was an error updating contacts.')
                    );
                Mage::logException($e);
            }
        }
        
        $this->_redirect('*/*/index');
    }

    /**
     * Not used
     */
    public function massListIdAction()
    {
        $contactIds = $this->getRequest()->getParam('contact');
        
        if (!is_array($contactIds)) {
            Mage::getSingleton('adminhtml/session')
                ->addError(
                    Mage::helper('budgetmailer')
                    ->__('Please select contacts.')
                );
        } else {
            try {
                foreach ($contactIds as $contactId) {
                    $contact = Mage::getSingleton('budgetmailer/contact');
                    $contact->load($contactId)
                        ->setListId(
                            $this->getRequest()->getParam('flag_list_id')
                        )
                        ->setIsMassupdate(true)
                        ->save();
                }
                
                $this->_getSession()
                    ->addSuccess(
                        $this->__(
                            'Total of %d contacts were successfully updated.',
                            count($contactIds)
                        )
                    );
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')
                    ->addError($e->getMessage());
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')
                    ->addError(
                        Mage::helper('budgetmailer')
                        ->__('There was an error updating contacts.')
                    );
                Mage::logException($e);
            }
        }
        
        $this->_redirect('*/*/index');
    }

    /**
     * Not used
     */
    public function exportCsvAction()
    {
        $fileName = 'contact.csv';
        $content = $this->getLayout()
            ->createBlock('budgetmailer/adminhtml_contact_grid')
            ->getCsv();
        $this->_prepareDownloadResponse($fileName, $content);
    }

    /**
     * Not used
     */
    public function exportExcelAction()
    {
        $fileName = 'contact.xls';
        $content = $this->getLayout()
            ->createBlock('budgetmailer/adminhtml_contact_grid')
            ->getExcelFile();
        $this->_prepareDownloadResponse($fileName, $content);
    }

    /**
     * Not used
     */
    public function exportXmlAction()
    {
        $fileName = 'contact.xml';
        $content = $this->getLayout()
            ->createBlock('budgetmailer/adminhtml_contact_grid')
            ->getXml();
        $this->_prepareDownloadResponse($fileName, $content);
    }

    /**
     * Not used
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')
            ->isAllowed('budgetmailer/contact');
    }
}
