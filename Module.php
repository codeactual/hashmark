<?php
// vim: fenc=utf-8:ft=php:ai:si:ts=4:sw=4:et:

/**
 * Hashmark_Module
 *
 * @filesource
 * @link        http://code.google.com/p/hashmark/
 * @link        http://framework.zend.com/manual/en/coding-standard.html
 * @link        http://phpdoc.org/tutorial.php
 * @copyright   Copyright (c) 2008-2011 David Smith
 * @license     http://www.opensource.org/licenses/mit-license.php MIT License
 * @package     Hashmark
 * @subpackage  Base
 * @version     $Id$
*/

/**
 * Base for all classes produced by Hashmark::getModule() factory.
 *
 * @package     Hashmark
 * @subpackage  Base
*/
abstract class Hashmark_Module
{
    /**
     * @var string Module base name, ex. 'Client'.
     */
    protected $_base;

    /**
     * @var mixed Base module configs, ex. for all 'Client' implementations.
     */
    protected $_baseConfig;
    
    /**
     * @var string Module type name, ex. 'DbDependent' (Hashmark_Module_DbDependent).
     */
    protected $_type;
    
    /**
     * @var mixed Module type-specific configs.
     */
    protected $_typeConfig;

    /**
     * @var Hashmark_Cache_*    Cache object.
     */
    protected $_cache;

    /**
     * @param string            $base     
     * @param mixed             $baseConfig
     * @param string            $type     
     * @param mixed             $typeConfig     
     * @param Hashmark_Cache_*  $cache  Instance of implementation selected
     *                                  in Config/Cache.php and created in
     *                                  Hashmark::getModule().
     * @return void
     */
    public function __construct($base, $baseConfig, $type, $typeConfig, $cache)
    {
        $this->_base = $base;
        $this->_baseConfig = $baseConfig;
        $this->_type = $type;
        $this->_typeConfig = $typeConfig;
        $this->_cache = $cache;
    }

    /**
     * Public access to $_base.
     *
     * @return string
     */
    public function getBase()
    {
        return $this->_base;
    }

    /**
     * Public access to $_baseConfig.
     *
     * @return string
     */
    public function getBaseConfig()
    {
        return $this->_baseConfig;
    }

    /**
     * Public access to $_type.
     *
     * @return string
     */
    public function getType()
    {
        return $this->_type;
    }

    /**
     * Public access to $_typeConfig.
     *
     * @return string
     */
    public function getTypeConfig()
    {
        return $this->_typeConfig;
    }
}
