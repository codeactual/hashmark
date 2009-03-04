<?php
// vim: fenc=utf-8:ft=php:ai:si:ts=4:sw=4:et:

/**
 * Hashmark_TestCase_Partition
 *
 * @filesource
 * @link        http://code.google.com/p/hashmark/
 * @link        http://framework.zend.com/manual/en/coding-standard.html
 * @link        http://phpdoc.org/tutorial.php
 * @copyright   Copyright (c) 2008-2009, Code Actual LLC
 * @license     http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package     Hashmark-Test
 * @subpackage  Base
 * @version     $Id: Partition.php 294 2009-02-13 03:48:59Z david $
*/

/**
 *      -   All test dates are in UTC.
 *      -   Only one type of scalar is used when others will not add coverage.
 *
 * @package     Hashmark-Test
 * @subpackage  Base
 */
class Hashmark_TestCase_Partition extends Hashmark_TestCase
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
        
        $this->_partition = Hashmark::getModule('Partition', '', $this->_db);
        $this->_core = Hashmark::getModule('Core', '', $this->_db);
    }

    /**
     * Provide sets of date ranges for testing (partition/merge) methods
     * which check range coverage.
     *
     *      -   Uses these range boundaries:
     *          2008-04-01 01:45:59
     *          2009-06-11 01:45:59
     *
     * @static
     * @access public 
     * @return Array    Test method argument sets.
     */
    public static function provideDateRangeCoverages()
    {
        static $data;

        if (!$data) {
            $date = array();

            // Range partially covered.
            $data[] = array(array('start' => '20080801',
                                  'startFull' => '2008-08-01 11:10:13',
                                  'end' => '20090215',
                                  'endFull' => '2009-02-15 11:10:13',
                                  'expectedMatch' => false));
            // Range covered.
            $data[] = array(array('start' => '20080322',
                                  'startFull' => '2008-03-22 11:10:13',
                                  'end' => '20090715',
                                  'endFull' => '2009-07-15 11:10:13',
                                  'expectedMatch' => true));
            // Range covered (exactly).
            $data[] = array(array('start' => '20080401',
                                  'startFull' => '2008-04-01 01:45:59',
                                  'end' => '20090611',
                                  'endFull' => '2009-06-11 01:45:59',
                                  'expectedMatch' => true));
            // Range uncovered.
            $data[] = array(array('start' => '20070522',
                                  'startFull' => '2007-05-22 11:10:13',
                                  'end' => '20080522',
                                  'endFull' => '2008-05-22 11:10:13',
                                  'expectedMatch' => false));
        }

        return $data;
    }
    
    /**
     * YYYYMM00 interval set with no elements that encompass either boundary.
     *
     *      -   Uses these range boundaries:
     *          2008-04-01 01:45:59
     *          2009-06-11 01:45:59
     *
     * @static
     * @access public 
     * @return Array    Test method argument sets.
     */
    public static function provideMonthIntervalsWithNoBoundMatches()
    {
        static $data;

        if (!$data) {
            // Set boolean element to true if we expect interval is in range.
            $date = array();
            $data[] = array('20080800', true);
            $data[] = array('20020700', false);
            $data[] = array('20090500', true);
            $data[] = array('20070400', false);
            $data[] = array('20081200', true);
            $data[] = array('20070400', false);
            $data[] = array('20080500', true);
            $data[] = array('20061100', false);
            $data[] = array('20081000', true);
            $data[] = array('20051200', false);
            $data[] = array('20030500', false);
            $data[] = array('20090400', true);
            $data[] = array('20090200', true);
        }

        return $data;
    }
    
    /**
     * YYYYMMDD interval set with no elements that match either boundary.
     *
     *      -   Uses these range boundaries:
     *          2008-04-01 01:45:59
     *          2009-06-11 01:45:59
     *
     * @static
     * @access public 
     * @return Array    Test method argument sets.
     */
    public static function provideDayIntervalsWithNoBoundMatches()
    {
        static $data;

        if (!$data) {
            // Set boolean element to true if we expect interval is in range.
            $date = array();
            $data[] = array('20080824', true);
            $data[] = array('20020707', false);
            $data[] = array('20090609', true);
            $data[] = array('20070409', false);
            $data[] = array('20081202', true);
            $data[] = array('20070423', false);
            $data[] = array('20080404', true);
            $data[] = array('20061103', false);
            $data[] = array('20081003', true);
            $data[] = array('20051211', false);
            $data[] = array('20030520', false);
            $data[] = array('20090415', true);
            $data[] = array('20090222', true);
        }

        return $data;
    }
    
    /**
     * YYYYMM(00|DD) interval set with no elements that match/encompass either boundary.
     *
     *      -   Uses these range boundaries:
     *          2008-04-01 01:45:59
     *          2009-06-11 01:45:59
     *
     * @static
     * @access public 
     * @return Array    Test method argument sets.
     */
    public static function provideMixedIntervalsWithNoBoundMatches()
    {
        static $data;

        if (!$data) {
            // Set boolean element to true if we expect interval is in range.
            $date = array();
            $data[] = array('20080800', true);
            $data[] = array('20020707', false);
            $data[] = array('20090610', true);
            $data[] = array('20070409', false);
            $data[] = array('20081200', true);
            $data[] = array('20070423', false);
            $data[] = array('20080500', true);
            $data[] = array('20061103', false);
            $data[] = array('20081000', true);
            $data[] = array('20051211', false);
            $data[] = array('20030520', false);
            $data[] = array('20090415', true);
            $data[] = array('20090222', true);
        }

        return $data;
    }
    
    /**
     * YYYYMM00 interval set which includes ones that encompass both boundaries.
     *
     *      -   Uses these range boundaries:
     *          2008-04-01 01:45:59
     *          2009-06-11 01:45:59
     *
     * @static
     * @access public 
     * @return Array    Test method argument sets.
     */
    public static function provideMonthIntervalsWithBoundMatches()
    {
        static $data;

        if (!$data) {
            // Set boolean element to true if we expect interval is in range.
            $date = array();
            $data[] = array('20080800', true);
            $data[] = array('20020700', false);
            $data[] = array('20090600', true);  // End match.
            $data[] = array('20070400', false);
            $data[] = array('20081200', true);
            $data[] = array('20070400', false);
            $data[] = array('20080400', true);  // Start match.
            $data[] = array('20061100', false);
            $data[] = array('20081000', true);
            $data[] = array('20051200', false);
            $data[] = array('20030500', false);
            $data[] = array('20090400', true);
            $data[] = array('20090200', true);
        }

        return $data;
    }
    
    /**
     * YYYYMMDD interval set which includes ones that match both boundaries.
     *
     *      -   Uses these range boundaries:
     *          2008-04-01 01:45:59
     *          2009-06-11 01:45:59
     *
     * @static
     * @access public 
     * @return Array    Test method argument sets.
     */
    public static function provideDayIntervalsWithBoundMatches()
    {
        static $data;

        if (!$data) {
            // Set boolean element to true if we expect interval is in range.
            $date = array();
            $data[] = array('20080824', true);
            $data[] = array('20020707', false);
            $data[] = array('20090610', true);  // End match.
            $data[] = array('20070409', false);
            $data[] = array('20081202', true);
            $data[] = array('20070423', false);
            $data[] = array('20080401', true);  // Start match.
            $data[] = array('20061103', false);
            $data[] = array('20081003', true);
            $data[] = array('20051211', false);
            $data[] = array('20030520', false);
            $data[] = array('20090415', true);
            $data[] = array('20090222', true);
        }

        return $data;
    }
    
    /**
     * YYYYMM(00|DD) interval set which includes ones that match/encompass both boundaries.
     *
     *      -   Uses these range boundaries:
     *          2008-04-01 01:45:59
     *          2009-06-11 01:45:59
     *
     * @static
     * @access public 
     * @return Array    Test method argument sets.
     */
    public static function provideMixedIntervalsWithBoundMatches()
    {
        static $data;

        if (!$data) {
            // Set boolean element to true if we expect interval is in range.
            $date = array();
            $data[] = array('20080800', true);
            $data[] = array('20020707', false);
            $data[] = array('20090611', true);  // End match.
            $data[] = array('20070409', false);
            $data[] = array('20081200', true);
            $data[] = array('20070423', false);
            $data[] = array('20080400', true);  // Start match.
            $data[] = array('20061103', false);
            $data[] = array('20081000', true);
            $data[] = array('20051211', false);
            $data[] = array('20030520', false);
            $data[] = array('20090415', true);
            $data[] = array('20090222', true);
        }

        return $data;
    }

    /**
     * @test
     * @group Partition
     * @group getsTableDefinition
     * @group getTableDefinition
     * @group getValidScalarTypes
     */
    public function getsDefinitionsOfPartitionTypeModels()
    {
        foreach ($this->_core->getValidScalarTypes() as $type) {
            $tableName = "samples_{$type}";
            $description = $this->_partition->getTableDefinition($tableName);
            $expectedSubstr = "CREATE TABLE `{$tableName}`";
            $this->assertTrue(false !== strpos($description, $expectedSubstr));
        }
    }

    /**
     * @test
     * @group Partition
     * @group getsListOfTablesLikeAnExpression
     * @group getTablesLike
     * @group getValidScalarTypes
     */
    public function getsListOfTablesLikeAnExpression()
    {
        $expectedTables = array('scalar%' => 'scalars',
                                'catego%' => 'categories',
                                '%estones' => 'milestones',
                                'jobs' => 'jobs');

        foreach ($this->_core->getValidScalarTypes() as $type) {
            $expectedTables["%_{$type}"] = "samples_{$type}";
        }
        
        foreach ($expectedTables as $likeExpr => $expectedTable) {
            $this->assertContains($expectedTable, $this->_partition->getTablesLike($likeExpr));
        }
    }
    
    /**
     * @test
     * @group Partition
     * @group createsAndDropsRegularTables
     * @group tableExists
     * @group createTable
     * @group getValidScalarTypes
     */
    public function createsAndDropsRegularTables()
    {
        $scalarId = self::randomString();
        // False positive check.
        $tableName = 'test_samples_' . $scalarId;
        $this->assertFalse($this->_partition->tableExists($tableName));

        $scalarFields = array();

        // Drop one at a time.
        foreach ($this->_core->getValidScalarTypes() as $type) {
            $scalarFields['name'] = self::randomString();
            $scalarFields['type'] = $type;
            $scalarId = $this->_core->createScalar($scalarFields);
            $tableName = 'test_samples_' . $scalarId;

            $this->assertFalse($this->_partition->tableExists($tableName));
            $this->_partition->createTable($scalarId, $tableName, $type);
            $this->assertTrue($this->_partition->tableExists($tableName));
            $this->_partition->dropTable($tableName);
            $this->assertFalse($this->_partition->tableExists($tableName));
        }
        
        // Drop all at once.
        $created = array();
        foreach ($this->_core->getValidScalarTypes() as $type) {
            $scalarFields['name'] = self::randomString();
            $scalarFields['type'] = $type;
            $scalarId = $this->_core->createScalar($scalarFields);
            $tableName = 'test_samples_' . $scalarId;

            $this->assertFalse($this->_partition->tableExists($tableName));
            $this->_partition->createTable($scalarId, $tableName, $type);
            $this->assertTrue($this->_partition->tableExists($tableName));

            $created[] = $tableName;
        }
        $this->_partition->dropTable($created);
        foreach ($created as $tableName) {
            $this->assertFalse($this->_partition->tableExists($tableName));
        }
    }
    
    /**
     * @test
     * @group Partition
     * @group getsMergeTableName
     * @group getMergeTableName
     */
    public function getsMergeTableName()
    {
        $scalarId = self::randomString();
        $start = "2008-04-01 01:45:59";
        $end = "2009-06-11 01:45:59";

        $expectedName = HASHMARK_PARTITION_MERGETABLE_PREFIX . "{$scalarId}_20080401_20090611";
        $actualName = $this->_partition->getMergeTableName($scalarId, $start, $end, '', $this->_db);

        $this->assertEquals($expectedName, $actualName);
    }
    
    /**
     * @test
     * @group Partition
     * @group checksMergeTableStatus
     * @group checkMergeTableStatus
     * @group createTable
     * @group createMergeTable
     * @group createScalar
     * @group getValidScalarTypes
     * @group getTableInfo
     */
    public function checksMergeTableStatus()
    {
        $scalarFields = array();

        foreach ($this->_core->getValidScalarTypes() as $type) {
            $createdTables = array();

            $scalarFields['name'] = self::randomString();
            $scalarFields['type'] = $type;
            $scalarId = $this->_core->createScalar($scalarFields);

            for ($regTable = 1; $regTable < 5; $regTable++) {
                $tableName = 'test_samples_' . self::randomString();
                $this->_partition->createTable($scalarId, $tableName, $type);
                $createdTables[] = $tableName;
            }

            $start = '2008-04-01 01:45:59';
            $end = '2009-06-11 01:45:59';
            
            $mergeTableName = $this->_partition->createMergeTable($scalarId, $start, $end, $createdTables);

            $this->assertTrue($this->_partition->checkMergeTableStatus($mergeTableName));
        }
    }
    
    /**
     * @test
     * @group Partition
     * @group checkMergeTableStatusRecognizesIncompatibleUnion
     * @group checkMergeTableStatus
     * @group createMergeTable
     * @group createTable
     * @group createScalar
     * @group getValidScalarTypes
     * @group getTableInfo
     */
    public function checkMergeTableStatusRecognizesIncompatibleUnion()
    {
        $types = $this->_core->getValidScalarTypes();
        $this->assertGreaterThan(1, count($types));

        $scalarFields = array();

        for ($t = 0; $t < 2; $t++) {
            $createdTables = array();

            $scalarFields['name'] = self::randomString();
            $scalarFields['type'] = $types[$t];
            $scalarId = $this->_core->createScalar($scalarFields);
                    
            $wrongType = ($types[$t] == $types[0] ? $types[1] : $types[0]);

            for ($regTable = 0; $regTable < 5; $regTable++) {
                $tableName = 'test_samples_' . self::randomString();

                if (0 == $regTable) {
                    $this->_partition->createTable($scalarId, $tableName, $wrongType);
                } else {
                    $this->_partition->createTable($scalarId, $tableName, $types[$t]);
                }

                $createdTables[] = $tableName;
            }

            $start = '2008-04-01 01:45:59';
            $end = '2009-06-11 01:45:59';

            $mergeTableName = $this->_partition->createMergeTable($scalarId, $start, $end, $createdTables);

            $this->assertFalse($this->_partition->checkMergeTableStatus($mergeTableName));
        }
    }
    
    /**
     * @test
     * @group Partition
     * @group getsMergeTableCreatedDate
     * @group getMergeTableCreatedDate
     * @group createTable
     * @group createMergeTable
     * @group createScalar
     * @group getValidScalarTypes
     * @group getTableInfo
     */
    public function getsMergeTableCreatedDate()
    {
        $scalarFields = array();

        foreach ($this->_core->getValidScalarTypes() as $type) {
            $createdTables = array();

            $scalarFields['name'] = self::randomString();
            $scalarFields['type'] = $type;
            $scalarId = $this->_core->createScalar($scalarFields);

            for ($regTable = 1; $regTable < 5; $regTable++) {
                $tableName = 'test_samples_' . self::randomString();
                $this->_partition->createTable($scalarId, $tableName, $type);
                $createdTables[] = $tableName;
            }

            $start = '2008-04-01 01:45:59';
            $end = '2009-06-11 01:45:59';
            
            $mergeTableName = $this->_partition->createMergeTable($scalarId, $start, $end, $createdTables);
            
            $modifiedDate = $this->_partition->getMergeTableCreatedDate($mergeTableName);

            $this->assertRegexp('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $modifiedDate);
        }
    }
    
    /**
     * @test
     * @group Partition
     * @group createsMergeTable
     * @group createMergeTable
     * @group checkMergeTableStatus
     * @group createTable
     * @group createScalar
     * @group getValidScalarTypes
     * @group getTableInfo
     */
    public function createsMergeTable()
    {
        $scalarFields = array();

        foreach ($this->_core->getValidScalarTypes() as $type) {
            $createdTables = array();

            $scalarFields['name'] = self::randomString();
            $scalarFields['type'] = $type;
            $scalarId = $this->_core->createScalar($scalarFields);

            for ($regTable = 0; $regTable < 5; $regTable++) {
                $tableName = 'test_samples_' . self::randomString();
                $this->_partition->createTable($scalarId, $tableName, $type);
                $createdTables[] = $tableName;
            }

            $start = '2008-04-01 01:45:59';
            $end = '2009-06-11 01:45:59';

            $expectedMergeTableName = HASHMARK_PARTITION_MERGETABLE_PREFIX . $scalarId . '_20080401_20090611';
            $actualMergeTableName = $this->_partition->createMergeTable($scalarId, $start, $end, $createdTables);

            $this->assertEquals($expectedMergeTableName, $actualMergeTableName);
            $status = $this->_partition->getTableInfo($actualMergeTableName);
            $this->assertEquals('MRG_MyISAM', $status['ENGINE']);
            $this->assertEquals(1, preg_match(HASHMARK_DATETIME_PREG_PATTERN, $status['TABLE_COMMENT']));
        }
    }
    
    /**
     * @test
     * @group Cron
     * @group getsAllMergeTables
     * @group getAllMergeTables
     */
    public function getsAllMergeTables()
    {
        $scalarId = self::randomString();
        $expectedTableName = HASHMARK_PARTITION_MERGETABLE_PREFIX . $scalarId;
        $this->_partition->createTable($scalarId, $expectedTableName, 'decimal');
        
        $unexpectedTableName = 'test_samples_' . $scalarId;
        $this->_partition->createTable($scalarId, $unexpectedTableName, 'decimal');

        $all = $this->_partition->getAllMergeTables();

        $allTableNames = array();
        foreach ($all as $fields) {
            $this->assertTrue(array_key_exists('TABLE_COMMENT', $fields));
            $allTableNames[] = $fields['TABLE_NAME'];
        }

        $this->assertFalse(in_array($unexpectedTableName, $allTableNames));
        $this->assertTrue(in_array($expectedTableName, $allTableNames));
    }
    
    /**
     * @test
     * @group Partition
     * @group getsTablesInRange
     * @group getTablesInRange
     * @group createTable
     * @group dropTable
     */
    public function getsTablesInRange()
    {
        $type = 'decimal';
    
        $scalarFields = array();
        $scalarFields['name'] = self::randomString();
        $scalarFields['type'] = $type;
        $scalarId = $this->_core->createScalar($scalarFields);

        $rangeStart = '2008-04-01 01:45:59';
        $rangeEnd = '2009-06-11 01:45:59';

        $tableSets = array();
        $tableSets[] = self::provideDayIntervalsWithNoBoundMatches();
        $tableSets[] = self::provideMonthIntervalsWithNoBoundMatches();
        $tableSets[] = self::provideMixedIntervalsWithNoBoundMatches();
        $tableSets[] = self::provideDayIntervalsWithBoundMatches();
        $tableSets[] = self::provideMonthIntervalsWithBoundMatches();
        $tableSets[] = self::provideMixedIntervalsWithBoundMatches();

        foreach ($tableSets as $set) {
            foreach ($set as $candidate) {
                list($date, $expectedMatch) = $candidate;

                // False positive check.
                $actualMatches = $this->_partition->getTablesInRange($scalarId, $rangeStart, $rangeEnd);
                $this->assertFalse($actualMatches);

                $tableName = "samples_{$scalarId}_{$date}";
                $this->_partition->createTable($scalarId, $tableName, $type);

                $actualMatches = $this->_partition->getTablesInRange($scalarId, $rangeStart, $rangeEnd);

                if ($expectedMatch) {
                    $this->assertArrayContainsOnly($expectedMatch, $actualMatches);
                } else {
                    $this->assertTrue(empty($actualMatches));
                }

                $this->_partition->dropTable($tableName);
            }
        }
    }

    /**
     * @test
     * @group Partition
     * @group getsMergeTableWithRange
     * @group getMergeTableWithRange
     * @group checkMergeTableStatus
     * @group createMergeTable
     * @group createTable
     * @group dropTable
     */
    public function getsMergeTableWithRange()
    {
        $scalarFields = array();

        $rangeStart = '2008-04-01 01:45:59';
        $rangeEnd = '2009-06-11 01:45:59';
        $type = 'decimal';

        $rangeCoverages = self::unwrapProviderData(self::provideDateRangeCoverages());

        foreach ($rangeCoverages as $coverage) {
            $scalarFields['name'] = self::randomString();
            $scalarFields['type'] = $type;
            $scalarId = $this->_core->createScalar($scalarFields);

            // False positive check.
            $actualMatch = $this->_partition->getMergeTableWithRange($scalarId, $rangeStart, $rangeEnd);
            $this->assertFalse($actualMatch);

            $createdTables = array();

            for ($regTable = 0; $regTable < 2; $regTable++) {
                $tableName = 'test_samples_' . self::randomString();
                $this->_partition->createTable($scalarId, $tableName, $type);
                $createdTables[] = $tableName;
            }

            $mergeTableName = HASHMARK_PARTITION_MERGETABLE_PREFIX . "{$scalarId}_{$coverage['start']}_{$coverage['end']}";
            $this->_partition->createMergeTable($scalarId, $coverage['startFull'], $coverage['endFull'], $createdTables);

            $actualMatch = $this->_partition->getMergeTableWithRange($scalarId, $rangeStart, $rangeEnd);

            if ($coverage['expectedMatch']) {
                $this->assertEquals($mergeTableName, $actualMatch);
            } else {
                $this->assertFalse($actualMatch);
            }

            $this->_partition->dropTable($mergeTableName);
        }
    }
    
    /**
     * @test
     * @group Partition
     * @group getsAnyTableWithRange
     * @group getAnyTableWithRange
     * @group createTable
     * @group dropTable
     */
    public function getsAnyTableWithRange()
    {
        $scalarFields = array();

        $rangeStart = '2008-04-01 01:45:59';
        $rangeEnd = '2009-06-11 01:45:59';
        $type = 'decimal';
            
        $scalarFields['name'] = self::randomString();
        $scalarFields['type'] = $type;
        $scalarId = $this->_core->createScalar($scalarFields);

        // False positive check.
        $actualMatches = $this->_partition->getAnyTableWithRange($scalarId, $rangeStart, $rangeEnd);
        $this->assertFalse($actualMatches);

        $firstSingleTable = "samples_{$scalarId}_20090302";
        $this->_partition->createTable($scalarId, $firstSingleTable, $type);

        // Found single table.
        $actualMatch = $this->_partition->getAnyTableWithRange($scalarId, $rangeStart, $rangeEnd);
        $this->assertEquals($firstSingleTable, $actualMatch);

        // Add another single table.
        $secondSingleTable = "samples_{$scalarId}_20090402";
        $this->_partition->createTable($scalarId, $secondSingleTable, $type);

        // Mock merge table.
        $oldMergeTableName = HASHMARK_PARTITION_MERGETABLE_PREFIX . "{$scalarId}_20080401_20090611";
        $this->_partition->createTable($scalarId, $oldMergeTableName, $type);

        // Found two single tables and found preexisting merge table.
        $actualMatch = $this->_partition->getAnyTableWithRange($scalarId, $rangeStart, $rangeEnd);
        $this->assertEquals($oldMergeTableName, $actualMatch);

        $this->_partition->dropTable($oldMergeTableName);

        $newMergeTableName = HASHMARK_PARTITION_MERGETABLE_PREFIX . "{$scalarId}_20080401_20090611";

        // Found two single tables and returned new merge table.
        $actualMatch = $this->_partition->getAnyTableWithRange($scalarId, $rangeStart, $rangeEnd);
        $this->assertEquals($newMergeTableName, $actualMatch);

        $this->_partition->dropTable($firstSingleTable);
        $this->_partition->dropTable($secondSingleTable);
        $this->_partition->dropTable($newMergeTableName);
    }

    /**
     * @test
     * @group Partition
     * @group getsIntervalTableName
     * @group getIntervalTableName
     */
    public function getsIntervalTableName()
    {
        $time = time();
        $data = array('d' => gmdate('Ymd', $time), 'm' => gmdate('Ym', $time) . '00');
        $scalarId = 101;

        foreach ($data as $interval => $timeId) {
            $intervalName = $this->_partition->getIntervalTableName($scalarId, $time, $interval);
            $this->assertEquals('samples_' . $scalarId . '_' . $timeId, $intervalName);
        }
    }
    
    /**
     * @test
     * @group Partition
     * @group queriesCurrentPartition
     * @group query
     * @group createScalar
     * @group getIntervalTableName
     * @group tableExists
     */
    public function queriesCurrentPartition()
    {
        $scalarFields = array();
        $scalarFields['name'] = self::randomString();
        $scalarFields['type'] = 'decimal';
        $scalarId = $this->_core->createScalar($scalarFields);

        $tableName = $this->_partition->getIntervalTableName($scalarId);

        $this->assertFalse($this->_partition->tableExists($tableName));
        $res = $this->_partition->query($scalarId, "INSERT INTO `{$tableName}` (`value`) VALUES (0.000)");
        $this->assertFalse(empty($res));
        $this->assertTrue($this->_partition->tableExists($tableName));
    }
    
    /**
     * @test
     * @group Partition
     * @group queriesSpecificPartitionAtDate
     * @group queryAtDate
     * @group createScalar
     * @group getIntervalTableName
     * @group tableExists
     */
    public function queriesSpecificPartitionAtDate()
    {
        $scalarFields = array();
        $scalarFields['name'] = self::randomString();
        $scalarFields['type'] = 'decimal';
        $scalarId = $this->_core->createScalar($scalarFields);

        $date = '2006-07-04 12:14:30';

        $tableName = $this->_partition->getIntervalTableName($scalarId, $date);

        $this->assertFalse($this->_partition->tableExists($tableName));
        $res = $this->_partition->queryAtDate($scalarId, "INSERT INTO `{$tableName}` (`value`) VALUES (0.000)", $date);
        $this->assertFalse(empty($res));
        $this->assertTrue($this->_partition->tableExists($tableName));
    }
    
    /**
     * @test
     * @group Partition
     * @group queriesRangeOfPartitions
     * @group queryInRange
     * @group createTable
     * @group createScalar
     */
    public function queriesRangeOfPartitions()
    {
        $rangeStart = '2008-04-01 01:45:59';
        $rangeEnd = '2009-06-11 01:45:59';
        $type = 'decimal';

        $sql = 'SELECT COUNT(*) FROM ~samples';
        
        $scalarFields = array();
            
        $scalarFields['name'] = self::randomString();
        $scalarFields['type'] = $type;
        $scalarId = $this->_core->createScalar($scalarFields);

        // False positive check.
        $actualResult = $this->_partition->queryInRange($scalarId, $rangeStart, $rangeEnd, $sql);
        $this->assertFalse($actualResult);

        $firstSingleTable = "samples_{$scalarId}_20090302";
        $this->_partition->createTable($scalarId, $firstSingleTable, $type);

        // Queried single table.
        $actualResult = $this->_partition->queryInRange($scalarId, $rangeStart, $rangeEnd, $sql);
        $this->assertFalse(empty($actualResult));

        $secondSingleTable = "samples_{$scalarId}_20090402";
        $this->_partition->createTable($scalarId, $secondSingleTable, $type);

        // Queried two merged tables.
        $actualResult = $this->_partition->queryInRange($scalarId, $rangeStart, $rangeEnd, $sql);
        $this->assertFalse(empty($actualResult));

        $expectedMergeTableName = HASHMARK_PARTITION_MERGETABLE_PREFIX . $scalarId . '_20080401_20090611';
        $this->assertTrue($this->_partition->tableExists($expectedMergeTableName));
    }
    
    /**
     * @test
     * @group Partition
     * @group copiesTableIntoTemp
     * @group copyToTemp
     * @group getTableInfo
     */
    public function copiesTableIntoTemp()
    {
        $scalarFields = array();
        $scalarFields['name'] = self::randomString();
        $scalarFields['type'] = 'decimal';
        $scalarId = $this->_core->createScalar($scalarFields);

        $srcName = 'test_samples_ ' . $scalarId;
        $this->_partition->createTable($scalarId, $srcName, 'decimal');
        $srcDef = str_replace('TABLE `' . $srcName,
                              '',
                              $this->_partition->getTableDefinition($srcName));
        
        $sql = "INSERT INTO `{$srcName}` (`value`) VALUES (1)";
        $this->_dbHelper->rawQuery($this->_db, $sql, '', $this->_db);

        $destName = $this->_partition->copyToTemp($srcName);
        $destDef = str_replace('TEMPORARY TABLE `' . $destName,
                               '',
                               $this->_partition->getTableDefinition($destName));
        $this->assertEquals($srcDef, $destDef);

        $this->_partition->dropTable(array($srcName, $destName));
    }
   
    /**
     * @test
     * @group Partition
     * @group createsTempTableFromQueryResults
     * @group createTempFromQuery
     */
    public function createsTempTableFromQueryResults()
    {
        $cron = Hashmark::getModule('Cron', '', $this->_db);
        
        $srcName = 'samples_analyst_temp';
        $srcDef = str_replace('TABLE `' . $srcName,
                              '',
                              $this->_partition->getTableDefinition($srcName));

        $scalarFields = array();
        $scalarFields['name'] = self::randomString();
        $scalarFields['type'] = 'decimal';
        $scalarId = $this->_core->createScalar($scalarFields);
        $jobId = $cron->startJob();
        $start = gmdate(HASHMARK_DATETIME_FORMAT);
        $end = $start;
        
        $cron->createSample($scalarId, $jobId, 1, $start, $end);

        // Trigger a DbHelper::query().
        $currentPartition = $this->_partition->getIntervalTableName($scalarId);
        $sql = "SELECT `end`, `value` FROM `{$currentPartition}`";
        $tempName = $this->_partition->createTempFromQuery($srcName,
                                                           '`x`, `y`',
                                                           $sql, array());
        $destDef = str_replace('TEMPORARY TABLE `' . $tempName,
                               '',
                               $this->_partition->getTableDefinition($tempName));
        $destDef = str_replace(' AUTO_INCREMENT=2', '', $destDef);
        $this->assertEquals($srcDef, $destDef);
        $exists = $this->_partition->tableExists(HASHMARK_PARTITION_MERGETABLE_PREFIX . "{$scalarId}_20080603_20081212");
        $this->assertTrue(empty($exists));
        
        // Trigger a DbHelper::queryInRange().
        $start = '2008-06-03 00:00:00';
        $end = '2008-12-12 00:00:00';
        $sql = 'SELECT `end`, `value` FROM ~samples';
        $values = array(':scalarId' => $scalarId, ':start' => $start, ':end' => $end);
        $cron->createSample($scalarId, $jobId, 1, $start, $start);
        $cron->createSample($scalarId, $jobId, 1, $end, $end);
        $tempName = $this->_partition->createTempFromQuery($srcName,
                                                           '`x`, `y`',
                                                           $sql, $values);
        $destDef = str_replace('TEMPORARY TABLE `' . $tempName,
                               '',
                               $this->_partition->getTableDefinition($tempName));
        $destDef = str_replace(' AUTO_INCREMENT=3', '', $destDef);
        $this->assertEquals($srcDef, $destDef);
        $expectedPartition = $this->_partition->getIntervalTableName($scalarId, $start);
        $exists = $this->_partition->tableExists(HASHMARK_PARTITION_MERGETABLE_PREFIX . "{$scalarId}_20080603_20081212");
        $this->assertFalse(empty($exists));
    }
}
