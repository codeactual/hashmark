<?php
// vim: fenc=utf-8:ft=php:ai:si:ts=4:sw=4:et:

/**
 * Hashmark_Cache_Memcache config.
 *
 *      -   Intended as place to override INI values, open connection or create
 *          pool, etc.
 *      -   Do not rename $config.
 *
 * @filesource
 * @link        http://code.google.com/p/hashmark/
 * @link        http://framework.zend.com/manual/en/coding-standard.html
 * @link        http://phpdoc.org/tutorial.php
 * @copyright   Copyright (c) 2008-2009, Code Actual LLC
 * @license     http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package     Hashmark
 * @subpackage  Config
 * @version     $Id: Memcache.php 263 2009-02-03 11:22:57Z david $
*/

if (extension_loaded('memcache')) {
    $config = array();
    $config['memcache'] = new Memcache();
    $config['memcache']->addServer('localhost', 11211);
}
