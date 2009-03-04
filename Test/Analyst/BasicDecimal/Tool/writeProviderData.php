<?php
// vim: fenc=utf-8:ft=php:ai:si:ts=4:sw=4:et:

/**
 * Collects test data from ./calc* scripts. Writes collection to
 * HASHMARK_ANALYST_TEST_PROVIDER_FILE for reads by test cases.
 *
 * @filesource
 * @link        http://code.google.com/p/hashmark/
 * @link        http://framework.zend.com/manual/en/coding-standard.html
 * @link        http://phpdoc.org/tutorial.php
 * @copyright   Copyright (c) 2008-2009, Code Actual LLC
 * @license     http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package     Hashmark-Test
 * @subpackage  Hashmark_Analyst_BasicDecimal
 * @version     $Id: writeProviderData.php 299 2009-02-13 05:35:03Z david $
*/

if (!function_exists('bccomp')) {
    die("\nbcmath unavailable. Aborting.\n");
}

$dirname = dirname(__FILE__);

/**
 * Turns on logging/error-reporting, Hashmark, Hashmark_TestCase, etc.
 */
require_once $dirname . '/../../../bootstrap.php';

/**
 * Loads dependencies of most calc* generator scripts.
 */
require_once $dirname . '/../../../../BcMath.php';
require_once $dirname . '/../../../../Config/DbHelper.php';
require_once $dirname . '/../../../../Analyst.php';
require_once $dirname . '/../../../../Analyst/BasicDecimal.php';
require_once $dirname . '/../../../../Test/Analyst/BasicDecimal.php';

$expProviders = array();
        
$required = array();
$required[] = $dirname . '/calcValuesAggAtInterval.php';
$required[] = $dirname . '/calcChanges.php';
$dependents = glob($dirname . '/calc*.php');
$sortedCalcFiles = array_unique(array_merge($required, $dependents));

foreach ($sortedCalcFiles as $calcFile) {
    // Ex. 'provideValuesAggAtInterval'.
    $calcName = str_replace('calc', 'provide', basename($calcFile, '.php'));

    // Each $calcFile defines a new $expValues.
    require_once $calcFile;

    $expProviders[$calcName] = $expValues;
}

file_put_contents($dirname . '/../Data/provider.php',
                  "<?php\n\$expProviders = " . var_export($expProviders, true) . ';');
