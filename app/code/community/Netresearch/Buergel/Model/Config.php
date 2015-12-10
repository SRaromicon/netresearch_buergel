<?php
/**
 * Netresearch_Buergel_Model_Config
 * 
 * @category   Scoring
 * @package    Netresearch_Scoring
 * @author     Stephan Hoyer <stephan.hoyer@netresearch.de>
 * @copyright  Copyright (c) 2011 Netresearch GmbH & Co.KG <http://www.netresearch.de/>
 */
class Netresearch_Buergel_Model_Config extends Netresearch_Scoring_Model_Config  
{
    const CONFIG_PATH_TEST_URL    = 'buergel/test_url';
    const CONFIG_PATH_LIVE_URL    = 'buergel/live_url';
    const CONFIG_PATH_TEST_MODE   = 'buergel/test_mode';
    const CONFIG_PATH_SERVICES    = 'buergel/services';
    
    const CONFIG_PATH_TIMEOUT     = 'buergel/timeout';
    const CONFIG_PATH_USER_ID     = 'buergel/user_id';
    const CONFIG_PATH_PASSWORD    = 'buergel/password';
    const CONFIG_PATH_CUSTOMER_ID = 'buergel/customer_id';
    const CONFIG_PATH_PRODUCT_CONCHECK  = 'buergel/product_nr_concheck';
    const CONFIG_PATH_PRODUCT_RISKCHECK = 'buergel/product_nr_riskcheck';
    
    const TIMEOUT = 1;

    protected $isCompanyAddress = false;
    
    /**
     * Returns url of test mode.
     * 
     * @return string
     */
    public function getTestUrl()
    {
        return $this->get(self::CONFIG_PATH_TEST_URL);
    }
    
    /**
     * Returns url of test mode.
     * 
     * @return string
     */
    public function getLiveUrl()
    {
        return $this->get(self::CONFIG_PATH_LIVE_URL);
    }
    
    /**
     * Indicates if the config is set to test ore live mode.
     * 
     * @return boolean
     */
    public function isTestMode()
    {
        return $this->get(self::CONFIG_PATH_TEST_MODE);
    }
    
    /**
     * Returns timeout in seconds when requesting the service.
     * 
     * @return int
     */
    public function getTimeout()
    {
        return $this->get(self::CONFIG_PATH_TIMEOUT);
    }
    
    public function getUserId()
    {
        return $this->get(self::CONFIG_PATH_USER_ID);
    }
    
    public function getPassword()
    {
        return $this->get(self::CONFIG_PATH_PASSWORD);
    }

    public function getCustomerId()
    {
        return $this->get(self::CONFIG_PATH_CUSTOMER_ID);
    }

    /**
     * compare scoring values
     * 
     * @param mixed $first  Scoring value to compare
     * @param mixed $second Scoring value to compare
     * 
     * @return boolean True if first is better
     */
    public function isBetterScoring($first, $second)
    {
        return (is_numeric($first)
            && is_numeric($second)
            && (float) $first < (float) $second
        );
    }

    /**
     * get data segment id to use for requests
     *
     * @return string
     */
    public function getSegment()
    {
        return $this->isConsumerCheck() ? 'C55QN01' : 'C55QN03';
    }

    /**
     * get segment version
     * 
     * @return string
     */
    public function getSegmentVersion()
    {
        $version = '0204';
        if (Netresearch_Buergel_Model_System_Source_Service::RISKCHECK_STANDARD == $this->getProductNr()) {
            $version = '0203';
        }
        if (Netresearch_Buergel_Model_System_Source_Service::CONCHECK_BASIC == $this->getProductNr()) {
            $version = '0203';
        }
        return $version;
    }

    public function setIsCompanyAddress($isCompany=true) {
        $this->isCompanyAddress = $isCompany;
    }

    public function isCompanyAddress()
    {
        return $this->isCompanyAddress;
    }

    public function getServices()
    {
        return explode(',', $this->get(self::CONFIG_PATH_SERVICES));
    }

    /**
     * get productnr to use for requests
     *
     * @return string
     */
    public function getProductNr()
    {
        $services = $this->getServices();
        foreach ($services as $service) {
            if (false == $this->isCompanyAddress() && 
                true == $this->isConCheck($service)) {
                return $service;
            }
        }
        return $this->getRiskCheckProductNr();
    }

    /**
     * get productNr for RiskCheck, if none is available return the ConCheck one
     * 
     * @return string
     */
    public function getRiskCheckProductNr()
    {
        $services = $this->getServices();
        if (0 == count($services)) {
            return null;
        }
        foreach ($services as $service) {
            if (false === $this->isConCheck($service)) {
                return $service;
            }
        }
        return Netresearch_Buergel_Model_System_Source_Service::CONCHECK;
    }

    /**
     * if this has to be a customer check
     *
     * @return boolean
     */
    public function isConsumerCheck()
    {
        return (
            Netresearch_Buergel_Model_System_Source_Service::CONCHECK == $this->getProductNr()
            || Netresearch_Buergel_Model_System_Source_Service::CONCHECK_BASIC == $this->getProductNr())
            ;
    }

    /**
     * if this has to be a risk check (which includes checking solvency of customers and companies)
     *
     * @return boolean
     */
    public function isRiskCheck()
    {
        return (Netresearch_Buergel_Model_System_Source_Service::RISKCHECK_STANDARD == $this->getProductNr()
            || Netresearch_Buergel_Model_System_Source_Service::RISKCHECK_ADVANCED == $this->getProductNr()
            || Netresearch_Buergel_Model_System_Source_Service::RISKCHECK_PROFESSIONAL == $this->getProductNr());
    }
    
    public function isConCheck($services)
    {
        return in_array($services,
                array(
                    Netresearch_Buergel_Model_System_Source_Service::CONCHECK,
                    Netresearch_Buergel_Model_System_Source_Service::CONCHECK_BASIC
                    )
            );
    }
}
