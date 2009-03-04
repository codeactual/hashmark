<?php
// vim: fenc=utf-8?=php???=4?=4?:

/**
 * Shared SQL query templates.
 *
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
 * @version     $Id: Partition.php 280 2009-02-06 08:58:59Z david $
*/

$sql = array();

$sql['getTableDefinition'] = 'SHOW CREATE TABLE `@name`';

$sql['tableExists'] = 'SELECT `TABLE_NAME` '
                    . 'FROM `INFORMATION_SCHEMA`.`TABLES` '
                    . 'WHERE `TABLE_NAME` = ?';
            
$sql['getTablesLike'] = 'SHOW TABLES LIKE ?';

$sql['dropTable'] = 'DROP TABLE IF EXISTS @list';
    
$sql['getTableInfo'] = 'SELECT * FROM `INFORMATION_SCHEMA`.`TABLES` WHERE `TABLE_NAME` = ?';

$sql['getAllMergeTables'] = 'SELECT `TABLE_NAME`, `TABLE_COMMENT` FROM `INFORMATION_SCHEMA`.`TABLES` '
                          . 'WHERE SUBSTR(`TABLE_NAME`, 1, 12) = "' . HASHMARK_PARTITION_MERGETABLE_PREFIX . '"';
