<?php

/**
 * Netresearch_Scoring_Model_System_Config_Backend_Solvency_SkipMethods
 *
 * @category   Scoring
 * @package    Netresearch_Scoring
 * @author     Thomas Birke <thomas.birke@netresearch.de>
 * @copyright  Copyright (c) 2012 Netresearch App Factory AG <http://www.netresearch.de/>
 */
class Netresearch_Scoring_Model_System_Config_Backend_Solvency_SkipMethods extends Mage_Core_Model_Config_Data {

    public function _beforeSave() {
        $newConfigData = Mage::app()->getRequest()->getParam('groups');
        $groups = $newConfigData['solvency-groups']['fields']['solvency-groups']['value'];
        $alwaysAllowed = null;
        foreach ($groups as $key => $group) {
            if (array_key_exists('methods', $group)) {
                if (is_null($alwaysAllowed)) {
                    $alwaysAllowed = $group['methods'];
                } else {
                    $alwaysAllowed = array_intersect($alwaysAllowed, $group['methods']);
                }
            }
        }
        $skippedButForbidden = array_diff($this->getValue(), $alwaysAllowed);
        if ($skippedButForbidden) {
            Mage::getSingleton('adminhtml/session')->addNotice(
                    Mage::helper('scoring')->__(
                            'Solvency validation will be skipped for orders with payment method <span style="font-style:italic">%s</span>, although there are solvency groups that deny that method! Please check your settings again!', implode(', ', $skippedButForbidden)
                    )
            );
        }

        parent::_beforeSave();
    }

}
