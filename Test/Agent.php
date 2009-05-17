<?php
// vim: fenc=utf-8:ft=php:ai:si:ts=4:sw=4:et:

/**
 * Hashmark_TestCase_Agent
 *
 * @filesource
 * @link        http://code.google.com/p/hashmark/
 * @link        http://framework.zend.com/manual/en/coding-standard.html
 * @link        http://phpdoc.org/tutorial.php
 * @copyright   Copyright (c) 2008-2009, Code Actual LLC
 * @license     http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package     Hashmark-Test
 * @subpackage  Base
 * @version     $Id$
*/

/**
 * @package     Hashmark-Test
 * @subpackage  Base
 */
abstract class Hashmark_TestCase_Agent extends Hashmark_TestCase
{
    /**
     * @test
     * @group Agent
     * @group runsAgent
     */
    abstract public function runsAgent();
}
