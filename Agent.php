<?php
// vim: fenc=utf-8:ft=php:ai:si:ts=4:sw=4:et:

/**
 * Hashmark_Agent
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
 * Interface of cron-triggered agents.
 *
 * @package     Hashmark
 * @subpackage  Base
 */
interface Hashmark_Agent
{
    /**
     * Return the human-readable name.
     *
     * @return string
     */
    public static function getName();

    /**
     * Return the description text.
     *
     * @return string
     */
    public static function getDescription();

    /**
     * Perform the agent's task, ex. sampling a data source, or testing for an
     * alert state and responding.
     *
     * @param Array     $agent  Agent-specific fields, ex. scalar ID, configs,
     *                          last run time, etc.
     * @return mixed    Agent-specific.
     */
    public static function run($agent = array());
}
