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
 * Mini subscribe widget (checkbox)
 *
 * @category    Professio
 * @package     Professio_BudgetMailer
 */
class Professio_BudgetMailer_Block_Subscribe_Mini
extends Mage_Core_Block_Template
{
    /**
     * Get config helper
     * @return Professio_BudgetMailer_Helper_Config
     */
    public function getConfigHelper()
    {
        return Mage::helper('budgetmailer/config');
    }
    
    /**
     * Get form data, in fact only returns new varien object
     * @return Varien_Object
     */
    public function getFormData()
    {
        return new Varien_Object;
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
}
