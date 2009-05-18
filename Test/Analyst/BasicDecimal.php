<?php
// vim: fenc=utf-8:ft=php:ai:si:ts=4:sw=4:et:

/**
 * Hashmark_TestCase_Analyst_BasicDecimal
 *
 * @filesource
 * @link        http://code.google.com/p/hashmark/
 * @link        http://framework.zend.com/manual/en/coding-standard.html
 * @link        http://phpdoc.org/tutorial.php
 * @copyright   Copyright (c) 2008-2009, Code Actual LLC
 * @license     http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package     Hashmark-Test
 * @subpackage  Hashmark_Analyst_BasicDecimal
 * @version     $Id$
*/

/**
 *      -   All test dates are in UTC.
 *
 * @package     Hashmark-Test
 * @subpackage  Hashmark_Analyst_BasicDecimal
 */
class Hashmark_TestCase_Analyst_BasicDecimal extends Hashmark_TestCase
{
    /**
     * @var Hashmark_Analyst_BasicDecimal   Fixture instance.
     */
    protected $_analyst;

    /**
     * @var Hashmark_Partition  Fixture instance.
     */
    protected $_partition;

    /**
     * @var Hashmark_Core   Fixture instance.
     */
    protected $_core;

    /**
     * @var Array   Tested subset of Hashmark_Analyst_BasicDecimal:$_aggFunctions.
     */
    protected static $_aggFunctions = array('AVG', 'SUM', 'MAX', 'MIN');

    /**
     * Resources needed for most tests.
     *
     * @return void
     */
    protected function setUp()
    {
        parent::setUp();
        
        $partition = Hashmark::getModule('Partition', '', $this->_db);
        $this->_analyst = Hashmark::getModule('Analyst', 'BasicDecimal', $this->_db, $partition);
        $this->_partition = Hashmark::getModule('Partition', '', $this->_db);
        $this->_core = Hashmark::getModule('Core', '', $this->_db);
    }

    /**
     * Public acess to $_aggFunctions.
     *
     * @return Array
     */
    public static function getAggFunctions()
    {
        return self::$_aggFunctions;
    }
        
    /**
     * Quads: sample DATETIME; sample value; placeholder for matching
     * provideValuesAt*() format; boolean true if within date range (inclusive).
     *
     *      -   Uses these range boundaries:
     *          2008-11-01 01:45:59
     *          2009-04-30 01:45:59
     *      -   Distinct and full group AVG/SUM/COUNT for interval groups should differ at least once.
     *
     * @return Array    Test method argument sets.
     */
    public static function provideValuesSamples()
    {
        static $data;

        if (!$data) {
            $data = array();
            $data[] = array('2002-07-07 00:00:00', '1000000000000000.0103', null, false);
            $data[] = array('2005-05-21 00:00:00', '1000000000000000.9989', null, false);
            $data[] = array('2008-10-30 00:00:00', '1000000000000000.3968', null, false);
            $data[] = array('2008-11-15 00:00:00', '1000000000000000.1724', null, true);
            $data[] = array('2008-11-30 00:00:00', '1000000000000000.6735', null, true);
            $data[] = array('2008-12-07 00:00:00', '1000000000000000.1697', null, true);
            $data[] = array('2008-12-15 00:00:00', '1000000000000000.3857', null, true);
            $data[] = array('2008-12-31 00:00:00', '-1000000000000000.2968', null, true);
            $data[] = array('2009-01-01 00:00:00', '1000000000000000.7870', null, true);
            $data[] = array('2009-01-15 00:00:00', '1000000000000000.9788', null, true);
            $data[] = array('2009-01-31 00:00:00', '1000000000000000.1697', null, true);
            $data[] = array('2009-02-01 00:00:00', '1000000000000000.3886', null, true);
            $data[] = array('2009-02-14 00:00:00', '-1000000000000000.1970', null, true);
            $data[] = array('2009-02-21 00:00:00', '1000000000000000.9788', null, true);
            $data[] = array('2009-02-28 00:00:00', '1000000000000000.8564', null, true);
            $data[] = array('2009-03-01 00:00:00', '1000000000000000.5109', null, true);
            $data[] = array('2009-03-15 00:00:00', '1000000000000000.6109', null, true);
            $data[] = array('2009-03-31 00:00:00', '-1000000000000000.1909', null, true);
            $data[] = array('2009-04-15 00:00:00', '1000000000000000.5109', null, true);
            $data[] = array('2009-04-30 00:00:00', '1000000000000000.5442', null, true);
            $data[] = array('2009-05-01 00:00:00', '1000000000000000.5224', null, false);
            $data[] = array('2009-07-14 00:00:00', '1000000000000000.2955', null, false);
            $data[] = array('2010-12-14 00:00:00', '1000000000000000.2955', null, false);
        }

        return $data;
    }
    
    /**
     * Quads: sample DATETIME; sample value; boolean true if most recent value in interval;
     * boolean true if within date range (inclusive).
     *
     *      -   Uses these range boundaries:
     *          2009-06-04 01:45:59
     *          2009-06-04 06:45:59
     *      -   Group aggregates consider all values in an interval, not just
     *          those expected in at-interval sets.
     *      -   Distinct and full group AVG/SUM/COUNT for interval groups should differ at least once.
     *
     * @return Array    Test method argument sets.
     */
    public static function provideValuesAtIntervalHourSamples()
    {
        static $data;

        if (!$data) {
            $data = array();
            $data[] = array('2009-06-04 00:00:00', '1000000000000000.4000', false, false);
            $data[] = array('2009-06-04 01:30:00', '1000000000000000.5210', false, false);
            $data[] = array('2009-06-04 01:50:00', '1000000000000000.6000', true, true);
            $data[] = array('2009-06-04 02:00:00', '-1000000000000000.7900', false, true);
            $data[] = array('2009-06-04 02:30:00', '1000000000000000.8500', false, true);
            $data[] = array('2009-06-04 02:40:00', '1000000000000000.9030', false, true);
            $data[] = array('2009-06-04 02:45:00', '1000000000000000.9030', true, true);
            $data[] = array('2009-06-04 03:00:00', '1000000000000000.8030', false, true);
            $data[] = array('2009-06-04 03:30:00', '1000000000000000.7030', false, true);
            $data[] = array('2009-06-04 03:45:00', '-1000000000000000.6030', true, true);
            $data[] = array('2009-06-04 04:00:00', '1000000000000000.5932', false, true);
            $data[] = array('2009-06-04 04:30:00', '1000000000000000.4000', false, true);
            $data[] = array('2009-06-04 04:45:00', '1000000000000000.3000', true, true);
            $data[] = array('2009-06-04 05:00:00', '1000000000000000.2030', false, true);
            $data[] = array('2009-06-04 05:30:00', '1000000000000000.1210', false, true);
            $data[] = array('2009-06-04 05:35:00', '1000000000000000.1210', false, true);
            $data[] = array('2009-06-04 05:45:00', '1000000000000000.2000', true, true);
            $data[] = array('2009-06-04 06:00:00', '1000000000000000.3900', false, true);
            $data[] = array('2009-06-04 06:30:00', '-1000000000000000.4000', false, true);
            $data[] = array('2009-06-04 06:45:00', '1000000000000000.5030', true, true);
            $data[] = array('2009-06-04 07:00:00', '1000000000000000.6900', false, false);
        }

        return $data;
    }
    
