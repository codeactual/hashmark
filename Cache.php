<?php
// vim: fenc=utf-8:ft=php:ai:si:ts=4:sw=4:et:

/**
 * Hashmark_Cache
 *
 * @filesource
 * @link        http://code.google.com/p/hashmark/
 * @link        http://framework.zend.com/manual/en/coding-standard.html
 * @link        http://phpdoc.org/tutorial.php
 * @copyright   Copyright (c) 2008-2009, Code Actual LLC
 * @license     http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package     Hashmark
 * @subpackage  Base
 * @version     $Id: Cache.php 300 2009-02-13 05:51:17Z david $
*/

/**
 * Base class for cache extension wrappers.
 *
 *      -   Implementations live in Cache/. Each defines methods wrapping
 *          functions for setting, group removal, etc.
 *      -   Group prefixing based on Alex Rickabaugh's:
 *          http://www.aminus.org/blogs/index.php/2007/12/30/memcached_set_invalidation?blog=2#c38801
 *
 * @abstract
 * @package     Hashmark
 * @subpackage  Base
 */
abstract class Hashmark_Cache extends Hashmark_Module
{
    /**
     * Retrieve a cached value by name.
     *
     * @access protected
     * @param string    $key    Key name.
     * @return mixed
     */
    abstract protected function _get($key);

    /**
     * Store a named value.
     *
     * @access protected
     * @param string    $key    Key name.
     * @param mixed     $value
     * @return boolean  True on success.
     */
    abstract protected function _set($key, $value);
    
    /**
     * Invalidate a key.
     *
     * @access protected
     * @param string    $key    Key name.
     * @return boolean  True on success.
     */
    abstract protected function _remove($name);

    /**
     * Namespacing wrapper for _get().
     *
     * @access public
     * @param string    $key    Key name.
     * @param string    $group  Group name.
     * @return mixed
     */
    public function get($key, $group = '')
    {
        return $this->_get($this->finalizeKey($key, $group));
    }
    
    /**
     * Namespacing wrapper for _set().
     *
     * @access public
     * @param string    $key    Key name.
     * @param mixed     $value
     * @param string    $group  Group name.
     * @return boolean  True on success.
     */
    public function set($key, $value, $group = '')
    {
        return $this->_set($this->finalizeKey($key, $group), $value);
    }
    
    /**
     * Namespacing wrapper for _remove().
     *
     * @access public
     * @param string    $key    Key name.
     * @param string    $group  Group name.
     * @return boolean  True on success.
     */
    public function remove($key, $group = '')
    {
        return $this->_remove($this->finalizeKey($key, $group));
    }

    /**
     * Add namespace and group prefix (if needed) to get/set/remove keys.
     *
     * @access public
     * @param string    $key    Key name.
     * @param string    $group  Group name.
     * @return string   Finalized key.
     */
    public function finalizeKey($key, $group = '')
    {
        $fullPrefix = 'Hashmark';

        if ($group) {
            $groupPrefix = $this->getGroupPrefix($group);
            if (!$groupPrefix) {
                $groupPrefix = $this->getGroupPrefix($group, true);
            }
            $fullPrefix .= $groupPrefix;
        }

        return $fullPrefix . $key;
    }
    
    /**
     * Return a value key prefix based on a group name.
     *
     * @access public
     * @param string    $group  Group name.
     * @param boolean   $new    If true, a new prefix is created.
     * @return string   Active group prefix; false on error.
     */
    public function getGroupPrefix($group, $new = false)
    {
        $key = '~Hashmark' . $group;

        if ($new) {
            $prefix = Hashmark_Util::randomSha1();
            if ($this->set($key, $prefix)) {
                return $prefix;
            } else {
                return false;
            }
        }

        return $this->get($key);
    }

    /**
     * Invalidates all value keys composed with $group prefix.
     *
     * @access public
     * @return boolean  True on success.
     */
    public function removeGroup($group)
    {
        return $this->getGroupPrefix($group, true) ? true : false;
    }
}
