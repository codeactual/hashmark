<?php
// vim: fenc=utf-8:ft=php:ai:si:ts=4:sw=4:et:

/**
 * Hashmark_TestCase_Client
 *
 * @filesource
 * @link        http://code.google.com/p/hashmark/
 * @link        http://framework.zend.com/manual/en/coding-standard.html
 * @link        http://phpdoc.org/tutorial.php
 * @copyright   Copyright (c) 2008-2009, Code Actual LLC
 * @license     http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package     Hashmark-Test
 * @subpackage  Base
 * @version     $Id: Client.php 294 2009-02-13 03:48:59Z david $
*/

/**
 * @package     Hashmark-Test
 * @subpackage  Base
 */
class Hashmark_TestCase_Client extends Hashmark_TestCase
{
    /**
     * Resources needed for most tests.
     *
     * @return void
     */
    protected function setUp()
    {
        parent::setUp();
        
        $this->_client = Hashmark::getModule('Client', '', $this->_db);
        $this->_core = Hashmark::getModule('Core', '', $this->_db);
    }

    /**
     * @test
     * @group Client
     * @group setsScalarStringValue
     * @group strings
     * @group set
     * @group get
     * @group createScalar
     * @group getScalarById
     */
    public function setsScalarStringValue()
    {
        foreach (self::unwrapProviderData(self::provideStringValues()) as $value) {
            $expectedFields = array();
            $expectedFields['name'] = self::randomString();
            $expectedFields['type'] = 'string';

            $expectedId = $this->_core->createScalar($expectedFields);

            $this->assertTrue($this->_client->set($expectedFields['name'], $value));

            // Ensure scalar change w/ getScalarById().
            $scalar = $this->_core->getScalarById($expectedId);
            $this->assertEquals($value, $scalar['value']);
            $this->assertLessThan(5, abs(strtotime($scalar['last_inline_change'] . ' UTC') - time()));
            
            // Ensure scalar change w/ get().
            $this->assertEquals($value, $this->_client->get($expectedFields['name']));
        }
    }

    /**
     * @test
     * @group Client
     * @group setsScalarDecimalValue
     * @group set
     * @group get
     * @group createScalar
     * @group getScalarById
     */
    public function setsScalarDecimalValue()
    {
        foreach (self::unwrapProviderData(self::provideDecimalValues()) as $value) {
            $expectedFields = array();
            $expectedFields['name'] = self::randomString();
            $expectedFields['type'] = 'decimal';

            $expectedId = $this->_core->createScalar($expectedFields);

            $this->assertTrue($this->_client->set($expectedFields['name'], $value));

            // Ensure scalar change w/ getScalarById().
            $scalar = $this->_core->getScalarById($expectedId);
            $this->assertEquals($value, $scalar['value']);
            $this->assertLessThan(5, abs(strtotime($scalar['last_inline_change'] . ' UTC') - time()));
           
            // Ensure scalar change w/ get().
            $this->assertDecimalEquals($value, $this->_client->get($expectedFields['name']));
        }
    }
    
    /**
     * @test
     * @group Client
     * @group setsScalarValueAndCreatesSample
     * @group set
     * @group get
     * @group createScalar
     * @group getScalarById
     * @group getLatestSample
     */
    public function setsScalarValueAndCreatesSample()
    {
        $cron = Hashmark::getModule('Cron', '', $this->_db);

        $expectedFields = array();
        $expectedFields['name'] = self::randomString();
        $expectedFields['type'] = 'decimal';
        $value = '1';

        $expectedId = $this->_core->createScalar($expectedFields);

        // Last parameter should enable sample write.
        $this->assertTrue($this->_client->set($expectedFields['name'], $value, true));

        // Ensure scalar change w/ getScalarById().
        $scalar = $this->_core->getScalarById($expectedId);
        $this->assertEquals($value, $scalar['value']);
        $time = time();
        $this->assertLessThan(5, abs(strtotime($scalar['last_sample_change'] . ' UTC') - $time));
        $this->assertLessThan(5, abs(strtotime($scalar['last_inline_change'] . ' UTC') - $time));

        // Ensure scalar change w/ get().
        $this->assertDecimalEquals($value, $this->_client->get($expectedFields['name']));

        // Ensure sample change.
        $sample = $cron->getLatestSample($expectedId);
        $this->assertDecimalEquals($value, $sample['value']);
    }

