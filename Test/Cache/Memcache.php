<?php
// vim: fenc=utf-8:ft=php:ai:si:ts=4:sw=4:et:

/**
 * Hashmark_TestCase_Cache_Memcache
 *
 * @filesource
 * @link        http://code.google.com/p/hashmark/
 * @link        http://framework.zend.com/manual/en/coding-standard.html
 * @link        http://phpdoc.org/tutorial.php
 * @copyright   Copyright (c) 2008-2009, Code Actual LLC
 * @license     http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package     Hashmark-Test
 * @subpackage  Hashmark_Cache
 * @version     $Id: Memcache.php 263 2009-02-03 11:22:57Z david $
*/

/**
 * @package     Hashmark-Test
 * @subpackage  Hashmark_Cache
 */
class Hashmark_TestCase_Cache_Memcache extends Hashmark_TestCase_Cache
{
    /**
     * Auto-skip if extension unavailable.
     */
    protected function setUp()
    {
        parent::setUp();

        if (!extension_loaded('memcache')) {
            $this->markTestSkipped('Skipping tests, extension not loaded');
        }
        
        $configFile = dirname(__FILE__) . '/../../Config/Cache/Memcache.php';
        if (!is_readable($configFile)) {
            $this->markTestSkipped('Skipping tests, config file missing or unreadable: ' . $configFile);
        }
    }
}
