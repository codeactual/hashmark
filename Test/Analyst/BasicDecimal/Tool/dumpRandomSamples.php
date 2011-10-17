<?php
// vim: fenc=utf-8:ft=php:ai:si:ts=4:sw=4:et:

/**
 * Dump SQL for creating scalars/samples for BasicDecimal module testing.
 *
 *      -   Assumes no concurrent scalar/sample writes.
 *      -   Does not use Sql/Analyst/BasicDecimal.php statements or modules to perform queries.
 *          since they're much slower for bulk inserts.
 *      -   Only writes columns which affect BasicDecimal methods.
 *
 * @filesource
 * @link        http://code.google.com/p/hashmark/
 * @link        http://framework.zend.com/manual/en/coding-standard.html
 * @link        http://phpdoc.org/tutorial.php
 * @copyright   Copyright (c) 2008-2011 David Smith
 * @license     http://www.opensource.org/licenses/mit-license.php MIT License
 * @package     Hashmark-Test
 * @subpackage  Hashmark_Analyst_BasicDecimal
 * @version     $Id$
*/

/**
 * For Hashmark::getModule().
 */
require_once dirname(__FILE__) . '/../../../bootstrap.php';

/**
 * For hashmark_random_samples().
 */
require_once HASHMARK_ROOT_DIR. '/Test/Analyst/BasicDecimal/Tool/randomSamples.php';

define('HASHMARK_DUMP_RANDOMSAMPLES_FILE',
       HASHMARK_ROOT_DIR . '/Test/Analyst/BasicDecimal/Data/randomSamples.sql');
define('HASHMARK_DUMP_RANDOMSAMPLES_TYPE', 'decimal');
define('HASHMARK_DUMP_RANDOMSAMPLES_SCALARS', 1);
define('HASHMARK_DUMP_RANDOMSAMPLES_COUNT', 100000);
define('HASHMARK_DUMP_RANDOMSAMPLES_RANDOM_SET_MAX', 50000);
define('HASHMARK_DUMP_RANDOMSAMPLES_SQL_BUFFER_MAX', 10000);
define('HASHMARK_DUMP_RANDOMSAMPLES_STARTDATE', '2008-01-01 00:00:00 UTC');
define('HASHMARK_DUMP_RANDOMSAMPLES_ENDDATE', '2009-01-01 23:59:59 UTC');

$db = Hashmark::getModule('DbHelper')->openDb('unittest');
$partition = Hashmark::getModule('Partition', '', $db);

// $tableDef is used for all sample CREATE TABLE statements.
$baseTableDef = $partition->getPartitionDefinition(HASHMARK_DUMP_RANDOMSAMPLES_TYPE);
$tableDef = preg_replace('/\n|CREATE TABLE `samples_' . HASHMARK_DUMP_RANDOMSAMPLES_TYPE . '`/', '', $baseTableDef);

// We will be incrementing manually.
$info = $partition->getTableInfo('scalars');
$scalarId = $info['AUTO_INCREMENT'];

for ($scalars = 0; $scalars < HASHMARK_DUMP_RANDOMSAMPLES_SCALARS; $scalars++) {
    $scalarFields = array('type' => HASHMARK_DUMP_RANDOMSAMPLES_TYPE,
                          'name' => Hashmark_Util::randomSha1());

    $sql = 'INSERT IGNORE INTO `scalars` '
         . '(`id`, `type`) '
         . 'VALUES (' . $scalarId++ . ', \'' . HASHMARK_DUMP_RANDOMSAMPLES_TYPE . "');\n";
    file_put_contents(HASHMARK_DUMP_RANDOMSAMPLES_FILE, $sql);

    $scalarSampleCnt = array();

    $createdTables = array();

    // Chunk random samples into sets to avoid memory limit.
    $scalarSampleCnt = 0;
    while ($scalarSampleCnt < HASHMARK_DUMP_RANDOMSAMPLES_COUNT) {
        // Last parameter will sort $samples by date ascending.
        $samples = hashmark_random_samples(HASHMARK_DUMP_RANDOMSAMPLES_TYPE,
                                           HASHMARK_DUMP_RANDOMSAMPLES_STARTDATE,
                                           HASHMARK_DUMP_RANDOMSAMPLES_ENDDATE,
                                           min(HASHMARK_DUMP_RANDOMSAMPLES_RANDOM_SET_MAX,
                                               HASHMARK_DUMP_RANDOMSAMPLES_COUNT - $scalarSampleCnt),
                                           false, false, null, null, true);

        $scalarSampleCnt += count($samples);

        $buffer = '';
        $bufferSize = 0;

        foreach ($samples as $timeData => $value) {
            list($time) = explode('=', $timeData);
            $sampleDate = Hashmark_Util::toDatetime($time);
            if ('string' == HASHMARK_DUMP_RANDOMSAMPLES_TYPE) {
                $value = $partition->escape($value);
            }

            // Create partitions as needed based on sample date.
            $tableName = $partition->getIntervalTableName($scalarId, $sampleDate);
            if (!isset($createdTables[$tableName])) {
                $createdTables[$tableName] = 1;
                $buffer .= "CREATE TABLE IF NOT EXISTS `{$tableName}` {$tableDef} AUTO_INCREMENT=1;\n";
                $bufferSize++;
            }
            
            $buffer .= "INSERT INTO `{$tableName}` "
                    . '(`value`, `end`) '
                    . "VALUES ('{$value}', '{$sampleDate}');\n";
            $bufferSize++;

            if ($bufferSize > HASHMARK_DUMP_RANDOMSAMPLES_SQL_BUFFER_MAX) {
                file_put_contents(HASHMARK_DUMP_RANDOMSAMPLES_FILE, $buffer, FILE_APPEND);
                $buffer = '';
                $bufferSize = 0;
            }
        }
        
        if ($buffer) {
            file_put_contents(HASHMARK_DUMP_RANDOMSAMPLES_FILE, $buffer, FILE_APPEND);
        }

        unset($samples);
    }

    unset($createdTables);
}
