<?php
// vim: fenc=utf-8:ft=php:ai:si:ts=4:sw=4:et:

/**
 * Use bcmath to calculate expected expProviders['provideChanges'] for Test/Analyst/BasicDecimal/Data/provider.php.
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

$sampleProviders = Hashmark_TestCase_Analyst_BasicDecimal::provideFullSamplesData();

$expValues = array();

foreach ($expProviders['provideChanges'] as $sampleProviderName => $changes) {
    $distinctChanges = array_unique($changes);

    $expValues[$sampleProviderName] = array('ALL' => array(), 'DIS' => array());

    $expValues[$sampleProviderName]['ALL']['AVG'] = Hashmark_BcMath::avg($changes);
    $expValues[$sampleProviderName]['DIS']['AVG'] = Hashmark_BcMath::avg($distinctChanges);

    $expValues[$sampleProviderName]['ALL']['SUM'] = Hashmark_BcMath::sum($changes);
    $expValues[$sampleProviderName]['DIS']['SUM'] = Hashmark_BcMath::sum($distinctChanges);

    $expValues[$sampleProviderName]['ALL']['MAX'] = Hashmark_BcMath::max($changes);
    $expValues[$sampleProviderName]['DIS']['MAX'] = $expValues[$sampleProviderName]['ALL']['MAX'];

    $expValues[$sampleProviderName]['ALL']['MIN'] = Hashmark_BcMath::min($changes);
    $expValues[$sampleProviderName]['DIS']['MIN'] = $expValues[$sampleProviderName]['ALL']['MIN'];
}
