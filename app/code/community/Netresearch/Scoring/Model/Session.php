<?php
/**
 * Magento Enterprise Edition
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magento Enterprise Edition License
 * that is bundled with this package in the file LICENSE_EE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.magentocommerce.com/license/enterprise-edition
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category   Scoring
 * @package    Netresearch_Scoring
 * @author     Thomas Kappel <thomas.kappel@netresearch.de>
 * @copyright  Copyright (c) 2011 Netresearch GmbH & Co.KG <http://www.netresearch.de/>
 */
class Netresearch_Scoring_Model_Session extends Mage_Core_Model_Session_Abstract
{
    /**
     * @method getSolvencyValidationResultArray
     * @method getSolvencyValidationValue
     * @method getAddressValidationResultArray
     * @method getSolvencyGroup
     * @method setSolvencyGroup
     */
    
    public function __construct()
    {
        $this->init('scoring');
    }
    
    /**
     * get scoring config
     * 
     * @return Netresearch_Scoring_Model_Config
     */
    protected function getConfig()
    {
        return Mage::getModel('scoring/config');
    }
    
    /**
     * Try to get the id of the customer
     * 
     * @return int|null
     */
    public function getCustomerId()
    {
        if (class_exists('Mage')) {
            return Mage::getSingleton('customer/session')->getCustomer()->getId();
        }
        return parent::getCustomerId();
    }
    
    public function hasSolvencyGroup()
    {
        return is_object($this->getSolvencyGroup());
    }
    
    public function getSolvencyValidationResultArray()
    {
        $success = $this->getSolvencyGroup()->allowsOrder($this->getQuote());
        $error   = false === $success;
        $errorMessages = null;
        if ($error) {
            $errorMessages = $this->getConfig()->getSolvencyValidationFailedMessage();
        }
        return array(
            'success'                 => $success,
            'error'                   => $error,
            'error_messages'          => $errorMessages,
            'allowed_payment_methods' => $this->getSolvencyGroup()->getPaymentMethods(),
            'goto_section'            => 'payment'
            //'scoring_value'           => $this->getSolvencyGroup()->getName()
        );
    }
    
    /**
     * Get current quote
     *
     * @return Mage_Sales_Model_Quote
     */
    public function getQuote()
    {
        return Mage::getSingleton('checkout/type_onepage')->getQuote();
    }
    
    /**
     * If the id of the customer is available
     * 
     * @return boolean
     */
    public function hasCustomerId()
    {
        return is_numeric($this->getCustomerId()) && 0 < $this->getCustomerId();
    }
    
    /**
     * return the log collection
     * Enter description here ...
     */
    protected function getLogCollection()
    {
        return Mage::getModel('scoring/log')->getCollection();
    }

    /**
     * clear session data
     *
     * @return void
     */
    public function unsetAll()
    {
        $this->setSolvencyGroup(null);
    }
}
