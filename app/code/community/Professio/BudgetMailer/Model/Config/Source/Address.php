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
 * Address type config source
 * 
 * @category   Professio
 * @package    Professio_BudgetMailer
 */
class Professio_BudgetMailer_Model_Config_Source_Address
{
    const BILLING = 'billing';
    const SHIPPING = 'shipping';
    
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array(
                'label' => Mage::helper('budgetmailer')->__('Billing Address'),
                'value' => self::BILLING
            ),
            array(
                'label' => Mage::helper('budgetmailer')->__('Shipping Address'),
                'value' => self::SHIPPING
            ),
        );
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return array(
            self::BILLING => 
                Mage::helper('budgetmailer')->__('Billing Address'),
            self::SHIPPING => 
                Mage::helper('budgetmailer')->__('Shipping Address'),
        );
    }   
}
