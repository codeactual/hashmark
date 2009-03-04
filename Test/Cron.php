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
 * @version     $Id: Cron.php 294 2009-02-13 03:48:59Z david $
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
     * @access protected
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
     * @group startsJob
     * @group startJob
     * @group getJobById
     */
    public function startsJob()
    {
        $expectedId = $this->_cron->startJob();
        
        $job = $this->_core->getJobById($expectedId);

        $this->assertEquals($expectedId, $job['id']);
        $this->assertNotEquals(HASHMARK_DATETIME_EMPTY, $job['start']);
        $this->assertEquals(HASHMARK_DATETIME_EMPTY, $job['end']);
    }
    
    /**
     * @test
     * @group Cron
     * @group endsJob
     * @group endJob
     * @group getJobById
     */
    public function endsJob()
    {
        $expectedId = $this->_cron->startJob();
        
        $this->assertTrue($this->_cron->endJob($expectedId));

        $job = $this->_core->getJobById($expectedId);

        $this->assertEquals($expectedId, $job['id']);
        $this->assertNotEquals(HASHMARK_DATETIME_EMPTY, $job['start']);
        $this->assertNotEquals(HASHMARK_DATETIME_EMPTY, $job['end']);
    }
    
    /**
     * @test
     * @group Cron
     * @group createsSampleAndUpdatesScalar
     * @group createSample
     * @group getSample
     * @group createScalar
     * @group getScalarById
     * @group startJob
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

            $expectedJobId = $this->_cron->startJob();

            $expectedStart = gmdate(HASHMARK_DATETIME_FORMAT, time() - 5);
            $expectedEnd = gmdate(HASHMARK_DATETIME_FORMAT);

            $sampleCreated = $this->_cron->createSample($expectedScalarId, $expectedJobId,
                                                 $value, $expectedStart, $expectedEnd);
            $this->assertTrue($sampleCreated);

            $sample = $this->_cron->getLatestSample($expectedScalarId);

            // Ensure samples table got updated.
            $this->assertEquals($expectedJobId, $sample['job_id']);
            $this->assertEquals($expectedStart, $sample['start']);
            $this->assertEquals($expectedEnd, $sample['end']);
            $this->assertEquals($value, $sample['value']);
            $this->assertEquals(1, $sample['id']);

            // Ensure associated scalar got updated.
            $scalar = $this->_core->getScalarById($expectedScalarId);
            $this->assertEquals($value, $scalar['value']);
            $this->assertEquals($expectedEnd, $scalar['last_sample_change']);
            $this->assertTrue(empty($scalar['sampler_error']));
            $this->assertEquals('Scheduled', $scalar['sampler_status']);
            $this->assertEquals(1, $scalar['sample_count']);
            
            // Ensure sample sequence is increasing in scalars and samples table.
            $sampleCreated = $this->_cron->createSample($expectedScalarId, $expectedJobId,
                                                        $value, $expectedStart, $expectedEnd);
            $this->assertTrue($sampleCreated);
            $nextSample = $this->_cron->getLatestSample($expectedScalarId);
            $this->assertEquals(2, $nextSample['id']);
        }
    }
    
    /**
     * @test
     * @group Cron
     * @group setsSamplerStatus
     * @group setSamplerStatus
     * @group createScalar
     * @group getValidScalarTypes
     * @group getScalarById
     */
    public function setsSamplerStatus()
    {
        $expectedScalar = array();

        foreach ($this->_core->getValidScalarTypes() as $type) {
            $expectedScalar['name'] = self::randomString();
            $expectedScalar['type'] = $type;

            $expectedScalarId = $this->_core->createScalar($expectedScalar);
            
            $this->_cron->setSamplerStatus($expectedScalarId, 'Running');
            $scalar = $this->_core->getScalarById($expectedScalarId);
            $this->assertEquals('Running', $scalar['sampler_status']);
            $this->assertEquals('', $scalar['sampler_error']);

            $this->_cron->setSamplerStatus($expectedScalarId, 'Unscheduled', '<error message>');
            $scalar = $this->_core->getScalarById($expectedScalarId);
            $this->assertEquals('Unscheduled', $scalar['sampler_status']);
            $this->assertEquals('<error message>', $scalar['sampler_error']);

            $this->_cron->setSamplerStatus($expectedScalarId, 'Scheduled');
            $scalar = $this->_core->getScalarById($expectedScalarId);
            $this->assertEquals('Scheduled', $scalar['sampler_status']);
            $this->assertEquals('', $scalar['sampler_error']);
        }
    }
    
    /**
     * @test
     * @group Cron
     * @group getsScheduledSamples
     * @group getScheduledSamplers
     * @group createScalar
     */
    public function getsScheduledSamples()
    {
        foreach (self::provideScalarsWithScheduledSamplers() as $scalarFields) {
            $expectedScalarId = $this->_core->createScalar($scalarFields);

            $scheduledScalars = $this->_cron->getScheduledSamplers();

            $scheduledScalarIds = array();
            foreach ($scheduledScalars as $scalar) {
                $scheduledScalarIds[] = $scalar['id'];
            }

            $this->assertTrue(in_array($expectedScalarId, $scheduledScalarIds));
        }
    }        
   
    /**
     * @test
     * @group Cron
     * @group avoidsUnscheduledSamples
     * @group getScheduledSamplers
     * @group createScalar
     */
    public function avoidsUnscheduledSamples()
    {
        $fieldsToTamper = array('sampler_handler', 'sampler_status', 'sampler_frequency', 'sampler_start');
        
        foreach (self::provideScalarsWithScheduledSamplers() as $scalarFields) {
            foreach ($fieldsToTamper as $tamperField) {
                switch ($tamperField) {
                    case 'sampler_handler':
                        $scalarFields[$tamperField] = '';
                        break;
                    case 'sampler_status':
                        $scalarFields[$tamperField] = 'Unscheduled';
                        break;
                    case 'sampler_frequency':
                        $scalarFields[$tamperField] = '1440';
                        break;
                    case 'sampler_start':
                        $scalarFields[$tamperField] = gmdate(HASHMARK_DATETIME_FORMAT, time() + 1400);
                        break;
                    default:
                        $this->assertTrue(false);
                        break;
                }

                $expectedScalarId = $this->_core->createScalar($scalarFields);

                $scheduledScalars = $this->_cron->getScheduledSamplers();

                $scheduledScalarIds = array();
                foreach ($scheduledScalars as $scalar) {
                    $scheduledScalarIds[] = $scalar['id'];
                }

                $this->assertFalse(in_array($expectedScalarId, $scheduledScalarIds));
            }
        }
    }        
    
    /**
     * @test
     * @group Cron
     * @group runsSamplers
     * @group runSamplers
     */
    public function runsSamplers()
    {
        $scheduledScalarIds = array();

        foreach (self::provideScalarsWithScheduledSamplers() as $scalarFields) {
            $scheduledScalarIds[] = $this->_core->createScalar($scalarFields);
        }
        
        $time = time();
        require dirname(__FILE__) . '/../Cron/Tool/runSamplers.php';
        
        foreach ($scheduledScalarIds as $scalarId) {
            $scalar = $this->_core->getScalarById($scalarId);
            $this->assertEquals('Scheduled', $scalar['sampler_status']);
            $this->assertLessThan(60, abs(strtotime($scalar['last_sample_change'] . ' UTC') - $time));
            $this->assertEquals('', $scalar['sampler_error']);
            $this->assertEquals('1234', $scalar['value']);
        }
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

        // Drop all merge tables to clear the slate.
        $priorMergeTables = $partition->getTablesLike(HASHMARK_PARTITION_MERGETABLE_PREFIX . '%');
        if ($priorMergeTables) {
            $partition->dropTable($priorMergeTables);
        }

        $scalarFields = array();
        $scalarFields['name'] = self::randomString();
        $scalarFields['type'] = 'decimal';
        $scalarId = $this->_core->createScalar($scalarFields);

        $regTableName = 'test_samples_' . self::randomString();
        $partition->createTable($scalarId, $regTableName, $scalarFields['type']);

        $now = time();
        $start = '2008-04-01 01:45:59';
        $mergeTableNames = array();

        // Create several merge tables from the same single normal table.
        // Space them 1 day apart.
        // Vary $end to make the merge table names unique.
        for ($t = 0; $t < 5; $t++) {
            $end = "2009-06-1{$t} 01:45:59";
            $comment = gmdate(HASHMARK_DATETIME_FORMAT, $now - ($t * 86400));
            $mergeTableNames[$t] = HASHMARK_PARTITION_MERGETABLE_PREFIX . "{$scalarId}_20080401_2009061{$t}";
            $actualTable = $partition->createMergeTable($scalarId, $start, $end, array($regTableName), $comment);
            $this->assertEquals($mergeTableNames[$t], $actualTable);
        }

        $maxDays = 3;
        $maxCount = 2;
        require dirname(__FILE__) . '/../Cron/Tool/gcMergeTables.php';

        for ($t = 0; $t < 5; $t++) {
            if ($t < 2) {
                $this->assertTrue($partition->tableExists($mergeTableNames[$t]));
            } else {
                $this->assertFalse($partition->tableExists($mergeTableNames[$t]));
            }
        }
    }
}
