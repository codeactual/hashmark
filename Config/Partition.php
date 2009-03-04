<?php
// vim: fenc=utf-8:ft=php:ai:si:ts=4:sw=4:et:

/**
 * Partition module configuration.
 *
 * @filesource
 * @link        http://code.google.com/p/hashmark/
 * @link        http://framework.zend.com/manual/en/coding-standard.html
 * @link        http://phpdoc.org/tutorial.php
 * @copyright   Copyright (c) 2008-2009, Code Actual LLC
 * @license     http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package     Hashmark
 * @subpackage  Config
 * @version     $Id: Partition.php 281 2009-02-06 17:09:14Z david $
*/

/**
 * Options:
 *
 *      d:  Daily partitions
 *          Table name format: samples_<scalarId>_<YYMMDD>_<YYMMDD>
 *      m:  Monthly partitions
 *          Table name format: samples_<scalarId>_<YYMM>_<YYMM>
 *
 */
define('HASHMARK_PARTITION_INTERVAL', 'd');

define('HASHMARK_PARTITION_MERGETABLE_PREFIX', 'samples_mrg_');
