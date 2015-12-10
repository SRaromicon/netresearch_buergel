<?php
/**
 * Netresearch_Scoring_Model_Config
 *
 * @category   Scoring
 * @package    Netresearch_Scoring
 * @author     Thomas Kappel <thomas.kappel@netresearch.de>
 * @copyright  Copyright (c) 2011 Netresearch GmbH & Co.KG <http://www.netresearch.de/>
 */
class Netresearch_Scoring_Model_Config
{
    /** @var array */
    protected $config;

    /** @var array */
    protected $solvencyGroups=array();

    public function __construct($config=null)
    {
        $this->config = $config;
        if (empty($this->config)) {
            $this->config = Mage::getStoreConfig('scoring');
        }
    }

    public function reset()
    {
        $this->config = Mage::getStoreConfig('scoring');
    }

    public function __call($method, $args)
    {
        $firstArgument = isset($args[0]) ? $args[0] : null;
        if ('get' == substr($method, 0, 3)) {
            return $this->get($this->_underscore(substr($method, 3)), $firstArgument);
        }
        if ('set' == substr($method, 0, 3)) {
            return $this->set($this->_underscore(substr($method, 3)), $firstArgument);
        }
    }

    /**
     * get config value
     *
     * @param string $path    Path of the config value
     * @param mixed  $default Default value
     *
     * @return mixed
     */
    public function get($path=null, $default=null)
    {
        $value = $this->config;
        if (false == is_null($path)) {
            foreach (explode('/', $path) as $key) {
                if (array_key_exists($key, $value)) {
                    $value = $value[$key];
                } else {
                    return null;
                }
            }
        }
        return $value;
    }

    /**
     * set config value (for testing purposes only!)
     *
     * @param string $path  Path of the config value
     * @param mixed  $value Value
     *
     * @return Netresearch_Scoring_Model_Config
     */
    public function set($path, $value)
    {
        if (is_string($path)) {
            $steps = explode('/', $path);
            if (array_key_exists($steps[0], $this->config)
                && array_key_exists(1, $steps)
                && array_key_exists($steps[1], $this->config[$steps[0]])
            ) {
                if (is_array($value)
                    || is_array($this->config[$steps[0]][$steps[1]])
                ) {
                    $value = array_merge(
                        $value,
                        $this->config[$steps[0]][$steps[1]]
                    );
                } else {
                    $this->config[$steps[0]][$steps[1]] = $value;
                }
            } else {
                foreach (array_reverse($steps, true) as $step) {
                    $value = array($step => $value);
                }
                $this->config = array_merge($this->config, $value);
            }
        }
        return $this;
    }

    public function getSolvencyGroups()
    {
        return unserialize($this->get('solvency-groups/solvency-groups'));
    }

    /**
     * get solvency group depending on a given score
     *
     * @param string $score
     *
     * @return Netresearch_Scoring_Model_Validation_Solvency_Group
     */
    public function getSolvencyGroupByScore($score)
    {
        if (0 < strlen($score) && false == isset($this->solvencyGroups[$score])) {
            $groups = $this->getSolvencyGroups();
            $groupCandidate = null;
            /* find matching group */
            foreach ($groups as $key=>$group) {
                if ($score == $group['score']) {
                    $groupCandidate = $group;
                    break;
                }
                if ($this->isBetterScoring($score, $group['score'])) {
                    if (is_null($groupCandidate)
                        || $this->isBetterScoring($group['score'], $groupCandidate['score'])
                    ) {
                        $groupCandidate = $group;
                    }
                }
            }
            if (false == is_null($groupCandidate)) {
                /* return new group instance */
                $this->solvencyGroups[$score] = new Netresearch_Scoring_Model_Validation_Solvency_Group(
                    $groupCandidate['name'],
                    $groupCandidate['score'],
                    $groupCandidate['methods']
                );
            }
        }
        if (isset($this->solvencyGroups[$score])) {
            return $this->solvencyGroups[$score];
        }
        return $this->getDefaultSolvencyGroup();
    }

    /**
     * Get solvency group depending on a given name
     *
     * @param string $name
     *
     * @return Netresearch_Scoring_Model_Validation_Solvency_Group
     */
    public function getSolvencyGroupByName($name)
    {
        foreach ($this->getSolvencyGroups() as $group) {
            if ($group['name'] === $name) {
                return new Netresearch_Scoring_Model_Validation_Solvency_Group(
                    $group['name'], 
                    $group['score'], 
                    $group['methods']
                );
            }
        }
    }

    /**
     * compare scoring values
     *
     * @param mixed $first  Scoring value to compare
     * @param mixed $second Scoring value to compare
     *
     * @return boolean True if first is better
     */
    public function isBetterScoring($first, $second)
    {
        return (is_numeric($first)
            && is_numeric($second)
            && (float) $second < (float) $first
        );
    }

    /**
     * get the default solvency group
     *
     * @param string $score
     *
     * @return Netresearch_Scoring_Model_Validation_Solvency_Group
     */
    public function getDefaultSolvencyGroup()
    {
        $groups = $this->getSolvencyGroups();
        $defaultGroupId = $this->get('solvency-groups/default-group');
        $helper = Mage::helper('scoring');
        if (!array_key_exists($defaultGroupId, $groups)) {
            throw new Exception(
                $helper->__('No proper default group selected, please contact merchant!')
            );
        }
        $group = $groups[$defaultGroupId];
        if (!isset($group['methods'])) {
            throw new Exception(
                $helper->__('No Payment methods available, please contact merchant!')
            );
        }
        return new Netresearch_Scoring_Model_Validation_Solvency_Group(
            $group['name'],
            $group['score'],
            $group['methods']
        );
    }

    /**
     * Get solvency validation error message
     *
     * @return string
     */
    public function getSolvencyValidationFailedMessage()
    {
        return $this->get('errormessages/solvency');
    }

    /**
     * If solvency validation is active
     *
     * @return boolean
     */
    public function isSolvencyValidationActive()
    {
        return 1 == $this->get('solvency/validation_active');
    }

    /**
     * If address validation is active
     *
     * @return boolean
     */
    public function isAddressValidationActive()
    {
        return 1 == $this->get('address/validation_active');
    }

    /**
     * If solvency validation is required for this quote
     *
     * @return boolean
     */
    public function isSolvencyValidationRequiredForQuote($quote)
    {
        if ($this->get('solvency/total_min') < $quote->getBaseGrandTotal()
            && false == $this->isSecurePaymentMethod($quote->getPayment()->getMethod())
        ) {
            return true;
        }
        return false;
    }

    /**
     * if payment method is assumed to be secure
     *
     * @param mixed $methodCode Payment method code
     * @return bool
     */
    public function isSecurePaymentMethod($methodCode)
    {
        return in_array(
            $methodCode,
            $this->getSecurePaymentMethods()
        );
    }

    public function getSecurePaymentMethods()
    {
        return explode(',', $this->get('solvency/skip_methods'));
    }

    /**
     * Converts field names for setters and geters
     *
     * $this->setMyField($value) === $this->setData('my_field', $value)
     * Uses cache to eliminate unneccessary preg_replace
     *
     * @param string $name
     * @return string
     */
    protected function _underscore($name)
    {
        return strtolower(preg_replace('/(.)([A-Z])/', "$1_$2", $name));
    }
}