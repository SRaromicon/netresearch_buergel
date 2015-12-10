<?php
/**
 * Netresearch_Scoring_Model_Observer
 * 
 * @category   Scoring
 * @package    Netresearch_Scoring
 * @author     Thomas Kappel <thomas.kappel@netresearch.de>
 * @copyright  Copyright (c) 2011 Netresearch GmbH & Co.KG <http://www.netresearch.de/>
 */
class Netresearch_Scoring_Model_Observer
{
    /** @var Netresearch_Scoring_Model_Config */
    protected $config;
    
    /** @var Netresearch_Scoring_Model_Session */
    protected $session;
    
    /** @var Netresearch_Scoring_Model_Service */
    protected $service;
    
    /**
     * constructor
     * 
     * @param Netresearch_Scoring_Model_Config  $config  Configuration model
     * @param Netresearch_Scoring_Model_Session $session Scoring session
     * 
     * @return Netresearch_Scoring_Model_Service
     */
    public function __construct($config=null, $session=null)
    {
        $this->config  = $config;
        $this->session = $session;
    }
    
    /**
     * check solvency and/or address
     *
     * @param $event
     * @throws Netresearch_Scoring_Model_Validation_Solvency_Exception
     * @throws Netresearch_Scoring_Model_Validation_Address_Exception
     */
    public function checkCustomerData($event)
    {
        if ($this->getConfig()->isSolvencyValidationActive()
            && $this->getConfig()->isSolvencyValidationRequiredForQuote($event->getQuote())
        ) {
            /* validate solvency */
            $solvencyGroup = $this->getService()->getSolvencyGroup($event->getQuote());
            $this->getSession()->setSolvencyGroup($solvencyGroup);
            if (false == is_null($solvencyGroup)
                && false == $solvencyGroup->allowsOrder($event->getQuote())
            ) {
                throw new Netresearch_Scoring_Model_Validation_Solvency_Exception(
                    $this->getConfig()->getSolvencyValidationFailedMessage()
                );
            }
        }
        
        
        if ($this->getConfig()->isAddressValidationActive()) {
            /* validate address */
            $addressValidation = $this->getService()->validateAddress($event->getQuote());
            if (false == is_null($addressValidation)
                && $addressValidation->isFailed()
            ) {
                throw new Netresearch_Scoring_Model_Validation_Address_Exception(
                    $addressValidation->getMessage()
                );
            }
        }
    }
    
    /**
     * Get scoring configuration
     * 
     * @return Netresearch_Scoring_Model_Config
     */
    public function getConfig()
    {
        if (empty($this->config)) {
            $this->config = Mage::getModel('scoring/config');
        }
        return $this->config;
    }
    
    /**
     * Get scoring session
     * 
     * @return Netresearch_Scoring_Model_Session
     */
    public function getSession()
    {
        if (empty($this->session)) {
            $this->session = Mage::getModel('scoring/session');
        }
        return $this->session;
    }

    /**
     * Get scoring service
     * 
     * @return Netresearch_Scoring_Model_Service
     */
    public function getService()
    {
        if (empty($this->service)) {
            $this->service = Mage::getModel('scoring/service');
        }
        return $this->service;
    }
    
    /**
     * Completes the log of a scoring request with the id of the customer which was requested.
     *
     * @param Mage_Observer $observer
     * @param int           $customerId
     */
    public function addCustomerIdToLogs($observer=null, $customerId)
    {
        $log = $this->getSession()->getLog();
        if (is_null($customerId)) {
            $customerId = Mage::getSingleton('customer/session')->getCustomer()->getId();
        } 
        $log->setCustomerId($customerId);
        $log->save();
    }

    /**
     * unset scoring session after customer logout
     *
     * @return void
     */
    public function unsetSession()
    {
        $this->getSession()->unsetAll();
    }

    /**
     * unset scoring session for guest after changing the billing address
     *
     * @return void
     */
    public function unsetGuestSessionAfterChangedBillingAddress()
    {
        if (false == Mage::getSingleton('customer/session')->getCustomer()->getId()) {
            $this->getSession()->unsetAll();
        }
    }

    /**
     * unset scoring session for guest after changing the shipping address if it is used for scoring
     *
     * @return void
     */
    public function unsetGuestSessionAfterChangedShippingAddress()
    {
        if (Netresearch_Scoring_Model_System_Config_Source_Address_Type::SHIPPING == $this->getConfig()->get('solvency/address_type')
            && false == Mage::getSingleton('customer/session')->getCustomer()->getId()
        ) {
            $this->getSession()->unsetAll();
        }
    }
}