    /**
     * Quads: sample DATETIME; sample value; boolean true if most recent value in interval;
     * boolean true if within date range (inclusive).
     *
     *      -   Uses these range boundaries:
     *          2009-06-04 01:45:59
     *          2009-06-11 01:45:59
     *      -   Group aggregates consider all values in an interval, not just
     *          those expected in at-interval sets.
     *      -   Distinct and full group AVG/SUM/COUNT for interval groups should differ at least once.
     *
     * @return Array    Test method argument sets.
     */
    public static function provideValuesAtIntervalDaySamples()
    {
        static $data;

        if (!$data) {
            $data = array();
            $data[] = array('2009-06-03 00:00:00', '1000000000000000.3432', false, false);
            $data[] = array('2009-06-04 00:00:00', '1000000000000000.4000', false, false);
            $data[] = array('2009-06-04 11:00:00', '1000000000000000.5210', false, true);
            $data[] = array('2009-06-04 22:00:00', '1000000000000000.1946', true, true);
            $data[] = array('2009-06-05 00:00:00', '1000000000000000.7904', false, true);
            $data[] = array('2009-06-05 11:00:00', '1000000000000000.8553', false, true);
            $data[] = array('2009-06-05 22:00:00', '1000000000000000.9365', true, true);
            $data[] = array('2009-06-06 00:00:00', '1000000000000000.5377', false, true);
            $data[] = array('2009-06-06 11:00:00', '1000000000000000.4089', false, true);
            $data[] = array('2009-06-06 22:00:00', '1000000000000000.3397', true, true);
            $data[] = array('2009-06-07 00:00:00', '1000000000000000.9184', false, true);
            $data[] = array('2009-06-07 05:00:00', '1000000000000000.2000', false, true);
            $data[] = array('2009-06-07 11:00:00', '1000000000000000.3224', false, true);
            $data[] = array('2009-06-07 22:00:00', '1000000000000000.2000', true, true);
            $data[] = array('2009-06-08 00:00:00', '-1000000000000000.0549', false, true);
            $data[] = array('2009-06-08 11:00:00', '1000000000000000.5947', false, true);
            $data[] = array('2009-06-08 22:00:00', '1000000000000000.9635', true, true);
            $data[] = array('2009-06-09 00:00:00', '1000000000000000.3720', false, true);
            $data[] = array('2009-06-09 11:00:00', '1000000000000000.2027', false, true);
            $data[] = array('2009-06-09 22:00:00', '1000000000000000.1946', true, true);
            $data[] = array('2009-06-10 00:00:00', '1000000000000000.9821', false, true);
            $data[] = array('2009-06-10 11:00:00', '1000000000000000.3701', false, true);
            $data[] = array('2009-06-10 22:00:00', '-1000000000000000.4504', true, true);
            $data[] = array('2009-06-11 00:00:00', '1000000000000000.5030', false, true);
            $data[] = array('2009-06-11 00:30:00', '1000000000000000.6810', false, true);
            $data[] = array('2009-06-11 01:00:00', '1000000000000000.6810', true, true);
            $data[] = array('2009-06-12 03:00:00', '1000000000000000.8604', false, false);
        }

        return $data;
    }
    
    /**
     * Quads: sample DATETIME; sample value; boolean true if most recent value in interval;
     * boolean true if within date range (inclusive).
     *
     *      -   Uses these range boundaries:
     *          2009-05-31 01:45:59
     *          2009-07-12 01:45:59
     *      -   Group aggregates consider all values in an interval, not just
     *          those expected in at-interval sets.
     *      -   Distinct and full group AVG/SUM/COUNT for interval groups should differ at least once.
     *
     * @return Array    Test method argument sets.
     */
    public static function provideValuesAtIntervalWeekSamples()
    {
        static $data;

        if (!$data) {
            $data = array();
            $data[] = array('2008-05-30 03:45:00', '1000000000000000.1332', false, false);
            $data[] = array('2009-06-03 03:14:21', '-1000000000000000.8809', false, true);
            $data[] = array('2009-06-06 03:36:59', '1000000000000000.9634', false, true);
            $data[] = array('2009-06-06 03:37:59', '1000000000000000.8231', false, true);
            $data[] = array('2009-06-07 03:37:59', '1000000000000000.7530', true, true);
            $data[] = array('2009-06-10 03:44:59', '1000000000000000.6330', false, true);
            $data[] = array('2009-06-11 03:44:59', '1000000000000000.6330', false, true);
            $data[] = array('2009-06-13 03:45:01', '1000000000000000.5831', false, true);
            $data[] = array('2009-06-13 21:01:01', '1000000000000000.4604', false, true);
            $data[] = array('2009-06-14 03:01:01', '-1000000000000000.0618', true, true);
            $data[] = array('2009-06-17 04:01:01', '1000000000000000.1103', false, true);
            $data[] = array('2009-06-20 01:01:01', '1000000000000000.6207', false, true);
            $data[] = array('2009-06-20 04:00:00', '1000000000000000.3006', false, true);
            $data[] = array('2009-06-21 10:30:07', '1000000000000000.2206', true, true);
            $data[] = array('2009-06-24 10:42:07', '1000000000000000.3028', false, true);
            $data[] = array('2009-06-25 09:45:10', '1000000000000000.7600', false, true);
            $data[] = array('2009-06-27 02:01:01', '1000000000000000.4705', false, true);
            $data[] = array('2009-06-27 09:45:10', '1000000000000000.7600', false, true);
            $data[] = array('2009-06-28 01:45:20', '1000000000000000.5101', true, true);
            $data[] = array('2009-07-01 20:14:32', '-1000000000000000.0310', false, true);
            $data[] = array('2009-07-05 00:00:00', '1000000000000000.7300', false, true);
            $data[] = array('2009-07-05 01:30:00', '1000000000000000.4513', true, true);
            $data[] = array('2009-07-08 01:30:00', '1000000000000000.5516', false, true);
            $data[] = array('2009-07-11 01:25:00', '1000000000000000.5516', false, true);
            $data[] = array('2009-07-12 01:30:00', '1000000000000000.4412', true, true);
            $data[] = array('2009-08-16 01:30:00', '1000000000000000.3118', false, false);
            $data[] = array('2010-07-05 00:00:00', '1000000000000000.1009', false, false);
        }

        return $data;
    }
    
