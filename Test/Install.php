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
 * @copyright   Copyright (c) 2008-2009, Code Actual LLC
 * @license     http://www.opensource.org/licenses/bsd-license.php New BSD License
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
                           'jobs', 'milestones', 'samples_string', 'samples_decimal',
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
    $db = @$dbHelper->openDb($profile);
    $openError = $dbHelper->openError($db);

    $testDetail = "with '{$profile}' profile in Config/DbHelper.php";

    if ($openError) {
        echo "==> WARN: DB connection failed {$testDetail}: {$openError}.\n";
        continue;
    }

    echo "PASS: Connected to DB {$testDetail}\n";

    $res = $dbHelper->query($db, $sql);

    $expectedTableNum = count($expectedTableList);

    if ($res->num_rows != $expectedTableNum) {
        $actualTableList = array();
        while($row = $res->fetch_row()) {
            $actualTableList[] = $row[0];
        }

        echo "==> FAIL: Found {$res->num_rows}/{$expectedTableNum} Hashmark tables. ",
             'Missing: ' . implode(', ', array_diff($expectedTableList, $actualTableList)) . ".\n";
        
        continue;
    }

    echo "PASS: Found all Hashmark tables {$testDetail}\n";
}

// Remaining tests won't touch query-reliant code.
$mockDb = 1;

/**
 *
 * Module loading checks.
 *
 */
$modList = array('Analyst' => 'BasicDecimal', 'Client' => '', 'Core' => '',
                 'Cache' => 'Static', 'Cron' => '', 'Partition' => '',
                 'Sampler' => 'YahooWeather');

foreach ($modList as $baseName => $typeName) {
    // Cache/Sampler modules don't use the DB argument,
    // but it should have no effect.
    $inst = Hashmark::getModule($baseName, $typeName, $mockDb);

    if ($typeName) {
        $className = "Hashmark_{$baseName}_{$typeName}";
    } else {
        $className = "Hashmark_{$baseName}";
    }

    $testDetail = "{$className} module.\n";
    if ($inst instanceof $className) {
        echo "PASS: Loaded {$testDetail}";
    } else {
        echo "FAIL: Could not load {$testDetail}";
    }
}

/**
 *
 * Misc. configuration value checks.
 *
 */
$inst = Hashmark::getModule('Cache');
$className = 'Hashmark_Cache_' . Hashmark::getConfig('Cache', '', 'default_type');
$testDetail = "{$className} module chosen in Config/Cache.php.\n";
if ($inst instanceof $className) {
    echo "PASS: Loaded {$testDetail}";
} else {
    echo "FAIL: Could not load {$testDetail}";
}

$mockScalarId = 1234;
$partitionTableName = Hashmark::getModule('Partition', '', $mockDb)->getIntervalTableName($mockScalarId);
$testDetail = "partition name with '"
            . Hashmark::getConfig('Partition', '', 'interval')
            . "' setting in Config/Partition.php.\n";

if ($partitionTableName) {
    echo "PASS: Built {$partitionTableName} {$testDetail}";
} else {
    echo "FAIL: Could not build {$testDetail}";
}
