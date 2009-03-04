<?php
// vim: fenc=utf-8:ft=php:ai:si:ts=4:sw=4:et:

/**
 * Hashmark_TestCase_Sampler
 *
 * @filesource
 * @link        http://code.google.com/p/hashmark/
 * @link        http://framework.zend.com/manual/en/coding-standard.html
 * @link        http://phpdoc.org/tutorial.php
 * @copyright   Copyright (c) 2008-2009, Code Actual LLC
 * @license     http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package     Hashmark-Test
 * @subpackage  Base
 * @version     $Id: Sampler.php 290 2009-02-11 04:55:11Z david $
*/

/**
 * @abstract
 * @package     Hashmark-Test
 * @subpackage  Base
 */
abstract class Hashmark_TestCase_Sampler extends Hashmark_TestCase
{
    /**
     * @test
     * @group Sampler
     * @group getsSamplerName
     */
    abstract public function getsSamplerName();
    
    /**
     * @test
     * @group Sampler
     * @group getsSamplerDescription
     */
    abstract public function getsSamplerDescription();
    
    /**
     * @test
     * @group Sampler
     * @group runsSampler
     */
    abstract public function runsSampler();
}
