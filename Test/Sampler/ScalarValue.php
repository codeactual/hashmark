<?php
// vim: fenc=utf-8:ft=php:ai:si:ts=4:sw=4:et:

/**
 * Hashmark_TestCase_Sampler_ScalarValue
 *
 * @filesource
 * @link        http://code.google.com/p/hashmark/
 * @link        http://framework.zend.com/manual/en/coding-standard.html
 * @link        http://phpdoc.org/tutorial.php
 * @copyright   Copyright (c) 2008-2009, Code Actual LLC
 * @license     http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package     Hashmark-Test
 * @subpackage  Hashmark_Sampler
 * @version     $Id$
*/

/**
 * @package     Hashmark-Test
 * @subpackage  Hashmark_Sampler
 */
class Hashmark_TestCase_Sampler_ScalarValue extends Hashmark_TestCase_Sampler
{
    /**
     * @test
     * @group Sampler
     * @group Test
     * @group runsSampler
     * @group run
     */
    public function runsSampler()
    {
        $expectedFields = array();
        $expectedFields['name'] = self::randomString();
        $expectedFields['value'] = 'test_value';
        $expectedFields['type'] = 'string';

        $expectedId = Hashmark::getModule('Core', '', $this->_db)->createScalar($expectedFields);
        $sample = Hashmark::getModule('Sampler', 'ScalarValue')->run(array('scalarId' => $expectedId));

        $this->assertEquals($expectedFields['value'], $sample);
    }
}