    /**
     * Quads: sample DATETIME; sample value; boolean true if most recent value in interval;
     * boolean true if within date range (inclusive).
     *
     *      -   Uses these range boundaries:
     *          2009-01-01 01:45:59
     *          2009-06-30 01:45:59
     *      -   Group aggregates consider all values in an interval, not just
     *          those expected in at-interval sets.
     *      -   Distinct and full group AVG/SUM/COUNT for interval groups should differ at least once.
     *
     * @return Array    Test method argument sets.
     */
    public static function provideValuesAtIntervalMonthSamples()
    {
        static $data;

        if (!$data) {
            $data = array();
            $data[] = array('2008-12-31 23:45:01', '1000000000000000.5932', false, false);
            $data[] = array('2009-01-01 03:37:59', '1000000000000000.7030', false, true);
            $data[] = array('2009-01-15 03:44:59', '1000000000000000.6030', false, true);
            $data[] = array('2009-01-31 03:45:00', '-1000000000000000.1932', false, true);
            $data[] = array('2009-01-31 09:45:00', '-1000000000000000.1932', true, true);
            $data[] = array('2009-02-01 00:00:00', '1000000000000000.2000', false, true);
            $data[] = array('2009-02-07 03:36:59', '1000000000000000.9030', false, true);
            $data[] = array('2009-02-15 03:14:21', '1000000000000000.8500', false, true);
            $data[] = array('2009-02-28 03:36:59', '1000000000000000.9030', false, true);
            $data[] = array('2009-02-28 03:37:59', '1000000000000000.8030', true, true);
            $data[] = array('2009-03-01 03:37:59', '1000000000000000.7030', false, true);
            $data[] = array('2009-03-15 03:44:59', '1000000000000000.6030', false, true);
            $data[] = array('2009-03-31 03:45:01', '1000000000000000.5932', false, true);
            $data[] = array('2009-03-31 21:01:01', '-1000000000000000.6210', true, true);
            $data[] = array('2009-04-01 03:01:01', '1000000000000000.9213', false, true);
            $data[] = array('2009-04-15 04:01:01', '1000000000000000.8500', false, true);
            $data[] = array('2009-04-30 01:01:01', '1000000000000000.4000', false, true);
            $data[] = array('2009-04-30 04:00:00', '1000000000000000.3020', true, true);
            $data[] = array('2009-05-01 10:30:07', '1000000000000000.2400', false, true);
            $data[] = array('2009-05-15 10:42:07', '-1000000000000000.3400', false, true);
            $data[] = array('2009-05-31 02:01:01', '1000000000000000.4000', false, true);
            $data[] = array('2009-05-31 09:45:10', '1000000000000000.4400', true, true);
            $data[] = array('2009-06-01 01:45:20', '1000000000000000.5400', false, true);
            $data[] = array('2009-06-07 01:30:00', '1000000000000000.9213', false, true);
            $data[] = array('2009-06-15 20:14:32', '-1000000000000000.6210', false, true);
            $data[] = array('2009-06-30 00:00:00', '1000000000000000.7000', false, true);
            $data[] = array('2009-06-30 01:30:00', '1000000000000000.9213', true, true);
            $data[] = array('2009-06-30 11:42:00', '1000000000000000.9213', false, false);
            $data[] = array('2009-07-01 00:00:00', '1000000000000000.9000', false, false);
        }

        return $data;
    }
    
    /**
     * Quads: sample DATETIME; sample value; boolean true if most recent value in interval;
     * boolean true if within date range (inclusive).
     *
     *      -   Uses these range boundaries:
     *          2009-01-01 01:45:59
     *          2012-12-31 01:45:59
     *      -   Group aggregates consider all values in an interval, not just
     *          those expected in at-interval sets.
     *      -   Distinct and full group AVG/SUM/COUNT for interval groups should differ at least once.
     *
     * @return Array    Test method argument sets.
     */
    public static function provideValuesAtIntervalYearSamples()
    {
        static $data;

        if (!$data) {
            $data = array();
            $data[] = array('2008-12-31 23:45:01', '1000000000000000.5932', false, false);
            $data[] = array('2009-01-01 04:01:01', '1000000000000000.8500', false, true);
            $data[] = array('2009-06-01 01:01:01', '1000000000000000.4907', false, true);
            $data[] = array('2009-12-31 04:00:00', '1000000000000000.3020', false, true);
            $data[] = array('2009-12-31 05:00:00', '1000000000000000.6355', true, true);
            $data[] = array('2010-01-01 10:30:07', '1000000000000000.2400', false, true);
            $data[] = array('2010-06-01 10:42:07', '1000000000000000.3451', false, true);
            $data[] = array('2010-09-01 10:30:07', '1000000000000000.2400', false, true);
            $data[] = array('2010-12-31 02:01:01', '1000000000000000.7182', false, true);
            $data[] = array('2010-12-31 03:01:01', '-1000000000000000.3712', true, true);
            $data[] = array('2011-01-01 09:45:10', '1000000000000000.4407', false, true);
            $data[] = array('2011-06-01 01:45:20', '1000000000000000.5404', false, true);
            $data[] = array('2011-12-31 20:14:32', '1000000000000000.6210', false, true);
            $data[] = array('2011-12-31 21:14:32', '-1000000000000000.9123', true, true);
            $data[] = array('2012-01-01 00:00:00', '1000000000000000.7001', false, true);
            $data[] = array('2012-06-01 01:30:00', '1000000000000000.9213', false, true);
            $data[] = array('2012-12-31 00:42:00', '-1000000000000000.8577', false, true);
            $data[] = array('2012-12-31 01:27:00', '1000000000000000.9213', true, true);
            $data[] = array('2013-01-01 00:00:00', '1000000000000000.1992', false, false);
        }

        return $data;
    }

    /**
     * Returns samples, and associated fields like data range,
     * from provide*() methods like provideValuesAtIntervalHourSamples().
     *
     *      -   Does not use @dataProvider format.
     *
     * @param string    $name  Provider method name. 
     * @return Array    If $name is empty, all sample data; otherwise
     *                  only the named section.
     */
    public static function provideFullSamplesData($name = '')
    {
        static $data;

        if (!$data) {
            $data = array();
            $data['provideValuesSamples'] = array('samples' => self::provideValuesSamples(),
                                                  'rangeStart' => '2008-11-01 01:45:59',
                                                  'rangeEnd' => '2009-04-30 01:45:59');
            $data['provideValuesAtIntervalHourSamples'] = array('interval' => 'h',
                                                                'samples' => self::provideValuesAtIntervalHourSamples(),
                                                                'rangeStart' => '2009-06-04 01:45:59',
                                                                'rangeEnd' => '2009-06-04 06:45:59');
            $data['provideValuesAtIntervalDaySamples'] = array('interval' => 'd',
                                                               'samples' => self::provideValuesAtIntervalDaySamples(),
                                                               'rangeStart' => '2009-06-04 01:45:59',
                                                               'rangeEnd' => '2009-06-11 01:45:59');
            $data['provideValuesAtIntervalWeekSamples'] = array('interval' => 'w',
                                                                'samples' => self::provideValuesAtIntervalWeekSamples(),
                                                                'rangeStart' => '2009-05-31 01:45:59',
                                                                'rangeEnd' => '2009-07-12 01:45:59');
            $data['provideValuesAtIntervalMonthSamples'] = array('interval' => 'm',
                                                                 'samples' => self::provideValuesAtIntervalMonthSamples(),
                                                                 'rangeStart' => '2009-01-01 01:45:59',
                                                                 'rangeEnd' => '2009-06-30 01:45:59');
            $data['provideValuesAtIntervalYearSamples'] = array('interval' => 'y',
                                                                'samples' => self::provideValuesAtIntervalYearSamples(),
                                                                'rangeStart' => '2009-01-01 01:45:59',
                                                                'rangeEnd' => '2012-12-31 01:45:59');
        }

        if (!$name) {
            return $data;
        }

        if (isset($data[$name])) {
            return $data[$name];
        }

        return false;
    }
    
