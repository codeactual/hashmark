<?php
// vim: fenc=utf-8:ft=php:ai:si:ts=4:sw=4:et:

/**
 * Drop partition tables created by unit tests.
 *
 *      -   Warning: FLUSH TABLES called at the end.
 *
 * @filesource
 * @link        http://code.google.com/p/hashmark/
 * @link        http://framework.zend.com/manual/en/coding-standard.html
 * @link        http://phpdoc.org/tutorial.php
 * @copyright   Copyright (c) 2008-2009, Code Actual LLC
 * @license     http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package     Hashmark
 * @subpackage  Cron
 * @version     $Id: gcUnitTestTables.php 294 2009-02-13 03:48:59Z david $
*/

/**
 * For getModule().
 */
require_once dirname(__FILE__) . '/../../Hashmark.php';

$db = Hashmark::getModule('DbHelper')->openDb('unittest');

$coreTables = array('categories', 'categories_milestones', 'categories_scalars',
                    'jobs', 'milestones', 'scalars');

foreach ($coreTables as $table) {
    $db->query($db, "TRUNCATE `{$table}`");
    $db->query($db, "ALTER TABLE `{$table}` SET AUTO_INCREMENT = 1");
}

$partition = Hashmark::getModule('Partition', '', $db);

$nonPartSampleTable = array('samples_string', 'samples_decimal', 'samples_analyst_temp');
$garbageTables = array_diff($partition->getTablesLike('%samples%'), $nonPartSampleTable);

if ($garbageTables) {
    $partition->dropTable($garbageTables);
    $db->query($db, 'FLUSH TABLES');
}
