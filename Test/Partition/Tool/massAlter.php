<?php
// vim: fenc=utf-8:ft=php:ai:si:ts=4:sw=4:et:

/**
 * Apply alterations to all matching tables.
 *
 * @filesource
 * @link        http://code.google.com/p/hashmark/
 * @link        http://framework.zend.com/manual/en/coding-standard.html
 * @link        http://phpdoc.org/tutorial.php
 * @copyright   Copyright (c) 2008-2009, Code Actual LLC
 * @license     http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package     Hashmark-Test
 * @subpackage  Hashmark_Partition
 * @version     $Id: massAlter.php 281 2009-02-06 17:09:14Z david $
*/

$likeExpr = '';
$alterExprs = array();

if (!$likeExpr) {
    die("\nNo LIKE expression.\n");
}

if (!$alterExprs) {
    die("\nNo ALTER expression(s).\n");
}

/**
 * For Hashmark::getModule().
 */
require_once dirname(__FILE__) . '/../../Hashmark.php';

$db = Hashmark::getModule('DbHelper', 'Mysqli')->openDb('unittest');
if (!$db) {
    die("\nNo DB link.\n");
}

$p = Hashmark::getModule('Partition', 'Mysqli', $db);

$targets = $p->getTablesLike($likeExpr);
if (!$targets) {
    die("\nNo matching tables.\n");
}

foreach ($targets as $table) {
    foreach ($alterExprs as $alter) {
        $res = $db->query("ALTER TABLE `{$table}` {$alter}");
        if (!$res) {
            die("\nAlter error: {$db->error}\n");
        }
        if ($db->affected_rows) {
            echo "`{$table}`: {$alter}\n";
        }
    }
}
