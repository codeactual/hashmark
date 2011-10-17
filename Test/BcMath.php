<?php
// vim: fenc=utf-8:ft=php:ai:si:ts=4:sw=4:et:

/**
 * Hashmark_TestCase_BcMath
 *
 * @filesource
 * @link        http://code.google.com/p/hashmark/
 * @link        http://framework.zend.com/manual/en/coding-standard.html
 * @link        http://phpdoc.org/tutorial.php
 * @copyright   Copyright (c) 2008-2011 David Smith
 * @license     http://www.opensource.org/licenses/mit-license.php MIT License
 * @package     Hashmark-Test
 * @version     $Id$
*/

/**
 * @package     Hashmark-Test
 */
class Hashmark_TestCase_BcMath extends Hashmark_TestCase
{
    /**
     * @test
     * @group BcMath
     * @group extensionLoaded
     * @group dependencies
     */
    public function extensionLoaded()
    {
        $this->assertTrue(extension_loaded('bcmath'));
    }
    
    /**
     * @test
     * @group BcMath
     * @group calculatesRoundedValue
     * @group round
     * @group assertDecimalEquals
     */
    public function calculatesRoundedValue()
    {
        $data = array();
        $data[] = array('0.5556', '0.555550', 4);
        $data[] = array('-0.5556', '-0.555550', 4);

        $data[] = array('0.2926', '0.292550', 4);
        $data[] = array('-0.2926', '-0.292550', 4);
        
        $data[] = array('0.0402', '0.04015000', 4);
        $data[] = array('-0.0402', '-0.04015000', 4);

        $data[] = array('0.0041', '0.00405000', 4);
        $data[] = array('-0.0041', '-0.00405000', 4);
        
        $data[] = array('0.0000', '0.00000001', 4);
        $data[] = array('-0.0000', '-0.00000001', 4);

        $data[] = array('0.0000', '0.00004999', 4);
        $data[] = array('-0.0000', '-0.00004999', 4);
        
        $data[] = array('0.0001', '0.00005', 4);
        $data[] = array('-0.0001', '-0.00005', 4);

        $data[] = array('0.0001', '0.000050', 4);
        $data[] = array('-0.0001', '-0.000050', 4);
        
        $data[] = array('0.9999', '0.9999', 4);
        $data[] = array('-0.9999', '-0.9999', 4);

        $data[] = array('1', '0.99995', 4);
        $data[] = array('-1', '-0.99995', 4);
        
        $data[] = array('1', '0.999950', 4);
        $data[] = array('-1', '-0.999950', 4);
        
        $data[] = array('999999999999999.9999', '999999999999999.999949', 4);
        $data[] = array('-999999999999999.9999', '-999999999999999.999949', 4);
        
        $data[] = array('1000000000000000', '999999999999999.999950', 4);
        $data[] = array('-1000000000000000', '-999999999999999.999950', 4);
        
        $data[] = array('1000000000000000', '1000000000000000.000049', 4);
        $data[] = array('-1000000000000000', '-1000000000000000.000049', 4);
        
        $data[] = array('1000000000000000.0001', '1000000000000000.000050', 4);
        $data[] = array('-1000000000000000.0001', '-1000000000000000.000050', 4);

        foreach ($data as $d) {
            list($expectedRound, $value, $precision) = $d;
            $this->assertDecimalEquals($expectedRound, Hashmark_BcMath::round($value, $precision));
        }
    }
    
