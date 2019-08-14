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
 * Select list config source
 * 
 * @category   Professio
 * @package    Professio_BudgetMailer
 */
class Professio_BudgetMailer_Model_Config_Source_List
{
    protected $_lists;
    
    public function getLists()
    {
        if (!isset($this->_lists)) {
            try {
                $client = Mage::getModel('budgetmailer/client')
                    ->getClient();
                $lists = $client->getLists();
                
                if (is_array($lists) && count($lists)) {
                    foreach ($lists as $list) {
                        $this->_lists[$list->id] = $list->list;
                    }
                }
            } catch (Exception $e) {
                $this->_lists = array();
                Mage::logException($e);
                Mage::log(
                    'budgetmailer/config_source_list::getLists() failed '
                    . ' with exception: ' . $e->getMessage()
                );
            }
        }
        
        return $this->_lists;
    }
    
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        static $options;
        
        if (!isset($options)) {
            $options = array();
            
            $options[] = array(
                'value' => '',
                'label' => Mage::helper('budgetmailer')->__('Select List')
            );
            
            foreach ($this->getLists() as $listId => $listName) {
                $options[] = array(
                    'value' => $listId,
                    'label' => $listName
                );
            }
        }
        
        return $options;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        static $array;
        
        if (!isset($array)) {
            $array = array(
                0 => Mage::helper('budgetmailer')->__('Select List')
            );
            $array = array_merge($array, $this->getLists());
        }
        
        return $array;
    }    
}
