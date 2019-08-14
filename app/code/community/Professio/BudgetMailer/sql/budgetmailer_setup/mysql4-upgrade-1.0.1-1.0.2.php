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

if (Mage::getIsDeveloperMode()) {
    Mage::log('budgetmailer_setup started');
}

$this->startSetup();

$rsCont = $this->getConnection()->dropTable(
    $this->getTable('budgetmailer/contact')
);
$rsList = $this->getConnection()->dropTable(
    $this->getTable('budgetmailer/list')
);

if (Mage::getIsDeveloperMode()) {
    Mage::log(
        'budgetmailer_setup $rsCont: ' . $rsCont . ', $rsList: ' . $rsList
    );
}

$this->endSetup();

if (Mage::getIsDeveloperMode()) {
    Mage::log('budgetmailer_setup ended');
}
