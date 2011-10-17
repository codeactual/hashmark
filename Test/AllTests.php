<?php
// vim: fenc=utf-8:ft=php:ai:si:ts=4:sw=4:et:

/**
 * Hashmark_AllTests
 *
 * @filesource
 * @link        http://code.google.com/p/hashmark/
 * @link        http://framework.zend.com/manual/en/coding-standard.html
 * @link        http://phpdoc.org/tutorial.php
 * @copyright   Copyright (c) 2008-2011 David Smith
 * @license     http://www.opensource.org/licenses/mit-license.php MIT License
 * @package     Hashmark-Test
 * @version     $Id$
*/

/**
 * Turns on logging/error-reporting, loads PHPUnit, etc.
 */
require_once dirname(__FILE__) . '/bootstrap.php';

/**
 * Run all suites from each discovered test type, ex. Core, Client, etc.
 *
 * @package     Hashmark-Test
 */
class Hashmark_AllTests
{
    /**
     * Auto-discover test types.
     *
     * @return void
     */
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('Hashmark - All Tests');

        $required = array();
        $required[] = HASHMARK_ROOT_DIR . '/Test/BcMath/AllTests.php';
        $required[] = HASHMARK_ROOT_DIR . '/Test/Assert/AllTests.php';
        $required[] = HASHMARK_ROOT_DIR . '/Test/Hashmark/AllTests.php';
        $required[] = HASHMARK_ROOT_DIR . '/Test/Module/AllTests.php';
        $required[] = HASHMARK_ROOT_DIR . '/Test/DbHelper/AllTests.php';
        $required[] = HASHMARK_ROOT_DIR . '/Test/Cache/AllTests.php';
        $dependents = glob(HASHMARK_ROOT_DIR . '/Test/*/AllTests.php');
        $sortedTestFiles = array_unique(array_merge($required, $dependents));

        foreach ($sortedTestFiles as $testTypeFile) {
            require_once $testTypeFile;

            // Ex. 'Hashmark_AllTests_Analyst'
            $suite->addTestSuite('Hashmark_AllTests_' . basename(dirname($testTypeFile)));
        }

        return $suite;
    }
}
