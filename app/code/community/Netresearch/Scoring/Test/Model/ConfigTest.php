<?php
/**
 * Netresearch_Scoring_Test_Model_ConfigTest
 *
 * @category   Scoring
 * @package    Netresearch_Scoring
 * @author     Thomas Birke <thomas.birke@netresearch.de>
 * @copyright  Copyright (c) 2012 Netresearch App Factory AG <http://www.netresearch.de/>
 */
class Netresearch_Scoring_Test_Model_ConfigTest extends EcomDev_PHPUnit_Test_Case_Config
{
    protected $config;

    public function setUp()
    {
        $this->config = Mage::getModel('scoring/config');
    }

    /**
     * we expect to get the value we set before
     *
     * @test
     */
    public function shouldReturnSameValueForSimplePath()
    {
        $value = 'first';
        $this->config->set('foo', $value);
        $this->assertEquals($value, $this->config->get('foo'));

        $value = 'second';
        $this->config->setFoo($value);
        $this->assertEquals($value, $this->config->get('foo'));

        $value = 'third';
        $this->config->set('foo', $value);
        $this->assertEquals($value, $this->config->getFoo());

        $value = 'fourth';
        $this->config->setFoo($value);
        $this->assertEquals($value, $this->config->getFoo());
    }

    /**
     * we expect to get the value we set before
     *
     * @test
     */
    public function shouldReturnSameValuesForDeeperPath()
    {
        $value = 'first';
        $this->config->set('foo/bar', $value);
        $this->assertEquals($value, $this->config->get('foo/bar'));

        $value = 'second';
        $this->config->setFoo(array('bar' => $value));
        $this->assertEquals($value, $this->config->get('foo/bar'));

        $value = 'third';
        $this->config->set('foo/bar', $value);
        $this->assertEquals(array('bar' => $value), $this->config->getFoo());

        $value = 'fourth';
        $this->config->setFoo(array('bar' => $value));
        $this->assertEquals(array('bar' => $value), $this->config->getFoo());

        $value = array('some' => 'value', 'another' => 'value');
        $this->config->setFooBar($value);
        $this->assertEquals($value, $this->config->getFooBar());
    }

    /**
     * we expect to get the best matching solvency group
     *
     * no test since that is overwritten in Buergel extension
     */
    public function shouldReturnBestMatchingSolvencyGroup()
    {
        $groups = array(
            array(
                'name'    => 'Adel verpflichtet',
                'score'   => '5000',
                'methods' => array('foo', 'bar')
            ),
            array(
                'name'    => 'Ottonormalbürger',
                'score'   => 'ABCD',
                'methods' => array('foo')
            ),
            array(
                'name'    => 'Prekariat',
                'score'   => '1000',
                'methods' => array('Barzahlung')
            ),
        );
        $this->config->reset();
        $this->config->set('solvency-groups', array(
            'default-group' => 1,
            'solvency-groups' => serialize($groups)
        ));
        $this->assertEquals('5000', $this->config->getSolvencyGroupByScore(5001)->getScore());
        $this->assertEquals('5000', $this->config->getSolvencyGroupByScore('5001')->getScore());
        $this->assertEquals('5000', $this->config->getSolvencyGroupByScore('5000')->getScore());
        $this->assertEquals('5000', $this->config->getSolvencyGroupByScore(5000)->getScore());
        $this->assertEquals('1000', $this->config->getSolvencyGroupByScore('4999')->getScore());
        $this->assertEquals('1000', $this->config->getSolvencyGroupByScore('1000')->getScore());
        $this->assertEquals('ABCD', $this->config->getSolvencyGroupByScore('ABCD')->getScore());
        $this->assertEquals('Adel verpflichtet', $this->config->getSolvencyGroupByScore('5001')->getName());
        $this->assertEquals('Ottonormalbürger', $this->config->getSolvencyGroupByScore('ABCD')->getName());
        $this->assertEquals('Prekariat', $this->config->getSolvencyGroupByScore('4999')->getName());
    }

    /**
     * we expect to get the default set group
     *
     * @test
     */
    public function shouldReturnDefaultGroup()
    {
        $groups = array(
            1 => array(
                'name'    => 'Reiche Schnösel',
                'score'   => '5000',
                'methods' => array('foo', 'bar')
            ),
            2 => array(
                'name'    => 'Arme Tropfe',
                'score'   => 'ABCD',
                'methods' => array('foo')
            )
        );
        $this->config->set('solvency-groups', array(
            'default-group' => 1,
            'solvency-groups' => serialize($groups)
        ));
        $this->assertEquals(
            $groups[1]['name'],
            $this->config->getDefaultSolvencyGroup()->getName(),
            'Default group is not the one we expexted.'
        );
    }

