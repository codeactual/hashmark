<?php
// vim: fenc=utf-8?=php???=4?=4?:

/**
 * Shared SQL query templates.
 *
 *      -   Generally $sql keys match module method names,
 *          ex. $sql['myMethod'] is used in Hashmark_MyModule::myMethod().
 *      -   :name macros will be escaped and quoted, ex. DATETIMEs.
 *      -   @name macros will not be escaped nor quoted, ex. SQL functions.
 *
 * @filesource
 * @link        http://code.google.com/p/hashmark/
 * @link        http://framework.zend.com/manual/en/coding-standard.html
 * @link        http://phpdoc.org/tutorial.php
 * @copyright   Copyright (c) 2008-2009, Code Actual LLC
 * @license     http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package     Hashmark
 * @subpackage  Sql
 * @version     $Id: Client.php 272 2009-02-04 13:27:16Z david $
*/

$sql = array();

$sql['set:updateScalar'] = 'UPDATE `scalars` '
                         . 'SET `value` = ?, '
                         . '`last_inline_change` = UTC_TIMESTAMP() '
                         . 'WHERE `name` = ?';

$sql['set:updateScalarForNewSample'] = 'UPDATE `scalars` '
                                     . 'SET `value` = ?, '
                                     . '`last_sample_change` = UTC_TIMESTAMP(), '
                                     . '`last_inline_change` = UTC_TIMESTAMP() '
                                     . 'WHERE `name` = ?';

$sql['set:insertSample'] = 'INSERT INTO ~samples '
                         . '(`value`, `start`, `end`) '
                         . 'VALUES (?, UTC_TIMESTAMP(), UTC_TIMESTAMP())';

$sql['get'] = 'SELECT `value` '
            . 'FROM `scalars` '
            . 'WHERE `name` = ? '
            . 'LIMIT 1';

$sum = 'CONVERT(`value`, DECIMAL' . HASHMARK_DECIMAL_SQLWIDTH . ') + @amount';

$sql['incr:updateScalar'] = 'UPDATE `scalars` '
                          . "SET `value` = {$sum}, "
                          . '`last_inline_change` = UTC_TIMESTAMP() '
                          . 'WHERE `name` = :name '
                          . 'AND `type` = "decimal"';

$sql['incr:updateScalarForNewSample'] = 'UPDATE `scalars` '
                                      . "SET `value` = {$sum}, "
                                      . '`last_sample_change` = UTC_TIMESTAMP(), '
                                      . '`last_inline_change` = UTC_TIMESTAMP() '
                                      . 'WHERE `name` = :name '
                                      . 'AND `type` = "decimal"';

$currentScalarValue = 'SELECT `value` FROM `scalars` WHERE `name` = ? LIMIT 1';

$sql['incr:insertSample'] = 'INSERT INTO ~samples '
                          . '(`value`, `start`, `end`) '
                          . "VALUES (({$currentScalarValue}), UTC_TIMESTAMP(), UTC_TIMESTAMP())";
