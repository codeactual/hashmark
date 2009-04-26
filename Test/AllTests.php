<?php
// vim: fenc=utf-8:ft=php:ai:si:ts=4:sw=4:et:

/**
 * Hashmark_AllTests
 *
 * @filesource
 * @link        http://code.google.com/p/hashmark/
 * @link        http://framework.zend.com/manual/en/coding-standard.html
 * @link        http://phpdoc.org/tutorial.php
 * @copyright   Copyright (c) 2008-2009, Code Actual LLC
 * @license     http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package     Hashmark-Test
 * @version     $Id: AllTests.php 296 2009-02-13 05:03:11Z david $
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

        $dirname = dirname(__FILE__);

        $required = array();
        $required[] = $dirname . '/BcMath/AllTests.php';
        $required[] = $dirname . '/Assert/AllTests.php';
        $required[] = $dirname . '/Hashmark/AllTests.php';
        $required[] = $dirname . '/Module/AllTests.php';
        $required[] = $dirname . '/DbHelper/AllTests.php';
        $required[] = $dirname . '/Cache/AllTests.php';
        $dependents = glob($dirname . '/*/AllTests.php');
        $sortedTestFiles = array_unique(array_merge($required, $dependents));

        foreach ($sortedTestFiles as $testTypeFile) {
            require_once $testTypeFile;

            // Ex. 'Hashmark_AllTests_Analyst'
            $suite->addTestSuite('Hashmark_AllTests_' . basename(dirname($testTypeFile)));
        }

        return $suite;
    }
}
