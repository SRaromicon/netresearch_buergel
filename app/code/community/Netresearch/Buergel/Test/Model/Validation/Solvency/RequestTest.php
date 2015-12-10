<?php
class Netresearch_Buergel_Test_Model_Validation_Solvency_RequestTest extends EcomDev_PHPUnit_Test_Case_Config
{
    protected $address;
    protected $config;
    protected $store;

    public function setUp()
    {
        $this->address = new Mage_Customer_Model_Address();
        $this->address->setFirstname('Joachim');
        $this->address->setLastname('Schmidt');
        $this->address->setStreet('Bei der Schmiede 5');
        $this->address->setCity('Hamburg');
        $this->address->setPostcode(21109);
        $this->address->setCountryId('DE');

        $this->store  = Mage::app()->getStore(0)->load(0);
        $this->store->resetConfig();
        $this->store->setConfig(
            'scoring/buergel/services',
            Netresearch_Buergel_Model_System_Source_Service::CONCHECK
        );

        $this->config = Mage::getModel('buergel/config');

        return parent::setUp();
    }

    /**
     * test Netresearch_Buergel_Model_Validation_Solvency_Request::convertAddress() with German address
     *
     * @return void
     */
    public function testConvertGermanAddress()
    {
        $this->address->setCountryId('DE');

        $request = new Netresearch_Buergel_Model_Validation_Solvency_Request($this->address);
        $request->setConfig($this->config);
        $this->assertEquals(
            array(
                'VORNAME'  => 'Joachim',
                'NAME1'    => 'Schmidt',
                'STRASSE'  => 'Bei der Schmiede',
                'HAUS_NR'  => '5',
                'ORT'      => 'Hamburg',
                'PLZ'      => '21109',
                'STAAT'    => '280'
            ),
            $request->convertAddress($this->address)
        );
    }

    /**
     * test Netresearch_Buergel_Model_Validation_Solvency_Request::convertAddress() with Austrian address
     *
     * @return void
     */
    public function testConvertAustrianAddress()
    {
        $this->address->setCountryId('AT');

        $request = new Netresearch_Buergel_Model_Validation_Solvency_Request($this->address);
        $request->setConfig($this->config);
        $this->assertEquals(
            array(
                'VORNAME'  => 'Joachim',
                'NAME1'    => 'Schmidt',
                'STRASSE'  => 'Bei der Schmiede',
                'HAUS_NR'  => '5',
                'ORT'      => 'Hamburg',
                'PLZ'      => '21109',
                'STAAT'    => '040'
            ),
            $request->convertAddress($this->address)
        );
    }

    /**
     * test Netresearch_Buergel_Model_Validation_Solvency_Request::convertAddress() with Swiss address
     *
     * @return void
     */
    public function testConvertSwissAddress()
    {
        $this->address->setCountryId('CH');

        $request = new Netresearch_Buergel_Model_Validation_Solvency_Request($this->address);
        $request->setConfig($this->config);
        $this->assertEquals(
            array(
                'VORNAME'  => 'Joachim',
                'NAME1'    => 'Schmidt',
                'STRASSE'  => 'Bei der Schmiede',
                'HAUS_NR'  => '5',
                'ORT'      => 'Hamburg',
                'PLZ'      => '21109',
                'STAAT'    => '756'
            ),
            $request->convertAddress($this->address)
        );
    }

    /**
     * test Netresearch_Buergel_Model_Validation_Solvency_Request::convertAddress() with Spanish address
     *
     * @return void
     */
    public function testConvertSpanishAddress()
    {
        $this->address->setCountryId('ES');

        $request = new Netresearch_Buergel_Model_Validation_Solvency_Request($this->address);
        $request->setConfig($this->config);
        $this->assertEquals(
            array(
                'VORNAME'  => 'Joachim',
                'NAME1'    => 'Schmidt',
                'STRASSE'  => 'Bei der Schmiede',
                'HAUS_NR'  => '5',
                'ORT'      => 'Hamburg',
                'PLZ'      => '21109',
                'STAAT'    => '276'
            ),
            $request->convertAddress($this->address)
        );
    }


    /**
     * test Netresearch_Buergel_Model_Validation_Solvency_Request::convertAddress() with Swiss address using RiskCheck
     *
     * @return void
     */
    public function testConvertSwissAddressForRiskCheck()
    {

        $this->store->resetConfig();
        $this->store->setConfig(
            'scoring/buergel/services',
            Netresearch_Buergel_Model_System_Source_Service::RISKCHECK_STANDARD
        );
        $this->config = Mage::getModel('buergel/config');
        $this->config->reset();

        $this->address->setCountryId('CH');

        $request = new Netresearch_Buergel_Model_Validation_Solvency_Request($this->address);
        $request->setConfig($this->config);
        $this->assertEquals(
            array(
                'NAME1'    => 'Joachim',
                'NAME2'    => 'Schmidt',
                'STRASSE'  => 'Bei der Schmiede',
                'HAUS_NR'  => '5',
                'ORT'      => 'Hamburg',
                'PLZ'      => '21109',
                'STAAT'    => '276'
            ),
            $request->convertAddress($this->address)
        );
    }

    public function testConvertBirthdate()
    {
        $quote = Mage::getModel('sales/quote');
        $quote->setCustomerDob(null);
        $this->assertEquals(
            array(),
            Mage::getModel('buergel/validation_solvency_request')->convertBirthdate($quote)
        );
        $quote->setCustomerDob(Mage::getModel('core/date')->gmtTimestamp('2012-11-10'));
        $this->assertEquals(
            array('GEBURTSDATUM' => '10.11.2012'),
            Mage::getModel('buergel/validation_solvency_request')->convertBirthdate($quote)
        );
    }

    public function testExcludeBirthdayIfThatIsNotGiven()
    {
        $quote = Mage::getModel('sales/quote');
        $quote->setCustomerDob(null);

        $this->config = Mage::getModel('buergel/config');
        $this->config->reset();

        $request = new Netresearch_Buergel_Model_Validation_Solvency_Request(
            $this->address,
            $quote
        );
        $request->setConfig($this->config);

        $requestString = $request->__toString();
        $this->assertNotContains('GEBURTSDATUM', $requestString);
    }

    public function testIncludeBirthdayIfThatIsGiven()
    {
        $quote = Mage::getModel('sales/quote');
        $quote->setCustomerDob(Mage::getModel('core/date')->gmtTimestamp('1974-03-02'));

        $this->config = Mage::getModel('buergel/config');
        $this->config->reset();

        $request = new Netresearch_Buergel_Model_Validation_Solvency_Request(
            $this->address,
            $quote
        );
        $request->setConfig($this->config);

        $requestString = $request->__toString();
        $this->assertContains(
            '<GEBURTSDATUM>02.03.1974</GEBURTSDATUM>',
            $requestString,
            'Expected birthdate to be part of the request'
        );
    }
}
