<?php
// vim: fenc=utf-8:ft=php:ai:si:ts=4:sw=4:et:

/**
 * Hashmark_AllTests_Module
 *
 * @filesource
 * @link        http://code.google.com/p/hashmark/
 * @link        http://framework.zend.com/manual/en/coding-standard.html
 * @link        http://phpdoc.org/tutorial.php
 * @copyright   Copyright (c) 2008-2011 David Smith
 * @license     http://www.opensource.org/licenses/mit-license.php MIT License
 * @package     Hashmark-Test
 * @subpackage  Hashmark_Module
 * @version     $Id$
*/

/**
 * Turns on logging/error-reporting, loads PHPUnit, etc.
 */
require_once dirname(__FILE__) . '/../bootstrap.php';

/**
 * @package     Hashmark-Test
 * @subpackage  Hashmark_Module
 */
class Hashmark_AllTests_Module
{
    /**
     * Auto-discover all tests.
     *
     * @return PHPUnit_Framework_TestSuite  Suite covering all implementations.
     */
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite(__METHOD__);

        // Hashmark_Module
        require_once HASHMARK_ROOT_DIR . '/Module.php';

        // Hashmark_TestCase_Module
        require_once HASHMARK_ROOT_DIR . '/Test/Module.php';

        foreach (glob(HASHMARK_ROOT_DIR . '/Module/*.php') as $typeFile) {
            $typeName = basename($typeFile, '.php');

            // Ex. class file for 'Hashmark_Module_DbDependent'
            require_once $typeFile;
            // Ex. class file for 'Hashmark_TestCase_Module_DbDependent'
            require_once HASHMARK_ROOT_DIR . '/Test/Module/' . $typeName . '.php';

            $suite->addTestSuite('Hashmark_TestCase_Module_' . $typeName);
        }

        return $suite;
    }
}
