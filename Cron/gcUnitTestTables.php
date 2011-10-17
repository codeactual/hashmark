#!/usr/bin/php
<?php
// vim: fenc=utf-8:ft=php:ai:si:ts=4:sw=4:et:

/**
 * Drop partition tables created by unit tests.
 *
 *      -   Warning: FLUSH TABLES called at the end.
 *
 * @filesource
 * @copyright   Copyright (c) 2008-2011 David Smith
 * @license     http://www.opensource.org/licenses/mit-license.php MIT License
 * @package     Hashmark
 * @subpackage  Cron
 * @version     $Id$
*/

/**
 * For getModule().
 */
require_once dirname(__FILE__) . '/../Hashmark.php';

$db = Hashmark::getModule('DbHelper')->openDb('unittest');

$coreTables = array('categories', 'categories_milestones', 'categories_scalars',
                    'milestones', 'scalars');

foreach ($coreTables as $table) {
    $db->query("TRUNCATE `{$table}`");
    $db->query("ALTER TABLE `{$table}` AUTO_INCREMENT = 1");
}

$partition = Hashmark::getModule('Partition', '', $db);

$nonPartSampleTable = array('samples_string', 'samples_decimal', 'samples_analyst_temp');
$garbageTables = array_diff($partition->getTablesLike('%samples%'), $nonPartSampleTable);

if ($garbageTables) {
    $partition->dropTable($garbageTables);
    $db->query('FLUSH TABLES');
}
