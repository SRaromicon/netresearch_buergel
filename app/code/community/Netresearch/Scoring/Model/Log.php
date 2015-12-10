<?php
/**
 * Netresearch_Scoring_Model_Log
 * 
 * @category   Scoring
 * @package    Netresearch_Scoring
 * @author     Thomas Kappel <thomas.kappel@netresearch.de>
 * @copyright  Copyright (c) 2011 Netresearch GmbH & Co.KG <http://www.netresearch.de/>
 */
class Netresearch_Scoring_Model_Log extends Mage_Core_Model_Abstract
{
    public function __construct()
    {
        $this->_init('scoring/log');
    }

    /**
     * Get the name of the solvency group
     * 
     * @return string
     */
    public function getSolvencyGroupName()
    {
        return $this->getGroup();
    }
    
    /**
     * Get the name of the solvency group
     * 
     * @return string
     */
    public function getSolvencyGroup()
    {
        return $this->getConfig()->getSolvencyGroupByName(
            $this->getSolvencyGroupName()
        );
    }

    
    /**
     * Set the count of checkouts after this request
     * 
     * @return Netresearch_Scoring_Model_Log
     */
    public function increaseCheckoutsAfterThisRequest()
    {
        $this->setCheckoutsAfterThisRequest(
            $this->getData('checkouts_after_this_request') + 1
        );
        return $this;
    }
    
    public function isStillValid()
    {
        $validTo   = time() - (int) $this->getConfig()->get('re-request/log_vadility_lifetime');
        $createdAt = new Zend_Date($this->getCreatedAt());
        $previousCheckouts = (int) $this->getCheckoutsAfterThisRequest();
        $maxCheckouts      = (int) $this->getConfig()->get('re-request/max_number_of_checkouts');
        return
            $previousCheckouts < $maxCheckouts
            && $validTo <= $createdAt->getTimestamp();
    }
    
    /**
     * get scoring config
     * 
     * @return Netresearch_Scoring_Model_Config
     */
    public function getConfig()
    {
        if (is_null($this->config)) {
            $this->config = Mage::getModel('scoring/config');
        }
        return $this->config;
    }
}
