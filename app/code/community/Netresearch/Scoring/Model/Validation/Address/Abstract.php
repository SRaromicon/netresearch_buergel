<?php
/**
 * Netresearch_Scoring_Model_Validation_Address_Abstract
 * 
 * @category   Scoring
 * @package    Netresearch_Scoring
 * @author     Thomas Kappel <thomas.kappel@netresearch.de>
 * @copyright  Copyright (c) 2011 Netresearch GmbH & Co.KG <http://www.netresearch.de/>
 */
abstract class Netresearch_Scoring_Model_Validation_Address_Abstract
{
    public function isFailed($quote)
    {
        return false;
    }
}