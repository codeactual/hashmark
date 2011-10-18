<?php
// vim: fenc=utf-8:ft=php:ai:si:ts=4:sw=4:et:

/**
 * Hashmark_TestCase_Cron_runAgents
 *
 * @filesource
 * @copyright   Copyright (c) 2008-2011 David Smith
 * @license     http://www.opensource.org/licenses/mit-license.php MIT License
 * @package     Hashmark-Test
 * @subpackage  Cron
 * @version     $Id$
*/

/**
 * @package     Hashmark-Test
 * @subpackage  Cron
 */
class Hashmark_TestCase_Cron_runAgents extends Hashmark_TestCase_Cron
{
    /**
     * @test
     * @group Cron
     * @group runsAgents
     * @group runAgents
     */
    public function runsAgents()
    {
        $core = Hashmark::getModule('Core', '', $this->_db);

        $scheduledScalarIds = array();

        $expected = array();
        $expected['name'] = self::randomString();
        $expected['type'] = 'decimal';
        $expected['value'] = 1234;
        $scalarId = $core->createScalar($expected);

        $agent = $core->getAgentByName('ScalarValue');
        if ($agent) {
            $agentId = $agent['id'];
        } else {
            $agentId = $core->createAgent('ScalarValue');
        }

        // Agent is scheduled to run immediately (0 frequency).
        $scalarAgentId = $core->createScalarAgent($scalarId, $agentId,
                                                         0, 'Scheduled');
        ob_start();
        require HASHMARK_ROOT_DIR . '/Cron/runAgents.php';
        ob_end_clean();

        // Assert scalar value changed in last 60 seconds.
        $actual = $core->getScalarById($scalarId);
        $this->assertEquals($expected['value'] + 5, (int) $actual['value']);

        // Assert scalar agent is ready for future run.
        $scalarAgent = $core->getScalarAgentById($scalarAgentId);
        $this->assertEquals('Scheduled', $scalarAgent['status']);
        $this->assertEquals('', $scalarAgent['error']);
        $this->assertLessThan(5, abs(strtotime($scalarAgent['lastrun'] . ' UTC') - time()));
    }
}
