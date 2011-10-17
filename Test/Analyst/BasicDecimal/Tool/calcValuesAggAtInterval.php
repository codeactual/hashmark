<?php
// vim: fenc=utf-8:ft=php:ai:si:ts=4:sw=4:et:

/**
 * Use bcmath to calculate expected values for Test/Analyst/BasicDecimal/Data/provider.php.
 *
 * @filesource
 * @link        http://code.google.com/p/hashmark/
 * @link        http://framework.zend.com/manual/en/coding-standard.html
 * @link        http://phpdoc.org/tutorial.php
 * @copyright   Copyright (c) 2008-2011 David Smith
 * @license     http://www.opensource.org/licenses/mit-license.php MIT License
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

    // Value sets indexed by interval-based groups, e.g. '20080612' day group.
    $groupValues = array();

    foreach ($sampleProviderData['samples'] as $sample) {
        list($end, $value, , $isInRange) = $sample;

        if ($isInRange) {
            $groupName = gmdate($intervalFormat, strtotime($end . ' UTC'));

            if (!isset($groupValues[$groupName])) {
                $groupValues[$groupName] = array();
            }

            $groupValues[$groupName][] = $value;
        } 
    }

    $expValues[$sampleProviderName] = array('ALL' => array_fill_keys($aggFunctions, array()),
                                            'DIS' => array_fill_keys($aggFunctions, array()));

    // Ex. calculate aggregates for all '2010' or '20120722' values.
    foreach ($groupValues as $groupName => $values) {
        $distinctValues = array_unique($values);

        $expValues[$sampleProviderName]['ALL']['AVG'][$groupName] = Hashmark_BcMath::avg($values);
        $expValues[$sampleProviderName]['DIS']['AVG'][$groupName] = Hashmark_BcMath::avg($distinctValues);

        $expValues[$sampleProviderName]['ALL']['SUM'][$groupName] = Hashmark_BcMath::sum($values);
        $expValues[$sampleProviderName]['DIS']['SUM'][$groupName] = Hashmark_BcMath::sum($distinctValues);

        $expValues[$sampleProviderName]['ALL']['MAX'][$groupName] = Hashmark_BcMath::max($values);
        $expValues[$sampleProviderName]['DIS']['MAX'][$groupName] = $expValues[$sampleProviderName]['ALL']['MAX'][$groupName];

        $expValues[$sampleProviderName]['ALL']['MIN'][$groupName] = Hashmark_BcMath::min($values);
        $expValues[$sampleProviderName]['DIS']['MIN'][$groupName] = $expValues[$sampleProviderName]['ALL']['MIN'][$groupName];
    }
}
