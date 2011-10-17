<?php
// vim: fenc=utf-8:ft=php:ai:si:ts=4:sw=4:et:

/**
 * Use bcmath to calculate expected values for Test/Analyst/BasicDecimal/Data/provider.php.
 *
 * @filesource
 * @copyright   Copyright (c) 2008-2011 David Smith
 * @license     http://www.opensource.org/licenses/mit-license.php MIT License
 * @package     Hashmark-Test
 * @subpackage  Hashmark_Analyst_BasicDecimal
 * @version     $Id$
*/

$sampleProviders = Hashmark_TestCase_Analyst_BasicDecimal::provideFullSamplesData();

$expValues = array();

foreach ($sampleProviders as $sampleProviderName => $providerData) {
    // Skip providers like 'provideValuesSamples'.
    if (empty($providerData['interval'])) {
        continue;
    }
    
    $intervalFormat = Hashmark_Analyst_BasicDecimal::getIntervalPhpFormat($sampleProviders[$sampleProviderName]['interval']);

    // Collect most recent values of each interval.
    $valuesAtInterval = array();
    foreach ($providerData['samples'] as $sample) {
        list($end, $value, $isMostRecentInInterval) = $sample;

        if ($isMostRecentInInterval) {
            $groupName = gmdate($intervalFormat, strtotime($end . ' UTC'));
            $valuesAtInterval[$groupName] = $value;
        }
    }
    ksort($valuesAtInterval);
    
    $expValues[$sampleProviderName] = array();

    // Collect value changes.
    $lastValue = null;
    foreach ($valuesAtInterval as $groupName => $value) {
        if (!is_null($lastValue)) {
            if (!isset($expValues[$sampleProviderName][$groupName])) {
                $expValues[$sampleProviderName][$groupName] = array();
            }
            $expValues[$sampleProviderName][$groupName] = bcsub($value, $lastValue);
        }
        $lastValue = $value;
    }
}
