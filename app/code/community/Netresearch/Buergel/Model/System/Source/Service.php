<?php
class Netresearch_Buergel_Model_System_Source_Service
{
    const CONCHECK                = '0040';
	const CONCHECK_PLUS           = '0043';
    const CONCHECK_BASIC = '0042';
    const RISKCHECK_STANDARD      = '0075';
    const RISKCHECK_ADVANCED      = '0046';
    const RISKCHECK_PROFESSIONAL  = '0077';

    public function toOptionArray()
    {
        return array(
            array(
                'value' => self::CONCHECK_BASIC,
                'label' => 'ConCheck basic',
            ),
            array(
                'value' => self::CONCHECK,
                'label' => 'ConCheck',
            ),
            array(
                'value' => self::CONCHECK_PLUS,
                'label' => 'ConCheck plus',
            ),
            array(
                'value' => self::RISKCHECK_STANDARD,
                'label' => 'RiskCheck standard',
            ),
            array(
                'value' => self::RISKCHECK_ADVANCED,
                'label' => 'RiskCheck Advanced',
            ),
            array(
                'value' => self::RISKCHECK_PROFESSIONAL,
                'label' => 'RiskCheck Professional',
            ),
        );
    }
}