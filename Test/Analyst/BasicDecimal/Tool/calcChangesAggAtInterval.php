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
 * @version     $Id$
*/

$expValues = array();

$sampleProviders = Hashmark_TestCase_Analyst_BasicDecimal::provideFullSamplesData();
$aggFunctions = Hashmark_TestCase_Analyst_BasicDecimal::getAggFunctions();

foreach ($sampleProviders as $sampleProviderName => $sampleProviderData) {
    // Skip providers like 'provideValuesSamples'.
    if (empty($sampleProviderData['interval'])) {
        continue;
    }
    
    $intervalFormat = Hashmark_Analyst_BasicDecimal::getIntervalPhpFormat($sampleProviderData['interval']);

    // Collect and sort in-range values.
    //  -   Value sets indexed by interval-based groups, e.g. '20080612' day group.
    $groupValues = array();
    foreach ($sampleProviderData['samples'] as $sample) {
        list($end, $value, , $isInRange) = $sample;

        if ($isInRange) {
            $groupName = gmdate($intervalFormat, strtotime($end . ' UTC'));

            if (!isset($groupValues[$groupName])) {
                $groupValues[$groupName] = array();
            }

            $groupValues[$groupName][$end] = $value;
        }
    }
    foreach ($groupValues as $groupName => $values) {
        ksort($groupValues[$groupName]);
    }

    // Collect value changes.
    $groupChanges = array();
    $lastValue = null;
    foreach ($groupValues as $groupName => $values) {
        foreach ($values as $currentValue) {
            if (!is_null($lastValue)) {
                if (!isset($groupChanges[$groupName])) {
                    $groupChanges[$groupName] = array();
                }
                $ch =bcsub($currentValue, $lastValue);
                $groupChanges[$groupName][] = bcsub($currentValue, $lastValue);
            }
            $lastValue = $currentValue;
        }
    }

    // Collect aggregates.
    $expValues[$sampleProviderName] = array('ALL' => array_fill_keys($aggFunctions, array()),
                                            'DIS' => array_fill_keys($aggFunctions, array()));
    foreach ($groupChanges as $groupName => $changes) {
        $distinctChanges = array_unique($changes);

        $expValues[$sampleProviderName]['ALL']['AVG'][$groupName] = Hashmark_BcMath::avg($changes);
        $expValues[$sampleProviderName]['DIS']['AVG'][$groupName] = Hashmark_BcMath::avg($distinctChanges);

        $expValues[$sampleProviderName]['ALL']['SUM'][$groupName] = Hashmark_BcMath::sum($changes);
        $expValues[$sampleProviderName]['DIS']['SUM'][$groupName] = Hashmark_BcMath::sum($distinctChanges);

        $expValues[$sampleProviderName]['ALL']['MAX'][$groupName] = Hashmark_BcMath::max($changes);
        $expValues[$sampleProviderName]['DIS']['MAX'][$groupName] = $expValues[$sampleProviderName]['ALL']['MAX'][$groupName];

        $expValues[$sampleProviderName]['ALL']['MIN'][$groupName] = Hashmark_BcMath::min($changes);
        $expValues[$sampleProviderName]['DIS']['MIN'][$groupName] = $expValues[$sampleProviderName]['ALL']['MIN'][$groupName];
    }
}
