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
 * Parent entities column renderer
 * 
 * @category   Professio
 * @package    Professio_BudgetMailer
 */
class Professio_BudgetMailer_Block_Adminhtml_Helper_Column_Renderer_Parent
extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Options
{
    /**
     * Render the column
     * 
     * @param Varien_Object $row
     * @return string
     */
    public function render(Varien_Object $row) 
    {
        $base = $this->getColumn()->getBaseLink();
        
        if (!$base) {
            return parent::render($row);
        }
        
        $options = $this->getColumn()->getOptions();
        
        if (!empty($options) && is_array($options)) {
            return $this->renderOptions($options, $row);
        }
    }
    
    /**
     * Get params from row
     * 
     * @param array $paramsData
     * @param object $row
     * @return array
     */
    public function getParams($paramsData, $row)
    {
        $params = array();
        
        if (is_array($paramsData)) {
            foreach ($paramsData as $name=>$getter) {
                if (is_callable(array($row, $getter))) {
                    $params[$name] = $row->{$getter}();
                }
            }
        }
        
        $staticParamsData = $this->getColumn()->getData('static');
        
        if (is_array($staticParamsData)) {
            foreach ($staticParamsData as $key=>$value) {
                $params[$key] = $value;
            }
        }
        
        return $params;
    }
    
    /**
     * Render options...
     * 
     * @param aray $options
     * @param type $row
     * @return type
     */
    protected function renderOptions($options, $row)
    {
        $value = $row->getData($this->getColumn()->getIndex());

        if (isset($options[$value])) {
            return $options[$value];
        } elseif (in_array($value, $options)) {
            return $value;
        }
    }
}
