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
 * Delete address config source
 * 
 * @category   Professio
 * @package    Professio_BudgetMailer
 */
class Professio_BudgetMailer_Model_Config_Source_Delete_Address
{
    const ON_DELETE_USE_NEW = 'use_new';
    const ON_DELETE_IGNORE = 'ignore';
    
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
                    ->__('Use new primary address'),
                'value' => self::ON_DELETE_USE_NEW
            ),
            array(
                'label' => Mage::helper('budgetmailer')->__('Do nothing'),
                'value' => self::ON_DELETE_IGNORE
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
            self::ON_DELETE_USE_NEW => Mage::helper('budgetmailer')
                ->__('Use new primary address'),
            self::ON_DELETE_IGNORE => Mage::helper('budgetmailer')
                ->__('Do nothing'),
        );
    }   
}
