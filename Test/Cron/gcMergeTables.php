<?php
// vim: fenc=utf-8:ft=php:ai:si:ts=4:sw=4:et:

/**
 * Hashmark_TestCase_Cron_gcMergeTables
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
class Hashmark_TestCase_Cron_gcMergeTables extends Hashmark_TestCase_Cron
{
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
        $scalarId = Hashmark::getModule('Core', '', $this->_db)->createScalar($scalar);

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

        ob_start();
        require HASHMARK_ROOT_DIR . '/Cron/gcMergeTables.php';
        ob_end_clean();

        for ($t = 0; $t < 5; $t++) {
            if ($t < 2) {
                $this->assertTrue($partition->tableExists($mergeTableNames[$t]), "Expected {$mergeTableNames[$t]} to exist");
            } else {
                $this->assertFalse($partition->tableExists($mergeTableNames[$t]), "Expected {$mergeTableNames[$t]} to be missing");
            }
        }
    }
}
