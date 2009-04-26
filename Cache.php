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
 * @version     $Id$
*/

/**
 * Zend_Cache wrapper.
 *
 *  -   Group keys based on Alex Rickabaugh's:
 *      http://www.aminus.org/blogs/index.php/2007/12/30/memcached_set_invalidation?blog=2#c38801
 *
 * @package     Hashmark
 * @subpackage  Base
 */
class Hashmark_Cache extends Hashmark_Module
{
    /**
     * @var Zend_Cache_Frontend_*   Current instance. See initModule().
     */
    protected $_cache;

    /**
     * @param mixed     $db         Connection object/resource.
     * @return boolean  False if module could not be initialized and is unusable.
     *                  Hashmark::getModule() will also then return false.
     */
    public function initModule()
    {
        if (!empty($this->_baseConfig['backEndName'])) {
            $this->_cache = Zend_Cache::factory('Core',
                                                $this->_baseConfig['backEndName'],
                                                $this->_baseConfig['frontEndOpts'],
                                                $this->_baseConfig['backEndOpts']);
        }

        // Do not require caching.
        return true;
    }

    /**
     * Expose cache adapter status.
     *
     * @return boolean
     */
    public function isConfigured()
    {
        return (boolean) $this->_cache;
    }

    /**
     * Namespace/group-key wrapper for load().
     *
     * @param string    $key
     * @param string    $group
     * @param boolean   $doNotTestCacheValidity     See Zend_Cache_Core::save().
     * @param boolean   $doNotUnserialize           See Zend_Cache_Core::save().
     * @return mixed
     */
    public function load($key, $group = '', $doNotTestCacheValidity = false, $doNotUnserialize = false)
    {
        if (!$this->_cache) {
            return false;
        }

        return $this->_cache->load($this->_finalizeKey($key, $group),
                                   $doNotTestCacheValidity, $doNotUnserialize);
    }
    
    /**
     * Namespace/group-key wrapper for save().
     *
     * @param mixed     $value
     * @param string    $key
     * @param string    $group
     * @param int       $specificLifetime   See Zend_Cache_Core::save().
     * @param int       $priority           See Zend_Cache_Core::save().
     * @throws Zend_Cache_Exception
     * @return boolean  True on success.
     */
    public function save($value, $key, $group = '', $specificLifetime = false, $priority = 8)
    {
        if (!$this->_cache) {
            return true;
        }

        return $this->_cache->save($value, $this->_finalizeKey($key, $group), array(),
                                   $specificLifetime, $priority);
    }
    
    /**
     * Namespace/group-key wrapper for remove().
     *
     * @param string    $key
     * @param string    $group
     * @return boolean  True on success.
     */
    public function remove($key, $group = '')
    {
        if (!$this->_cache) {
            return true;
        }

        return $this->_cache->remove($this->_finalizeKey($key, $group));
    }

    /**
     * Invalidates all value keys composed with $group key.
     *
     * @return boolean  True on success.
     */
    public function removeGroup($group)
    {
        return $this->_getGroupKey($group, true) ? true : false;
    }

    /**
     * Add namespace and group key (if needed) to get/set/remove keys.
     *
     * @param string    $key    Key name.
     * @param string    $group  Group name.
     * @return string   Finalized key.
     */
    protected function _finalizeKey($key, $group = '')
    {
        $fullKey = 'Hashmark';

        if ($group) {
            $groupKey = $this->_getGroupKey($group);
            if (!$groupKey) {
                $groupKey = $this->_getGroupKey($group, true);
            }
            $fullKey .= $groupKey;
        }

        return $fullKey . $key;
    }
    
    /**
     * Return a value key key based on a group name.
     *
     * @param string    $group  Group name.
     * @param boolean   $new    If true, a new key is created.
     * @return string   Active group key; false on error.
     */
    protected function _getGroupKey($group, $new = false)
    {
        $groupKeyKey = 'Hashmark' . $group;

        if ($new) {
            $groupKeyValue = Hashmark_Util::randomSha1();
            if ($this->save($groupKeyValue, $groupKeyKey)) {
                return $groupKeyValue;
            } else {
                return false;
            }
        }

        return $this->load($groupKeyKey);
    }
}
