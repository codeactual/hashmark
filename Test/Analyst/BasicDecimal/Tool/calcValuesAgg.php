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
