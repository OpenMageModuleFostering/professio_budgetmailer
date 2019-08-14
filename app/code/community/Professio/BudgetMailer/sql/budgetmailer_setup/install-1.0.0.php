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

$table = $this->getConnection()
    ->newTable($this->getTable('budgetmailer/contact'))

    ->addColumn(
        'entity_id', 
        Varien_Db_Ddl_Table::TYPE_INTEGER, 
        null, 
        array(
            'identity' => true,
            'nullable' => false,
            'primary' => true,
        ), 
        'Contact ID'
    )

    ->addColumn(
        'customer_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'unsigned' => true,
            'nullable' => false,
        ), 'Customer ID'
    )

    ->addColumn(
        'list_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'unsigned' => true,
            'nullable' => true,
        ), 'List ID'
    )

    ->addColumn(
        'budgetmailer_id', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
            'nullable' => true,
        ), 'Budgetmailer ID'
    )

    ->addColumn(
        'email', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
            'nullable' => true,
        ), 'Email'
    )

    ->addColumn(
        'company_name', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
            'nullable' => true,
        ), 'Company Name'
    )

    ->addColumn(
        'first_name', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
            'nullable' => true,
        ), 'First Name'
    )

    ->addColumn(
        'insertion', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
            'nullable' => true,
        ), 'Insertion'
    )

    ->addColumn(
        'last_name', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        'nullable' => true,
            ), 'Last Name'
    )

    ->addColumn(
        'sex', Varien_Db_Ddl_Table::TYPE_SMALLINT, 255, array(
            'nullable' => true,
        ), 'Sex'
    )

    ->addColumn(
        'address', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        'nullable' => false,
            ), 'Address'
    )

    ->addColumn(
        'postal_code', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
            'nullable' => true,
        ), 'Postal Code'
    )

    ->addColumn(
        'city', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
            'nullable' => true,
        ), 'City'
    )

    ->addColumn(
        'country_code', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
            'nullable' => true,
        ), 'Country Code'
    )

    ->addColumn(
        'telephone', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        'nullable' => true,
            ), 'Telephone'
    )

    ->addColumn(
        'mobile', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
            'nullable' => true,
        ), 'Mobile'
    )

    ->addColumn(
        'remarks', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', array(
        'nullable' => true,
            ), 'Remarks'
    )

    ->addColumn(
        'tags', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
            'nullable' => true,
        ), 'Tags'
    )

    ->addColumn(
        'unsubscribed', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
            'nullable' => true,
        ), 'Unsubscribed'
    )

    ->addColumn(
        'status', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(), 
        'Contact Status'
    )

    ->addColumn(
        'updated_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(), 
        'Contact Modification Time'
    )

    ->addColumn(
        'created_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(), 
        'Contact Creation Time'
    )

    ->addIndex(
        $this->getIdxName('customer/entity', array('customer_id')), 
        array('customer_id')
    )
    
    ->addIndex(
        $this->getIdxName('budgetmailer/list', array('list_id')), 
        array('list_id')
    )
    
    ->addIndex(
        $this->getIdxName('budgetmailer/contact', array('email')),
        array('email')
    )
    
    ->addIndex(
        $this->getIdxName(
            'budgetmailer/contact', array('budgetmailer_id')
        ),
        array('budgetmailer_id')
    )
    
    ->setComment('Contact Table');

$this->getConnection()->createTable($table);

$table = $this->getConnection()
    ->newTable($this->getTable('budgetmailer/list'))

    ->addColumn(
        'entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'identity' => true,
            'nullable' => false,
            'primary' => true,
        ), 'List ID'
    )

    ->addColumn(
        'budgetmailer_id', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
            'nullable' => false,
        ), 'Budgetmailer ID'
    )

    ->addColumn(
        'name', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
            'nullable' => false,
        ), 'Name'
    )

    ->addColumn(
        'status', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(), 
        'List Status'
    )

    ->addColumn(
        'updated_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(), 
        'List Modification Time'
    )

    ->addColumn(
        'created_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(), 
        'List Creation Time'
    )

    ->setComment('List Table');

$this->getConnection()->createTable($table);

$this->endSetup();

if (Mage::getIsDeveloperMode()) {
    Mage::log('budgetmailer_setup ended');
}