    /**
     * Return expected aggregate/change/moving/etc. values based on samples
     * from provideValuesSamples(), provideValuesSamplesAtInterval*, etc.
     *
     *      -   Does not use @dataProvider format.
     *
     * @param string    $name   Name of section in provider.php data.
     * @return Array    If $name is empty, all provider.php data; otherwise
     *                  only the named section.
     * @see Test/BasicDecimal/Tool/ scripts for recalculating expected values.
     */
    public static function provideExpectedValues($name = '')
    {
        static $expProviders;

        if (!$expProviders) {
            require_once HASHMARK_ROOT_DIR . '/Test/Analyst/BasicDecimal/Data/provider.php';
        }

        if (!$name) {
            return $expProviders;
        }

        if (isset($expProviders[$name])) {
            return $expProviders[$name];
        }

        return false;
    }

    /**
     * @test
     * @group BasicDecimal
     * @group hasGeneratedProviderData
     */
    public function hasGeneratedProviderData()
    {
        $this->assertTrue(is_readable(HASHMARK_ROOT_DIR . '/Test/Analyst/BasicDecimal/Data/provider.php'),
                          'Generate data: php -f Test/Analyst/BasicDecimal/Tool/writeProviderData.php');
    }
    
    /**
     * @test
     * @group Analyst
     * @group BasicDecimal
     * @group throwsOnMissingSql
     * @group query
     */
    public function throwsOnMissingSql()
    {
        $thrown = false;
        $values = array();

        foreach (array('', array(), 1) as $sql) {
            try {
                $this->_analyst->multiQuery($sql, $values);
            } catch (Exception $e) {
                $thrown = true;
            }

            $this->assertTrue($thrown);
        }
    }
    
    /**
     * @test
     * @group BasicDecimal
     * @group findsValues
     * @group values
     * @group decimal
     * @group createScalar
     * @group createSample
     */
    public function findsValues()
    {
        $scalarFields = array('name' => self::randomString(), 'type' => 'decimal');
        $scalarId = $this->_core->createScalar($scalarFields);

        $sampleProvider = self::provideFullSamplesData('provideValuesSamples');
        $this->assertFalse(empty($sampleProvider));
        
        $expValues = array();

        foreach ($sampleProvider['samples'] as $sample) {
            list($end, $value, , $isInRange) = $sample;

            if ($isInRange) {
                $expValues[$end] = $value;
            }

            $this->_partition->createSample($scalarId, $value, $end);
        }

        $actValues = $this->_analyst->values($scalarId, 50, $sampleProvider['rangeStart'], $sampleProvider['rangeEnd']);

        $this->assertEquals(count($expValues), count($actValues));

        foreach ($actValues as $actual) {
            $this->assertDecimalEquals($expValues[$actual['x']], $actual['y']);
        }
    }
    
    /**
     * @test
     * @group BasicDecimal
     * @group findsValuesAtInterval
     * @group valuesAtInterval
     * @group decimal
     * @group createScalar
     * @group createSample
     */
    public function findsValuesAtInterval()
    {
        $intervalProviders = array(self::provideFullSamplesData('provideValuesAtIntervalHourSamples'),
                                   self::provideFullSamplesData('provideValuesAtIntervalDaySamples'),
                                   self::provideFullSamplesData('provideValuesAtIntervalWeekSamples'),
                                   self::provideFullSamplesData('provideValuesAtIntervalMonthSamples'),
                                   self::provideFullSamplesData('provideValuesAtIntervalHourSamples'));
        
        foreach ($intervalProviders as $sampleProvider) {
            $this->assertFalse(empty($sampleProvider));

            $sampleSets = array($sampleProvider['samples'], array_reverse($sampleProvider['samples']));

            foreach ($sampleSets as $samples) {
                $scalarFields = array('name' => self::randomString(), 'type' => 'decimal');
                $scalarId = $this->_core->createScalar($scalarFields);

                $expValues = array();

                foreach ($samples as $sample) {
                    list($end, $value, $isMostRecentInInterval) = $sample;

                    if ($isMostRecentInInterval) {
                        $expValues[$end] = $value;
                    }

                    $this->_partition->createSample($scalarId, $value, $end);
                }

                $actValues = $this->_analyst->valuesAtInterval($scalarId, 50, $sampleProvider['rangeStart'], $sampleProvider['rangeEnd'], $sampleProvider['interval']);
                    
                $errorMsg = "int={$sampleProvider['interval']}";

                $this->assertEquals(count($expValues), count($actValues), $errorMsg);

                foreach ($actValues as $actual) {
                    $this->assertDecimalEquals($expValues[$actual['x']], $actual['y'], $errorMsg . " x={$actual['x']}");
                }
            }
        }
    }
    
    /**
     * @test
     * @group BasicDecimal
     * @group findsValuesAgg
     * @group valuesAgg
     * @group decimal
     * @group createScalar
     * @group createSample
     */
    public function findsValuesAgg()
    {
        $scalarFields = array('name' => self::randomString(), 'type' => 'decimal');
        $scalarId = $this->_core->createScalar($scalarFields);

        $sampleProvider = self::provideFullSamplesData('provideValuesSamples');
        $this->assertFalse(empty($sampleProvider));

        $expValues = self::provideExpectedValues('provideValuesAgg');
        $this->assertFalse(empty($expValues));

        foreach ($sampleProvider['samples'] as $sample) {
            list($end, $value) = $sample;
            $this->_partition->createSample($scalarId, $value, $end);
        }

        foreach (self::$_aggFunctions as $aggFunc) {
            $errorMsg = "val=ALL f={$aggFunc}";
            $actual = $this->_analyst->valuesAgg($scalarId, $sampleProvider['rangeStart'], $sampleProvider['rangeEnd'], $aggFunc, false);
            $this->assertDecimalEquals($expValues['ALL'][$aggFunc], $actual, $errorMsg);
            
            $errorMsg = "val=DIS f={$aggFunc}";
            $actual = $this->_analyst->valuesAgg($scalarId, $sampleProvider['rangeStart'], $sampleProvider['rangeEnd'], $aggFunc, true);
            $this->assertDecimalEquals($expValues['DIS'][$aggFunc], $actual, $errorMsg);
        }
    }
    
