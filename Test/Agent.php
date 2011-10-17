<?php
// vim: fenc=utf-8:ft=php:ai:si:ts=4:sw=4:et:

/**
 * Hashmark_TestCase_Agent
 *
 * @filesource
 * @copyright   Copyright (c) 2008-2011 David Smith
 * @license     http://www.opensource.org/licenses/mit-license.php MIT License
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
