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
 * @copyright   Copyright (c) 2008-2011 David Smith
 * @license     http://www.opensource.org/licenses/mit-license.php MIT License
 * @package     Hashmark-Test
 * @subpackage  Hashmark_Analyst_BasicDecimal
 * @version     $Id$
*/

if (!function_exists('bccomp')) {
    die("\nbcmath unavailable. Aborting.\n");
}

/**
 * Turns on logging/error-reporting, Hashmark, Hashmark_TestCase, etc.
 */
require_once dirname(__FILE__) . '/../../../bootstrap.php';

/**
 * Loads dependencies of most calc* generator scripts.
 */
require_once HASHMARK_ROOT_DIR . '/BcMath.php';
require_once HASHMARK_ROOT_DIR . '/Config/DbHelper.php';
require_once HASHMARK_ROOT_DIR . '/Analyst.php';
require_once HASHMARK_ROOT_DIR . '/Analyst/BasicDecimal.php';
require_once HASHMARK_ROOT_DIR . '/Test/Analyst/BasicDecimal.php';

$expProviders = array();
        
$required = array();
$required[] = HASHMARK_ROOT_DIR . '/Test/Analyst/BasicDecimal/Tool/calcValuesAggAtInterval.php';
$required[] = HASHMARK_ROOT_DIR . '/Test/Analyst/BasicDecimal/Tool/calcChanges.php';
$dependents = glob(HASHMARK_ROOT_DIR . '/Test/Analyst/BasicDecimal/Tool/calc*.php');
$sortedCalcFiles = array_unique(array_merge($required, $dependents));

foreach ($sortedCalcFiles as $calcFile) {
    // Ex. 'provideValuesAggAtInterval'.
    $calcName = str_replace('calc', 'provide', basename($calcFile, '.php'));

    // Each $calcFile defines a new $expValues.
    require_once $calcFile;

    $expProviders[$calcName] = $expValues;
}

file_put_contents(HASHMARK_ROOT_DIR . '/Test/Analyst/BasicDecimal/Data/provider.php',
                  "<?php\n\$expProviders = " . var_export($expProviders, true) . ';');
