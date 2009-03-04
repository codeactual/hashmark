<?php
// vim: fenc=utf-8:ft=php:ai:si:ts=4:sw=4:et:

/**
 * Hashmark_Module
 *
 * @filesource
 * @link        http://code.google.com/p/hashmark/
 * @link        http://framework.zend.com/manual/en/coding-standard.html
 * @link        http://phpdoc.org/tutorial.php
 * @copyright   Copyright (c) 2008-2009, Code Actual LLC
 * @license     http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package     Hashmark
 * @subpackage  Base
 * @version     $Id: Module.php 296 2009-02-13 05:03:11Z david $
*/

/**
 * Base for all classes produced by Hashmark::getModule() factory.
 *
 * @abstract
 * @package     Hashmark
 * @subpackage  Base
*/
abstract class Hashmark_Module
{
    /**
     * @access protected
     * @var string Module base name, ex. 'Client'.
     */
    protected $_base;

    /**
     * @access protected
     * @var mixed Base module configs, ex. for all 'Client' implementations.
     */
    protected $_baseConfig;
    
    /**
     * @access protected
     * @var string Module type name, ex. 'Mysqli'.
     */
    protected $_type;
    
    /**
     * @access protected
     * @var mixed Module type-specific configs, ex. Hashmark_Client_Mysqli configs.
     */
    protected $_typeConfig;

    /**
     * @access protected
     * @param string    $base     
     * @param mixed     $baseConfig
     * @param string    $type     
     * @param mixed     $typeConfig     
     * @return void
     */
    public function __construct($base, $baseConfig, $type, $typeConfig)
    {
        $this->_base = $base;
        $this->_baseConfig = $baseConfig;
        $this->_type = $type;
        $this->_typeConfig = $typeConfig;
    }

    /**
     * Public access to $_base.
     *
     * @access public
     * @return string
     */
    public function getBase()
    {
        return $this->_base;
    }

    /**
     * Public access to $_baseConfig.
     *
     * @access public
     * @return string
     */
    public function getBaseConfig()
    {
        return $this->_baseConfig;
    }

    /**
     * Public access to $_type.
     *
     * @access public
     * @return string
     */
    public function getType()
    {
        return $this->_type;
    }

    /**
     * Public access to $_typeConfig.
     *
     * @access public
     * @return string
     */
    public function getTypeConfig()
    {
        return $this->_typeConfig;
    }
}
