<?php
/**
 * Netresearch_Scoring_Model_Service
 * 
 * @category   Scoring
 * @package    Netresearch_Scoring
 * @author     Thomas Kappel <thomas.kappel@netresearch.de>
 * @copyright  Copyright (c) 2011 Netresearch GmbH & Co.KG <http://www.netresearch.de/>
 */
class Netresearch_Scoring_Model_Service extends Mage_Core_Model_Abstract
{
    /**
     * @var Netresearch_Scoring_Model_Config
     */
    protected $config;
    
    /**
     * @var Netresearch_Scoring_Model_Session
     */
    protected $session;

    /**
     * @var Netresearch_Scoring_Helper_Data
     */
    protected $helper;
    
    /**
     * @var Netresearch_Scoring_Model_Log
     */
    protected $logCollection;
    
    /**
     * constructor
     * 
     * @param Netresearch_Scoring_Model_Config                $config        Config
     * @param Netresearch_Scoring_Model_Session               $session       Session
     * @param Netresearch_Scoring_Helper_Data                 $helper        Helper
     * @param Netresearch_Scoring_Model_Resource_Log_Collection $logCollection Log collection
     * 
     * @return Netresearch_Scoring_Model_Service
     */
    public function __construct($config=null, $session=null, $helper=null, $logCollection=null)
    {
        $this->config = $config;
        $this->session = $session;
        $this->logCollection = $logCollection;
        $this->helper = $helper;
        if (is_null($this->helper)) {
            $this->helper = Mage::helper('scoring');
        }
    }
    
    public function getConfig()
    {
        if (empty($this->config)) {
            $this->config = Mage::getModel('scoring/config');
        }
        return $this->config;
    }
    
    public function getSession()
    {
        if (empty($this->session)) {
            $this->session = Mage::getModel('scoring/session');
        }
        return $this->session;
    }
    
    /**
     * Validate address - to be overwritten
     * 
     * @param Mage_Sales_Model_Quote $quote
     */
    public function validateAddress($quote)
    {
        
    }
    
    /**
     * Get solvency group
     * 
     * call parent method FIRST when overwriting this method!
     * 
     * @param Mage_Sales_Model_Quote $quote
     */
    public function getSolvencyGroup($quote)
    {
        if (false == $this->getSession()->hasCustomerId()
            && $this->getSession()->hasSolvencyGroup()
        ) {
            return $this->getSession()->getSolvencyGroup();
        }
        if ($this->getSession()->hasCustomerId()
            && $customerId = $this->getSession()->getCustomerId()
        ) {
            $log = $this->getLastLogOfCustomer();
            if ($log && $log->isStillValid()) {
                $log->increaseCheckoutsAfterThisRequest();
                $log->save();
                return $log->getSolvencyGroup();
            }
        }
        /* call parent method FIRST when overwriting this method! */
    }
    
    /**
     * Get last log of a customer
     * 
     * @param int $customerId
     * 
     * @return Netresearch_Scoring_Model_Log
     */
    public function getLastLogOfCustomer()
    {
        return $this->helper->getLastLogOfCustomer(
            $this->session->getCustomerId()
        );
    }
    
    /**
     * Returns shiping or billing address of given quote
     * regarding configuration
     * 
     * @param Mage_Sales_Model_Quote $quote
     */
    public function getAddress($quote)
    {
        switch ($this->config->get('solvency/address_type')) {
            case Netresearch_Scoring_Model_System_Config_Source_Address_Type::BILLING:
                return $quote->getBillingAddress();
            default:
                return $quote->getShippingAddress();
        }
    }
    
    /**
     * handle errors resulting in wrong interface usage
     *
     * @param string    $method
     * @param mixed     $return_code
     * @param Exception $exception
     * @param string    $message
     *
     * @return void
     */
    protected function handleInterfaceError($method, $return_code=null, $exception=null, $message=null)
    {
        $message = $method . ' failed: ' . $message;
        $message .= is_null($return_code)
            ? 'Got no return code'
            : sprintf('Got return code %s.', $return_code);
        if (false == is_null($exception)) {
            $message .= "\n".$exception->getMessage();
        }

        if ($this->config->get('scoring/mail/mail_active')) {
            $mailTemplateId = $this->config->get('scoring/mail/mail_template');

            $mailer = Mage::getModel('scoring/mailer')->sendMail(
                'scoring interface error occured',
                $mailTemplateId,
                array(
                    'message'     => $message,
                    'logging_key' => $this->getLoggingKey()
                )
            );
        }
        $message .= ' ' . $this->getLoggingKey();
    }
}
