<?php
// vim: fenc=utf-8:ft=php:ai:si:ts=4:sw=4:et:

/**
 * Hashmark_TestListener_Benchmark
 *
 * @filesource
 * @link        http://code.google.com/p/hashmark/
 * @link        http://framework.zend.com/manual/en/coding-standard.html
 * @link        http://phpdoc.org/tutorial.php
 * @copyright   Copyright (c) 2008-2011 David Smith
 * @license     http://www.opensource.org/licenses/mit-license.php MIT License
 * @package     Hashmark-Test
 * @subpackage  Listener
 * @version     $Id$
*/

/**
 * Stores test/suite run counts and total times.
 *
 * @package     Hashmark-Test
 * @subpackage  Listener
 */
class Hashmark_TestListener_Benchmark implements PHPUnit_Framework_TestListener
{
    /**
     * @var Array   Start microtimes indexed by suite name. 
     */
    protected static $suiteStarts = array();
    /**
     * @var float   Current test's starting microtime.
     */
    protected static $currentTestStart;

    /**
     * @var Array   Start microtimes indexed by suite name. 
     */
    protected static $suites = array();
    
    /**
     * @var Array   Assoc. of details about each test indexed by test name.
     *
     *      'totalTime':    Total microtime.
     *      'runs':         Test run count (for tests run on multiple
     *                      implementation classes, ex. DbHelper and Cache).
     */
    protected static $tests = array();

    /**
     * Start suite timers.
     */
    public function startTestSuite(PHPUnit_Framework_TestSuite $suite)
    {
        $name = $suite->getName();

        self::$suiteStarts[$name] = microtime(true);
        if (!isset(self::$suites[$name])) {
            self::$suites[$name] = array('runs' => 0, 'totalTime' => 0);
        }
    }

    /**
     * End suite timers.
     */
    public function endTestSuite(PHPUnit_Framework_TestSuite $suite)
    {
        $name = $suite->getName();

        self::$suites[$name]['runs']++;
        self::$suites[$name]['totalTime'] += microtime(true) - self::$suiteStarts[$name];
    }
    
    /**
     * Start test timers.
     */
    public function startTest(PHPUnit_Framework_Test $test)
    {
        self::$currentTestStart = microtime(true);

        $name = $test->getName();
        if (!isset(self::$tests[$name])) {
            self::$tests[$name] = array('runs' => 0, 'totalTime' => 0);
        }
    }

    /**
     * End test timers.
     */
    public function endTest(PHPUnit_Framework_Test $test, $time)
    {
        $name = $test->getName();

        self::$tests[$name]['runs']++;
        self::$tests[$name]['totalTime'] += microtime(true) - self::$currentTestStart;
    }

    /**
     * Public access to $suites and $tests.
     */
    public function getResults()
    {
        return array('suites' => self::$suites, 'tests' => self::$tests);
    }

    /**
     * Nothing to track.
     */
    public function addError(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
    }

    /**
     * Nothing to track.
     */
    public function addFailure(PHPUnit_Framework_Test $test, PHPUnit_Framework_AssertionFailedError $e, $time)
    {
    }

    /**
     * Nothing to track.
     */
    public function addIncompleteTest(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
    }

    /**
     * Nothing to track.
     */
    public function addSkippedTest(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
    }
}
