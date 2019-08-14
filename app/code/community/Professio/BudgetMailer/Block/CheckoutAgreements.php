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
 * Override of checkout agreements allowing newsletter sign-up 
 * for onepage checkout.
 *
 * @category    Professio
 * @package     Professio_BudgetMailer
 */
class Professio_BudgetMailer_Block_CheckoutAgreements
extends Mage_Checkout_Block_Agreements
{
    /**
     * Override block template
     *
     * @return string
     */
    protected function _toHtml()
    {
        $uri = filter_input(INPUT_SERVER, 'REQUEST_URI');
        
        if (substr_count($uri, 'onepage')) {
            $this->setTemplate('budgetmailer/onepage-agreements.phtml');
        }
        
        return parent::_toHtml();
    }

    /**
     * Check if customer is signed up
     * @return bool
     */
    public function isCurrentCustomerSignedUp()
    {
        if (Mage::getSingleton('customer/session')->isLoggedIn()) {
            $customer = Mage::getSingleton('customer/session')->getCustomer();
            
            if ($customer) {
                $client = Mage::getSingleton('budgetmailer/client')
                    ->getClient();
                $contact = $client->getContact($customer->getEmail());
                
                if ($contact && !$contact->unsubscribed) {
                    return true;
                }
            }
        }
        
        return false;
    }
    
    /**
     * Check if sign-up is hidden
     * @return bool
     */
    public function isSignupHidden()
    {
        return Professio_BudgetMailer_Model_Config_Source_Account::HIDDENCHECKED
            == Mage::helper('budgetmailer/config')->getAdvancedCreateAccount();
    }
    
    /**
     * Check if sign-up is checked
     * @return bool
     */
    public function isSignupChecked()
    {
        $v = Mage::helper('budgetmailer/config')->getAdvancedCreateAccount();
        
        return 
            Professio_BudgetMailer_Model_Config_Source_Account::HIDDENCHECKED
            == $v
            || Professio_BudgetMailer_Model_Config_Source_Account::CHECKED
            == $v;
    }
    
    /**
     * Get config helper 
     * @return Professio_BudgetMailer_Helper_Config
     */
    public function getConfigHelper()
    {
        return Mage::helper('budgetmailer/config');
    }
}
