<?php
// vim: fenc=utf-8:ft=php:ai:si:ts=4:sw=4:et:

/**
 * Perform some surface tests to test installation.
 *
 *      -   Uses the 'cron' DB connection profile in Config/DbHelper.php.
 *
 * @filesource
 * @link        http://code.google.com/p/hashmark/
 * @link        http://framework.zend.com/manual/en/coding-standard.html
 * @link        http://phpdoc.org/tutorial.php
 * @copyright   Copyright (c) 2008-2011 David Smith
 * @license     http://www.opensource.org/licenses/mit-license.php MIT License
 * @package     Hashmark-Test
 * @version     $Id$
*/

ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);

/**
 * For Hashmark::getModule().
 */
require_once dirname(__FILE__) . '/../Hashmark.php';

$dbHelper = Hashmark::getModule('DbHelper');
if (!$dbHelper) {
    $msg = "\nCould not create a new DbHelper object. Does DbHelper/"
         . Hashmark::getConfig('DbHelper', '', 'default_type')
         . ".php exist?\n";
    exit($msg);
}

/**
 *
 * Database table/profile checks.
 * 
 */
$expectedTableList = array('categories', 'categories_milestones', 'categories_scalars',
                           'milestones', 'samples_string', 'samples_decimal',
                           'samples_analyst_temp', 'scalars');

$expectedTableSql = $expectedTableList;
foreach ($expectedTableSql as $t => $name) {
    $expectedTableSql[$t] = '"' . $name . '"';
}
$expectedTableSql = implode(',', $expectedTableSql);

$sql = 'SELECT `TABLE_NAME` FROM `INFORMATION_SCHEMA`.`TABLES` '
     . "WHERE `TABLE_NAME` IN ({$expectedTableSql}) "
     . 'AND `TABLE_SCHEMA` = DATABASE()';

$profileList = array('cron', 'unittest');

foreach ($profileList as $profile) {
    $testDetail = "with '{$profile}' profile in Config/DbHelper.php";
    
    $db = $dbHelper->openDb($profile);

    try {
        $db->getConnection();
    } catch (Exception $e) {
        $openError = $db->getConnection()->error;
        echo "====> WARN: DB connection failed {$testDetail}: {$openError}.\n";
        continue;
    }
    
    echo "pass: Connected to DB {$testDetail}\n";

    $rows = $db->fetchAll($sql);

    $expectedTableNum = count($expectedTableList);
    $actualTableNum = count($rows);

    if ($actualTableNum != $expectedTableNum) {
        $actualTableList = array();
        foreach ($rows as $row) {
            $actualTableList[] = $row[0];
        }

        echo "====> FAIL: Found {$actualTableNum}/{$expectedTableNum} Hashmark tables. ",
             'Missing: ' . implode(', ', array_diff($expectedTableList, $actualTableList)) . ".\n";
        
        continue;
    }

    echo "pass: Found all Hashmark tables {$testDetail}\n";
}

// Remaining tests won't touch query-reliant code.
$mockDb = 1;

/**
 *
 * Module loading checks.
 *
 */
$modList = array('BcMath' => '', 'Cache' => '', 'Client' => '', 'Core' => '', 'DbHelper' => '',
                 'Partition' => '', 'Agent' => 'YahooWeather', 'Test' => 'FakeModuleType');

foreach ($modList as $baseName => $typeName) {
    // Cache modules don't use the DB argument,
    // but it should have no effect.
    $inst = Hashmark::getModule($baseName, $typeName, $mockDb);

    if ($typeName) {
        $className = "Hashmark_{$baseName}_{$typeName}";
    } else {
        $className = "Hashmark_{$baseName}";
    }

    $testDetail = "{$className} module.\n";
    if ($inst instanceof $className) {
        echo "pass: Loaded {$testDetail}";
    } else {
        echo "====> FAIL: Could not load {$testDetail}";
    }
}

/**
 *
 * Misc. configuration value checks.
 *
 */
$mockScalarId = 1234;
$partitionTableName = Hashmark::getModule('Partition', '', $mockDb)->getIntervalTableName($mockScalarId);
$testDetail = "partition name with '"
            . Hashmark::getConfig('Partition', '', 'interval')
            . "' setting in Config/Partition.php.\n";

if ($partitionTableName) {
    echo "pass: Built {$partitionTableName} {$testDetail}";
} else {
    echo "====> FAIL: Could not build {$testDetail}";
}