    /**
     * @test
     * @group BasicDecimal
     * @group findsValuesAggAtInterval
     * @group valuesAggAtInterval
     * @group decimal
     * @group createScalar
     * @group createSample
     */
    public function findsValuesAggAtInterval()
    {
        $allSampleProviders = self::provideFullSamplesData();
        $this->assertFalse(empty($allSampleProviders));

        $expProvider = self::provideExpectedValues('provideValuesAggAtInterval');
        $this->assertFalse(empty($expProvider));

        foreach ($expProvider as $sampleProviderName => $expValues) {
            $sampleProvider = $allSampleProviders[$sampleProviderName];
            $this->assertFalse(empty($sampleProvider));

            $expCount = count($expValues['ALL']['AVG']);
            $expDistinctCount = count($expValues['DIS']['AVG']);
            
            $sampleSets = array($sampleProvider['samples'], array_reverse($sampleProvider['samples']));
                
            foreach ($sampleSets as $samples) {
                $scalarFields = array('name' => self::randomString(), 'type' => 'decimal');
                $scalarId = $this->_core->createScalar($scalarFields);

                foreach ($samples as $sample) {
                    list($end, $value) = $sample;
                    $this->_partition->createSample($scalarId, $value, $end);
                }

                foreach (self::$_aggFunctions as $aggFunc) {
                    $errorMsg = "data={$sampleProviderName} val=ALL f={$aggFunc} int={$sampleProvider['interval']}";
                    $actValues = $this->_analyst->valuesAggAtInterval($scalarId, $sampleProvider['rangeStart'], $sampleProvider['rangeEnd'], $sampleProvider['interval'], $aggFunc, false);
                    $this->assertEquals($expCount, count($actValues), $errorMsg);
                    foreach ($actValues as $actual) {
                        $this->assertDecimalEquals($expValues['ALL'][$aggFunc][$actual['x']], $actual['y'], $errorMsg . " x={$actual['x']}");
                    }

                    $errorMsg = "data={$sampleProviderName} val=DIS f={$aggFunc} int={$sampleProvider['interval']}";
                    $actValues = $this->_analyst->valuesAggAtInterval($scalarId, $sampleProvider['rangeStart'], $sampleProvider['rangeEnd'], $sampleProvider['interval'], $aggFunc, true);
                    $this->assertEquals($expDistinctCount, count($actValues), $errorMsg);
                    foreach ($actValues as $actual) {
                        $this->assertDecimalEquals($expValues['DIS'][$aggFunc][$actual['x']], $actual['y'], $errorMsg . " x={$actual['x']}");
                    }
                }
            }
        }
    }
    
    /**
     * @test
     * @group BasicDecimal
     * @group findsValuesNestedAggsAtInterval
     * @group valuesNestedAggAtInterval
     * @group decimal
     * @group createScalar
     * @group createSample
     */
    public function findsValuesNestedAggsAtInterval()
    {
        $allSampleProviders = self::provideFullSamplesData();
        $this->assertFalse(empty($allSampleProviders));

        $expProvider = self::provideExpectedValues('provideValuesNestedAggAtInterval');
        $this->assertFalse(empty($expProvider));

        foreach ($expProvider as $sampleProviderName => $expValues) {
            $sampleProvider = $allSampleProviders[$sampleProviderName];
            $this->assertFalse(empty($sampleProvider));

            $scalarFields = array('name' => self::randomString(), 'type' => 'decimal');
            $scalarId = $this->_core->createScalar($scalarFields);

            foreach ($sampleProvider['samples'] as $sample) {
                list($end, $value) = $sample;
                $this->_partition->createSample($scalarId, $value, $end);
            }

            foreach (self::$_aggFunctions as $aggFuncOuter) {
                foreach (self::$_aggFunctions as $aggFuncInner) {
                    $errorMsg = "data={$sampleProviderName} val=ALL func={$aggFuncOuter}({$aggFuncInner}) int={$sampleProvider['interval']}";
                    $actualAgg = $this->_analyst->valuesNestedAggAtInterval($scalarId, $sampleProvider['rangeStart'], $sampleProvider['rangeEnd'], $sampleProvider['interval'], $aggFuncOuter, false, $aggFuncInner, false);
                    $this->assertDecimalEquals($expValues['ALL'][$aggFuncOuter][$aggFuncInner], $actualAgg, $errorMsg);

                    $errorMsg = "data={$sampleProviderName} val=DIS_INNER func={$aggFuncOuter}({$aggFuncInner}) int={$sampleProvider['interval']}";
                    $actualAgg = $this->_analyst->valuesNestedAggAtInterval($scalarId, $sampleProvider['rangeStart'], $sampleProvider['rangeEnd'], $sampleProvider['interval'], $aggFuncOuter, false, $aggFuncInner, true);
                    $this->assertDecimalEquals($expValues['DIS_INNER'][$aggFuncOuter][$aggFuncInner], $actualAgg, $errorMsg);

                    $errorMsg = "data={$sampleProviderName} val=DIS_OUTER func={$aggFuncOuter}({$aggFuncInner}) int={$sampleProvider['interval']}";
                    $actualAgg = $this->_analyst->valuesNestedAggAtInterval($scalarId, $sampleProvider['rangeStart'], $sampleProvider['rangeEnd'], $sampleProvider['interval'], $aggFuncOuter, true, $aggFuncInner, false);
                    $this->assertDecimalEquals($expValues['DIS_OUTER'][$aggFuncOuter][$aggFuncInner], $actualAgg, $errorMsg);
                    
                    $errorMsg = "data={$sampleProviderName} val=DIS_BOTH func={$aggFuncOuter}({$aggFuncInner}) int={$sampleProvider['interval']}";
                    $actualAgg = $this->_analyst->valuesNestedAggAtInterval($scalarId, $sampleProvider['rangeStart'], $sampleProvider['rangeEnd'], $sampleProvider['interval'], $aggFuncOuter, true, $aggFuncInner, true);
                    $this->assertDecimalEquals($expValues['DIS_BOTH'][$aggFuncOuter][$aggFuncInner], $actualAgg, $errorMsg);
                }
            }
        }
    }
    
