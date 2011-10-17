<?php
// vim: fenc=utf-8:ft=php:ai:si:ts=4:sw=4:et:

/**
 * Default configuration.
 *
 * @filesource
 * @link        http://code.google.com/p/hashmark/
 * @link        http://framework.zend.com/manual/en/coding-standard.html
 * @link        http://phpdoc.org/tutorial.php
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
                         'frontEndOpts' => array(),
                         'backEndOpts' => array());

$config['Cron'] = array('merge_gc_max_count' => 30,
                        'merge_gc_max_days' => 10);

$config['DbHelper'] = array();

$config['DbHelper']['decimal_total_width'] = 20;    // Match DECIMAL(D,M) data types in Sql/Schema/hashmark.sql

$config['DbHelper']['decimal_right_width'] = 4;     // Match DECIMAL(D,M) data types in Sql/Schema/hashmark.sql

$config['DbHelper']['decimal_round_scale'] = $config['DbHelper']['decimal_right_width'] * 2;

$config['DbHelper']['decimal_sql_width'] = "({$config['DbHelper']['decimal_total_width']},{$config['DbHelper']['decimal_right_width']})";

$config['DbHelper']['div_precision_increment'] = 4;

$config['DbHelper']['profile'] = array();

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
 * interval options:
 *
 *      d:  Daily partitions
 *          Table name format: samples_<scalarId>_<YYMMDD>_<YYMMDD>
 *      m:  Monthly partitions
 *          Table name format: samples_<scalarId>_<YYMM>_<YYMM>
 */
$config['Partition'] = array('interval' => 'm',
                             'mergetable_prefix' => 'samples_mrg_');

$config['Test'] = array('base' => 'Test',
                        'override_me' => 'not overwritten',
                        'ext_config_paths' => array(dirname(__FILE__) . '/../Test/ExtConfig/Test'),
                        'ext_module_paths' => array(dirname(__FILE__) . '/../Test/ExtModule/Test'));

$config['Test_FakeModuleType'] = array('type' => 'FakeModuleType');
