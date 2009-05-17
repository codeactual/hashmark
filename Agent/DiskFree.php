<?php
// vim: fenc=utf-8:ft=php:ai:si:ts=4:sw=4:et:

/**
 * Hashmark_Agent_DiskFree
 *
 * @filesource
 * @link        http://code.google.com/p/hashmark/
 * @link        http://framework.zend.com/manual/en/coding-standard.html
 * @link        http://phpdoc.org/tutorial.php
 * @copyright   Copyright (c) 2008-2009, Code Actual LLC
 * @license     http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package     Hashmark
 * @subpackage  Agent
 * @version     $Id$
 */

/**
 * Free disk space inspector.
 *
 * Example code.
 *
 * @package     Hashmark
 * @subpackage  Agent
 */
class Hashmark_Agent_DiskFree implements Hashmark_Agent
{
    /**
     * @see Parent/interface signature docs.
     */
    public static function getName()
    {
        return 'Free disk space';
    }

    /**
     * @see Parent/interface signature docs.
     */
    public static function getDescription()
    {
        return 'Free bytes on partition /';
    }

    /**
     * @see Parent/interface signature docs.
     */
    public static function run(&$agent)
    {
        return disk_free_space('/');
    }
}
