<?php
// vim: fenc=utf-8:ft=php:ai:si:ts=4:sw=4:et:

/**
 * Hashmark_AllTests_Analyst
 *
 * @filesource
 * @copyright   Copyright (c) 2008-2011 David Smith
 * @license     http://www.opensource.org/licenses/mit-license.php MIT License
 * @package     Hashmark-Test
 * @subpackage  Hashmark_Analyst
 * @version     $Id$
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
        $suite = new PHPUnit_Framework_TestSuite(__METHOD__);
        
        // Hashmark_Analyst
        require_once HASHMARK_ROOT_DIR . '/Analyst.php';
        
        foreach (glob(HASHMARK_ROOT_DIR . '/Analyst/*.php') as $typeFile) {
            $typeName = basename($typeFile, '.php');

            // Ex. class file for 'Hashmark_Analyst_BasicDecimal'
            require_once $typeFile;
            // Ex. class file for 'Hashmark_TestCase_Analyst_BasicDecimal'
            require_once HASHMARK_ROOT_DIR . '/Test/Analyst/' . $typeName . '.php';

            $suite->addTestSuite('Hashmark_TestCase_Analyst_' . $typeName);
        }

        return $suite;
    }
}
