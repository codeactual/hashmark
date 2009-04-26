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

$sampleProviders = Hashmark_TestCase_Analyst_BasicDecimal::provideFullSamplesData();
$aggFunctions = Hashmark_TestCase_Analyst_BasicDecimal::getAggFunctions();

foreach ($sampleProviders as $sampleProviderName => $sampleProviderData) {
    $expValues[$sampleProviderName] = array('ALL' => array_fill_keys($aggFunctions, array()),
                                            'DIS' => array_fill_keys($aggFunctions, array()));

    $values = array();

    foreach ($sampleProviderData['samples'] as $sample) {
        list($end, $value, , $isInRange) = $sample;

        if (!$isInRange) {
            continue;
        }

        $values[] = $value;

        $distinctValues = array_unique($values);

        $expValues[$sampleProviderName]['ALL']['AVG'][$end] = Hashmark_BcMath::avg($values);
        $expValues[$sampleProviderName]['DIS']['AVG'][$end] = Hashmark_BcMath::avg($distinctValues);

        $expValues[$sampleProviderName]['ALL']['SUM'][$end] = Hashmark_BcMath::sum($values);
        $expValues[$sampleProviderName]['DIS']['SUM'][$end] = Hashmark_BcMath::sum($distinctValues);

        $expValues[$sampleProviderName]['ALL']['MAX'][$end] = Hashmark_BcMath::max($values);
        $expValues[$sampleProviderName]['DIS']['MAX'][$end] = $expValues[$sampleProviderName]['ALL']['MAX'][$end];

        $expValues[$sampleProviderName]['ALL']['MIN'][$end] = Hashmark_BcMath::min($values);
        $expValues[$sampleProviderName]['DIS']['MIN'][$end] = $expValues[$sampleProviderName]['ALL']['MIN'][$end];
    }
}
