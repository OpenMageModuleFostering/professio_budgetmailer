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
 * Update entity config source
 * 
 * @category   Professio
 * @package    Professio_BudgetMailer
 */
class Professio_BudgetMailer_Model_Config_Source_Update
{
    const ON_UPDATE_UPDATE = 'update';
    const ON_UPDATE_IGNORE = 'ignore';
    
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array(
                'label' => Mage::helper('budgetmailer')->__('Update Contact'),
                'value' => self::ON_UPDATE_UPDATE
            ),
            array(
                'label' => Mage::helper('budgetmailer')->__('Do nothing'),
                'value' => self::ON_UPDATE_IGNORE
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
            self::ON_UPDATE_UPDATE => 
                Mage::helper('budgetmailer')->__('Update Contact'),
            self::ON_UPDATE_IGNORE => 
                Mage::helper('budgetmailer')->__('Do nothing'),
        );
    }  
}
