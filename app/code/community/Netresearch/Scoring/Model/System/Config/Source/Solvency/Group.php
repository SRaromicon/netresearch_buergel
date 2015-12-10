<?php
/**
 * @category   Scoring
 * @package    Netresearch_Scoring
 * @author     Stephan Hoyer <stephan.hoyer@netresearch.de>
 * @copyright  Copyright (c) 2011 Netresearch GmbH & Co.KG <http://www.netresearch.de/>
 */
class Netresearch_Scoring_Model_System_Config_Source_Solvency_Group
{
    const SHIPPING = 'shipping';
    const BILLING = 'billing';

    public function toOptionArray()
    {
        $groups = array(array(
            'value' => '',
            'label' => Mage::helper('scoring')->__('-- none --')
        ));
        $existingGroups = Mage::getModel('scoring/config')->getSolvencyGroups();
        if (is_array($existingGroups)) {
            foreach ($existingGroups as $id => $group) {
                if($id <= 0) {
                    continue;
                }
                $groups[] = array(
                    'value' => $id,
                    'label' => $group['name'] 
                        ? $group['name']
                        : Mage::helper('scoring')->__('Group %d', $id)
                );
            }
        }
        return $groups;
    }
}
