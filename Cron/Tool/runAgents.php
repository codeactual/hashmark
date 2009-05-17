<?php
// vim: fenc=utf-8:ft=php:ai:si:ts=4:sw=4:et:

/**
 * Run scheduled agents.
 *
 * @filesource
 * @link        http://code.google.com/p/hashmark/
 * @link        http://framework.zend.com/manual/en/coding-standard.html
 * @link        http://phpdoc.org/tutorial.php
 * @copyright   Copyright (c) 2008-2009, Code Actual LLC
 * @license     http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package     Hashmark
 * @subpackage  Cron
 * @version     $Id$
*/

/**
 * For getModule().
 */
require_once dirname(__FILE__) . '/../../Hashmark.php';

$db = Hashmark::getModule('DbHelper')->openDb('cron');
$cron = Hashmark::getModule('Cron', '', $db);

$scheduledAgents = $cron->getScheduledAgents();
if (empty($scheduledAgents)) {
    exit;
}

// Reuse previously loaded agent objects since they have no properties.
$cache = array();

foreach ($scheduledAgents as $scalarAgent) {
    if ('Running' == $scalarAgent['status']) {
        $cron->setScalarAgentStatus($scalarAgent['id'], 'Unscheduled',
                              'Last sample did not finish.');
        continue;
    }

    if (!isset($cache[$scalarAgent['name']])) {
        try {
            $cache[$scalarAgent['name']] = Hashmark::getModule('Agent',
                                                               $scalarAgent['name']);
        } catch (Exception $e) {
            $error = sprintf('Agent "%s" module missing: %s',
                             $scalarAgent['name'], $e->getMessage());
            $cron->setScalarAgentStatus($scalarAgent['id'], 'Unscheduled', $error);
            continue;
        }
    }

    if (!$cache[$scalarAgent['name']]) {
        $error = "Agent '{$scalarAgent['name']}' was missing";
        $cron->setScalarAgentStatus($scalarAgent['id'], 'Unscheduled', $error);
        continue;
    }

    $cron->setScalarAgentStatus($scalarAgent['id'], 'Running');

    // It's OK if $start and $end are the same.
    $start = time();
    $value = $cache[$scalarAgent['name']]->run($scalarAgent);
    $end = time();

    $cron->setScalarAgentStatus($scalarAgent['id'], 'Scheduled', '', $end);

    if (is_null($value)) {
        $error = "Agent '{$scalarAgent['name']}' could not finish";
        $cron->setScalarAgentStatus($scalarAgent['id'], 'Unscheduled', $error);
        continue;
    }

    if (!$cron->createSample($scalarAgent['id'], $value, $start, $end)) {
        $error = sprintf('Could not save sample: start=%s end=%s value=%s',
                         $start, $end, $value);
        $cron->setScalarAgentStatus($scalarAgent['id'], 'Scheduled', $error);
    }
}
