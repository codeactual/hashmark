<?php
// vim: fenc=utf-8:ft=php:ai:si:ts=4:sw=4:et:

/**
 * Hashmark_Cache_Static
 *
 * @filesource
 * @link        http://code.google.com/p/hashmark/
 * @link        http://framework.zend.com/manual/en/coding-standard.html
 * @link        http://phpdoc.org/tutorial.php
 * @copyright   Copyright (c) 2008-2009, Code Actual LLC
 * @license     http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package     Hashmark
 * @subpackage  Hashmark_Cache
 * @version     $Id: Static.php 263 2009-02-03 11:22:57Z david $
*/

/**
 * Default cache wrapper using static PHP array. 
 *
 *      -   Placeholder in case no cache extension is available.
 *
 * @package     Hashmark
 * @subpackage  Hashmark_Cache
 */
class Hashmark_Cache_Static extends Hashmark_Cache
{
    /**
     * @var Array   Assoc. of cache key/value pairs.
     */
    protected static $_storage;

    /**
     * @see Abstract parent signature docs.
     */
    protected function _get($key)
    {
        if (isset(self::$_storage[$key])) {
            return self::$_storage[$key];
        }

        return false;
    }

    /**
     * @see Abstract parent signature docs.
     */
    protected function _set($key, $value)
    {
        self::$_storage[$key] = $value;

        return true;
    }
    
    /**
     * @see Abstract parent signature docs.
     */
    protected function _remove($key)
    {
        unset(self::$_storage[$key]);
        return true;
    }
}
