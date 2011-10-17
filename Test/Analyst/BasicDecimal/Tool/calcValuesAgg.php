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

$values = array();

$provideValuesSamples = Hashmark_TestCase_Analyst_BasicDecimal::provideFullSamplesData('provideValuesSamples');

foreach ($provideValuesSamples['samples'] as $sample) {
    list($end, $value, , $isInRange) = $sample;

    if ($isInRange) {
        $values[] = $value;
    }
}

$distinctValues = array_unique($values);

$expValues = array('ALL' => array(), 'DIS' => array());

$expValues['ALL']['AVG'] = Hashmark_BcMath::avg($values);
$expValues['DIS']['AVG'] = Hashmark_BcMath::avg($distinctValues);

$expValues['ALL']['SUM'] = Hashmark_BcMath::sum($values);
$expValues['DIS']['SUM'] = Hashmark_BcMath::sum($distinctValues);

$expValues['ALL']['MAX'] = Hashmark_BcMath::max($values);
$expValues['DIS']['MAX'] = $expValues['ALL']['MAX'];

$expValues['ALL']['MIN'] = Hashmark_BcMath::min($values);
$expValues['DIS']['MIN'] = $expValues['ALL']['MIN'];
