<?php
// vim: fenc=utf-8:ft=php:ai:si:ts=4:sw=4:et:

/**
 * Default configuration.
 *
 * @filesource
 * @copyright   Copyright (c) 2008-2011 David Smith
 * @license     http://www.opensource.org/licenses/mit-license.php MIT License
 * @package     Hashmark
 * @subpackage  Config
 * @version     $Id$
*/

/**
 * Default module configs -- do not edit here.
 *
 *      Ex. to override $config['Cache'], define $config in Config/Cache.php.
 *      Applies to all modules.
 */
$config = array();

$config['Cache'] = array('backEndName' => '',
                         'frontEndOpts' => array('automatic_serialization' => true),
                         'backEndOpts' => array());

// Hard limits for merge table expiration.
$config['Cron'] = array('merge_gc_max_count' => 30,
                        'merge_gc_max_days' => 10);

$config['DbHelper'] = array();

// Match DECIMAL(D,M) columns in Sql/Schema/hashmark.sql. Alter both if needed.
$config['DbHelper']['decimal_total_width'] = 20;
$config['DbHelper']['decimal_right_width'] = 4;

// Used by Hashmark_BcMath when generating expected floating-point aggregates.
// It probably only needs to be slightly longer than 'decimal_right_width' to match MySQL rounding.
$config['DbHelper']['decimal_round_scale'] = $config['DbHelper']['decimal_right_width'] * 2;

// Match your MySQL server's 'div_precision_increment' setting.
// Allows classes like Hashmark_Analyst_BasicDecimal to know whether it should adjust its session setting for DECIMAL calculations.
$config['DbHelper']['div_precision_increment'] = 4;

$config['DbHelper']['profile'] = array();

// For use by agent scripts.
$config['DbHelper']['profile']['cron'] = array('adapter' => 'Mysqli',
                                               'params' => array('host' => '',
                                                                 'port' => 3360,
                                                                 'dbname' => 'hashmark',
                                                                 'username' => '',
                                                                 'password' => ''));

$config['DbHelper']['profile']['unittest'] = array('adapter' => 'Mysqli',
                                                   'params' => array('host' => '',
                                                                     'port' => 3360,
                                                                     'dbname' => 'hashmark_test',
                                                                     'username' => '',
                                                                     'password' => ''));

/**
 * Merge table partitioning.
 *
 * Interval options:
 *   'd':  Daily. Table name format: samples_<scalarId>_<YYMMDD>_<YYMMDD>.
 *   'm':  Monthly. Table name format: samples_<scalarId>_<YYMM>_<YYMM>.
 */
$config['Partition'] = array('interval' => 'm',
                             'mergetable_prefix' => 'samples_mrg_');

// Ignore. Used by tests covering code that depends on naming conventions.
$config['Test'] = array('base' => 'Test',
                        'override_me' => 'not overwritten',
                        'ext_config_paths' => array(dirname(__FILE__) . '/../Test/ExtConfig/Test'),
                        'ext_module_paths' => array(dirname(__FILE__) . '/../Test/ExtModule/Test'));
$config['Test_FakeModuleType'] = array('type' => 'FakeModuleType');
