<?php
/**
 * Netresearch_Buergel_Model_Service
 *
 * @category   Scoring
 * @package    Netresearch_Buergel
 * @author     Thomas Birke <thomas.birke@netresearch.de>
 * @copyright  Copyright (c) 2012 Netresearch GmbH & Co.KG <http://www.netresearch.de/>
 */
class Netresearch_Buergel_Model_Service extends Netresearch_Scoring_Model_Service
{
    protected $fixedAddress=array();

    /**
     * get customer solvency
     *
     * @param Mage_Sales_Model_Quote $quote
     *
     * @return Netresearch_Scoring_Model_Validation_Solvency_Group
     */
    public function getSolvencyGroup($quote)
    {
        $solvencyGroup = parent::getSolvencyGroup($quote);
        if ($solvencyGroup
            && false == $this->getConfig()->get('basic/check_always')
            && true == $this->getSession()->hasSolvencyGroup()
        ) {
            return $solvencyGroup;
        }
        Mage::log('running a new solvency check');
        try {
            /** @var $request Zend_Http_Client */
            $request = $this->getSolvencyRequest($quote);
            /** @var $response SimpleXMLElement */
            $response = $request->request()->getBody();

            if ($this->getConfig()->isTestmode()) {
                Mage::log(urldecode((string) $request->getLastRequest()));
                Mage::log((string) $response);
            }
            $response = new Netresearch_Buergel_Model_Validation_Solvency_Response(
                $response
            );
            $this->fixedAddress = $response->getFixedAddress();
            return $response->getGroup();
        } catch (Exception $e) {
            Mage::logException($e);
            return $this->getConfig()->getDefaultSolvencyGroup();
        }
    }

    /**
     * (non-PHPdoc)
     * @see ee-1.9.0/app/code/community/Netresearch/Scoring/Model/Netresearch_Scoring_Model_Service::getConfig()
     *
     * @return Netresearch_Buergel_Model_Config
     */
    public function getConfig()
    {
        if (empty($this->config)) {
            $this->config = Mage::getModel('buergel/config');
        }
        return $this->config;
    }

    /**
     * get solvency request
     *
     * @param Mage_Sales_Model_Quote $quote
     *
     * @return Zend_Http_Client
     */
    public function getSolvencyRequest(Mage_Sales_Model_Quote $quote)
    {
        if ($this->getConfig()->isTestmode()) {
            $url = $this->getConfig()->getTestUrl();
        } else {
            $url = $this->getConfig()->getLiveUrl();
        }
        $client = new Zend_Http_Client($url, array(
            'maxredirects' => 0,
            'timeout'      => $this->getConfig()->getTimeout(),
        ));
        $request = new Netresearch_Buergel_Model_Validation_Solvency_Request(
            $this->getAddress($quote),
            $quote
        );
        $request->setHelper($this->getHelper());
        $request->setConfig($this->getConfig());
        $client->setMethod(Zend_Http_Client::POST);
        $client->setAuth(
            $this->getConfig()->getUserId(), 
            $this->getConfig()->getPassword()
        );
        $client->setParameterPost('eing_dat', '' . $request);
        return $client;
    }
}