    /**
     * we expect to raise an exception if configuration is set to non existing group
     *
     * @test
     */
    public function shouldRaiseNoGroupExceptionBecauseWrongConfig()
    {
        $this->config->set('solvency-groups', array(
            'default-group' => 1,
            'solvency-groups' => serialize(array())
        ));
        try {
            $this->config->getDefaultSolvencyGroup();
        } catch (Exception $e) {
            $this->assertEquals(
                'No proper default group selected, please contact merchant!',
                $e->getMessage(),
                'Exception has not expected message.'
            );
            return;
        }
        $this->fail('An expected exception has not been raised.');
    }

    /**
     * we expect to raise an exception if configuration is set to non existing group
     *
     * @test
     */
    public function shouldRaiseNoGroupExceptionBecausNoConfig()
    {
        $groups = array(
            0 => array(
                'name'    => 'Reiche Schnösel',
                'score'   => '5000',
                'methods' => array('foo', 'bar')
            ),
            1 => array(
                'name'    => 'Arme Tropfe',
                'score'   => 'ABCD',
                'methods' => array('foo')
            )
        );
        $this->config->set('solvency-groups', array(
            'solvency-groups' => serialize(array())
        ));
        try {
            $this->config->getDefaultSolvencyGroup();
        } catch (Exception $e) {
            $this->assertEquals(
                'No proper default group selected, please contact merchant!',
                $e->getMessage(),
                'Exception has not expected message.'
            );
            return;
        }
        $this->fail('An expected exception has not been raised.');
    }

    /**
     * We expect to get right solvencyGroup by given name
     *
     * @test
     */
    public function shouldReturnGroupByGivenName()
    {
        $groups = array(
            0 => array(
                'name'    => 'Foobar',
                'score'   => '5000',
                'methods' => array('foo', 'bar')
            ),
            1 => array(
                'name'    => 'BarBar',
                'score'   => 'ABCD',
                'methods' => array('foo')
            )
        );
        $this->config->set('solvency-groups/solvency-groups', serialize($groups));
        $this->assertEquals(
            $groups[1]['name'],
            $this->config->getSolvencyGroupByName($groups[1]['name'])->getName(),
            'Group given by name is not the one we expexted.'
        );
    }

    /**
     * if order requires solvency validation
     *
     * @test
     */
    public function shouldOrderRequireSolvencyValidation()
    {
        $this->store  = Mage::app()->getStore(0)->load(0);
        $this->store->resetConfig();
        $pathTotalMin = 'scoring/solvency/total_min';
        $pathSkipMethods = 'scoring/solvency/skip_methods';

        $this->store->setConfig($pathTotalMin, 10);
        $this->store->setConfig($pathSkipMethods, 'cc_saved,paypal');

        $config = Mage::getModel('scoring/config');
        $quote = Mage::getModel('sales/quote');
        $quote->getPayment()->setMethod('cc_saved');

        /* secure payment method, but below min total */
        $quote->setBaseGrandTotal(9);
        $this->assertFalse(
            $config->isSolvencyValidationRequiredForQuote($quote),
            'expected quote not to require solvency validation (secure method, below min)'
        );

        /* secure payment method, above min total */
        $quote->setBaseGrandTotal(1000);
        $this->assertFalse(
            $config->isSolvencyValidationRequiredForQuote($quote),
            'expected quote not to require solvency validation (secure method, above min)'
        );

        /* insecure payment method, below min total */
        $quote->setBaseGrandTotal(9);
        $quote->getPayment()->setMethod('open_invoice');
        $this->assertFalse(
            $config->isSolvencyValidationRequiredForQuote($quote),
            'expected quote not to require solvency validation (insecure method, below min)'
        );

        /* insecure payment method, above min total */
        $quote->setBaseGrandTotal(1000);
        $config->set('solvency/secure_methods', array('cc_saved'));
        $this->assertTrue(
            $config->isSolvencyValidationRequiredForQuote($quote),
            'expected quote to require solvency validation (insecure method, above min)'
        );
    }
}