    /**
     * @test
     * @group BasicDecimal
     * @group findsValuesAggAtRecurrence
     * @group valuesAggAtRecurrence
     * @group decimal
     * @group createScalar
     * @group createSample
     */
    public function findsValuesAggAtRecurrence()
    {
        $allSampleProviders = self::provideFullSamplesData();
        $this->assertFalse(empty($allSampleProviders));

        $expProvider = self::provideExpectedValues('provideValuesAggAtRecurrence');
        $this->assertFalse(empty($expProvider));

        foreach ($expProvider as $sampleProviderName => $expValues) {
            $sampleProvider = $allSampleProviders[$sampleProviderName];
            $this->assertFalse(empty($sampleProvider));

            $scalarFields = array('name' => self::randomString(), 'type' => 'decimal');
            $scalarId = $this->_core->createScalar($scalarFields);

            foreach ($sampleProvider['samples'] as $sample) {
                list($end, $value) = $sample;
                $this->_partition->createSample($scalarId, $value, $end);
            }

            foreach (Hashmark_Analyst_BasicDecimal::getRecurFunctions() as $recurFunc) {
                foreach (self::$_aggFunctions as $aggFunc) {
                    $errorMsg = "data={$sampleProviderName} val=ALL recur={$recurFunc} agg={$aggFunc}";
                    $actValues = $this->_analyst->valuesAggAtRecurrence($scalarId, $sampleProvider['rangeStart'], $sampleProvider['rangeEnd'], $recurFunc, $aggFunc, false);
                    foreach ($actValues as $actual) {
                        $this->assertDecimalEquals($expValues['ALL'][$recurFunc][$actual['x']][$aggFunc], $actual['y'], $errorMsg . " x={$actual['x']}");
                    }

                    $errorMsg = "data={$sampleProviderName} val=DIS recur={$recurFunc} agg={$aggFunc}";
                    $actValues = $this->_analyst->valuesAggAtRecurrence($scalarId, $sampleProvider['rangeStart'], $sampleProvider['rangeEnd'], $recurFunc, $aggFunc, true);
                    foreach ($actValues as $actual) {
                        $this->assertDecimalEquals($expValues['DIS'][$recurFunc][$actual['x']][$aggFunc], $actual['y'], $errorMsg . " x={$actual['x']}");
                    }
                }
            }
        }
    }
    
    /**
     * @test
     * @group BasicDecimal
     * @group findsChanges
     * @group changes
     * @group decimal
     * @group createScalar
     * @group createSample
     */
    public function findsChanges()
    {
        $allSampleProviders = self::provideFullSamplesData();
        $this->assertFalse(empty($allSampleProviders));

        $expProvider = self::provideExpectedValues('provideChanges');
        $this->assertFalse(empty($expProvider));

        foreach ($expProvider as $sampleProviderName => $expValues) {
            $sampleProvider = $allSampleProviders[$sampleProviderName];
            $this->assertFalse(empty($sampleProvider));

            $scalarFields = array('name' => self::randomString(), 'type' => 'decimal');
            $scalarId = $this->_core->createScalar($scalarFields);

            foreach ($sampleProvider['samples'] as $sample) {
                list($end, $value) = $sample;
                $this->_partition->createSample($scalarId, $value, $end);
            }

            $errorMsg = "data={$sampleProviderName}";

            $actValues = $this->_analyst->changes($scalarId, 50, $sampleProvider['rangeStart'], $sampleProvider['rangeEnd']);

            $this->assertEquals(count($expValues), count($actValues), $errorMsg);

            foreach ($actValues as $actual) {
                $this->assertDecimalEquals($expValues[$actual['x']], $actual['y2'], $errorMsg . " x={$actual['x']}");
            }
        }
    }
    
    /**
     * @test
     * @group BasicDecimal
     * @group findsChangesAtInterval
     * @group changesAtInterval
     * @group decimal
     * @group createScalar
     * @group createSample
     */
    public function findsChangesAtInterval()
    {
        $allSampleProviders = self::provideFullSamplesData();
        $this->assertFalse(empty($allSampleProviders));

        $expProvider = self::provideExpectedValues('provideChangesAtInterval');
        $this->assertFalse(empty($expProvider));

        foreach ($expProvider as $sampleProviderName => $expValues) {
            $sampleProvider = $allSampleProviders[$sampleProviderName];
            $this->assertFalse(empty($sampleProvider));

            $scalarFields = array('name' => self::randomString(), 'type' => 'decimal');
            $scalarId = $this->_core->createScalar($scalarFields);

            foreach ($sampleProvider['samples'] as $sample) {
                list($end, $value) = $sample;
                $this->_partition->createSample($scalarId, $value, $end);
            }

            $actValues = $this->_analyst->changesAtInterval($scalarId, 50, $sampleProvider['rangeStart'], $sampleProvider['rangeEnd'], $sampleProvider['interval']);

            $errorMsg = "data={$sampleProviderName} int={$sampleProvider['interval']}";

            $this->assertEquals(count($expValues), count($actValues), $errorMsg);

            foreach ($actValues as $actual) {
                $this->assertDecimalEquals($expValues[$actual['x']], $actual['y2'], $errorMsg . " x={$actual['x']}");
            }
        }
    }
    
    /**
     * @test
     * @group BasicDecimal
     * @group findsChangesAgg
     * @group changesAgg
     * @group decimal
     * @group createScalar
     * @group createSample
     */
    public function findsChangesAgg()
    {
        $allSampleProviders = self::provideFullSamplesData();
        $this->assertFalse(empty($allSampleProviders));

        $expProvider = self::provideExpectedValues('provideChangesAgg');
        $this->assertFalse(empty($expProvider));

        foreach ($expProvider as $sampleProviderName => $expValues) {
            $sampleProvider = $allSampleProviders[$sampleProviderName];
            $this->assertFalse(empty($sampleProvider));

            $scalarFields = array('name' => self::randomString(), 'type' => 'decimal');
            $scalarId = $this->_core->createScalar($scalarFields);

            foreach ($sampleProvider['samples'] as $sample) {
                list($end, $value) = $sample;
                $this->_partition->createSample($scalarId, $value, $end);
            }

            foreach (self::$_aggFunctions as $aggFunc) {
                $errorMsg = "val=ALL f={$aggFunc}";
                $actual = $this->_analyst->changesAgg($scalarId, $sampleProvider['rangeStart'], $sampleProvider['rangeEnd'], $aggFunc, false);
                $this->assertDecimalEquals($expValues['ALL'][$aggFunc], $actual, $errorMsg);

                $errorMsg = "val=DIS f={$aggFunc}";
                $actual = $this->_analyst->changesAgg($scalarId, $sampleProvider['rangeStart'], $sampleProvider['rangeEnd'], $aggFunc, true);
                $this->assertDecimalEquals($expValues['DIS'][$aggFunc], $actual, $errorMsg);
            }
        }
    }
    