    /**
     * @test
     * @group Client
     * @group incrementsScalarDecimalValue
     * @group incr
     * @group set
     * @group get
     * @group createScalar
     * @group getScalarById
     */
    public function incrementsScalarDecimalValue()
    {
        foreach (self::provideIncrementValues() as $value) {
            $expectedFields = array();
            $expectedFields['name'] = self::randomString();
            $expectedFields['type'] = 'decimal';

            $expectedId = $this->_core->createScalar($expectedFields);

            $this->assertTrue($this->_client->set($expectedFields['name'], $value['start']));
            if ($value['delta']) {
                $this->assertTrue($this->_client->incr($expectedFields['name'], $value['delta']));
            } else {
                // Not even the timestamp column will have changed.
                $this->_client->incr($expectedFields['name'], $value['delta']);
            }

            // Ensure scalar change w/ getScalarById().
            $scalar = $this->_core->getScalarById($expectedId);
            $this->assertDecimalEquals($value['sum'], $scalar['value']);
            $this->assertLessThan(5, abs(strtotime($scalar['last_inline_change'] . ' UTC') - time()));

            // Ensure scalar change w/ get().
            $this->assertDecimalEquals($value['sum'], $this->_client->get($expectedFields['name']));
        }
    }

    /**
     * @test
     * @group Client
     * @group decrementsScalarDecimalValue
     * @group decr
     * @group set
     * @group get
     * @group createScalar
     * @group getScalarById
     */
    public function decrementsScalarDecimalValue()
    {
        foreach (self::provideDecrementValues() as $value) {
            $expectedFields = array();
            $expectedFields['name'] = self::randomString();
            $expectedFields['type'] = 'decimal';

            $expectedId = $this->_core->createScalar($expectedFields);

            $this->assertTrue($this->_client->set($expectedFields['name'], $value['start']));
            if ($value['delta']) {
                $this->assertTrue($this->_client->decr($expectedFields['name'], $value['delta']));
            } else {
                // Not even the timestamp column will have changed.
                $this->_client->decr($expectedFields['name'], $value['delta']);
            }

            // Ensure scalar change w/ getScalarById().
            $scalar = $this->_core->getScalarById($expectedId);
            $this->assertDecimalEquals($value['sum'], $scalar['value']);
            $this->assertLessThan(5, abs(strtotime($scalar['last_inline_change'] . ' UTC') - time()));

            // Ensure scalar change w/ get().
            $this->assertDecimalEquals($value['sum'], $this->_client->get($expectedFields['name']));
        }
    }

    /**
     * @test
     * @group Client
     * @group incrementsScalarDecimalValueAndCreatesSample
     * @group incr
     * @group set
     * @group get
     * @group createScalar
     * @group getScalarById
     */
    public function incrementsScalarDecimalValueAndCreatesSample()
    {
        $cron = Hashmark::getModule('Cron', '', $this->_db);
        
        $value = array('start' => '1.0001', 'delta' => '100000000000000.0001', 'sum' => '100000000000001.0002');

        $expectedFields = array();
        $expectedFields['name'] = self::randomString();
        $expectedFields['type'] = 'decimal';

        $expectedId = $this->_core->createScalar($expectedFields);

        // Initial value.
        $this->assertTrue($this->_client->set($expectedFields['name'], $value['start'], true));

        // Last parameter should enable sample write.
        if ($value['delta']) {
            $this->assertTrue($this->_client->incr($expectedFields['name'], $value['delta'], true));
        } else {
            // Not even the timestamp column will have changed.
            $this->_client->incr($expectedFields['name'], $value['delta'], true);
        }

        // Ensure scalar change w/ getScalarById().
        $scalar = $this->_core->getScalarById($expectedId);
        $this->assertDecimalEquals($value['sum'], $scalar['value']);
        $time = time();
        $this->assertLessThan(5, abs(strtotime($scalar['last_sample_change'] . ' UTC') - $time));
        $this->assertLessThan(5, abs(strtotime($scalar['last_inline_change'] . ' UTC') - $time));

        // Ensure scalar change w/ get().
        $this->assertDecimalEquals($value['sum'], $this->_client->get($expectedFields['name']));

        // Ensure sample change.
        $sample = $cron->getLatestSample($expectedId);
        $this->assertDecimalEquals($value['sum'], $sample['value']);
    }

