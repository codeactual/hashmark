<?php
// vim: fenc=utf-8:ft=php:ai:si:ts=4:sw=4:et:

/**
 * Hashmark_Sampler_ScalarValue
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
 * Samples the scalar's current value.
 *
 * Enables a scalar to receive inline updates via set() or incr(), and only
 * be sampled periodically via cron.
 *
 * @package     Hashmark
 * @subpackage  Hashmark_Sampler
 */
class Hashmark_Sampler_ScalarValue extends Hashmark_Sampler
{
    /**
     * @see Abstract parent signature docs.
     */
    public static function getName()
    {
        return 'Current value';
    }

    /**
     * @see Abstract parent signature docs.
     */
    public static function getDescription()
    {
        return 'Samples the scalar\'s current value';
    }

    /**
     * @see Abstract parent signature docs.
     */
    public static function run($scalarId)
    {
        $db = Hashmark::getModule('DbHelper', HASHMARK_DBHELPER_DEFAULT_TYPE)->openDb('cron');
        $client = Hashmark::getModule('Client', '', $db);

        if (!$client) {
            return null;
        }

        return $client->get((int) $scalarId);
    }
}