    /**
     * @test
     * @group BasicDecimal
     * @group findsChangesAggAtInterval
     * @group changesAggAtInterval
     * @group decimal
     * @group createScalar
     * @group createSample
     */
    public function findsChangesAggAtInterval()
    {
        $allSampleProviders = self::provideFullSamplesData();
        $this->assertFalse(empty($allSampleProviders));

        $expProvider = self::provideExpectedValues('provideChangesAggAtInterval');
        $this->assertFalse(empty($expProvider));

        foreach ($expProvider as $sampleProviderName => $expValues) {
            $sampleProvider = $allSampleProviders[$sampleProviderName];
            $this->assertFalse(empty($sampleProvider));

            $aggCount = count($expValues['ALL']['AVG']);
            $aggDistinctCount = count($expValues['DIS']['AVG']);

            $scalarFields = array('name' => self::randomString(), 'type' => 'decimal');
            $scalarId = $this->_core->createScalar($scalarFields);

            foreach ($sampleProvider['samples'] as $sample) {
                list($end, $value) = $sample;
                $this->_partition->createSample($scalarId, $value, $end);
            }

            foreach (self::$_aggFunctions as $aggFunc) {
                $errorMsg = "data={$sampleProviderName} val=ALL f={$aggFunc} int={$sampleProvider['interval']}";
                $actValues = $this->_analyst->changesAggAtInterval($scalarId, $sampleProvider['rangeStart'], $sampleProvider['rangeEnd'], $sampleProvider['interval'], $aggFunc, false);
                $this->assertEquals($aggCount, count($actValues), $errorMsg);
                foreach ($actValues as $actual) {
                    $this->assertDecimalEquals($expValues['ALL'][$aggFunc][$actual['x']], $actual['y'], $errorMsg . " x={$actual['x']}");
                }

                $errorMsg = "data={$sampleProviderName} val=DIS f={$aggFunc} int={$sampleProvider['interval']}";
                $actValues = $this->_analyst->changesAggAtInterval($scalarId, $sampleProvider['rangeStart'], $sampleProvider['rangeEnd'], $sampleProvider['interval'], $aggFunc, true);
                $this->assertEquals($aggDistinctCount, count($actValues), $errorMsg);
                foreach ($actValues as $actual) {
                    $this->assertDecimalEquals($expValues['DIS'][$aggFunc][$actual['x']], $actual['y'], $errorMsg . " x={$actual['x']}");
                }
            }
        }
    }
    
    /**
     * @test
     * @group BasicDecimal
     * @group findsChangesNestedAggsAtInterval
     * @group changesNestedAggAtInterval
     * @group decimal
     * @group createScalar
     * @group createSample
     */
    public function findsChangesNestedAggsAtInterval()
    {
        $allSampleProviders = self::provideFullSamplesData();
        $this->assertFalse(empty($allSampleProviders));

        $expProvider = self::provideExpectedValues('provideChangesNestedAggAtInterval');
        $this->assertFalse(empty($expProvider));

        foreach ($expProvider as $sampleProviderName => $expValues) {
            $sampleProvider = $allSampleProviders[$sampleProviderName];
            $this->assertFalse(empty($sampleProvider));

            $scalarFields = array('name' => self::randomString(), 'type' => 'decimal');
            $scalarId = $this->_core->createScalar($scalarFields);

            foreach ($sampleProvider['samples'] as $sample) {
                list($end, $value) = $sample;
                $this->_partition->createSample($scalarId, $value, $end);
            }

            foreach (self::$_aggFunctions as $aggFuncOuter) {
                foreach (self::$_aggFunctions as $aggFuncInner) {
                    $errorMsg = "data={$sampleProviderName} val=ALL func={$aggFuncOuter}({$aggFuncInner}) int={$sampleProvider['interval']}";
                    $actualAgg = $this->_analyst->changesNestedAggAtInterval($scalarId, $sampleProvider['rangeStart'], $sampleProvider['rangeEnd'], $sampleProvider['interval'], $aggFuncOuter, false, $aggFuncInner, false);
                    $this->assertDecimalEquals($expValues['ALL'][$aggFuncOuter][$aggFuncInner], $actualAgg, $errorMsg);

                    $errorMsg = "data={$sampleProviderName} val=DIS_INNER func={$aggFuncOuter}({$aggFuncInner}) int={$sampleProvider['interval']}";
                    $actualAgg = $this->_analyst->changesNestedAggAtInterval($scalarId, $sampleProvider['rangeStart'], $sampleProvider['rangeEnd'], $sampleProvider['interval'], $aggFuncOuter, false, $aggFuncInner, true);
                    $this->assertDecimalEquals($expValues['DIS_INNER'][$aggFuncOuter][$aggFuncInner], $actualAgg, $errorMsg);

                    $errorMsg = "data={$sampleProviderName} val=DIS_OUTER func={$aggFuncOuter}({$aggFuncInner}) int={$sampleProvider['interval']}";
                    $actualAgg = $this->_analyst->changesNestedAggAtInterval($scalarId, $sampleProvider['rangeStart'], $sampleProvider['rangeEnd'], $sampleProvider['interval'], $aggFuncOuter, true, $aggFuncInner, false);
                    $this->assertDecimalEquals($expValues['DIS_OUTER'][$aggFuncOuter][$aggFuncInner], $actualAgg, $errorMsg);
                    
                    $errorMsg = "data={$sampleProviderName} val=DIS_BOTH func={$aggFuncOuter}({$aggFuncInner}) int={$sampleProvider['interval']}";
                    $actualAgg = $this->_analyst->changesNestedAggAtInterval($scalarId, $sampleProvider['rangeStart'], $sampleProvider['rangeEnd'], $sampleProvider['interval'], $aggFuncOuter, true, $aggFuncInner, true);
                    $this->assertDecimalEquals($expValues['DIS_BOTH'][$aggFuncOuter][$aggFuncInner], $actualAgg, $errorMsg);
                }
            }
        }
    }
    
    /**
     * @test
     * @group BasicDecimal
     * @group findsChangesAggAtRecurrence
     * @group changesAggAtRecurrence
     * @group decimal
     * @group createScalar
     * @group createSample
     */
    public function findsChangesAggAtRecurrence()
    {
        $allSampleProviders = self::provideFullSamplesData();
        $this->assertFalse(empty($allSampleProviders));

        $expProvider = self::provideExpectedValues('provideChangesAggAtRecurrence');
        $this->assertFalse(empty($expProvider));

        foreach ($expProvider as $sampleProviderName => $expValues) {
            $sampleProvider = $allSampleProviders[$sampleProviderName];
            $this->assertFalse(empty($sampleProvider));

            $scalarFields = array('name' => self::randomString(), 'type' => 'decimal');
            $scalarId = $this->_core->createScalar($scalarFields);

            foreach ($sampleProvider['samples'] as $sample) {
                list($end, $value) = $sample;
                $this->_partition->createSample($scalarId, $value, $end);
            }

            foreach (Hashmark_Analyst_BasicDecimal::getRecurFunctions() as $recurFunc) {
                foreach (self::$_aggFunctions as $aggFunc) {
                    $errorMsg = "data={$sampleProviderName} val=ALL recur={$recurFunc} agg={$aggFunc}";
                    $actValues = $this->_analyst->changesAggAtRecurrence($scalarId, $sampleProvider['rangeStart'], $sampleProvider['rangeEnd'], $recurFunc, $aggFunc, false);
                    foreach ($actValues as $actual) {
                        $this->assertDecimalEquals($expValues['ALL'][$recurFunc][$actual['x']][$aggFunc], $actual['y'], $errorMsg . " x={$actual['x']}");
                    }

                    $errorMsg = "data={$sampleProviderName} val=DIS recur={$recurFunc} agg={$aggFunc}";
                    $actValues = $this->_analyst->changesAggAtRecurrence($scalarId, $sampleProvider['rangeStart'], $sampleProvider['rangeEnd'], $recurFunc, $aggFunc, true);
                    foreach ($actValues as $actual) {
                        $this->assertDecimalEquals($expValues['DIS'][$recurFunc][$actual['x']][$aggFunc], $actual['y'], $errorMsg . " x={$actual['x']}");
                    }
                }
            }
        }
    }
   
