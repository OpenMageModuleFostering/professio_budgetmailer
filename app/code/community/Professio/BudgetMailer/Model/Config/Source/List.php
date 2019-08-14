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
                $collection = Mage::getModel('budgetmailer/list')
                    ->getCollection();

                if ($collection) {
                    $collection->load();
                    $this->_lists = $collection->getIterator();
                } else {
                    $this->_lists = array();
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
                'value' => 0,
                'label' => Mage::helper('budgetmailer')->__('Select List')
            );
            
            foreach ($this->getLists() as $list) {
                $options[] = array(
                    'value' => $list->getBudgetmailerId(),
                    'label' => $list->getName()
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
            $array = array();
            $array[0] = Mage::helper('budgetmailer')->__('Select List');
            
            foreach ($this->getLists() as $list) {
                $array[$list->getId()] = $list->getName();
            }
        }
        
        return $array;
    }    
}
