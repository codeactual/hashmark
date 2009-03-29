<?php
// vim: fenc=utf-8:ft=php:ai:si:ts=4:sw=4:et:

/**
 * Global configuration.
 *
 * @filesource
 * @link        http://code.google.com/p/hashmark/
 * @link        http://framework.zend.com/manual/en/coding-standard.html
 * @link        http://phpdoc.org/tutorial.php
 * @copyright   Copyright (c) 2008-2009, Code Actual LLC
 * @license     http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package     Hashmark
 * @subpackage  Config
 * @version     $Id: Hashmark.php 287 2009-02-10 23:40:56Z david $
*/

/**
 * Should match DECIMAL(D,M) data types in Sql/Schema/hashmark.sql.
 */
define('HASHMARK_DECIMAL_TOTALWIDTH', 20);
define('HASHMARK_DECIMAL_RIGHTWIDTH', 4);
define('HASHMARK_DECIMAL_ROUND_SCALE', HASHMARK_DECIMAL_RIGHTWIDTH * 2);
define('HASHMARK_DECIMAL_SQLWIDTH', '(' . HASHMARK_DECIMAL_TOTALWIDTH . ',' . HASHMARK_DECIMAL_RIGHTWIDTH . ')');

/**
 * DB server setting.
 *
 *      -   Less likely that this will differ between profiles.
 */
define('HASHMARK_DBHELPER_DIV_PRECISION_INCREMENT', 4);