    /**
     * @test
     * @group BasicDecimal
     * @group findsFrequencies
     * @group frequency
     * @group decimal
     * @group createScalar
     * @group createSample
     */
    public function findsFrequencies()
    {
        $allSampleProviders = self::provideFullSamplesData();
        $this->assertFalse(empty($allSampleProviders));

        $expProvider = self::provideExpectedValues('provideFrequency');
        $this->assertFalse(empty($expProvider));

        foreach ($expProvider as $sampleProviderName => $expValues) {
            $sampleProvider = $allSampleProviders[$sampleProviderName];
            $this->assertFalse(empty($sampleProvider));
        
            $scalarFields = array('name' => self::randomString(), 'type' => 'decimal');
            $scalarId = $this->_core->createScalar($scalarFields);

            foreach ($sampleProvider['samples'] as $sample) {
                list($end, $value) = $sample;
                $this->_partition->createSample($scalarId, $value, $end);
            }

            $errorMsg = "data={$sampleProviderName}} ASC";
            $actValues = $this->_analyst->frequency($scalarId, 50, $sampleProvider['rangeStart'], $sampleProvider['rangeEnd'], false);
            $this->assertEquals(count($expValues), count($actValues), $errorMsg);
            foreach ($actValues as $actual) {
                $this->assertEquals($expValues[$actual['x']], $actual['y'], $errorMsg . " val={$actual['x']}");
            }

            krsort($expValues);

            $errorMsg = "data={$sampleProviderName}} DESC";
            $actValues = $this->_analyst->frequency($scalarId, 50, $sampleProvider['rangeStart'], $sampleProvider['rangeEnd'], true);
            $this->assertEquals(count($expValues), count($actValues), $errorMsg);
            foreach ($actValues as $actual) {
                $this->assertEquals($expValues[$actual['x']], $actual['y'], $errorMsg . " val={$actual['x']}");
            }
        }
    }
    
    /**
     * @test
     * @group BasicDecimal
     * @group findsMoving
     * @group moving
     * @group decimal
     * @group createScalar
     * @group createSample
     */
    public function findsMoving()
    {
        $allSampleProviders = self::provideFullSamplesData();
        $this->assertFalse(empty($allSampleProviders));

        $expProvider = self::provideExpectedValues('provideMoving');
        $this->assertFalse(empty($expProvider));

        foreach ($expProvider as $sampleProviderName => $expValues) {
            $sampleProvider = $allSampleProviders[$sampleProviderName];
            $this->assertFalse(empty($sampleProvider));
        
            $scalarFields = array('name' => self::randomString(), 'type' => 'decimal');
            $scalarId = $this->_core->createScalar($scalarFields);

            foreach ($sampleProvider['samples'] as $sample) {
                list($end, $value) = $sample;
                $this->_partition->createSample($scalarId, $value, $end);
            }

            foreach (self::$_aggFunctions as $aggFunc) {
                $errorMsg = "val=ALL f={$aggFunc}";
                $actValues = $this->_analyst->moving($scalarId, 50, $sampleProvider['rangeStart'], $sampleProvider['rangeEnd'], $aggFunc, false);
                foreach ($actValues as $actual) {
                    $this->assertDecimalEquals($expValues['ALL'][$aggFunc][$actual['x']], $actual['y2'], $errorMsg . " x={$actual['x']}");
                }

                $errorMsg = "val=DIS f={$aggFunc}";
                $actValues = $this->_analyst->moving($scalarId, 50, $sampleProvider['rangeStart'], $sampleProvider['rangeEnd'], $aggFunc, true);
                foreach ($actValues as $actual) {
                    $this->assertDecimalEquals($expValues['DIS'][$aggFunc][$actual['x']], $actual['y2'], $errorMsg . " x={$actual['x']}");
                }
            }
        }
    }
    
    /**
     * @test
     * @group BasicDecimal
     * @group findsMovingAtInterval
     * @group movingAtInterval
     * @group decimal
     * @group createScalar
     * @group createSample
     */
    public function findsMovingAtInterval()
    {
        $allSampleProviders = self::provideFullSamplesData();
        $this->assertFalse(empty($allSampleProviders));

        $expProvider = self::provideExpectedValues('provideMovingAtInterval');
        $this->assertFalse(empty($expProvider));

        foreach ($expProvider as $sampleProviderName => $expValues) {
            $sampleProvider = $allSampleProviders[$sampleProviderName];
            $this->assertFalse(empty($sampleProvider));

            $expCount = count($expValues['ALL']['AVG']);
            $expDistinctCount = count($expValues['DIS']['AVG']);

            $scalarFields = array('name' => self::randomString(), 'type' => 'decimal');
            $scalarId = $this->_core->createScalar($scalarFields);

            foreach ($sampleProvider['samples'] as $sample) {
                list($end, $value) = $sample;
                $this->_partition->createSample($scalarId, $value, $end);
            }

            foreach (self::$_aggFunctions as $aggFunc) {
                $errorMsg = "data={$sampleProviderName} val=ALL f={$aggFunc} int={$sampleProvider['interval']}";
                $actValues = $this->_analyst->movingAtInterval($scalarId, 50, $sampleProvider['rangeStart'], $sampleProvider['rangeEnd'], $sampleProvider['interval'], $aggFunc, false);
                $this->assertEquals($expCount, count($actValues), $errorMsg);
                foreach ($actValues as $actual) {
                    $this->assertDecimalEquals($expValues['ALL'][$aggFunc][$actual['x']], $actual['y2'], $errorMsg . " x={$actual['x']}");
                }

                $errorMsg = "data={$sampleProviderName} val=DIS f={$aggFunc} int={$sampleProvider['interval']}";
                $actValues = $this->_analyst->movingAtInterval($scalarId, 50, $sampleProvider['rangeStart'], $sampleProvider['rangeEnd'], $sampleProvider['interval'], $aggFunc, true);
                $this->assertEquals($expDistinctCount, count($actValues), $errorMsg);
                foreach ($actValues as $actual) {
                    $this->assertDecimalEquals($expValues['DIS'][$aggFunc][$actual['x']], $actual['y2'], $errorMsg . " x={$actual['x']}");
                }
            }
        }
    }
}
