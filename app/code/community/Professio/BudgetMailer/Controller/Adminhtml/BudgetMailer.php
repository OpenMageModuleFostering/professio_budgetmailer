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
 * Module base admin controller
 *
 * @category    Professio
 * @package     Professio_BudgetMailer
 */
class Professio_BudgetMailer_Controller_Adminhtml_BudgetMailer
extends Mage_Adminhtml_Controller_Action
{
    /**
     * Check if current user is allowed to use this controller.
     * @return boolean
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')
            ->isAllowed('admin/system/convert/professio_budgetmailer');
    }
}
