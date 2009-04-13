<?php
/**
 * DbHelper module configuration.
 *
 * @filesource
 * @link        http://code.google.com/p/hashmark/
 * @link        http://framework.zend.com/manual/en/coding-standard.html
 * @link        http://phpdoc.org/tutorial.php
 * @copyright   Copyright (c) 2008-2009, Code Actual LLC
 * @license     http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package     Hashmark
 * @subpackage  Config
 * @version     $Id: DbHelper.php 282 2009-02-06 17:09:38Z david $
*/

$config['profile']['cron'] = array('host' => 'localhost',
                                   'sock' => '/var/run/mysqld/mysqld.sock',
                                   'port' => '3306',
                                   'name' => 'hashmark',
                                   'user' => 'root',
                                   'pass' => '',
                                   'div_precision_increment', 4);

$config['profile']['unittest'] = array('host' => 'localhost',
                                       'sock' => '/var/run/mysqld/mysqld.sock',
                                       'port' => '3306',
                                       'name' => 'hashmark_test',
                                       'user' => 'root',
                                       'pass' => '',
                                       'div_precision_increment', 4);
