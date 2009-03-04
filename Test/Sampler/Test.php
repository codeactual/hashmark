<?php
// vim: fenc=utf-8:ft=php:ai:si:ts=4:sw=4:et:

/**
 * Hashmark_TestCase_Sampler_Test
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
class Hashmark_TestCase_Sampler_Test extends Hashmark_TestCase_Sampler
{
    /**
     * @test
     * @group Sampler
     * @group Test
     * @group getsSamplerName
     * @group getName
     */
    public function getsSamplerName()
    {
        $this->assertEquals('Test', Hashmark::getModule('Sampler', 'Test')->getName());
    }
    
    /**
     * @test
     * @group Sampler
     * @group Test
     * @group getsSamplerDescription
     * @group getDescription
     */
    public function getsSamplerDescription()
    {
        $this->assertEquals('Unit test sampler', Hashmark::getModule('Sampler', 'Test')->getDescription());
    }
    
    /**
     * @test
     * @group Sampler
     * @group Test
     * @group runsSampler
     * @group run
     */
    public function runsSampler()
    {
        $fakeScalarId = 1;
        $sample = Hashmark::getModule('Sampler', 'Test')->run($fakeScalarId);
        $this->assertEquals('1234', $sample);
    }
}
