<?php
// vim: fenc=utf-8:ft=php:ai:si:ts=4:sw=4:et:

/**
 * Hashmark_Sampler
 *
 * @filesource
 * @link        http://code.google.com/p/hashmark/
 * @link        http://framework.zend.com/manual/en/coding-standard.html
 * @link        http://phpdoc.org/tutorial.php
 * @copyright   Copyright (c) 2008-2009, Code Actual LLC
 * @license     http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package     Hashmark
 * @subpackage  Base
 * @version     $Id: Sampler.php 263 2009-02-03 11:22:57Z david $
 */
        
/**
 * Base class for sample acquiring classes.
 *
 * Implementations live in Sampler/. Each defines a new data source in run().
 * Other methods like getName() allow a front-end to simply glob() for available
 * samplers and their properties, rather than maintain them in a DB.
 *
 * @abstract
 * @package     Hashmark
 * @subpackage  Base
 */
abstract class Hashmark_Sampler extends Hashmark_Module
{
    /**
     * Return the human-readable name.
     *
     * @static
     * @access public
     * @return string
     */
    abstract public static function getName();

    /**
     * Return the description text.
     *
     * @static
     * @access public
     * @return string
     */
    abstract public static function getDescription();

    /**
     * Return the new scalar/sample value using an arbitrary data source.
     *
     * @static
     * @access public
     * @param int       $scalarId   Scalar being sampled.
     * @return string   New value; null on error.
     */
    abstract public static function run($scalarId);
}
