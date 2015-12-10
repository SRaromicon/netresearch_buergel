<?php
/**
 * Netresearch_Buergel_Model_Validation_Solvency_Request
 * 
 * @category   Scoring
 * @package    Netresearch_Buergel
 * @author     Stephan Hoyer <stephan.hoyer@netresearch.de>
 * @copyright  Copyright (c) 2011 Netresearch GmbH & Co.KG <http://www.netresearch.de/>
 */
class Netresearch_Buergel_Model_Validation_Solvency_Request
{
    protected $address;
    protected $quote;
    protected $config;
    protected $helper;

    public function __construct($address, $quote=null)
    {
        $this->address = $address;
        $this->quote   = $quote;
    }

    /**
     * Creates XML represantation of the request an returns it.
     *
     * @return string
     */
    public function __toString()
    {
        /* service may depend on if address is a company address or not, so we let the config know that */
        $this->config->setIsCompanyAddress(0 < strlen($this->address->getCompany()));

        try {
            $this->data = array();
            $this->data['HEADER'] = array(
                'SYSTEM_CODE'           => 'BAS',
                'KOMM_METHODE'          => 'TS',
                'KOMM_TIMEOUT_SEKUNDEN' => '003',
                'GP_ID'                 => $this->getConfig()->getSegment(),
                'KNDNR'                 => '042068481',
                'KNDFILIALE'            => 'KNDFILIALE',
                'TRANSFNK'              => 'AN',
                'USERID'                => $this->getConfig()->getUserId(),
                'SEGMENTNAME'           => $this->getConfig()->getSegment(),
                'SEGMENTVERSION'        => $this->getConfig()->getSegmentVersion(),
                'FREMD_USERID'          => '',
                'DIALOGSPRACHE'         => '01',
                'XML_MARKUP_KZ'         => '01',
            );
            $this->data[$this->getConfig()->getSegment()] = array_merge(
                $this->convertAddress($this->address),
                $this->convertBirthdate($this->quote),
                array(
                    'PRODUKT_NR'    => $this->getConfig()->getProductNr(),
                    'VERSANDART'    => 6,
                    'ANF_ART'       => 70,
                    'LIEFERSPRACHE' => 1,
                    'KND_KEY1'      => '',
                    'KND_KEY2'      => ''
                )
            );
            return $this->getHelper()->array2xml($this->data, 'BWIDATA');
        } catch (Exception $e) {
            Mage::log($e->getMessage());
        }
    }

    /**
     * Takes a magento address object and generates an array 
     * containing data for gateway request
     *
     * @param Mage_Customer_Model_Address $address
     *
     * @return array
     */
    public function convertAddress($address)
    {
        if ($this->getConfig()->isConsumerCheck()) {
            switch ($address->getCountryId()) {
            case 'AT':
                $staat = '040';
                break;
            case 'CH':
                $staat = '756';
                break;
            case 'DE':
                $staat = '280';
                break;
            default:
                $staat = '276';
                break;
            }
        } else {
            $staat = 'DE' == $address->getCountryId() ? '280' : '276';
        }
        $addressData = array(
            $this->_getFirstNameFieldName() => utf8_decode($this->_getFirstNameValue($address)),
            $this->_getLastNameFieldName()  => utf8_decode($this->_getLastNameValue($address)),
            'STRASSE' => utf8_decode($this->getStreet(implode(' ', $address->getStreet()))),
            'HAUS_NR' => utf8_decode($this->getStreetNumber(implode(' ', $address->getStreet()))),
            'ORT'     => utf8_decode($address->getCity()),
            'PLZ'     => utf8_decode($address->getPostcode()),
            'STAAT'   => $staat
        );
        return $addressData;
    }

    /**
     * add birthdate to params list, if it is available
     *
     * @param Mage_Sales_Model_Quote $quote Quote
     * @return array Either empty or with birthdate
     */
    public function convertBirthdate($quote)
    {
        if (is_null($quote->getCustomerDob())) {
            return array();
        }
        return array(
            'GEBURTSDATUM' => Mage::getModel('core/date')->date('d.m.Y', $quote->getCustomerDob())
        );
    }

    /**
     * Get value for first name
     *
     * @return string
     */
    protected function _getFirstNameValue($address)
    {
        if ($this->getConfig()->isRiskCheck() && 0 < strlen($address->getCompany())) {
            return $address->getCompany();
        }
        return $address->getFirstname();
    }

    /**
     * Get value for last name
     *
     * @return string
     */
    protected function _getLastNameValue($address)
    {
        if ($this->getConfig()->isConsumerCheck() || 0 == strlen($address->getCompany())) {
            return $address->getLastname();
        }
    }

    /**
     * Get field name for first name depending of used service
     *
     * @return string
     */
    protected function _getFirstNameFieldName()
    {
        return $this->getConfig()->isConsumerCheck() ? 'VORNAME' : 'NAME1';
    }

    /**
     * Get field name for last name depending of used service
     *
     * @return string
     */
    protected function _getLastNameFieldName()
    {
        return $this->getConfig()->isConsumerCheck() ? 'NAME1' : 'NAME2';
    }

    /**
     * Splits street and returns name part.
     * 
     * @param string $street
     * 
     * @return string
     */
    protected function getStreet($street) {
        $streetParts = $this->splitStreetNumber($street);
        return $streetParts['street'];
    }

    /**
     * Splits street and returns number part.
     * 
     * @param string $street
     * 
     * @return string
     */
    protected function getStreetNumber($street) {
        $streetParts = $this->splitStreetNumber($street);
        return $streetParts['street_number'];
    }

    /**
     * Splits street and returns parts as array.
     * 
     * @param string $street
     * 
     * @return array
     */
    protected function splitStreetNumber($street)
    {
        $streetParts = array();
        $street = preg_split("/\s/", $street);
        // cut of last block of street and treat as street number
        if(count($street) > 1) {
            $streetParts['street_number'] = array_pop($street);
            $streetParts['street'] = implode(' ', $street);
        } else {
            $streetParts['street_number'] = '';
            $streetParts['street'] = implode(' ', $street);
        }
        return $streetParts;
    }

    /**
     * getHelper 
     * 
     * @return Netresearch_Buergel_Helper_Scoring
     */
    protected function getHelper()
    {
        if (is_null($this->helper)) {
            $this->helper = Mage::helper('scoring');
        }
        return $this->helper;
    }

    public function setHelper($helper)
    {
        $this->helper = $helper;
        return $this;
    }

    /**
     * Get scoring configuration
     * 
     * @return Netresearch_Buergel_Model_Config
     */
    protected function getConfig()
    {
        return $this->config;
    }

    public function setConfig($config)
    {
        $this->config = $config;
        return $this;
    }

}
