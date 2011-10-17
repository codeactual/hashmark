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

$aggFunctions = Hashmark_TestCase_Analyst_BasicDecimal::getAggFunctions();

foreach ($expProviders['provideChangesAggAtInterval'] as $sampleProviderName => $providerAggs) {
    $expValues[$sampleProviderName] = array('ALL' => array_fill_keys($aggFunctions, array()),
                                            'DIS_INNER' => array_fill_keys($aggFunctions, array()),
                                            'DIS_OUTER' => array_fill_keys($aggFunctions, array()),
                                            'DIS_BOTH' => array_fill_keys($aggFunctions, array()));


    foreach ($aggFunctions as $aggFuncInner) { 
        $distinctOuterValues = array_unique($providerAggs['ALL'][$aggFuncInner]);
        $distinctBothValues = array_unique($providerAggs['DIS'][$aggFuncInner]);

        $expValues[$sampleProviderName]['ALL']['AVG'][$aggFuncInner] = Hashmark_BcMath::avg($providerAggs['ALL'][$aggFuncInner]);
        $expValues[$sampleProviderName]['DIS_INNER']['AVG'][$aggFuncInner] = Hashmark_BcMath::avg($providerAggs['DIS'][$aggFuncInner]);
        $expValues[$sampleProviderName]['DIS_OUTER']['AVG'][$aggFuncInner] = Hashmark_BcMath::avg($distinctOuterValues);
        $expValues[$sampleProviderName]['DIS_BOTH']['AVG'][$aggFuncInner] = Hashmark_BcMath::avg($distinctBothValues);
        
        $expValues[$sampleProviderName]['ALL']['SUM'][$aggFuncInner] = Hashmark_BcMath::sum($providerAggs['ALL'][$aggFuncInner]);
        $expValues[$sampleProviderName]['DIS_INNER']['SUM'][$aggFuncInner] = Hashmark_BcMath::sum($providerAggs['DIS'][$aggFuncInner]);
        $expValues[$sampleProviderName]['DIS_OUTER']['SUM'][$aggFuncInner] = Hashmark_BcMath::sum($distinctOuterValues);
        $expValues[$sampleProviderName]['DIS_BOTH']['SUM'][$aggFuncInner] = Hashmark_BcMath::sum($distinctBothValues);
        
        $expValues[$sampleProviderName]['ALL']['MAX'][$aggFuncInner] = Hashmark_BcMath::max($providerAggs['ALL'][$aggFuncInner]);
        $expValues[$sampleProviderName]['DIS_INNER']['MAX'][$aggFuncInner] = Hashmark_BcMath::max($providerAggs['DIS'][$aggFuncInner]);
        $expValues[$sampleProviderName]['DIS_OUTER']['MAX'][$aggFuncInner] = Hashmark_BcMath::max($distinctOuterValues);
        $expValues[$sampleProviderName]['DIS_BOTH']['MAX'][$aggFuncInner] = Hashmark_BcMath::max($distinctBothValues);
        
        $expValues[$sampleProviderName]['ALL']['MIN'][$aggFuncInner] = Hashmark_BcMath::min($providerAggs['ALL'][$aggFuncInner]);
        $expValues[$sampleProviderName]['DIS_INNER']['MIN'][$aggFuncInner] = Hashmark_BcMath::min($providerAggs['DIS'][$aggFuncInner]);
        $expValues[$sampleProviderName]['DIS_OUTER']['MIN'][$aggFuncInner] = Hashmark_BcMath::min($distinctOuterValues);
        $expValues[$sampleProviderName]['DIS_BOTH']['MIN'][$aggFuncInner] = Hashmark_BcMath::min($distinctBothValues);
    }
}