    /**
     * @test
     * @group Client
     * @group decrementsScalarDecimalValueAndCreatesSample
     * @group decr
     * @group set
     * @group get
     * @group createScalar
     * @group getScalarById
     */
    public function decrementsScalarDecimalValueAndCreatesSample()
    {
        $cron = Hashmark::getModule('Cron', '', $this->_db);
              
        $value = array('start' => '100000000000000.0001', 'delta' => '.0001', 'sum' => '100000000000000.0000');

        $expectedFields = array();
        $expectedFields['name'] = self::randomString();
        $expectedFields['type'] = 'decimal';

        $expectedId = $this->_core->createScalar($expectedFields);

        // Initial value.
        $this->assertTrue($this->_client->set($expectedFields['name'], $value['start'], true));

        // Last parameter should enable sample write.
        if ($value['delta']) {
            $this->assertTrue($this->_client->decr($expectedFields['name'], $value['delta'], true));
        } else {
            // Not even the timestamp column will have changed.
            $this->_client->decr($expectedFields['name'], $value['delta'], true);
        }

        // Ensure scalar change w/ getScalarById().
        $scalar = $this->_core->getScalarById($expectedId);
        $this->assertDecimalEquals($value['sum'], $scalar['value']);
        $time = time();
        $this->assertLessThan(5, abs(strtotime($scalar['last_sample_change'] . ' UTC') - $time));
        $this->assertLessThan(5, abs(strtotime($scalar['last_inline_change'] . ' UTC') - $time));

        // Ensure scalar change w/ get().
        $this->assertDecimalEquals($value['sum'], $this->_client->get($expectedFields['name']));

        // Ensure sample change.
        $sample = $cron->getLatestSample($expectedId);
        $this->assertDecimalEquals($value['sum'], $sample['value']);
    }

    /**
     * @test
     * @group Client
     * @group scalarCreatedIfNotExists
     * @group incr
     */
    public function scalarCreatedIfNotExists()
    {
        $this->_client->createScalarIfNotExists(true);

        $expectedName = self::randomString();
        $expectedValue = self::randomDecimal();

        $this->_client->incr($expectedName, $expectedValue);
        $scalar = $this->_core->getScalarByName($expectedName);

        $this->assertDecimalEquals($expectedValue, $scalar['value']);
    }

    /**
     * @test
     * @group Client
     * @group scalarCreatedIfNotExistsWithNewSample
     * @group incr
     */
    public function scalarCreatedIfNotExistsWithNewSample()
    {
        $this->_client->createScalarIfNotExists(true);

        $expectedName = self::randomString();
        $expectedValue = self::randomDecimal();

        $this->_client->incr($expectedName, $expectedValue, true);
        $scalar = $this->_core->getScalarByName($expectedName);

        $cron = Hashmark::getModule('Cron', '', $this->_db);
        $sample = $cron->getLatestSample($scalar['id']);
        $this->assertDecimalEquals($expectedValue, $sample['value']);
    }
    
    /**
     * Covers Google Code issue 33 regression addressed in r17 and r18.
     *
     *  -   http://code.google.com/p/hashmark/source/detail?r=17
     * 
     * @test
     * @group Client
     * @group createdIfNotExistsSettingDoesNotAffectExistingScalar
     * @group incr
     */
    public function createdIfNotExistsSettingDoesNotAffectExistingScalar()
    {
        $this->_client->createScalarIfNotExists(true);

        $expectedFields = array();
        $expectedFields['name'] = self::randomString();
        $expectedFields['type'] = 'decimal';
        $expectedFields['value'] = '1';

        $expectedId = $this->_core->createScalar($expectedFields);

        $this->_client->incr($expectedFields['name'], '1');
        $scalar = $this->_core->getScalarById($expectedId);

        $this->assertDecimalEquals('2', $scalar['value']);
    }
}
