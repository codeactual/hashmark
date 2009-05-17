<?php
// vim: fenc=utf-8:ft=php:ai:si:ts=4:sw=4:et:

/**
 * Hashmark_TestCase_Agent_ScalarValue
 *
 * @filesource
 * @link        http://code.google.com/p/hashmark/
 * @link        http://framework.zend.com/manual/en/coding-standard.html
 * @link        http://phpdoc.org/tutorial.php
 * @copyright   Copyright (c) 2008-2009, Code Actual LLC
 * @license     http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package     Hashmark-Test
 * @subpackage  Hashmark_Agent
 * @version     $Id$
*/

/**
 * @package     Hashmark-Test
 * @subpackage  Hashmark_Agent
 */
class Hashmark_TestCase_Agent_ScalarValue extends Hashmark_TestCase_Agent
{
    /**
     * @test
     * @group Agent
     * @group Test
     * @group runsAgent
     * @group run
     */
    public function runsAgent()
    {
        $expectedFields = array();
        $expectedFields['name'] = self::randomString();
        $expectedFields['value'] = 'test_value';
        $expectedFields['type'] = 'string';

        $expectedId = Hashmark::getModule('Core', '', $this->_db)->createScalar($expectedFields);
        $sample = Hashmark::getModule('Agent', 'ScalarValue')->run(array('scalarId' => $expectedId));

        $this->assertEquals($expectedFields['value'], $sample);
    }
}
