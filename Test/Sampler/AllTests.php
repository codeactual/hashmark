<?php
// vim: fenc=utf-8:ft=php:ai:si:ts=4:sw=4:et:

/**
 * Hashmark_AllTests_Sampler
 *
 * @filesource
 * @link        http://code.google.com/p/hashmark/
 * @link        http://framework.zend.com/manual/en/coding-standard.html
 * @link        http://phpdoc.org/tutorial.php
 * @copyright   Copyright (c) 2008-2009, Code Actual LLC
 * @license     http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package     Hashmark-Test
 * @subpackage  Hashmark_Sampler
 * @version     $Id: AllTests.php 263 2009-02-03 11:22:57Z david $
*/

/**
 * Turns on logging/error-reporting, loads PHPUnit, etc.
 */
require_once dirname(__FILE__) . '/../bootstrap.php';

/**
 * @package     Hashmark-Test
 * @subpackage  Hashmark_Sampler
 */
class Hashmark_AllTests_Sampler
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
        
        // Hashmark_Sampler
        require_once $dirname . '/../../Sampler.php';

        // Hashmark_TestCase_Sampler
        require_once $dirname . '/../Sampler.php';

        foreach (glob($dirname . '/*.php') as $typeFile) {
            $typeName = basename($typeFile, '.php');

            if ('AllTests' != $typeName) {
                // Ex. class file for 'Hashmark_Sampler_ScalarValue'
                require_once $dirname . '/../../Sampler/' . $typeName . '.php';
                // Ex. class file for 'Hashmark_TestCase_Sampler_ScalarValue'
                require_once $dirname . '/' . $typeName . '.php';

                $suite->addTestSuite('Hashmark_TestCase_Sampler_' . $typeName);
            }
        }

        return $suite;
    }
}
