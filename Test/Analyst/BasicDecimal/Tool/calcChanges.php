<?php
// vim: fenc=utf-8:ft=php:ai:si:ts=4:sw=4:et:

/**
 * Use bcmath to calculate expected values for Test/Analyst/BasicDecimal/Data/provider.php.
 *
 * @filesource
 * @link        http://code.google.com/p/hashmark/
 * @link        http://framework.zend.com/manual/en/coding-standard.html
 * @link        http://phpdoc.org/tutorial.php
 * @copyright   Copyright (c) 2008-2009, Code Actual LLC
 * @license     http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package     Hashmark-Test
 * @subpackage  Hashmark_Analyst_BasicDecimal
 * @version     $Id: calcChanges.php 298 2009-02-13 05:19:37Z david $
*/

$sampleProviders = Hashmark_TestCase_Analyst_BasicDecimal::provideFullSamplesData();

$expValues = array();

foreach ($sampleProviders as $sampleProviderName => $sampleProviderData) {
    // Collect and sort in-range values.
    $inRangeSamples = array();
    foreach ($sampleProviderData['samples'] as $sample) {
        list($end, $value, , $isInRange) = $sample;

        if ($isInRange) {
            $inRangeSamples[$end] = $value;
        }
    }
    ksort($inRangeSamples);

    // Collect value changes.
    $expValues[$sampleProviderName] = array();
    $lastValue = null;
    foreach ($inRangeSamples as $end => $value) {
        if (!is_null($lastValue)) {
            $expValues[$sampleProviderName][$end] = bcsub($value, $lastValue);
        }
        $lastValue = $value;
    }
}
