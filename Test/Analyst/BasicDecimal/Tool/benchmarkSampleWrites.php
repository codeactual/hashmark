<?php
// vim: fenc=utf-8:ft=php:ai:si:ts=4:sw=4:et:

/**
 * Benchmark sample writes and preparatory steps.
 *
 * @filesource
 * @copyright   Copyright (c) 2008-2011, David Smith
 * @license     http://www.opensource.org/licenses/mit-license.php MIT License
 * @package     Hashmark-Test
 * @subpackage  Hashmark_Analyst_BasicDecimal
 * @version     $Id$
*/

/**
 * For Hashmark::getModule().
 */
require_once dirname(__FILE__) . '/../../../bootstrap.php';

/**
 * For hashmark_random_samples().
 */
require_once HASHMARK_ROOT_DIR . '/Test/Analyst/BasicDecimal/Tool/randomSamples.php';

define('HASHMARK_CREATESAMPLES_TYPE', 'decimal');
define('HASHMARK_CREATESAMPLES_SCALARS', 1);
define('HASHMARK_CREATESAMPLES_COUNT', 10000);
define('HASHMARK_CREATESAMPLES_STARTDATE', '2009-01-01 00:00:00 UTC');
define('HASHMARK_CREATESAMPLES_ENDDATE', '2009-01-01 23:59:59 UTC');

$db = Hashmark::getModule('DbHelper')->openDb('unittest');
$core = Hashmark::getModule('Core', '', $db);
$partition = $core->getModule('Partition');

$rndSampleTime = 0;
$createScalarTime = 0;
$createSampleTime = 0;
$totalSampleCnt = 0;

$startDatetime = gmdate(HASHMARK_DATETIME_FORMAT);

for ($scalars = 0; $scalars < HASHMARK_CREATESAMPLES_SCALARS; $scalars++) {
    $start = microtime(true);
    $samples = hashmark_random_samples(HASHMARK_CREATESAMPLES_TYPE,
                                       HASHMARK_CREATESAMPLES_STARTDATE,
                                       HASHMARK_CREATESAMPLES_ENDDATE,
                                       HASHMARK_CREATESAMPLES_COUNT);
    $end = microtime(true);
    $rndSampleTime += $end - $start;

    $scalarFields = array('type' => HASHMARK_CREATESAMPLES_TYPE,
                          'name' => Hashmark_Util::randomSha1());
    $start = microtime(true);
    $scalarId = $core->createScalar($scalarFields);
    $end = microtime(true);
    $createScalarTime += $end - $start;

    $start = microtime(true);
    $end = microtime(true);

    $sampleCnt = count($samples);
    $start = microtime(true);
    foreach ($samples as $timeData => $value) {
        list($time) = explode('=', $timeData);

        $partition->createSample($scalarId, $value, $time);
    }
    $end = microtime(true);
    $createSampleTime += $end - $start;

    $totalSampleCnt += $sampleCnt;
    echo "scalarId: {$scalarId}\n";
}

$rndSampleRate = sprintf('%0.4f', round(($totalSampleCnt / $rndSampleTime) * 60, 4));
$createScalarRate = sprintf('%0.4f', round(($totalSampleCnt / $createScalarTime) * 60, 4));
$createSampleRate = sprintf('%0.4f', round(($totalSampleCnt / $createSampleTime) * 60, 4));
$timerTotal = $rndSampleTime + $createSampleTime + $createSampleTime;
$timerTotal = sprintf('%0.4f', round($timerTotal, 4));

echo "\ntotalSampleCnt: {$totalSampleCnt} rows\n";
echo "timerTotal: {$timerTotal} secs\n";
echo "randomSampleRate: {$rndSampleRate} rows/min\n";
echo "createScalar: {$createScalarRate} rows/min\n";
echo "createSample: {$createSampleRate} rows/min\n";
