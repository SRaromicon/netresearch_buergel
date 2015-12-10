<?php
/**
 * Netresearch_Buergel_Model_Observer
 * 
 * @category   Scoring
 * @package    Netresearch_Buergel
 * @author     Thomas Kappel <thomas.kappel@netresearch.de>
 * @copyright  Copyright (c) 2011 Netresearch GmbH & Co.KG <http://www.netresearch.de/>
 */
class Netresearch_Buergel_Model_Observer
{
    protected $session;
    
    protected $service;
    
    /**
     * constructor
     *
     * @param Netresearch_Scoring_Model_Service $service
     * @param Netresearch_Scoring_Model_Session $session
     * 
     * @return Netresearch_Buergel_Model_Observer
     */
    public function __construct($service=null, $session=null)
    {
        $this->service = $service;
        if (is_null($this->service)) {
            $this->service = Mage::getModel('scoring/service');
        }
        $this->session = $session;
        if (is_null($this->session)) {
            $this->session = Mage::getModel('scoring/session');
        }
    }
    
    /**
     * get session
     *
     * @return Netresearch_Scoring_Model_Session
     */
    public function getSession()
    {
        return $this->session;
    }
    
    /**
     * get service
     *
     * @return Netresearch_Scoring_Model_Service
     */
    public function getService()
    {
        return $this->service;
    }
}
