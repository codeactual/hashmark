<?php
// vim: fenc=utf-8:ft=php:ai:si:ts=4:sw=4:et:

/**
 * Hashmark_Sampler_DiskFree
 *
 * @filesource
 * @link        http://code.google.com/p/hashmark/
 * @link        http://framework.zend.com/manual/en/coding-standard.html
 * @link        http://phpdoc.org/tutorial.php
 * @copyright   Copyright (c) 2008-2009, Code Actual LLC
 * @license     http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package     Hashmark
 * @subpackage  Hashmark_Sampler
 * @version     $Id$
 */

/**
 * Free disk space sampler.
 *
 * Example code.
 *
 * @package     Hashmark
 * @subpackage  Hashmark_Sampler
 */
class Hashmark_Sampler_DiskFree extends Hashmark_Sampler
{
    /**
     * @see Abstract parent signature docs.
     */
    public static function getName()
    {
        return 'Free disk space';
    }

    /**
     * @see Abstract parent signature docs.
     */
    public static function getDescription()
    {
        return 'Free bytes on partition /';
    }

    /**
     * @see Abstract parent signature docs.
     */
    public static function run($params = array())
    {
        return disk_free_space('/');
    }
}
