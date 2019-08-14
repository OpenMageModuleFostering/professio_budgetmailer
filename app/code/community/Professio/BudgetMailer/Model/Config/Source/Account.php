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
 * Account type config source
 * 
 * @category   Professio
 * @package    Professio_BudgetMailer
 */
class Professio_BudgetMailer_Model_Config_Source_Account
{
    const CHECKED = 'checked';
    const UNCHECKED = 'unchecked';
    const HIDDENCHECKED = 'hiddenchecked';
    
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array(
                'label' => Mage::helper('budgetmailer')
                    ->__('Display checked checkbox'),
                'value' => self::CHECKED
            ),
            array(
                'label' => Mage::helper('budgetmailer')
                    ->__('Display unchecked checkbox'),
                'value' => self::UNCHECKED
            ),
            array(
                'label' => Mage::helper('budgetmailer')
                    ->__('Don\'t display checkbox, sign-up automatically'),
                'value' => self::HIDDENCHECKED
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
            self::CHECKED => 
                Mage::helper('budgetmailer')->__('Display checked checkbox'),
            self::UNCHECKED => 
                Mage::helper('budgetmailer')->__('Display unchecked checkbox'),
            self::HIDDENCHECKED => 
                Mage::helper('budgetmailer')
                    ->__('Don\'t display checkbox, sign-up automatically'),
        );
    }   
}
