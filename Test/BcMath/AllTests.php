<?php
// vim: fenc=utf-8:ft=php:ai:si:ts=4:sw=4:et:

/**
 * Hashmark_AllTests_BcMath
 *
 * @filesource
 * @copyright   Copyright (c) 2008-2011 David Smith
 * @license     http://www.opensource.org/licenses/mit-license.php MIT License
 * @package     Hashmark-Test
 * @subpackage  Hashmark_BcMath
 * @version     $Id$
*/

/**
 * Turns on logging/error-reporting, loads PHPUnit, etc.
 */
require_once dirname(__FILE__) . '/../bootstrap.php';

/**
 * @package     Hashmark-Test
 * @subpackage  Hashmark_BcMath
 */
class Hashmark_AllTests_BcMath
{
    /**
     * Auto-discover all tests.
     *
     * @return PHPUnit_Framework_TestSuite  Suite covering all implementations.
     */
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite(__METHOD__);

        // Hashmark_BcMath
        require_once HASHMARK_ROOT_DIR . '/BcMath.php';

        // Hashmark_TestCase_BcMath
        require_once HASHMARK_ROOT_DIR . '/Test/BcMath.php';

        $suite->addTestSuite('Hashmark_TestCase_BcMath');

        return $suite;
    }
}
