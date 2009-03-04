<?php
// vim: fenc=utf-8:ft=php:ai:si:ts=4:sw=4:et:

/**
 * Hashmark_TestCase
 *
 * @filesource
 * @link        http://code.google.com/p/hashmark/
 * @link        http://framework.zend.com/manual/en/coding-standard.html
 * @link        http://phpdoc.org/tutorial.php
 * @copyright   Copyright (c) 2008-2009, Code Actual LLC
 * @license     http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package     Hashmark-Test
 * @subpackage  Base
 * @version     $Id: Case.php 294 2009-02-13 03:48:59Z david $
*/

/**
 * Base class for all module test classes.
 *
 * @abstract
 * @package     Hashmark-Test
 * @subpackage  Base
 */
abstract class Hashmark_TestCase extends PHPUnit_Framework_TestCase
{
    /**
     * @access protected
     * @var boolean     If true, PHPUnit lets globals persist between tests.
     * @link http://sebastian-bergmann.de/archives/797-Global-Variables-and-PHPUnit.html
     */
    protected $backupGlobals = false;

    /**
     * @access protected
     * @var mixed   Database connection object/resource.
     * @see setUp()
     */
    protected $_db;
    
    /**
     * @access protected
     * @var Hashmark_DbHelper_*    Instance created in setUp().
     */
    protected $_dbHelper;

    /**
     * @access protected
     * @var string  Test case type, ex. 'Mysql'.
     * @see setUp()
     */
    protected $_type;

    /**
     * Set up the test case fixtures.
     * 
     * @access protected
     * @return void
     */
    protected function setUp()
    {
        // Extract suffix (ex. 'Mysql') from class (ex. 'Hashmark_TestCase_Client_Mysql'). 
        $className = get_class($this);

        $this->_type = substr($className, strrpos($className, '_') + 1);

        // Auto-load a DbHelper instance for DB dependent modules.
        $matchingDbHelperFile = dirname(__FILE__) . '/../DbHelper/' . HASHMARK_DBHELPER_DEFAULT_TYPE . '.php';
        if (is_readable($matchingDbHelperFile)) {
            $this->_dbHelper = Hashmark::getModule('DbHelper', HASHMARK_DBHELPER_DEFAULT_TYPE);
            $this->_db = $this->_dbHelper->openDb('unittest');
        }
    }
    
    /**
     * @access protected
     * @return void
     */
    protected function tearDown()
    {
        if ($this->_dbHelper) {
            $this->_dbHelper->closeDb($this->_db);
        }
    }

    /**
     * Return a @dataProvider-compat argument set without the array()
     * wrapping around each value.
     *
     * @static
     * @access public
     * @return Array
     */
    public static function unwrapProviderData($providerData)
    {
        array_walk($providerData, create_function('&$v,$k', '$v = $v[0];'));
        return $providerData;
    }


    /**
     * Provide valid increment values (initial, delta, expected result).
     *
     * @static
     * @access public
     * @return Array    Test method argument sets.
     * @see Loaded data file for return value format.
     */
    public static function provideIncrementValues()
    {
        static $data;

        require_once dirname(__FILE__) . '/Core/Data/' . __FUNCTION__ . '.php';

        return $data;
    }

    /**
     * Provide valid decrement values (initial, delta, expected result).
     *
     * @static
     * @access public
     * @return Array    Test method argument sets.
     * @see Loaded data file for return value format.
     */
    public static function provideDecrementValues()
    {
        static $data;

        require_once dirname(__FILE__) . '/Core/Data/' . __FUNCTION__ . '.php';

        return $data;
    }

    /**
     * Provide valid string values.
     *
     * @static
     * @access public
     * @return Array    Test method argument sets.
     */
    public static function provideStringValues()
    {
        static $data;

        if (!$data) {
            $data = array(array(''),
                          array(' '),
                          array('87a46c25bc0723ada70db470198e887d'));
        }

        return $data;
    }

    /**
     * Provide valid decimal values.
     *
     * @static
     * @access public
     * @return Array    Test method argument sets.
     */
    public static function provideDecimalValues()
    {
        static $data;

        if (!$data) {
            $data = array(array('0'),
                          array('1'),
                          array('-1'),
                          array('1.0001'),
                          array('-1.0001'),
                          array('0.0001'),
                          array('-0.0001'),
                          array('1000000000000000.0001'),
                          array('-1000000000000000.0001'));
        }

        return $data;
    }

    /**
     * Provide names which should never identify a scalar.
     *
     * @static
     * @access public
     * @return Array    Test method argument sets.
     */
    public static function provideInvalidScalarNames()
    {
        static $data;

        if (!$data) {
            $data = array(array(''),
                          array(' '),
                          array(null),
                          array(true),
                          array(false),
                          array(0),
                          array(1),
                          array(-1));
        }

        return $data;
    }

    /**
     * Provide sets of scalar types and values.
     *
     * @static
     * @access public
     * @return Array    Test method argument sets.
     * 
     * Format:
     *
     *      array(array('decimal', 0),
     *            ...
     *            array('string', 'aef448733247db5be49ae8597aa94d59S'));
     */
    public static function provideScalarTypesAndValues()
    {
        static $data;

        if (!$data) {
            $strings = self::provideStringValues();
            $numbers = self::provideDecimalValues();

            $data = array();

            foreach ($strings as $str) {
                $data[] = array('string', $str[0]);
            }

            foreach ($numbers as $num) {
                $data[] = array('decimal', $num[0]);
            }
        }

        return $data;
    }

