<?php
// vim: fenc=utf-8:ft=php:ai:si:ts=4:sw=4:et:

/**
 * Apply alterations to all matching tables.
 *
 * @filesource
 * @link        http://code.google.com/p/hashmark/
 * @link        http://framework.zend.com/manual/en/coding-standard.html
 * @link        http://phpdoc.org/tutorial.php
 * @copyright   Copyright (c) 2008-2011 David Smith
 * @license     http://www.opensource.org/licenses/mit-license.php MIT License
 * @package     Hashmark-Test
 * @subpackage  Hashmark_Partition
 * @version     $Id$
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

$db = Hashmark::getModule('DbHelper')->openDb('unittest');
if (!$db) {
    die("\nNo DB link.\n");
}

$p = Hashmark::getModule('Partition', '', $db);

$targets = $p->getTablesLike($likeExpr);
if (!$targets) {
    die("\nNo matching tables.\n");
}

foreach ($targets as $table) {
    foreach ($alterExprs as $alter) {
        $stmt = $db->query("ALTER TABLE `{$table}` {$alter}");
        if ($stmt->rowCount()) {
            echo "`{$table}`: {$alter}\n";
        }
        unset($stmt);
    }
}
