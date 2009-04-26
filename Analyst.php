<?php
// vim: fenc=utf-8:ft=php:ai:si:ts=4:sw=4:et:

/**
 * Hashmark_Analyst
 *
 * @filesource
 * @link        http://code.google.com/p/hashmark/
 * @link        http://framework.zend.com/manual/en/coding-standard.html
 * @link        http://phpdoc.org/tutorial.php
 * @copyright   Copyright (c) 2008-2009, Code Actual LLC
 * @license     http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package     Hashmark
 * @subpackage  Base
 * @version     $Id: Analyst.php 296 2009-02-13 05:03:11Z david $
*/

/**
 * Base class for sample analysers suites.
 *
 * Implementations live in Analyst/. Each suite queries sample partitions
 * of one or more scalars to extract an analytical result, ex. simple
 * aggregates or raw value/date lists.
 * 
 * @abstract
 * @package     Hashmark
 * @subpackage  Base
 */
abstract class Hashmark_Analyst extends Hashmark_Module_DbDependent
{
}
