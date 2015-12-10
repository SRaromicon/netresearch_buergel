<?php
/**
 * One page checkout status
 *
 * @category   Scoring
 * @package    Netresearch_Scoring
 * @author     Thomas Kappel <thomas.kappel@netresearch.de>
 * @copyright  Copyright (c) 2011 Netresearch GmbH & Co.KG <http://www.netresearch.de/>
 */

/**
 * Netresearch_Scoring_Block_Checkout_Onepage_Payment_Methods
 *
 * @category   Scoring
 * @package    Netresearch_Scoring
 * @author     Thomas Kappel <thomas.kappel@netresearch.de>
 * @copyright  Copyright (c) 2011 Netresearch GmbH & Co.KG <http://www.netresearch.de/>
 */
class Netresearch_Scoring_Block_Checkout_Onepage_Payment_Methods extends Mage_Checkout_Block_Onepage_Payment_Methods
{
    /**
     * Retrieve availale payment methods
     *
     * @return array
     */
    public function getMethods()
    {
        // Get payment methods form scoring session.
        if (false == Mage::getModel('scoring/session')->hasSolvencyGroup()) {
            return parent::getMethods();
        }
        $methods = Mage::getModel('scoring/session')->getSolvencyValidationResultArray();
        $result  = array();
        // Check allowed payment methods and retrieve availale payment methods.
        foreach (parent::getMethods() as $method):
            // Hide payment methods, which are not allowed exists in scoring session.
            if (
                false == isset($methods['allowed_payment_methods'])
                || in_array($method->getCode(), $methods['allowed_payment_methods'])
            ) {
                $result[] = $method;
            }
        endforeach;
        return $result;
    }
}
