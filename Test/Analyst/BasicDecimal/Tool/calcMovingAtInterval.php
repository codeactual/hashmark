<?php
// vim: fenc=utf-8:ft=php:ai:si:ts=4:sw=4:et:

/**
 * Use bcmath to calculate expected valuesByEnd for Test/Analyst/BasicDecimal/Data/provider.php.
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

foreach ($expProviders['provideMoving'] as $sampleProviderName => $movingValues) {
    // Skip providers like 'provideValuesSamples'.
    if (empty($sampleProviders[$sampleProviderName]['interval'])) {
        continue;
    }
    
    $intervalFormat = Hashmark_Analyst_BasicDecimal::getIntervalPhpFormat($sampleProviders[$sampleProviderName]['interval']);
    
    $expValues[$sampleProviderName] = array();

    foreach ($movingValues as $allOrDistinct => $valuesByAgg) {
        foreach ($valuesByAgg as $aggFunc => $valuesByEnd) {
            $groupMax = array();
            foreach ($valuesByEnd as $end => $value) {
                $groupName = gmdate($intervalFormat, strtotime($end . ' UTC'));

                if (empty($groupMax[$groupName]) || $groupMax[$groupName] < $end) {
                    $expValues[$sampleProviderName][$allOrDistinct][$aggFunc][$groupName] = $value;
                    $groupMax[$groupName] = $end;
                }
            }
        }
    }
}
