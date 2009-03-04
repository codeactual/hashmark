<?php
// vim: fenc=utf-8:ft=php:ai:si:ts=4:sw=4:et:

/**
 * Run scheduled samplers.
 *
 * @filesource
 * @link        http://code.google.com/p/hashmark/
 * @link        http://framework.zend.com/manual/en/coding-standard.html
 * @link        http://phpdoc.org/tutorial.php
 * @copyright   Copyright (c) 2008-2009, Code Actual LLC
 * @license     http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package     Hashmark
 * @subpackage  Cron
 * @version     $Id: runSamplers.php 294 2009-02-13 03:48:59Z david $
*/

/**
 * For getModule().
 */
require_once dirname(__FILE__) . '/../../Hashmark.php';

$db = Hashmark::getModule('DbHelper', HASHMARK_DBHELPER_DEFAULT_TYPE)->openDb('cron');
$cron = Hashmark::getModule('Cron', '', $db);

$scheduledScalars = $cron->getScheduledSamplers();
if (empty($scheduledScalars)) {
    exit;
}

$jobId = $cron->startJob();

// Reuse previously loaded sampler objects since they have no properties.
$cache = array();

foreach ($scheduledScalars as $scalar) {
    if ('Running' == $scalar['sampler_status']) {
        $cron->setSamplerStatus($scalar['id'], 'Unscheduled', 'Last sample did not finish.');
        continue;
    }

    if (!isset($cache[$scalar['sampler_handler']])) {
        try {
            $cache[$scalar['sampler_handler']] = Hashmark::getModule('Sampler', $scalar['sampler_handler']);
        } catch (Exception $e) {
            $error = "Sampler unavailable: " . $e->getMessage();
            $cron->setSamplerStatus($scalar['id'], 'Unscheduled', $error);
            continue;
        }
    }

    if (!$cache[$scalar['sampler_handler']]) {
        $cron->setSamplerStatus($scalar['id'], 'Unscheduled',
                                "Sampler '{$scalar['sampler_handler']}' was missing.");
        continue;
    }

    $cron->setSamplerStatus($scalar['id'], 'Running');

    // It's OK if $start and $end are the same.
    $start = time();
    $value = $cache[$scalar['sampler_handler']]->run($scalar['id']);
    $end = time();

    $cron->setSamplerStatus($scalar['id'], 'Scheduled');

    if (is_null($value)) {
        $cron->setSamplerStatus($scalar['id'], 'Unscheduled',
                                "Sampler '{$scalar['sampler_handler']}' could not finish.");
        continue;
    }

    if (!$cron->createSample($scalar['id'], $jobId, $value, $start, $end)) {
        $cron->setSamplerStatus($scalar['id'], 'Scheduled',
                                "Could not save sample: j={$jobId} st={$start} en={$end} v={$value}");
    }
}

$cron->endJob($jobId);
