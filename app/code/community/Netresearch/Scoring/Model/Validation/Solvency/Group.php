<?php
/**
 * Netresearch_Scoring_Model_Validation_Solvency_Group
 * 
 * @category   Scoring
 * @package    Netresearch_Scoring
 * @author     Thomas Kappel <thomas.kappel@netresearch.de>
 * @copyright  Copyright (c) 2011 Netresearch GmbH & Co.KG <http://www.netresearch.de/>
 */
class Netresearch_Scoring_Model_Validation_Solvency_Group
{
    /** @var string */
    protected $name;
    
    /** @var string */
    protected $score;
    
    /** @var array */
    protected $paymentMethods;
    
    /**
     * constructor
     * 
     * @param string $name    Name of the solvency group
     * @param string $score   Minimum solvency score
     * @param array  $methods Payment methods
     * 
     * @return Netresearch_Scoring_Model_Validation_Solvency_Group
     */
    public function __construct($name, $score, $paymentMethods)
    {
        $this->name           = $name;
        $this->score          = $score;
        $this->paymentMethods = $paymentMethods;
    }
    
    /**
     * get the name of the solvency group
     * 
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
    
    /**
     * get the score of the solvency group
     * 
     * @return string
     */
    public function getScore()
    {
        return $this->score;
    }
    
    /**
     * get the name of the solvency group
     * 
     * @return array
     */
    public function getPaymentMethods()
    {
        return $this->paymentMethods;
    }
    
    /**
     * if order is allowed for this user
     *
     * @param Mage_Sales_Model_Quote $quote
     * 
     * @return boolean
     */
    public function allowsOrder(Mage_Sales_Model_Quote $quote)
    {
        return in_array($quote->getPayment()->getMethod(), $this->getPaymentMethods());
    }
}