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
 * Data helper
 *
 * @category    Professio
 * @package     Professio_BudgetMailer
 */
class Professio_BudgetMailer_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Test http headers signature
     * 
     * @param array $headers
     * @return boolean
     */
    public function checkSignature($headers)
    {
        $signature = hash_hmac(
            'sha256', 
            $headers['SALT'], 
            Mage::helper('budgetmailer/config')->getApiSecret(),
            true
        );
        
        $signatureEncoded = base64_encode($signature);
        
        Mage::log(
            'budgetmailer/data_helper::checkSignature() '
            . 'headers: ' . json_encode($headers)
            . 'signature: ' . $signature
            . 'signature encoded: ' 
            . str_replace('%3d', '=', $signatureEncoded)
        );
        
        return $signatureEncoded 
            == str_replace('%3d', '=', $signatureEncoded);
    }
    
    /**
     * Get order tags (ordered products category names)
     * 
     * @param Mage_Sales_Model_Order $order
     * @return array
     */
    public function getOrderTags(Mage_Sales_Model_Order $order)
    {
        $items = $order->getAllItems();
        $orderTags = array();

        foreach ($items as $item) {
            $product = $item->getProduct();
            $categoryCollection = $product->getCategoryCollection();
            $categoryCollection->addAttributeToSelect('name');
            $categoryCollection->clear()->load();

            foreach ($categoryCollection->getIterator() as $category) {
                $orderTags[] = $category->getName();
            }
        }
        
        return $orderTags;
    }
    
    /**
     * Retrieve customer's primary address of configured type
     * @param Mage_Customer_Model_Customer $customer
     * @return Mage_Customer_Model_Address
     */
    public function getCustomersPrimaryAddress(
        Mage_Customer_Model_Customer $customer
    )
    {
        $addressType = Mage::helper('budgetmailer/config')
            ->getAdvancedAddressType();
        
        Mage::log(
            'budgetmailer/data_helper::getCustomersPrimaryAddress() '
            . 'type: ' . $addressType
        );
        
        switch($addressType) {
            case 'billing':
                $address = $customer->getPrimaryBillingAddress();
                break;
            case 'shipping':
                $address = $customer->getPrimaryShippingAddress();
                break;
        }
        
        Mage::log(
            'budgetmailer/data_helper::getCustomersPrimaryAddress() '
            . ' address id: ' . ( $address ? $address->getEntityId() : 'no' )
        );
        
        return $address;
    }
    
    /**
     * Get default website id 
     * 
     * @return integer
     */
    public function getDefaultWebsiteId()
    {
        $resource = Mage::getSingleton('core/resource');
        $sql = 'SELECT * FROM ' . $resource->getTableName('core/store')
            . ' WHERE `code` = "default"';
        
        $connection = Mage::getSingleton('core/resource')
            ->getConnection('core_read');
        
        $row = $connection->fetchOne($sql);
        
        return $row ? $row : 1;
    }
    
    public function getCategoryNamesOfOrderedProducts(
        Mage_Customer_Model_Customer $customer
    ) {
        $states = Mage::getSingleton('sales/order_config')
            ->getVisibleOnFrontStates();
        
        $collection = Mage::getResourceModel('sales/order_collection')
            ->addFieldToSelect('*')
            ->addFieldToFilter('customer_id', $customer->getId())
            ->addFieldToFilter('state', array('in' => $states))
            ->setOrder('created_at', 'desc');
        
        $collection->load();
        
        $tags = array();
        
        foreach($collection->getIterator() as $order) {
            $tags = array_merge($tags, $this->getOrderTags($order));
        }
        
        return array_unique($tags);
    }
}
