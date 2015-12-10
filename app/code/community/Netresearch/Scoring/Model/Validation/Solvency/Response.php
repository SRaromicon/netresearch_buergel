<?php
/**
 * Netresearch_Scoring_Model_Validation_Solvency_Response
 * 
 * @category   Scoring
 * @package    Netresearch_Scoring
 * @author     Thomas Kappel <thomas.kappel@netresearch.de>
 * @copyright  Copyright (c) 2011 Netresearch GmbH & Co.KG <http://www.netresearch.de/>
 */
class Netresearch_Scoring_Model_Validation_Solvency_Response
{
    /** @var Netresearch_Scoring_Model_Session $session */
    protected $session;
    
    /** @var Netresearch_Scoring_Model_Log $log */
    protected $log;
    
    /** @var Netresearch_Scoring_Model_Config $config */
    protected $config;
    
    /**
     * constructor
     * 
     * @param mixed $response Response of the service
     * 
     * @return Netresearch_Scoring_Model_Validation_Solvency_Response
     */
    public function __construct($response)
    {
        $this->logSolvency();
        $this->getSession()->setSolvencyGroup($this->getGroup());
    }
    
    /**
     * Get response as a string
     * 
     * @return string
     */
    public function getResponseString()
    {
        return serialize(isset($this->response) ? $this->response : null);
    }
    
    /**
     * log solvency
     * 
     * @return void
     */
    protected function logSolvency()
    {
        $group = $this->getGroup();
        if ($group instanceof Netresearch_Scoring_Model_Validation_Solvency_Group) {
            $this->getLog()->setGroup($this->getGroup()->getName());
            $this->getLog()->setCustomerId($this->getSession()->getCustomerId());
        }
        $this->getLog()->setResponse(utf8_encode($this->getResponseString()));
        $this->getLog()->save();
        $this->getSession()->setLog($this->getLog());
    }
    
    /**
     * get the log model
     * 
     * @param Netresearch_Scoring_Model_Log $log
     * 
     * @return Netresearch_Scoring_Model_Log
     */
    protected function getLog(Netresearch_Scoring_Model_Log $log=null)
    {
        if (empty($this->log)) {
            if (empty($log)) {
                if ($this->getSession()->getCustomerId()) {
                    $log = Mage::helper('scoring')->getLastLogOfCustomer(
                        $this->getSession()->getCustomerId()
                    );
                } else {
                    $log = Mage::getModel('scoring/session')->getLog();
                }
            }
            if (empty($log)) {
                $log = Mage::getModel('scoring/log');
            }
            $this->log = $log;
        }
        return $this->log;
    }
    
    /**
     * get the config model
     * 
     * @param Netresearch_Scoring_Model_Config $config
     * 
     * @return Netresearch_Scoring_Model_Config
     */
    protected function getConfig(Netresearch_Scoring_Model_Config $config=null)
    {
        
        if (empty($this->config)) {
            if (empty($config)) {
                $config = Mage::getModel('scoring/config');
            }
            $this->config = $config;
        }
        return $this->config;
    }
    
    /**
     * get the session 
     *
     * @param Netresearch_Scoring_Model_Session $session
     *
     * @param Netresearch_Scoring_Model_Session $session
     */
    protected function getSession(Netresearch_Scoring_Model_Session $session=null)
    {
        if (is_null($this->session)) {
            if (is_null($session)) {
                $session = Mage::getModel('scoring/session');
            }
            $this->session = $session;
        }
        return $this->session;
    } 

    /**
     * get the message of the solvency response
     *
     * @param Netresearch_Scoring_Model_Session $scoringSession
     */
    public function getMessage(Netresearch_Scoring_Model_Session $scoringSession=null)
    {
        return $scoringSession->getSolvencyMessage();
    }
    
    /**
     * if order is allowed for this user
     *
     * @param Mage_Sales_Model_Quote $quote
     * 
     * @return boolean
     */
    public function allowsOrder(Mage_Sales_Model_Quote $quote)
    {
        return $this->getGroup()->allowsOrder($quote);
    }
    
    /**
     * get solvency group
     *
     * @return Netresearch_Scoring_Model_Validation_Solvency_Group
     */
    public function getGroup()
    {
        return $this->getConfig()->getSolvencyGroupByScore($this->getScore());
    }
}
