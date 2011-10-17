#!/usr/bin/php
<?php
// vim: fenc=utf-8:ft=php:ai:si:ts=4:sw=4:et:

/**
 * Drop merge tables based on age and count hard limit limits.
 *
 *      -   Warning: FLUSH TABLES called at the end.
 *
 * @filesource
 * @link        http://code.google.com/p/hashmark/
 * @link        http://framework.zend.com/manual/en/coding-standard.html
 * @link        http://phpdoc.org/tutorial.php
 * @copyright   Copyright (c) 2008-2011 David Smith
 * @license     http://www.opensource.org/licenses/mit-license.php MIT License
 * @package     Hashmark
 * @subpackage  Cron
 * @version     $Id$
*/

/**
 * For getModule() and cron constants.
 */
require_once dirname(__FILE__) . '/../Hashmark.php';

// Max. table age in days.
if (!isset($maxDays)) {
    $maxDays = Hashmark::getConfig('Cron', '', 'merge_gc_max_days');
}
 
// Max. table count. Non-expired tables will be sorted
// by age descending and then pruned.
if (!isset($maxCount)) {
    $maxCount = Hashmark::getConfig('Cron', '', 'merge_gc_max_count');
}
   
$db = Hashmark::getModule('DbHelper')->openDb('cron');
$partition = Hashmark::getModule('Partition', '', $db);

$garbageTables = array();
$keepTables = array();
$now = time();
$all = $partition->getAllMergeTables();

foreach ($all as $table) {
    $unixTime = strtotime($table['TABLE_COMMENT'] . ' UTC');

    // Ex. merge tables who no longer match an updated partition schema.
    if (!$unixTime) {
        $garbageTables[] = $table['TABLE_NAME'];
        continue;
    }

    if ($unixTime + ($maxDays * 86400) < $now) {
        $garbageTables[] = $table['TABLE_NAME'];
        continue;
    }

    $keepTables[$unixTime] = $table['TABLE_NAME'];
}

// Enforce hard limit.
krsort($keepTables);
$excessTables = array_slice($keepTables, $maxCount);
$garbageTables = array_merge($garbageTables, $excessTables);

if ($garbageTables) {
    $partition->dropTable($garbageTables);
    $db->query('FLUSH TABLES');
}
