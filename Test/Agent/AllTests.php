<?php
// vim: fenc=utf-8:ft=php:ai:si:ts=4:sw=4:et:

/**
 * Hashmark_AllTests_Agent
 *
 * @filesource
 * @copyright   Copyright (c) 2008-2011 David Smith
 * @license     http://www.opensource.org/licenses/mit-license.php MIT License
 * @package     Hashmark-Test
 * @subpackage  Hashmark_Agent
 * @version     $Id$
*/

/**
 * Turns on logging/error-reporting, loads PHPUnit, etc.
 */
require_once dirname(__FILE__) . '/../bootstrap.php';

/**
 * @package     Hashmark-Test
 * @subpackage  Hashmark_Agent
 */
class Hashmark_AllTests_Agent
{
    /**
     * Auto-discover all tests.
     *
     * @return PHPUnit_Framework_TestSuite  Suite covering all implementations.
     */
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite(__METHOD__);
        
        // Hashmark_Agent
        require_once HASHMARK_ROOT_DIR . '/Agent.php';
        
        // Hashmark_TestCase_Agent
        require_once HASHMARK_ROOT_DIR . '/Test/Agent.php';

        foreach (glob(HASHMARK_ROOT_DIR . '/Test/Agent/T*.php') as $typeTestFile) {
            $typeName = basename($typeTestFile, '.php');

            if ('AllTests' != $typeName) {
                // Ex. class file for 'Hashmark_Agent_ScalarValue'
                require_once HASHMARK_ROOT_DIR . '/Agent/' . $typeName . '.php';
                // Ex. class file for 'Hashmark_TestCase_Agent_ScalarValue'
                require_once $typeTestFile;

                $suite->addTestSuite('Hashmark_TestCase_Agent_' . $typeName);
            }
        }

        return $suite;
    }
}
