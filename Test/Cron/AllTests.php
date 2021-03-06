<?php
// vim: fenc=utf-8:ft=php:ai:si:ts=4:sw=4:et:

/**
 * Hashmark_AllTests_Cron
 *
 * @filesource
 * @copyright   Copyright (c) 2008-2011 David Smith
 * @license     http://www.opensource.org/licenses/mit-license.php MIT License
 * @package     Hashmark-Test
 * @subpackage  Cron
 * @version     $Id$
*/

/**
 * Turns on logging/error-reporting, loads PHPUnit, etc.
 */
require_once dirname(__FILE__) . '/../bootstrap.php';

/**
 * @package     Hashmark-Test
 * @subpackage  Cron
 */
class Hashmark_AllTests_Cron
{
    /**
     * Auto-discover all tests.
     *
     * @return PHPUnit_Framework_TestSuite  Suite covering all implementations.
     */
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite(__METHOD__);
        
        // Hashmark_TestCase_Cron
        require_once HASHMARK_ROOT_DIR . '/Test/Cron.php';

        foreach (glob(HASHMARK_ROOT_DIR . '/Test/Cron/*.php') as $typeTestFile) {
            $typeName = basename($typeTestFile, '.php');

            if ('AllTests' != $typeName) {
                // Ex. test file for Cron/gcMergeTables.php
                require_once $typeTestFile;

                $suite->addTestSuite('Hashmark_TestCase_Cron_' . $typeName);
            }
        }

        return $suite;
    }
}
