<?php
// vim: fenc=utf-8:ft=php:ai:si:ts=4:sw=4:et:

/**
 * Benchmark all tests or a single module.
 *
 * php -f BenchmarkAllTests [module base name]
 *
 * Ex. php -f BenchmarkAllTests Core
 *
 * @filesource
 * @link        http://code.google.com/p/hashmark/
 * @link        http://framework.zend.com/manual/en/coding-standard.html
 * @link        http://phpdoc.org/tutorial.php
 * @copyright   Copyright (c) 2008-2009, Code Actual LLC
 * @license     http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package     Hashmark-Test
 * @version     $Id: BenchmarkAllTests.php 275 2009-02-04 15:05:06Z david $
*/

$dirname = dirname(__FILE__);

// Only use one module suite.
if (2 == $argc) {
    $moduleSuiteFile = $dirname . '/' . $argv[1] . '/AllTests.php';
    if (is_readable($moduleSuiteFile)) {
        /**
         * Load script in prep. for addTestSuite().
         */
        require_once $moduleSuiteFile;
        $suiteName = 'Hashmark_AllTests_' . $argv[1];
    }
} 

if (!isset($suiteName)) {
    require_once $dirname . '/AllTests.php';
    $suiteName = 'Hashmark_AllTests';
}

/**
 * For Hashmark_TestListener_Benchmark static data/methods.
 */
require_once $dirname . '/Listener/Benchmark.php';

define('HASHMARK_BENCHMARK_PRECISION', 4);
define('HASHMARK_BENCHMARK_RANKLIMIT', 15);

$suite = new PHPUnit_Framework_TestSuite('Hashmark Test Benchmark');
$suite->addTestSuite($suiteName);
$result = new PHPUnit_Framework_TestResult();
$result->addListener(new Hashmark_TestListener_Benchmark());
$suite->run($result);

$results = Hashmark_TestListener_Benchmark::getResults();

$totalTime = 0;

$sortedSuites = array();
foreach ($results['suites'] as $name => $stats) {
    // Skip wrappers.
    if ($name != 'Hashmark Test Benchmark' && false === strpos($name, '::suite')) {
        // Use average for tests are ran on multiple implementation classes.
        $sortedSuites[$name] = $stats['totalTime'] / $stats['runs'];
        $totalTime += $stats['totalTime'];
    }
}
arsort($sortedSuites);

echo "\nTop Suites\n\n";
foreach (array_slice($sortedSuites, 0, HASHMARK_BENCHMARK_RANKLIMIT) as $name => $avg) {
    $avg = sprintf('%0.' . HASHMARK_BENCHMARK_PRECISION . 'f', round($avg, HASHMARK_BENCHMARK_PRECISION));
    echo "{$name}: {$avg}\n";
}

$sortedTests = array();
foreach ($results['tests'] as $name => $stats) {
    // Use average for tests are ran on multiple implementation classes.
    $sortedTests[$name] = $stats['totalTime'] / $stats['runs'];
}
arsort($sortedTests);

echo "\nTop Tests (suites combined)\n\n";
foreach (array_slice($sortedTests, 0, HASHMARK_BENCHMARK_RANKLIMIT) as $name => $avg) {
    $avg = sprintf('%0.' . HASHMARK_BENCHMARK_PRECISION . 'f', round($avg, HASHMARK_BENCHMARK_PRECISION));
    echo "{$name}: {$avg}\n";
}
