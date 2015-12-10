<?php
/**
 * Netresearch_Buergel_Model_System_Config_Service
 * 
 * @category   Scoring
 * @package    Netresearch_Buergel
 * @author     Thomas Birke <thomas.birke@netresearch.de>
 * @copyright  Netresearch App Factory AG <http://www.netresearch.de/>
 */
class Netresearch_Buergel_Model_System_Config_Service extends Mage_Core_Model_Config_Data
{
    /**
     * Make sure that only one RiskCheck or ConCheck service is enabled
     *
     * @return void
     */
    public function _beforeSave()
    {
        $services = $this->getValue();
        $riskChecks = array();
        $conChecks = array();
        foreach ($services as $key=>$service) {
            if (true === Mage::getModel('buergel/config')->isConCheck($service)) {
               $conChecks[] = $service;
            } else {
                $riskChecks[] = $service;
            }
        }
        if (1 < count($riskChecks)) {
            Mage::throwException('It is not possible to activate more than one RiskCheck service.');
        }
        if (1 < count($conChecks)) {
            Mage::throwException('It is not possible to activate more than one ConCheck service.');
        }

        parent::_beforeSave();
    }
}

