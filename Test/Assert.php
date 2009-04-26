<?php
// vim: fenc=utf-8:ft=php:ai:si:ts=4:sw=4:et:

/**
 * Hashmark_TestCase_Assert
 *
 * @filesource
 * @link        http://code.google.com/p/hashmark/
 * @link        http://framework.zend.com/manual/en/coding-standard.html
 * @link        http://phpdoc.org/tutorial.php
 * @copyright   Copyright (c) 2008-2009, Code Actual LLC
 * @license     http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package     Hashmark-Test
 * @version     $Id$
*/

/**
 * Tests for custom assertions in Hashmark_TestCase.
 *
 * @package     Hashmark-Test
 */
class Hashmark_TestCase_Assert extends Hashmark_TestCase
{
    /**
     * @test
     * @group Assert
     * @group testAssertDecimalEquals
     * @group assertDecimalEquals
     */
    public function comparesDecimalStrings()
    {
        $data = array();
        $data[] = array('1000000000000000.1300', '1000000000000000.1300', true);
        $data[] = array('1000000000000000.130', '1000000000000000.1300', true);
        $data[] = array('1000000000000000.13', '1000000000000000.1300', true);
        $data[] = array('1000000000000000.1301', '1000000000000000.1300', false);
        $data[] = array('1000000000000000.3', '1000000000000000.1', false);
        $data[] = array('1000000000000000', '1000000000000000', true);
        $data[] = array('1000000000000001', '1000000000000000', false);
        
        $data[] = array('-1000000000000000.1300', '-1000000000000000.1300', true);
        $data[] = array('-1000000000000000.130', '-1000000000000000.1300', true);
        $data[] = array('-1000000000000000.13', '-1000000000000000.1300', true);
        $data[] = array('-1000000000000000.1301', '-1000000000000000.1300', false);
        $data[] = array('-1000000000000000.3', '-1000000000000000.1', false);
        $data[] = array('-1000000000000000', '-1000000000000000', true);
        $data[] = array('-1000000000000001', '-1000000000000000', false);

        $data[] = array('.1300', '0.1300', true);
        $data[] = array('-.1300', '-0.1300', true);
        
        $data[] = array('1300', '.1300', false);
        $data[] = array('-1300', '-0.1300', false);
        $data[] = array('-1300', '-.1300', false);

        foreach ($data as $d) {
            list($a, $b, $isExpectedEqual) = $d;

            $actualEquals = $this->checkDecimalEquals($a, $b);

            $message = "a={$a} b={$b}";

            if ($isExpectedEqual) {
                $this->assertTrue($actualEquals, $message . ' expected=EQUAL');
            } else {
                $this->assertFalse($actualEquals, $message . ' expected=UNEQUAL');
            }
        }
    }

    /**
     * @test
     * @group Assert
     * @group testAssertArrayContainsOnly
     * @group assertArrayContainsOnly
     */
    public function detectsArraysWithOneExpectedElement()
    {
        // No type check.
        $this->assertTrue($this->checkArrayContainsOnly(1, array(1)));
        $this->assertFalse($this->checkArrayContainsOnly(1, array(1, 2)));
        $this->assertFalse($this->checkArrayContainsOnly(1, array()));
        $this->assertFalse($this->checkArrayContainsOnly(1, array(1, 1)));
        $this->assertFalse($this->checkArrayContainsOnly(1, 1));
        
        // Strict.
        $this->assertFalse($this->checkArrayContainsOnly(1, array('1'), true));
        $this->assertFalse($this->checkArrayContainsOnly('1000000000000000.84766667', array('1000000000000000.84766668'), true));
        $this->assertTrue($this->checkArrayContainsOnly('1000000000000000.84766667', array('1000000000000000.84766667'), true));
    }
}
