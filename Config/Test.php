<?php
// vim: fenc=utf-8:ft=php:ai:si:ts=4:sw=4:et:

/**
 * For unit tests, ex. covering Hashmark::getModule().
 *
 * @filesource
 * @link        http://code.google.com/p/hashmark/
 * @link        http://framework.zend.com/manual/en/coding-standard.html
 * @link        http://phpdoc.org/tutorial.php
 * @copyright   Copyright (c) 2008-2009, Code Actual LLC
 * @license     http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package     Hashmark
 * @subpackage  Config
 * @version     $Id: Test.php 263 2009-02-03 11:22:57Z david $
*/

/**
 * For assertion that getModule() loads this script.
 */
define('HASHMARK_TEST_CONFIG_NAME', 'HASHMARK_TEST_CONFIG_VALUE');

$config = array('base' => 'Test');

/**
 * Paths inspected before Samplers/ when loading an external sampler type.
 *
 * No trailing slashes.
 */
$config['ext_config_paths'] = array(dirname(__FILE__) . '/../Test/ExtConfig');
$config['ext_module_paths'] = array(dirname(__FILE__) . '/../Test/ExtModule');
