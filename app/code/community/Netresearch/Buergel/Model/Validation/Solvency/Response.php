<?php
/**
 * Netresearch_Scoring_Model_Validation_Solvency_Response
 * 
 * @category   Scoring
 * @package    Netresearch_Scoring
 * @author     Thomas Kappel <thomas.kappel@netresearch.de>
 * @copyright  Copyright (c) 2011 Netresearch GmbH & Co.KG <http://www.netresearch.de/>
 */
class Netresearch_Buergel_Model_Validation_Solvency_Response
    extends Netresearch_Scoring_Model_Validation_Solvency_Response
{
    /**
     * @var SimpleXMLElement $response
     */
    protected $response;
    
    /**
     * Reformats response xml and creates simple xml object
     * 
     * @param string $responseXml
     * 
     * @return Netresearch_Buergel_Model_Validation_Solvency_Response
     */
    public function __construct($responseXml)
    {
        $responseXml = str_replace("\n", "", $responseXml);
        $responseXml = str_replace("\r", "", $responseXml);
        $responseXml = str_replace("\t", "", $responseXml);
        $this->response = simplexml_load_string($responseXml);
        parent::__construct($responseXml);
    }
    
    public function getResponseString()
    {
        return $this->response->asXML();
    }
    
    /**
     * get xpath relative to the body
     * 
     * @param string $bodyPath
     * 
     * @return string XPath
     */
    protected function getXpath($bodyPath)
    {
        return '/BWIDATA/*[name()!="HEADER"]' . $bodyPath;
    }
    
    public function getScore()
    {
        $score = $this->getResponseDataByXPath('/SCORE_WERT/text()');
        return '0000' === $score ? null : $score;
    }
    
    public function getDescription()
    {
        return $this->getResponseDataByXPath('/HINWEIS_TEXT/text()', '');
    }
    
    /**
     * If address was fixed
     * 
     * @return boolean
     */
    public function hasFixedAddress()
    {
        return in_array(
            (int) $this->getResponseDataByXPath('/ANSCHR_HERKUNFT/text()'),
            array(3, 4)
        );
    }
    
    public function getFixedAddress()
    {
        return array(
            'firstname' => $this->getResponseDataByXPath('/VORNAME/text()'),
            'lastname'  => $this->getResponseDataByXPath('/NAME1/text()'),
            'street'    => $this->getResponseDataByXPath('/STRASSE/text()')
                            . ' ' . $this->getResponseDataByXPath('/HAUS_NR/text()'),
            'zip'       => $this->getResponseDataByXPath('/PLZ/text()'),
            'city'      => $this->getResponseDataByXPath('/ORT/text()'),
        );
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
        if ($this->getConfig()->isTestMode()) {
            $this->getLog()->setResponse(utf8_encode($this->getResponseString()));
        }
        $this->getLog()->save();
        $this->getSession()->setLog($this->getLog());
    }
    
    /**
     * Parses the response with given xpath. 
     * If value isn't present, $fallback is returned.
     * 
     * @param String $xpath
     * @param mixed $fallback
     * 
     * @return mixed
     */
    protected function getResponseDataByXPath($xpath, $fallback=null) 
    {
        $data = $this->response->xpath($this->getXpath($xpath));
        if(is_array($data)) {
            return (string) current($data);
        }
        return $fallback;
    }
}
