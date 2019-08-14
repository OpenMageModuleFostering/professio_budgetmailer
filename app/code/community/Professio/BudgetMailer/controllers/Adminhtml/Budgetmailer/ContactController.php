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
     * Check if current user can use this controller
     * @return bool
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')
            ->isAllowed('budgetmailer/contact');
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
                $rs = array('total' => 0, 'fail' => 0, 'success' => 0);
                $contactIdsChunks = array_chunk(
                    $contactIds, Professio_BudgetMailer_Model_Client::LIMIT
                );

                foreach($contactIdsChunks as $chunk) {
                    $collection = Mage::getModel('budgetmailer/contact')
                        ->getCollection();
                    $collection->addFieldToFilter(
                        'entity_id', array('in' => $chunk)
                    );
                    $collection->load();

                    $contacts = array();
                    $map = array();

                    foreach($collection->getIterator() as $contact) {
                        $contactApi = new stdClass();
                        $contactApi->email = $contact->getEmail();

                        $contacts[] = $contactApi;
                        $map[$contact->getEmail()] = $contact->getId();
                    }

                    list($total, $fail, $success, $contactsDeleted) 
                        = Mage::getSingleton('budgetmailer/client')->postContacts(
                            $contacts, Professio_BudgetMailer_Model_Client::BULK_DELETE
                        );

                    $rs['total'] += $total;
                    $rs['fail'] += $fail;
                    $rs['success'] += $success;
                    
                    if (isset($contactsDeleted->Success) && is_array($contactsDeleted->Success)) {
                        foreach($contactsDeleted->Success as $contactApi) {
                            if (isset($map[$contactApi->email])) {
                                $contact = Mage::getModel('budgetmailer/contact');
                                $contact->setEmail($contactApi->email); // set email to avoid reload from API
                                $contact->setId($map[$contactApi->email]);
                                $contact->delete(false);
                            }
                        }
                    }
                }
                
                Mage::getSingleton('adminhtml/session')
                    ->addSuccess(
                        sprintf(
                            $this->__(
                                'Deleting contacts finished '
                                . '(completed: %d, fail: %d, success: %d).'
                            ),
                            $rs['total'], $rs['fail'], $rs['success']
                        )
                    );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')
                    ->addError(
                        Mage::helper('budgetmailer')
                            ->__('There was an unexpected error deleting the contact(s).')
                    );
                
                Mage::logException($e);
            }
        }
        
        $this->_redirect('*/*/index');
    }
    
    /**
     * Unsubscribe multiple contacts as mass action
     */
    public function massUnsubscribeAction()
    {
        $contactIds = $this->getRequest()->getParam('contact');
        
        if (!is_array($contactIds)) {
            Mage::getSingleton('adminhtml/session')
                ->addError(
                    Mage::helper('budgetmailer')
                    ->__('Please select contacts to unsubscribe.')
                );
        } else {
            try {
                $rs = array('total' => 0, 'fail' => 0, 'success' => 0);
                $contactIdsChunks = array_chunk(
                    $contactIds, Professio_BudgetMailer_Model_Client::LIMIT
                );

                foreach($contactIdsChunks as $chunk) {
                    $collection = Mage::getModel('budgetmailer/contact')
                        ->getCollection();
                    $collection->addFieldToFilter(
                        'entity_id', array('in' => $chunk)
                    );
                    $collection->load();

                    $contacts = array();
                    $map = array();

                    foreach($collection->getIterator() as $contact) {
                        $contactApi = new stdClass();
                        $contactApi->email = $contact->getEmail();

                        $contacts[] = $contactApi;
                        $map[$contact->getEmail()] = $contact;
                    }

                    list($total, $fail, $success, $contactsDeleted) 
                        = Mage::getSingleton('budgetmailer/client')->postContacts(
                            $contacts, Professio_BudgetMailer_Model_Client::BULK_UNSUB
                        );

                    $rs['total'] += $total;
                    $rs['fail'] += $fail;
                    $rs['success'] += $success;
                    
                    if (isset($contactsDeleted->Success) && is_array($contactsDeleted->Success)) {
                        foreach($contactsDeleted->Success as $contactApi) {
                            if (isset($map[$contactApi->email])) {
                                $contact = $map[$contactApi->email];
                                $contact->setUnsubscribed(true);
                                $contact->save(false);
                            }
                        }
                    }
                }
                
                Mage::getSingleton('adminhtml/session')
                    ->addSuccess(
                        sprintf(
                            $this->__(
                                'Unsubscribing contacts finished '
                                . '(completed: %d, fail: %d, success: %d).'
                            ),
                            $rs['total'], $rs['fail'], $rs['success']
                        )
                    );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')
                    ->addError(
                        Mage::helper('budgetmailer')
                            ->__('There was an unexpected error unsubscribing the contact(s).')
                    );
                
                Mage::logException($e);
            }
        }
        
        $this->_redirect('*/*/index');
    }
    
    /**
     * Delete and unsubscribe multiple contacts as mass action
     */
    public function massDeleteUnsubscribeAction()
    {

        $contactIds = $this->getRequest()->getParam('contact');
        
        if (!is_array($contactIds)) {
            Mage::getSingleton('adminhtml/session')
                ->addError(
                    Mage::helper('budgetmailer')
                    ->__('Please select contacts to unsubscribe and delete.')
                );
        } else {
            try {
                $rs = array('total' => 0, 'fail' => 0, 'success' => 0);
                $contactIdsChunks = array_chunk(
                    $contactIds, Professio_BudgetMailer_Model_Client::LIMIT
                );

                foreach($contactIdsChunks as $chunk) {
                    $collection = Mage::getModel('budgetmailer/contact')
                        ->getCollection();
                    $collection->addFieldToFilter(
                        'entity_id', array('in' => $chunk)
                    );
                    $collection->load();

                    $contacts = array();
                    $map = array();

                    foreach($collection->getIterator() as $contact) {
                        $contactApi = new stdClass();
                        $contactApi->email = $contact->getEmail();

                        $contacts[] = $contactApi;
                        $map[$contact->getEmail()] = $contact->getId();
                    }

                    list($total, $fail, $success, $contactsDeleted) 
                        = Mage::getSingleton('budgetmailer/client')->postContacts(
                            $contacts, Professio_BudgetMailer_Model_Client::BULK_DELUNSUB
                        );

                    $rs['total'] += $total;
                    $rs['fail'] += $fail;
                    $rs['success'] += $success;
                    
                    if (isset($contactsDeleted->Success) && is_array($contactsDeleted->Success)) {
                        foreach($contactsDeleted->Success as $contactApi) {
                            if (isset($map[$contactApi->email])) {
                                $contact = Mage::getModel('budgetmailer/contact');
                                $contact->setEmail($contactApi->email); // set email to avoid reload from API
                                $contact->setId($map[$contactApi->email]);
                                $contact->delete(false);
                            }
                        }
                    }
                }
                
                Mage::getSingleton('adminhtml/session')
                    ->addSuccess(
                        sprintf(
                            $this->__(
                                'Unsubscribing and deleting contacts finished '
                                . '(completed: %d, fail: %d, success: %d).'
                            ),
                            $rs['total'], $rs['fail'], $rs['success']
                        )
                    );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')
                    ->addError(
                        Mage::helper('budgetmailer')
                            ->__('There was an unexpected error unsubscribing and deleting the contact(s).')
                    );
                
                Mage::logException($e);
            }
        }
        
        $this->_redirect('*/*/index');
    }
}
