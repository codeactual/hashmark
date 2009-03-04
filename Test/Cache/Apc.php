<?php
// vim: fenc=utf-8:ft=php:ai:si:ts=4:sw=4:et:

/**
 * Hashmark_TestCase_Cache_Apc
 *
 * @filesource
 * @link        http://code.google.com/p/hashmark/
 * @link        http://framework.zend.com/manual/en/coding-standard.html
 * @link        http://phpdoc.org/tutorial.php
 * @copyright   Copyright (c) 2008-2009, Code Actual LLC
 * @license     http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package     Hashmark-Test
 * @subpackage  Hashmark_Cache
 * @version     $Id$
*/

/**
 * @package     Hashmark-Test
 * @subpackage  Hashmark_Cache
 */
class Hashmark_TestCase_Cache_Apc extends Hashmark_TestCase_Cache
{
    /**
     * Auto-skip if extension unavailable.
     */
    protected function setUp()
    {
        parent::setUp();

        if (!extension_loaded('apc')) {
            $this->markTestSkipped('Skipping tests, extension not loaded');
        }

        if (!ini_get('apc.enable_cli')) {
            $this->markTestSkipped('Skipping tests, CLI not enabled');
        }
    }
}
