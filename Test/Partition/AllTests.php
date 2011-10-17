<?php
// vim: fenc=utf-8:ft=php:ai:si:ts=4:sw=4:et:

/**
 * Hashmark_AllTests_Partition
 *
 * @filesource
 * @copyright   Copyright (c) 2008-2011 David Smith
 * @license     http://www.opensource.org/licenses/mit-license.php MIT License
 * @package     Hashmark-Test
 * @subpackage  Hashmark_Partition
 * @version     $Id$
*/

/**
 * Turns on logging/error-reporting, loads PHPUnit, etc.
 */
require_once dirname(__FILE__) . '/../bootstrap.php';

/**
 * @package     Hashmark-Test
 * @subpackage  Hashmark_Partition
 */
class Hashmark_AllTests_Partition
{
    /**
     * Auto-discover all tests.
     *
     * @return PHPUnit_Framework_TestSuite  Suite covering all implementations.
     */
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite(__METHOD__);
        
        // Hashmark_Partition
        require_once HASHMARK_ROOT_DIR . '/Partition.php';

        // Hashmark_TestCase_Partition
        require_once HASHMARK_ROOT_DIR . '/Test/Partition.php';

        $suite->addTestSuite('Hashmark_TestCase_Partition');

        return $suite;
    }
}
