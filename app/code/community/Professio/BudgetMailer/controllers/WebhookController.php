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
 * Implementation of BudgetMailer web hook
 *
 * @category    Professio
 * @package     Professio_BudgetMailer
 */
class Professio_BudgetMailer_WebhookController 
extends Mage_Core_Controller_Front_Action
{
    protected $_headers = array(
        'APIKEY', 'SALT', 'SIGNATURE'
    );
    
    /**
     * Get HTTP headers
     * @return array
     */
    protected function getHeaders()
    {
        $headers = array();
        
        foreach ($this->_headers as $k) {
            $headers[$k] = $this->getRequest()->getHeader($k);
        }
        
        return $headers;
    }
    
    /**
     * Get budgetmailer helper
     * @return Professio_BudgetMailer_Helper_Data
     */
    protected function getHelper()
    {
        return Mage::helper('budgetmailer');
    }
    
    /**
     * Index action - listening for webhook calls.
     * 
     * @return void
     */
    public function indexAction()
    {
        Mage::log(
            'budgetmailer/webhook_controller::indexAction() start'
        );
        
        try {
            $body = $this->getRequest()->getRawBody();
            $actions = json_decode($body);
            $actions = is_object($actions) && isset($actions->data)
            && is_array($actions->data) && count($actions->data)
            ? $actions->data : array();
            $headers = $this->getHeaders();
            
            Mage::log(
                'budgetmailer/webhook_controller::indexAction() '
                . 'body: ' . $body . ', actions: ' . json_encode($actions)
                . 'headers: ' . json_encode($headers)
            );
        
            list($code, $message) = $this->processHook($actions, $headers);
            
            $this->getResponse()->setHeader(
                $this->getRequest()->getServer('SERVER_PROTOCOL'), 
                $code, true
            );

            $this->getResponse()->setBody($message);
            
            Mage::log(
                'budgetmailer/webhook_controller::indexAction() message: '
                . $message . ', status: ' . $code
            );
        } catch (Exception $e) {
            Mage::logException($e);
            
            $this->getResponse()->setHeader(
                $this->getRequest()->getServer('SERVER_PROTOCOL'), 
                500, true
            );
            
            $this->getResponse()->setBody(
                Mage::helper('budgetmailer')
                ->__('Unexpected error:') 
                . ' ' . $e->getMessage() . '.'
            );
            
            Mage::log(
                'budgetmailer/webhook_controller::indexAction() exception: '
                . $e->getMessage()
            );
        }
        
        Mage::log(
            'budgetmailer/webhook_controller::indexAction() end'
        );
    }
    
    protected function processHook($actions, $headers)
    {
        // INFO HTTPS CHECK
        if (false && !$this->getRequest()->isSecure()) {
            $code = 500;
            $message = $this->getHelper()
                ->__('This URL works only with HTTPS.');
        } else if (
            !Mage::helper('budgetmailer/config')->isSyncWebhookEnabled()) {
            $code = 503;
            $message = $this->getHelper()->__('Webhook disabled.');
        } else if (
            !$this->getHelper()->checkSignature($headers)) {
            $code = 403;
            $message = $this->getHelper()->__('Invalid signature.');
        } else if (!is_array($actions) || !count($actions)) {
            $code = 200;
            $message = $this->getHelper()->__('Nothing to do.');
        } else {
            $code = 200;
            $message = $this->getHelper()->__('OK');
            Mage::getSingleton('budgetmailer/importer')
                ->hook($actions, $headers);
        }
        
        return array($code, $message);
    }
}
