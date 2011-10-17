<?php
// vim: fenc=utf-8:ft=php:ai:si:ts=4:sw=4:et:

/**
 * Hashmark_TestCase
 *
 * @filesource
 * @link        http://code.google.com/p/hashmark/
 * @link        http://framework.zend.com/manual/en/coding-standard.html
 * @link        http://phpdoc.org/tutorial.php
 * @copyright   Copyright (c) 2008-2011 David Smith
 * @license     http://www.opensource.org/licenses/mit-license.php MIT License
 * @package     Hashmark-Test
 * @subpackage  Base
 * @version     $Id$
*/

/**
 * Base class for all module test classes.
 *
 * @package     Hashmark-Test
 * @subpackage  Base
 */
abstract class Hashmark_TestCase extends PHPUnit_Framework_TestCase
{
    /**
     * @var boolean     If true, PHPUnit lets globals persist between tests.
     * @link http://sebastian-bergmann.de/archives/797-Global-Variables-and-PHPUnit.html
     */
    protected $backupGlobals = false;

    /**
     * @var Zend_Db_Adapter_*    Current instance.
     * @see setUp()
     */
    protected $_db;

    /**
     * @var string  Test case type, ex. 'DbDependent' (Hashmark_Module_DbDependent).
     * @see setUp()
     */
    protected $_type;

    /**
     * Set up the test case fixtures.
     * 
     * @return void
     */
    protected function setUp()
    {
        // Extract suffix (ex. 'DbDependent') from class (ex. 'Hashmark_TestCase_Module_DbDependent'). 
        $className = get_class($this);

        $this->_type = substr($className, strrpos($className, '_') + 1);
        $this->_db = Hashmark::getModule('DbHelper')->openDb('unittest');
    }
    
    /**
     * @return void
     */
    protected function tearDown()
    {
        $this->_db->closeConnection();
    }

    /**
     * Return a @dataProvider-compat argument set without the array()
     * wrapping around each value.
     *
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
     * @return Array    Test method argument sets.
     * @see Loaded data file for return value format.
     */
    public static function provideIncrementValues()
    {
        static $data;

        require_once HASHMARK_ROOT_DIR . '/Test/Core/Data/' . __FUNCTION__ . '.php';

        return $data;
    }

    /**
     * Provide valid decrement values (initial, delta, expected result).
     *
     * @return Array    Test method argument sets.
     * @see Loaded data file for return value format.
     */
    public static function provideDecrementValues()
    {
        static $data;

        require_once HASHMARK_ROOT_DIR . '/Test/Core/Data/' . __FUNCTION__ . '.php';

        return $data;
    }

    /**
     * Provide valid string values.
     *
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
     * Return a random decimal string. Based on DECIMAL(M,D) configuration.
     *
     * @see DECIMAL type, http://dev.mysql.com/doc/refman/5.0/en/numeric-type-overview.html#id4944739
     * @see Config/Hashmark.php, $config['DbHelper']
     * @return string
     */
    public static function randomDecimal()
    {
        $decimalTotalWidth = Hashmark::getConfig('DbHelper', '', 'decimal_total_width');
        $decimalRightWidth = Hashmark::getConfig('DbHelper', '', 'decimal_right_width');

        // Random DECIMAL(M,D) numbers.

        // Before decimal point:
        $value = '';
        $wholeDigits = mt_rand(1, $decimalTotalWidth - $decimalRightWidth);
        for ($d = 0; $d < $wholeDigits; $d++) {
            $value .= mt_rand(0, 9);
        }

        // After:
        $value = preg_replace('/^0+/', '', $value) . '.';
        $pointDigits = mt_rand(1, $decimalRightWidth);
        for ($d = 0; $d < $pointDigits; $d++) {
            $value .= mt_rand(0, 9);
        }
        return $value;
    }
    
    /**
     * Return a random string.
     *
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
     * @param string    $expected
     * @param string    $actual
     * @return boolean  True if equal.
     */
    public static function checkDecimalEquals($expected, $actual) 
    {
        if (!is_string($expected) || !is_string($actual)) {
            return false;
        }
        
        bcscale(Hashmark::getConfig('DbHelper', '', 'decimal_right_width'));

        return 0 === bccomp($expected, $actual);
    }
    
    /**
     * Uses bccomp() to check equality of two strings representing decimal values.
     *
     *      -   Uses assertTrue() internally to increment assertion count.
     *
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
