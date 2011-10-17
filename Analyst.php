<?php
// vim: fenc=utf-8:ft=php:ai:si:ts=4:sw=4:et:

/**
 * Hashmark_Analyst
 *
 * @filesource
 * @copyright   Copyright (c) 2008-2011 David Smith
 * @license     http://www.opensource.org/licenses/mit-license.php MIT License
 * @package     Hashmark
 * @subpackage  Base
 * @version     $Id$
*/

/**
 * Base class for sample analysers suites.
 *
 * Implementations live in Analyst/. Each suite queries sample partitions
 * of one or more scalars to extract an analytical result, ex. simple
 * aggregates or raw value/date lists.
 * 
 * @package     Hashmark
 * @subpackage  Base
 */
abstract class Hashmark_Analyst extends Hashmark_Module_DbDependent
{
}
