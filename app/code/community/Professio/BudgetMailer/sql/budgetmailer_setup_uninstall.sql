/*
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

-- For versions 1.0.0 - 1.0.3:

-- DROP TABLE budgetmailer_contact;
-- DROP TABLE budgetmailer_list;

-- From 1.0.4:

DELETE FROM core_config_data WHERE `path` LIKE 'budgetmailer%';
DELETE FROM core_resource WHERE code = 'budgetmailer_setup';
