<?php
/**
 * Netresearch_Scoring_Model_Validation_Solvency_Handler
 * 
 * @category   Scoring
 * @package    Netresearch_Scoring
 * @author     Thomas Kappel <thomas.kappel@netresearch.de>
 * @copyright  Copyright (c) 2011 Netresearch GmbH & Co.KG <http://www.netresearch.de/>
 *
 */
class Netresearch_Scoring_Model_Validation_Solvency_Handler
{
    /**
     * @var Netresearch_Scoring_Model_Config
     */
    protected $config;
    
    /**
     * constructor
     * 
     * @param Netresearch_Scoring_Model_Config $config Configuration model
     * 
     * @return Netresearch_Scoring_Model_Service
     */
    public function __construct($config=null)
    {
        $this->config = $config;
        if (empty($this->config)) {
            $this->config = Mage::getModel('scoring/config');
        }
    }
    
    /**
     * get scoring config
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
    
    public function handleFail($event)
    {
        return Mage::getModel('scoring/session')->getSolvencyValidationResultArray();
    }

    /**
     * FIXME
     * 
     * get codes of allowed payment methods depending on scoring value
     * 
     * @return array Allowed payment methods
     */
    protected function _getReducedPaymentMethods()
    {
        return array();
    }
}