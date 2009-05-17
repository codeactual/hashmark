<?php
// vim: fenc=utf-8:ft=php:ai:si:ts=4:sw=4:et:

/**
 * Hashmark_Cron
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
class Hashmark_TestCase_Cron extends Hashmark_TestCase
{
    /**
     * Resources needed for most tests.
     *
     * @return void
     */
    protected function setUp()
    {
        parent::setUp();
        
        $this->_cron = Hashmark::getModule('Cron', '', $this->_db);
        $this->_core = Hashmark::getModule('Core', '', $this->_db);
    }
    
    /**
     * @test
     * @group Cron
     * @group createsSampleAndUpdatesScalar
     * @group createSample
     * @group getSample
     * @group createScalar
     * @group getScalarById
     */
    public function createsSampleAndUpdatesScalar()
    {
        $partition = Hashmark::getModule('Partition', '', $this->_db);
        
        foreach (self::provideScalarTypesAndValues() as $data) {
            list($type, $value) = $data;

            $expectedScalar = array();
            $expectedScalar['name'] = self::randomString();
            $expectedScalar['type'] = $type;

            $expectedScalarId = $this->_core->createScalar($expectedScalar);

            $expectedStart = gmdate(HASHMARK_DATETIME_FORMAT, time() - 5);
            $expectedEnd = gmdate(HASHMARK_DATETIME_FORMAT);

            $sampleCreated = $this->_cron->createSample($expectedScalarId, $value,
                                                        $expectedStart, $expectedEnd);
            $this->assertTrue($sampleCreated);

            $sample = $this->_cron->getLatestSample($expectedScalarId);

            // Ensure samples table got updated.
            $this->assertEquals($expectedStart, $sample['start']);
            $this->assertEquals($expectedEnd, $sample['end']);
            $this->assertEquals($value, $sample['value']);
            $this->assertEquals(1, $sample['id']);

            // Ensure associated scalar got updated.
            $scalar = $this->_core->getScalarById($expectedScalarId);
            $this->assertEquals($value, $scalar['value']);
            $this->assertEquals($expectedEnd, $scalar['last_agent_change']);
            $this->assertEquals(1, $scalar['sample_count']);
            
            // Ensure sample sequence is increasing in scalars and samples table.
            $sampleCreated = $this->_cron->createSample($expectedScalarId, $value, 
                                                        $expectedStart, $expectedEnd);
            $this->assertTrue($sampleCreated);
            $nextSample = $this->_cron->getLatestSample($expectedScalarId);
            $this->assertEquals(2, $nextSample['id']);
        }
    }
    
    /**
     * @test
     * @group Cron
     * @group setsScalarAgentStatus
     * @group setScalarAgentStatus
     * @group createAgent
     * @group createScalar
     * @group getScalarAgentById
     */
    public function setsScalarAgentStatus()
    {
        $agentId = $this->_core->createAgent(self::randomString());

        $scalar = array();
        $scalar['name'] = self::randomString();
        $scalar['type'] = 'decimal';;
        $scalarId = $this->_core->createScalar($scalar);
            
        $scalarAgentId = $this->_core->createScalarAgent($scalarId, $agentId, 0);
            
        // Only change status and error, leaving default lastrun. 
        $status = 'Scheduled';
        $error = self::randomString();
        $this->_cron->setScalarAgentStatus($scalarAgentId, $status, $error);
        $scalarAgent = $this->_core->getScalarAgentById($scalarAgentId);
        $this->assertEquals($status, $scalarAgent['status']);
        $this->assertEquals($error, $scalarAgent['error']);
        $this->assertEquals(HASHMARK_DATETIME_EMPTY, $scalarAgent['lastrun']);
        
        // Change all three.
        $status = 'Scheduled';
        $error = self::randomString();
        $lastrun = time();
        $this->_cron->setScalarAgentStatus($scalarAgentId, $status, $error, $lastrun);
        $scalarAgent = $this->_core->getScalarAgentById($scalarAgentId);
        $this->assertEquals($status, $scalarAgent['status']);
        $this->assertEquals($error, $scalarAgent['error']);
        $this->assertEquals($lastrun, strtotime($scalarAgent['lastrun'] . ' UTC'));
    }
    
    /**
     * @test
     * @group Cron
     * @group getsScheduledAgents
     * @group getScheduledAgents
     * @group createScalar
     */
    public function getsScheduledAgents()
    {
        $scalar = array();
        $scalar['name'] = self::randomString();
        $scalar['type'] = 'decimal';
        $scalarId = $this->_core->createScalar($scalar);

        $agentId = $this->_core->createAgent(self::randomString());

        $time = time();
        $expectedIds = array();

        $expectedIds[] = $this->_core->createScalarAgent($scalarId, $agentId,
                                                           0, 'Scheduled', '',
                                                           $time);
        $expectedIds[] = $this->_core->createScalarAgent($scalarId, $agentId,
                                                           1440, 'Scheduled');

        $scheduledAgents = $this->_cron->getScheduledAgents();

        foreach ($scheduledAgents as $scalarAgent) {
            // Ignore records created outside this test.
            if ($scalarId == $scalarAgent['scalar_id']) {
                $actualIds[] = $scalarAgent['id'];
            }
        }
                
        $this->assertEquals($expectedIds, $actualIds);
    }        
   
    /**
     * @test
     * @group Cron
     * @group avoidsUnscheduledAgents
     * @group getScheduledAgents
     * @group createScalar
     */
    public function avoidsUnscheduledAgents()
    {
        $scalar = array();
        $scalar['name'] = self::randomString();
        $scalar['type'] = 'decimal';
        $scalarId = $this->_core->createScalar($scalar);

        $agentId = $this->_core->createAgent(self::randomString());

        $time = time();
        $unexpectedIds = array();

        $unexpectedIds[] = $this->_core->createScalarAgent($scalarId, $agentId,
                                                           0, 'Unscheduled', '',
                                                           $time);
        // Won't start until tomorrow.
        $unexpectedIds[] = $this->_core->createScalarAgent($scalarId, $agentId,
                                                           0, 'Scheduled', '',
                                                           $time + 1400);

        $scheduledAgents = $this->_cron->getScheduledAgents();

        $actualIds = array();
        foreach ($scheduledAgents as $scalarAgent) {
            // Ignore records created outside this test.
            if ($scalarId == $scalarAgent['scalar_id']) {
                $msg = sprintf('scalar: %d, agent: %d, join id: %d',
                               $scalarId, $agentId, $scalarAgent['id']);
                $this->assertTrue(false, $msg);
                return;
            }
        }
                
        $this->assertTrue(true);
    }        
    
    /**
     * @test
     * @group Cron
     * @group runsAgents
     * @group runAgents
     */
    public function runsAgents()
    {
        $scheduledScalarIds = array();

        $scalar = array();
        $scalar['name'] = self::randomString();
        $scalar['type'] = 'decimal';
        $scalar['value'] = '1234';
        $scalarId = $this->_core->createScalar($scalar);

        $agent = $this->_core->getAgentByName('ScalarValue');
        if ($agent) {
            $agentId = $agent['id'];
        } else {
            $agentId = $this->_core->createAgent('ScalarValue');
        }

        // Agent is scheduled to run immediately (0 frequency).
        $scalarAgentId = $this->_core->createScalarAgent($scalarId, $agentId,
                                                         0, 'Scheduled');

        require HASHMARK_ROOT_DIR . '/Cron/Tool/runAgents.php';
        
        // Assert scalar value changed in last 60 seconds.
        $scalar = $this->_core->getScalarById($scalarId);
        $this->assertEquals($scalar['value'], $scalar['value']);
        
        // Assert scalar agent is ready for future run.
        $scalarAgent = $this->_core->getScalarAgentById($scalarAgentId);
        $this->assertEquals('Scheduled', $scalarAgent['status']);
        $this->assertEquals('', $scalarAgent['error']);
        $this->assertLessThan(5, abs(strtotime($scalarAgent['lastrun'] . ' UTC') - time()));
    }
    
    /**
     * @test
     * @group Cron
     * @group collectsGarbageMergeTables
     * @group getAllMergeTables
     */
    public function collectsGarbageMergeTables()
    {
        $partition = Hashmark::getModule('Partition', '', $this->_db);
        $mergeTablePrefix = Hashmark::getConfig('Partition', '', 'mergetable_prefix');

        // Drop all merge tables to clear the slate.
        $priorMergeTables = $partition->getTablesLike($mergeTablePrefix . '%');
        if ($priorMergeTables) {
            $partition->dropTable($priorMergeTables);
        }

        $scalar = array();
        $scalar['name'] = self::randomString();
        $scalar['type'] = 'decimal';
        $scalarId = $this->_core->createScalar($scalar);

        $regTableName = 'test_samples_' . self::randomString();
        $partition->createTable($scalarId, $regTableName, $scalar['type']);

        $now = time();
        $start = '2008-04-01 01:45:59';
        $mergeTableNames = array();

        // Create several merge tables from the same single normal table.
        // Space them 1 day apart.
        // Vary $end to make the merge table names unique.
        for ($t = 0; $t < 5; $t++) {
            $end = "2009-06-1{$t} 01:45:59";
            $comment = gmdate(HASHMARK_DATETIME_FORMAT, $now - ($t * 86400));
            $mergeTableNames[$t] = $mergeTablePrefix . "{$scalarId}_20080401_2009061{$t}";
            $actualTable = $partition->createMergeTable($scalarId, $start, $end, array($regTableName), $comment);
            $this->assertEquals($mergeTableNames[$t], $actualTable);
        }

        $maxDays = 3;
        $maxCount = 2;
        require HASHMARK_ROOT_DIR . '/Cron/Tool/gcMergeTables.php';

        for ($t = 0; $t < 5; $t++) {
            if ($t < 2) {
                $this->assertTrue($partition->tableExists($mergeTableNames[$t]), "Expected {$mergeTableNames[$t]} to exist");
            } else {
                $this->assertFalse($partition->tableExists($mergeTableNames[$t]), "Expected {$mergeTableNames[$t]} to be missing");
            }
        }
    }
}