    /**
     * @test
     * @group BcMath
     * @group calculatesSum
     * @group sum
     * @group assertDecimalEquals
     */
    public function calculatesSum()
    {
        $data = array();
        
        $data[] = array('-0.0803', array('1000000000000000.3701',
                                         '-1000000000000000.4504'));
        $data[] = array('0.0803', array('-1000000000000000.3701',
                                        '1000000000000000.4504'));
        $data[] = array('2000000000000000.8205', array('1000000000000000.3701',
                                                       '1000000000000000.4504'));
        $data[] = array('2000000000000000.9008', array('1000000000000000.4504',
                                                       '1000000000000000.4504'));
        $data[] = array('2000000000000000.7402', array('1000000000000000.3701',
                                                       '1000000000000000.3701'));
        $data[] = array('-2000000000000000.8205', array('-1000000000000000.3701',
                                                       '-1000000000000000.4504'));
        $data[] = array('-2000000000000000.9008', array('-1000000000000000.4504',
                                                       '-1000000000000000.4504'));
        $data[] = array('-2000000000000000.7402', array('-1000000000000000.3701',
                                                       '-1000000000000000.3701'));

        foreach ($data as $d) {
            list($expectedSum, $values) = $d;
            $this->assertDecimalEquals($expectedSum, Hashmark_BcMath::sum($values));
        }
    }

    /**
     * @test
     * @group BcMath
     * @group calculatesAvg
     * @group avg
     * @group assertDecimalEquals
     */
    public function calculatesAvg()
    {
        $data = array();
        $data[] = array('-0.0402', array('1000000000000000.3701',
                                         '-1000000000000000.4504'));
        $data[] = array('0.0402', array('-1000000000000000.3701',
                                        '1000000000000000.4504'));
        $data[] = array('1000000000000000.4103', array('1000000000000000.3701',
                                                       '1000000000000000.4504'));
        $data[] = array('1000000000000000.4504', array('1000000000000000.4504',
                                                       '1000000000000000.4504'));
        $data[] = array('1000000000000000.3701', array('1000000000000000.3701',
                                                       '1000000000000000.3701'));

        foreach ($data as $d) {
            list($expectedAvg, $values) = $d;
            $this->assertDecimalEquals($expectedAvg, Hashmark_BcMath::avg($values));
        }
    }

    /**
     * @test
     * @group BcMath
     * @group calculatesMaxValue
     * @group max
     * @group assertDecimalEquals
     */
    public function calculatesMaxValue()
    {
        $data = array();
        $data[] = array('1000000000000000.3701', array('0000000000000000.0001',
                                                       '1000000000000000.3701',
                                                       '0000000000000000.0002',
                                                       '-1000000000000000.4504'));
        $data[] = array('1000000000000000.4504', array('0000000000000000.0001',
                                                       '1000000000000000.3701',
                                                       '0000000000000000.0002',
                                                       '1000000000000000.4504'));
        $data[] = array('-0000000000000000.0001', array('-0000000000000000.0001',
                                                        '-1000000000000000.3701',
                                                        '-0000000000000000.0002',
                                                        '-1000000000000000.4504'));
        $data[] = array('0000000000000000.0002', array('-0000000000000000.0001',
                                                       '-1000000000000000.3701',
                                                       '0000000000000000.0002',
                                                       '-1000000000000000.4504'));

        foreach ($data as $d) {
            list($expectedMax, $values) = $d;
            $this->assertDecimalEquals($expectedMax, Hashmark_BcMath::max($values));
        }
    }

    /**
     * @test
     * @group BcMath
     * @group calculatesMinValue
     * @group min
     * @group assertDecimalEquals
     */
    public function calculatesMinValue()
    {
        $data = array();
        $data[] = array('-1000000000000000.3701', array('-0000000000000000.0001',
                                                        '-1000000000000000.3701',
                                                        '-0000000000000000.0002',
                                                        '1000000000000000.4504'));
        $data[] = array('-1000000000000000.4504', array('0000000000000000.0001',
                                                        '-1000000000000000.3701',
                                                        '0000000000000000.0002',
                                                        '-1000000000000000.4504'));
        $data[] = array('0000000000000000.0001', array('0000000000000000.0001',
                                                       '1000000000000000.3701',
                                                       '0000000000000000.0002',
                                                       '1000000000000000.4504'));
        $data[] = array('-0000000000000000.0002', array('-0000000000000000.0001',
                                                        '1000000000000000.3701',
                                                        '-0000000000000000.0002',
                                                        '1000000000000000.4504'));

        foreach ($data as $d) {
            list($expectedMin, $values) = $d;
            $this->assertDecimalEquals($expectedMin, Hashmark_BcMath::min($values));
        }
    }
}
