<?php
// vim: fenc=utf-8:ft=php:ai:si:ts=4:sw=4:et:

/**
 * Hashmark_AllTests_Client
 *
 * @filesource
 * @copyright   Copyright (c) 2008-2011 David Smith
 * @license     http://www.opensource.org/licenses/mit-license.php MIT License
 * @package     Hashmark-Test
 * @subpackage  Hashmark_Client
 * @version     $Id$
*/

/**
 * Turns on logging/error-reporting, loads PHPUnit, etc.
 */
require_once dirname(__FILE__) . '/../bootstrap.php';

/**
 * @package     Hashmark-Test
 * @subpackage  Hashmark_Client
 */
class Hashmark_AllTests_Client
{
    /**
     * Auto-discover all tests.
     *
     * @return PHPUnit_Framework_TestSuite  Suite covering all implementations.
     */
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite(__METHOD__);
        
        // Hashmark_Client
        require_once HASHMARK_ROOT_DIR . '/Client.php';

        // Hashmark_TestCase_Client
        require_once HASHMARK_ROOT_DIR . '/Test/Client.php';

        $suite->addTestSuite('Hashmark_TestCase_Client');

        return $suite;
    }
}
