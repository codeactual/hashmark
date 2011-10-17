<?php
// vim: fenc=utf-8:ft=php:ai:si:ts=4:sw=4:et:

/**
 * Hashmark_Agent_ScalarValue
 *
 * @filesource
 * @copyright   Copyright (c) 2008-2011 David Smith
 * @license     http://www.opensource.org/licenses/mit-license.php MIT License
 * @package     Hashmark
 * @subpackage  Agent
 * @version     $Id$
 */

/**
 * Samples the scalar's current value.
 *
 * Enables a scalar to receive inline updates via set() or incr(), and only
 * be sampled periodically via cron.
 *
 * @package     Hashmark
 * @subpackage  Agent
 */
class Hashmark_Agent_ScalarValue implements Hashmark_Agent
{
    /**
     * @see Parent/interface signature docs.
     */
    public static function getName()
    {
        return 'Current value';
    }

    /**
     * @see Parent/interface signature docs.
     */
    public static function getDescription()
    {
        return 'Samples the scalar\'s current value';
    }

    /**
     * @see Parent/interface signature docs.
     */
    public static function run(&$agent)
    {
        if (empty($agent['scalar_id'])) {
            return;
        }

        $db = Hashmark::getModule('DbHelper')->openDb('cron');
        $client = Hashmark::getModule('Client', '', $db);
        $partition = Hashmark::getModule('Partition', '', $db);

        if (!$client) {
            return;
        }

        $value = $client->get((int) $agent['scalar_id']);

        $time = time();
        if (!$partition->createSample($agent['id'], $value, $time)) {
            $agent['error'] = sprintf('Could not save sample: start=%s end=%s value=%s',
                                      $start, $end, $value);
        }
    }
}
