<?php
// vim: fenc=utf-8?=php???=4?=4?:

/**
 * Shared SQL query templates.
 *
 *      -   :name macros will be escaped and quoted, ex. DATETIMEs.
 *      -   @name macros will not be escaped nor quoted, ex. SQL functions.
 *
 * @filesource
 * @link        http://code.google.com/p/hashmark/
 * @link        http://framework.zend.com/manual/en/coding-standard.html
 * @link        http://phpdoc.org/tutorial.php
 * @copyright   Copyright (c) 2008-2009, Code Actual LLC
 * @license     http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package     Hashmark
 * @subpackage  Sql
 * @version     $Id: Cron.php 280 2009-02-06 08:58:59Z david $
*/

$sql = array();

$sql['startJob'] = 'INSERT INTO `jobs` '
                 . '(`start`) '
                 . 'VALUES (UTC_TIMESTAMP())';

$sql['endJob'] = 'UPDATE `jobs` '
               . 'SET `end` = UTC_TIMESTAMP() '
               . 'WHERE `id` = ?';

/**
 *      -   Synchronize value from latest Cron-obtained value.
 *      -   Record time of sync.
 *      -   Reset any past sampler error to flag successful write.
 *      -   Flip sampler status from "Running" back to "Scheduled".
 *      -   Increment the count used to seed sample partition table AUTO_INCREMENT
 *          values for `id`.
 */
$sql['createSample:updateScalar'] = 'UPDATE `scalars` '
                                  . 'SET `value` = ?, '
                                  . '`last_sample_change` = ?, '
                                  . '`sampler_error` = "", '
                                  . '`sampler_status` = "Scheduled", '
                                  . '`sample_count` = `sample_count` + 1 '
                                  . 'WHERE `id` = ?';

$sql['createSample:insertSample'] = 'INSERT INTO ~samples '
                                  . '(`job_id`, `value`, `start`, `end`) '
                                  . 'VALUES (?, ?, ?, ?)';

$sql['getLatestSample'] = 'SELECT * FROM ~samples ORDER BY `id` DESC LIMIT 1';

$sql['setSamplerStatus'] = 'UPDATE `scalars` '
                         . 'SET `sampler_status` = ?, '
                         . '`sampler_error` = ? '
                         . 'WHERE `id` = ?';

// "Running" means it's scheduled but the last run didn't finish.
$statusMatch = '(`sampler_status` IN ("Scheduled", "Running"))';
// Recurrence interval has been reached.
$isDue = '(`last_sample_change` + INTERVAL `sampler_frequency` MINUTE <= UTC_TIMESTAMP())';
// Start date/time has been reached or none was specified.
$canStart = '(`sampler_start` = "' . HASHMARK_DATETIME_EMPTY . '" OR `sampler_start` <= UTC_TIMESTAMP())';
$hasNeverFinished = '`last_sample_change` = "' . HASHMARK_DATETIME_EMPTY . '"';

$sql['getScheduledSamplers'] = 'SELECT `id`, `sampler_handler`, `sampler_status` '
                            . 'FROM `scalars` '
                            . "WHERE {$statusMatch} "
                            . "AND ({$isDue} OR {$hasNeverFinished}) "
                            . "AND {$canStart} "
                            . 'AND `sampler_handler` != ""';
