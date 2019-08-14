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
 * List admin block
 *
 * @category    Professio
 * @package     Professio_BudgetMailer
 */
class Professio_BudgetMailer_Block_Adminhtml_List
extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    /**
     * Constructor
     * 
     * @return void
     */
    public function __construct()
    {
        $this->_controller         = 'adminhtml_list';
        $this->_blockGroup         = 'budgetmailer';
        
        parent::__construct();
        
        $this->_headerText         = Mage::helper('budgetmailer')->__('List');
        $this->_updateButton(
            'add', 
            'label', 
            Mage::helper('budgetmailer')->__('Add List')
        );
    }
}
