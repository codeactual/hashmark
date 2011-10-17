<?php
// vim: fenc=utf-8:ft=php:ai:si:ts=4:sw=4:et:

/**
 * Kitchen sink bootstrap file for all tests.
 *
 * Extra per-run parsing, but makes it easier to write/maintain tests.
 *
 * @filesource
 * @copyright   Copyright (c) 2008-2011 David Smith
 * @license     http://www.opensource.org/licenses/mit-license.php MIT License
 * @package     Hashmark-Test
 * @version     $Id$
*/

ini_set('error_reporting', E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', dirname(__FILE__) . '/error_log');

/**
 * For Hashmark::getModule().
 */
require_once dirname(__FILE__) . '/../Hashmark.php';

/**
 * For Hashmark_TestCase
 */
require_once HASHMARK_ROOT_DIR . '/Test/Case.php';

/**
 * Ex. forces Hashmark_DbHelper::openDb() to always use the 'unittest' profile.
 */
define('HASHMARK_TEST_MODE', 1);

require_once '/usr/lib/php/PHPUnit/Autoload.php';
