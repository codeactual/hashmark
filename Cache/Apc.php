<?php
// vim: fenc=utf-8:ft=php:ai:si:ts=4:sw=4:et:

/**
 * Hashmark_Cache_Apc
 *
 * @filesource
 * @link        http://code.google.com/p/hashmark/
 * @link        http://framework.zend.com/manual/en/coding-standard.html
 * @link        http://phpdoc.org/tutorial.php
 * @copyright   Copyright (c) 2008-2009, Code Actual LLC
 * @license     http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package     Hashmark
 * @subpackage  Hashmark_Cache
 * @version     $Id: Apc.php 263 2009-02-03 11:22:57Z david $
*/

/**
 * Alternative PHP Cache wrappers.
 * 
 * @package     Hashmark
 * @subpackage  Hashmark_Cache
 */
class Hashmark_Cache_Apc extends Hashmark_Cache
{
    /**
     * Called by Hashmark::getModule() to inject dependencies.
     *
     * @access public
     * @return boolean  False if module could not be initialized and is unusable.
     *                  Hashmark::getModule() will also then return false.
     */
    public function initModule()
    {
        return extension_loaded('apc');
    }

    /**
     * @see Abstract parent signature docs.
     */
    protected function _get($key)
    {
        return apc_fetch($key);
    }

    /**
     * @see Abstract parent signature docs.
     */
    protected function _set($key, $value)
    {
        return apc_store($key, $value);
    }

    /**
     * @see Abstract parent signature docs.
     */
    protected function _remove($key)
    {
        return apc_delete($key);
    }
}
