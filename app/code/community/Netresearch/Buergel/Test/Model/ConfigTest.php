<?php
class Netresearch_Buergel_Test_Model_ConfigTest extends EcomDev_PHPUnit_Test_Case_Config
{
    /**
     * @var Mage_Core_Model_Store
     */
    protected $store;

    /**
     * @var Netresearch_Buergel_Model_Config
     */
    protected $config;

    public function setUp()
    {
        $this->store  = Mage::app()->getStore(0)->load(0);
        $this->config = Mage::getModel('buergel/config');
        parent::setUp();
    }

    public function testGetProductNrConCheck()
    {
        $this->store->resetConfig();

        $path = 'scoring/buergel/services';
        $this->config = Mage::getModel('buergel/config');
        $this->assertEquals(Netresearch_Buergel_Model_System_Source_Service::CONCHECK, $this->config->getProductNr());
        $this->config->setIsCompanyAddress();
        $this->assertEquals(Netresearch_Buergel_Model_System_Source_Service::CONCHECK, $this->config->getProductNr());
        $this->config->reset();
    }

    public function testGetProductNrConCheckAndRiskCheckStandard()
    {
        $this->store->resetConfig();
        $path = 'scoring/buergel/services';
        $this->store->setConfig($path, implode(',', array(
            Netresearch_Buergel_Model_System_Source_Service::CONCHECK,
            Netresearch_Buergel_Model_System_Source_Service::RISKCHECK_STANDARD,
        )));
        $this->config = Mage::getModel('buergel/config');
        $this->config->reset();
        $this->assertEquals(
            array(
                Netresearch_Buergel_Model_System_Source_Service::CONCHECK,
                Netresearch_Buergel_Model_System_Source_Service::RISKCHECK_STANDARD,
            ),
            $this->config->getServices()
        );
        $this->assertFalse($this->config->isCompanyAddress());
        $this->assertEquals(Netresearch_Buergel_Model_System_Source_Service::CONCHECK, $this->config->getProductNr());

        $this->config->setIsCompanyAddress();
        $this->assertTrue($this->config->isCompanyAddress());
        $this->assertEquals(Netresearch_Buergel_Model_System_Source_Service::RISKCHECK_STANDARD, $this->config->getProductNr());
        $this->config->reset();
    }

    public function testGetProductNrConCheckAndRiskCheckAdvanced()
    {
        $this->store->resetConfig();
        $path = 'scoring/buergel/services';
        $this->store->setConfig($path, implode(',', array(
            Netresearch_Buergel_Model_System_Source_Service::CONCHECK,
            Netresearch_Buergel_Model_System_Source_Service::RISKCHECK_ADVANCED,
        )));
        $this->config->reset();
        $this->assertEquals(Netresearch_Buergel_Model_System_Source_Service::CONCHECK, $this->config->getProductNr());

        $this->config->setIsCompanyAddress();
        $this->assertEquals(Netresearch_Buergel_Model_System_Source_Service::RISKCHECK_ADVANCED, $this->config->getProductNr());
        $this->config->reset();
    }

    public function testGetProductNrConCheckAndRiskCheckProfessional()
    {
        $path = 'scoring/buergel/services';
        $this->store->resetConfig();
        $this->store->setConfig($path, implode(',', array(
            Netresearch_Buergel_Model_System_Source_Service::CONCHECK,
            Netresearch_Buergel_Model_System_Source_Service::RISKCHECK_PROFESSIONAL,
        )));
        $this->config->reset();
        $this->config->setIsCompanyAddress(false);
        $this->assertEquals(Netresearch_Buergel_Model_System_Source_Service::CONCHECK, $this->config->getProductNr());

        $this->store->setConfig($path, implode(',', array(
            Netresearch_Buergel_Model_System_Source_Service::RISKCHECK_PROFESSIONAL,
        )));
        $this->config->setIsCompanyAddress(true);
        $this->assertEquals(Netresearch_Buergel_Model_System_Source_Service::RISKCHECK_PROFESSIONAL, $this->config->getProductNr());
        $this->config->reset();
    }

    public function testGetProductNrRiskCheckAdvanced()
    {
        $path = 'scoring/buergel/services';
        $this->store->resetConfig();
        $this->store->setConfig($path, Netresearch_Buergel_Model_System_Source_Service::RISKCHECK_ADVANCED);
        $this->config = Mage::getModel('buergel/config');
        $this->config->reset();
        $this->config->setIsCompanyAddress(false);
        $this->assertEquals(Netresearch_Buergel_Model_System_Source_Service::RISKCHECK_ADVANCED, $this->config->getProductNr());

        $this->config->setIsCompanyAddress();
        $this->assertEquals(Netresearch_Buergel_Model_System_Source_Service::RISKCHECK_ADVANCED, $this->config->getProductNr());
        $this->config->reset();
    }
}
