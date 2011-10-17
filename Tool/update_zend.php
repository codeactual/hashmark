#!/usr/bin/php
<?php
// vim: fenc=utf-8:ft=php:ai:si:ts=4:sw=4:et:

/**
 * Update Zend Framework components to a specified version.
 *
 * @filesource
 * @copyright   Copyright (c) 2008-2011 David Smith
 * @license     http://www.opensource.org/licenses/mit-license.php MIT License
 * @package     Hashmark
 * @subpackage  Tool
 * @version     $Id$
*/

if (2 != $argc) {
    $usage = "\nRun from Hashmark root directory.\n\n"
           . "usage:   Tool/update_zend.php [VERSION]\n"
           . "example: Tool/update_zend.php 1.8.1\n\n";
    exit($usage);
}

$ZF_SVNPATH = "http://framework.zend.com/svn/framework/standard/tags/release-{$argv[1]}";
echo "VERIFY: {$ZF_SVNPATH}\n";

$ZF_SVNINFO = @file_get_contents($ZF_SVNPATH);

if (!$ZF_SVNINFO) {
    exit("NOT FOUND: {$ZF_SVNINFO}\n");
}

$files = array('library/Zend/Cache.php',
               'library/Zend/Db.php',
               'library/Zend/Exception.php',
               'LICENSE.txt',
               'library/Zend/Loader.php',
               'library/Zend/Registry.php');

foreach ($files as $file) {
    $local = 'Zend/' . basename($file);
    echo "FETCH: {$local}\n";
    `svn cat {$ZF_SVNPATH}/{$file} > {$local}`;
}

$dirs = array('Cache',
              'Db',
              'Loader');

foreach ($dirs as $dir) {
    $local = "Zend/{$dir}";
    echo "FETCH: {$local}\n";
    `svn export --force {$ZF_SVNPATH}/library/Zend/{$dir} {$local}`;
}

exit("Updated Zend Framework to {$argv[1]}\n");
