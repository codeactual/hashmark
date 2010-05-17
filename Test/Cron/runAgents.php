<?php
// vim: fenc=utf-8:ft=php:ai:si:ts=4:sw=4:et:

/**
 * Hashmark_TestCase_Cron_runAgents
 *
 * @filesource
 * @link        http://code.google.com/p/hashmark/
 * @link        http://framework.zend.com/manual/en/coding-standard.html
 * @link        http://phpdoc.org/tutorial.php
 * @copyright   Copyright (c) 2008-2009, Code Actual LLC
 * @license     http://www.opensource.org/licenses/bsd-license.php New BSD License
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

        $scalar = array();
        $scalar['name'] = self::randomString();
        $scalar['type'] = 'decimal';
        $scalar['value'] = '1234';
        $scalarId = $core->createScalar($scalar);

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
        $scalar = $core->getScalarById($scalarId);
        $this->assertEquals($scalar['value'], $scalar['value']);
        
        // Assert scalar agent is ready for future run.
        $scalarAgent = $core->getScalarAgentById($scalarAgentId);
        $this->assertEquals('Scheduled', $scalarAgent['status']);
        $this->assertEquals('', $scalarAgent['error']);
        $this->assertLessThan(5, abs(strtotime($scalarAgent['lastrun'] . ' UTC') - time()));
    }
}
