<?php
/**
 * @category   Scoring
 * @package    Netresearch_Scoring
 * @author     Thomas Kappel <thomas.kappel@netresearch.de>
 * @copyright  Copyright (c) 2011 Netresearch GmbH & Co.KG <http://www.netresearch.de/>
 */

class Netresearch_Scoring_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function array2xml($value, $name='array', $beginning=true)
    {
        $output = "";
        if ($beginning) {
            $output .= '<?xml version="1.0" encoding="UTF-8"?>';
            $output .= sprintf('<%s>', $name);
        }

        // This is required because XML standards do not allow a tag to start with a number or symbol, you can change this value to whatever you like:
        $ArrayNumberPrefix = 'ARRAY_NUMBER_';
        if (is_array($value)) {
            foreach ($value as $root=>$child) {
                $root = is_string($root) ? $root : $ArrayNumberPrefix . $root;
                $output .= sprintf('<%s>%s</%s>', 
                    $root, 
                    $this->array2xml($child,NULL,false), 
                    $root
                );
            }
        } else {
            $output .= $value;
        }
        if ($beginning)  {
            $output .= sprintf('</%s>', $name);
        }
        return $output;
    }
    
    /**
     * Get last log of a customer
     * 
     * @param int $customerId
     * 
     * @return Netresearch_Scoring_Model_Log
     */
    public function getLastLogOfCustomer($customerId)
    {
        return $this->getLogCollection()
            ->addFieldToFilter('customer_id', $customerId)
            ->getFirstItem();
    }
    
    /**
     * Get the log collection
     * 
     * @return Netresearch_Scoring_Model_Mysql4_Log_Collection
     */
    public function getLogCollection()
    {
        if (empty($this->logCollection)) {
            $this->logCollection = Mage::getModel('scoring/log')->getCollection();
        }
        return $this->logCollection;
    }
}