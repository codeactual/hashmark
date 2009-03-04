<?php
// vim: fenc=utf-8:ft=php:ai:si:ts=4:sw=4:et:

/**
 * Hashmark_Cache_Memcache
 *
 * @filesource
 * @link        http://code.google.com/p/hashmark/
 * @link        http://framework.zend.com/manual/en/coding-standard.html
 * @link        http://phpdoc.org/tutorial.php
 * @copyright   Copyright (c) 2008-2009, Code Actual LLC
 * @license     http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package     Hashmark
 * @subpackage  Hashmark_Cache
 * @version     $Id: Memcache.php 263 2009-02-03 11:22:57Z david $
*/

/**
 * Memcache extension wrappers.
 *
 * @package     Hashmark
 * @subpackage  Hashmark_Cache
 */
class Hashmark_Cache_Memcache extends Hashmark_Cache
{
    /**
     * @access protected
     * @var Memcache
     */
    protected $_memcache;

    /**
     * Called by Hashmark::getModule() to inject dependencies.
     *
     * @access public
     * @return boolean  False if module could not be initialized and is unusable.
     *                  Hashmark::getModule() will also then return false.
     */
    public function initModule()
    {
        if ($this->_typeConfig['memcache'] instanceof Memcache) {
            $this->_memcache = $this->_typeConfig['memcache'];
            return true;
        }

        return false;
    }

    /**
     * @see Abstract parent signature docs.
     */
    protected function _get($key)
    {
        return $this->_memcache->get($key);
    }

    /**
     * @see Abstract parent signature docs.
     */
    protected function _set($key, $value)
    {
        return $this->_memcache->set($key, $value);
    }
    
    /**
     * @see Abstract parent signature docs.
     */
    protected function _remove($key)
    {
        return $this->_memcache->delete($key);
    }
}
