<?php
// vim: fenc=utf-8:ft=php:ai:si:ts=4:sw=4:et:

/**
 * Hashmark_TestCase_Agent_Test
 *
 * @filesource
 * @copyright   Copyright (c) 2008-2011 David Smith
 * @license     http://www.opensource.org/licenses/mit-license.php MIT License
 * @package     Hashmark-Test
 * @subpackage  Hashmark_Agent
 * @version     $Id$
*/

/**
 * @package     Hashmark-Test
 * @subpackage  Hashmark_Agent
 */
class Hashmark_TestCase_Agent_Test extends Hashmark_TestCase_Agent
{
    /**
     * @test
     * @group Agent
     * @group Test
     * @group getsAgentName
     * @group getName
     */
    public function getsAgentName()
    {
        $this->assertEquals('Test', Hashmark::getModule('Agent', 'Test')->getName());
    }
    
    /**
     * @test
     * @group Agent
     * @group Test
     * @group getsAgentDescription
     * @group getDescription
     */
    public function getsAgentDescription()
    {
        $this->assertEquals('Unit test agent', Hashmark::getModule('Agent', 'Test')->getDescription());
    }
    
    /**
     * @test
     * @group Agent
     * @group Test
     * @group runsAgent
     * @group run
     */
    public function runsAgent()
    {
        $fakeScalarId = 1;
        $sample = Hashmark::getModule('Agent', 'Test')->run($fakeScalarId);
        $this->assertEquals('1234', $sample);
    }
}
