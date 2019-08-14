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
 * List collection resource
 * 
 * @category   Professio
 * @package    Professio_BudgetMailer
 */
class Professio_BudgetMailer_Form_Element_Tags
extends Varien_Data_Form_Element_Abstract
{
    /**
     * Constructor
     * @param array $data
     */
    public function __construct($data)
    {
        parent::__construct($data);
        
        $this->setType('budgetmailer_tags');
    }

    /**
     * Get element html
     * 
     * @return string
     */
    public function getElementHtml()
    {
        $html = '<div id="budgetmailer_tags">';
        $html .= '<!--<input id="budgetmailer_tags_remove" '
            . 'name="tags_remove" type="hidden"/>-->';
        
        $html .= '<script type="text/javascript">
var $budgetmailer_tags = $("budgetmailer_tags");
//var $budgetmailer_tags_remove = $("budgetmailer_tags_remove");

function budgetmailer_new(el) {
    $budgetmailer_tags.insert("<input name=\"' 
    . $this->getName() 
    . '[]\" type=\"text\" value=\"\"/> <button class=\"budgetmailer-remove\" '
    . 'onclick=\"return budgetmailer_remove(this);\">' 
    . Mage::helper('budgetmailer')->__('Remove') 
    . '</button><br>");

    return false;
}

function budgetmailer_remove(el) {
    var button = $(el);
    var input = button.previous("input");
    var br = button.next("br");
    //var remove = $budgetmailer_tags_remove.getValue();
    //$budgetmailer_tags_remove.setValue(remove + "," + input.getValue());

    button.remove();
    $(input).remove();
    $(br).remove();

    return false;
}
</script>';
        
        $html .= '<button id="budgetmailer_add" onclick="return '
            . 'budgetmailer_new(this);">' 
            . Mage::helper('budgetmailer')->__('Add Tag') 
            . '</button><br>';
        
        if (is_array($this->getValue()) && count($this->getValue())) {
            foreach ($this->getValue() as $tag) {
                $html .= '<input name="' 
                    . $this->getName() 
                    . '[]" type="text" value="' 
                    . $tag 
                    . '"/> <button class="budgetmailer-remove" '
                    . 'onclick="return budgetmailer_remove(this);">' 
                    . Mage::helper('budgetmailer')->__('Remove') 
                    . '</button><br>';
            }
        } else {
            $html .= Mage::helper('budgetmailer')->__('No Tags');
        }
        
        $html .= '</div>';
        
        return $html;
    }
}
