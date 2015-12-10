<?php
/**
 * Netresearch_Scoring_Model_Validation_Address_Handler
 * 
 * @category   Scoring
 * @package    Netresearch_Scoring
 * @author     Thomas Kappel <thomas.kappel@netresearch.de>
 * @copyright  Copyright (c) 2011 Netresearch GmbH & Co.KG <http://www.netresearch.de/>
 */
class Netresearch_Scoring_Model_Validation_Address_Handler
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
        if (is_null($this->config)) {
            $this->config = Mage::getModel('scoring/config');
        }
    }
    
    public function handleFail($event)
    {
        Mage::getModel('scoring/session')->setAddressValidationResultArray(array(
            'error_messages' => $this->config->get('scoring/errormessages/address'),
        ));
    }
}