    /**
     * Provide fields of scalars scheduled for sampling.
     *
     * @static
     * @access public
     * @return Array    Test method argument sets.
     */
    public static function provideScalarsWithScheduledSamplers()
    {
        static $data;

        if (!$data) {
            foreach (Hashmark_Core::getValidScalarTypes() as $type) {
                $argSet = array();
                $argSet['name'] = self::randomString();
                $argSet['type'] = $type;
                $argSet['description'] = self::randomString();
                $argSet['sampler_status'] = 'Scheduled';
                $argSet['sampler_handler'] = 'Test';
                $argSet['sampler_start'] = gmdate(HASHMARK_DATETIME_FORMAT);
            
                // 0-minute frequencies will make them due to run immediately.
                $argSet['sampler_frequency'] = 0;
                
                $data[] = $argSet;
            }
        }

        return $data;
    }

    /**
     * Return a random decimal string.
     *
     * @static
     * @access public
     * @return string
     */
    public static function randomDecimal()
    {
        // Random DECIMAL(M,D) numbers.
        // @see DECIMAL type, http://dev.mysql.com/doc/refman/5.0/en/numeric-type-overview.html#id4944739

        // Before decimal point:
        $value = '';
        $wholeDigits = mt_rand(1, HASHMARK_DECIMAL_TOTALWIDTH - HASHMARK_DECIMAL_RIGHTWIDTH);
        for ($d = 0; $d < $wholeDigits; $d++) {
            $value .= mt_rand(0, 9);
        }

        // After:
        $value = preg_replace('/^0+/', '', $value) . '.';
        $pointDigits = mt_rand(1, HASHMARK_DECIMAL_RIGHTWIDTH);
        for ($d = 0; $d < $pointDigits; $d++) {
            $value .= mt_rand(0, 9);
        }
        return $value;
    }
    
    /**
     * Return a random string.
     *
     * @static
     * @access public
     * @param int   $minLength  1 to 40.
     * @param int   $maxLength  1 to 40.
     * @return string
     * @throws Exception If $minLength or $maxLength is greater than 40 or negative.
     */
    public static function randomString($minLength = 30, $maxLength = 30)
    {
        $str = Hashmark_Util::randomSha1();

        if ($maxLength > 0 && $maxLength < 41 && $minLength > 0 && $minLength <= $maxLength) {
            return substr($str, 0, mt_rand($minLength, $maxLength));
        }

        throw new Exception('Random string limits are invalid.', HASHMARK_EXCEPTION_VALIDATION);
    }
    
    /**
     * Testable logic for assertArrayContainsOnly().
     *
     * @static
     * @access public
     * @param mixed     $needle     Only expected element of $haystack.
     * @param Array     $haystack
     * @param boolean   $strict     If true, $a === $b logic is used; othewise in_array().
     * @return boolean  True if $haystack only contains one $needle.
     */
    public static function checkArrayContainsOnly($needle, $haystack, $strict = false)
    {
        if (!is_array($haystack) || count($haystack) != 1) {
            return false;
        }

        if ($strict) {
            $values = array_values($haystack);
            return $values[0] === $needle;
        } else {
            return in_array($needle, $haystack);
        }
    }

    /**
     * Assert $haystack contains only one element that is equal to $needle.
     *
     *      -   Uses assertTrue() internally to increment assertion count.
     *
     * @access public
     * @param mixed     $needle     Only expected element of $haystack.
     * @param Array     $haystack
     * @param boolean   $strict     If true, $a === $b logic is used; othewise in_array().
     * @return void
     */
    public function assertArrayContainsOnly($needle, $haystack, $message = '')
    {
        if (self::checkArrayContainsOnly($needle, $haystack, $message)) {
            $this->assertTrue(true);
        } else {
            $needleType = gettype($needle);
            $needle = str_replace("\n", '', print_r($needle, true));
            $haystackType = gettype($haystack);
            $haystack = str_replace("\n", '', print_r($haystack, true));

            if ($message) {
                $message .= "\n";
            }

            $message .= "Failed asserting that Array <{$haystackType}:{$haystack}> "
                      . "contains only element <{$needleType}:{$needle}>.";

            $this->assertTrue(false, $message);
        }
    }
    
    /**
     * Testable logic for assertDecimalEquals().
     *
     * @static
     * @access public
     * @param string    $expected
     * @param string    $actual
     * @return boolean  True if equal.
     */
    public static function checkDecimalEquals($expected, $actual) 
    {
        if (!is_string($expected) || !is_string($actual)) {
            return false;
        }

        bcscale(HASHMARK_DECIMAL_RIGHTWIDTH);

        return 0 === bccomp($expected, $actual);
    }
    
    /**
     * Uses bccomp() to check equality of two strings representing decimal values.
     *
     *      -   Uses assertTrue() internally to increment assertion count.
     *
     * @access public
     * @param string    $expected
     * @param string    $actual
     * @return void
     */
    public function assertDecimalEquals($expected, $actual, $message = '')
    {

        if (self::checkDecimalEquals($expected, $actual)) {
            $this->assertTrue(true);
        } else {
            $actualType = gettype($actual);
            $expectedType = gettype($expected);
            
            if ($message) {
                $message .= "\n";
            }

            $message .= "Failed asserting that actual decimal string <{$actualType}:"
                      . "{$actual}> equals expected <{$expectedType}:{$expected}>.";

            $this->assertTrue(false, $message);
        }
    }
}
