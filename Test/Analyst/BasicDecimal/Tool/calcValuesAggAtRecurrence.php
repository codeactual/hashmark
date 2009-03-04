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
 * @version     $Id: calcValuesAggAtRecurrence.php 299 2009-02-13 05:35:03Z david $
*/

$recurFormats = Hashmark_Analyst_BasicDecimal::getRecurFormats();
$recurFunctions = array_keys($recurFormats);
$sampleProviders = Hashmark_TestCase_Analyst_BasicDecimal::provideFullSamplesData();

$expValues = array();

foreach ($sampleProviders as $sampleProviderName => $sampleProviderData) {
    $groupValues = array_fill_keys($recurFunctions, array());

    foreach ($sampleProviderData['samples'] as $sample) {
        list($end, $value, , $isInRange) = $sample;

        if (!$isInRange) {
            continue;
        }

        // Group values by their recurrence, ex. DAYOFMONTH values.
        foreach ($recurFormats as $recurFunc => $format) {
            $groupName = gmdate($format, strtotime($end . ' UTC'));

            // Adjust for PHP/MySQL diff.
            if ('z' == $format) {
                $groupName += 1;
            }

            if (!isset($groupValues[$recurFunc][$groupName])) {
                $groupValues[$recurFunc][$groupName] = array();
            }

            // Ex. $groupValues['DAYOFMONTH']['22'] = $value;
            $groupValues[$recurFunc][$groupName][] = $value;
        }
    }

    $expValues[$sampleProviderName] = array('ALL' => array_fill_keys($recurFunctions, array()),
                                            'DIS' => array_fill_keys($recurFunctions, array()));

    foreach ($groupValues as $recurFunc => $recurValues) {
        foreach ($recurValues as $groupName => $values) {
            if (!isset($expValues[$sampleProviderName]['ALL'][$recurFunc][$groupName])) {
                $expValues[$sampleProviderName]['ALL'][$recurFunc][$groupName] = array();
            }
            if (!isset($expValues[$sampleProviderName]['DIS'][$recurFunc][$groupName])) {
                $expValues[$sampleProviderName]['DIS'][$recurFunc][$groupName] = array();
            }

            $distinctValues = array_unique($values);

            $expValues[$sampleProviderName]['ALL'][$recurFunc][$groupName]['AVG'] = Hashmark_BcMath::avg($values);
            $expValues[$sampleProviderName]['DIS'][$recurFunc][$groupName]['AVG'] = Hashmark_BcMath::avg($distinctValues);

            $expValues[$sampleProviderName]['ALL'][$recurFunc][$groupName]['SUM'] = Hashmark_BcMath::sum($values);
            $expValues[$sampleProviderName]['DIS'][$recurFunc][$groupName]['SUM'] = Hashmark_BcMath::sum($distinctValues);

            $expValues[$sampleProviderName]['ALL'][$recurFunc][$groupName]['MAX'] = Hashmark_BcMath::max($values);
            $expValues[$sampleProviderName]['DIS'][$recurFunc][$groupName]['MAX'] = $expValues[$sampleProviderName]['ALL'][$recurFunc][$groupName]['MAX'];

            $expValues[$sampleProviderName]['ALL'][$recurFunc][$groupName]['MIN'] = Hashmark_BcMath::min($values);
            $expValues[$sampleProviderName]['DIS'][$recurFunc][$groupName]['MIN'] = $expValues[$sampleProviderName]['ALL'][$recurFunc][$groupName]['MIN'];
        }
    }
}
