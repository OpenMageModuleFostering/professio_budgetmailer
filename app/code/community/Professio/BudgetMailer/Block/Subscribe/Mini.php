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
}
