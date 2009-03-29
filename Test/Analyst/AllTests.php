<?php
// vim: fenc=utf-8:ft=php:ai:si:ts=4:sw=4:et:

/**
 * Hashmark_AllTests_Analyst
 *
 * @filesource
 * @link        http://code.google.com/p/hashmark/
 * @link        http://framework.zend.com/manual/en/coding-standard.html
 * @link        http://phpdoc.org/tutorial.php
 * @copyright   Copyright (c) 2008-2009, Code Actual LLC
 * @license     http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package     Hashmark-Test
 * @subpackage  Hashmark_Analyst
 * @version     $Id: AllTests.php 298 2009-02-13 05:19:37Z david $
*/

/**
 * Turns on logging/error-reporting, loads PHPUnit, etc.
 */
require_once dirname(__FILE__) . '/../bootstrap.php';

/**
 * @package     Hashmark-Test
 * @subpackage  Hashmark_Analyst
 */
class Hashmark_AllTests_Analyst
{
    /**
     * Auto-discover all tests.
     *
     * @return PHPUnit_Framework_TestSuite  Suite covering all implementations.
     */
    public static function suite()
    {
        $dirname = dirname(__FILE__);
        $suite = new PHPUnit_Framework_TestSuite(__METHOD__);
        
        // Hashmark_Analyst
        require_once $dirname . '/../../Analyst.php';
        
        foreach (glob($dirname . '/../../Analyst/*.php') as $typeFile) {
            $typeName = basename($typeFile, '.php');

            // Ex. class file for 'Hashmark_Analyst_BasicDecimal'
            require_once $dirname . '/../../Analyst/' . $typeName . '.php';
            // Ex. class file for 'Hashmark_TestCase_Analyst_BasicDecimal'
            require_once $dirname . '/' . $typeName . '.php';

            $suite->addTestSuite('Hashmark_TestCase_Analyst_' . $typeName);
        }

        return $suite;
    }
}
