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

$recurFormats = Hashmark_Analyst_BasicDecimal::getRecurFormats();
$recurFunctions = array_keys($recurFormats);

$expValues = array();

foreach ($expProviders['provideChanges'] as $sampleProviderName => $changes) {
    $groupValues = array_fill_keys($recurFunctions, array());

    foreach ($changes as $end => $value) {
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

            // Ex. $groupValues['DAYOFMONTH']['22'][...] = $value;
            // $end is needed for sorting later.
            $groupValues[$recurFunc][$groupName][$end] = $value;
        }
    }

    $expValues[$sampleProviderName] = array('ALL' => array_fill_keys($recurFunctions, array()),
                                            'DIS' => array_fill_keys($recurFunctions, array()));

    foreach ($groupValues as $recurFunc => $recurValues) {
        foreach ($recurValues as $groupName => $values) {
            // Collect change aggregates.
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
