<?php
// vim: fenc=utf-8:ft=php:ai:si:ts=4:sw=4:et:

/**
 * Hashmark_TestCase_Util
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
 * @package     Hashmark-Test
 */
class Hashmark_TestCase_Util extends Hashmark_TestCase
{
    /**
     * @test
     * @group Util
     * @group convertsDataTypesToDatetimes
     * @group toDateTime
     */
    public function convertsDataTypesToDatetimes()
    {
        $expectedDatetime = '2009-01-31 15:03:56';
        $this->assertEquals($expectedDatetime, Hashmark_Util::toDateTime(1233414236));
        $this->assertEquals($expectedDatetime, Hashmark_Util::toDateTime('20090131150356'));
        $this->assertEquals($expectedDatetime, Hashmark_Util::toDateTime($expectedDatetime));
        $this->assertFalse(Hashmark_Util::toDateTime(-1));
        $this->assertFalse(Hashmark_Util::toDateTime('120090131150356'));
        $this->assertFalse(Hashmark_Util::toDateTime(''));
        $this->assertFalse(Hashmark_Util::toDateTime(false));
    }
    
    /**
     * @test
     * @group Util
     * @group sortsStringsByDescendingLength
     * @group sortByStrlenReverse
     */
    public function sortsStringsByDescendingLength()
    {
        $sorted = array('-----', '----', '-', '---', '--');
        $expected = array('-----', '----', '---', '--', '-');
        usort($sorted, array('Hashmark_Util', 'sortByStrlenReverse'));
        $this->assertEquals($expected, $sorted);
    }
}
