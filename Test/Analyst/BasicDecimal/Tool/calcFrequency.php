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
 * @version     $Id: calcFrequency.php 298 2009-02-13 05:19:37Z david $
*/

$expValues = array();

foreach (Hashmark_TestCase_Analyst_BasicDecimal::provideFullSamplesData() as $sampleProviderName => $sampleProviderData) {
    $expValues[$sampleProviderName] = array();

    foreach ($sampleProviderData['samples'] as $sample) {
        list(, $value, , $isInRange) = $sample;

        if ($isInRange) {
            if (!isset($expValues[$sampleProviderName][$value])) {
                $expValues[$sampleProviderName][$value] = 0;
            }

            $expValues[$sampleProviderName][$value]++;
        }
    }
}
