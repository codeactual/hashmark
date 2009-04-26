<?php
// vim: fenc=utf-8:ft=php:ai:si:ts=4:sw=4:et:

/**
 * Hashmark_TestCase_DbHelper
 *
 * @filesource
 * @link        http://code.google.com/p/hashmark/
 * @link        http://framework.zend.com/manual/en/coding-standard.html
 * @link        http://phpdoc.org/tutorial.php
 * @copyright   Copyright (c) 2008-2009, Code Actual LLC
 * @license     http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package     Hashmark-Test
 * @subpackage  Base
 * @version     $Id$
*/

/**
 *      -   All test dates are in UTC.
 *
 * @package     Hashmark-Test
 * @subpackage  Base
 */
class Hashmark_TestCase_DbHelper extends Hashmark_TestCase
{
    /**
     * @test
     * @group DbHelper
     * @group opensDbConnection
     * @group openDb
     */
    public function opensDbConnection()
    {
        $dbHelper = Hashmark::getModule('DbHelper');
        $db = $dbHelper->openDb('unittest');
        $this->assertTrue(is_subclass_of($db, 'Zend_Db_Adapter_Abstract'));
    }

    /**
     * @test
     * @group DbHelper
     * @group reusesDbConnection
     * @group reuseDb
     */
    public function reusesDbConnection()
    {
        $config = Hashmark::getConfig('DbHelper');
        $link = new mysqli($config['profile']['unittest']['params']['host'],
                           $config['profile']['unittest']['params']['username'],
                           $config['profile']['unittest']['params']['password'],
                           $config['profile']['unittest']['params']['dbname'],
                           $config['profile']['unittest']['params']['port']);
        $db = Hashmark::getModule('DbHelper')->reuseDb($link, 'Mysqli');
        $this->assertEquals('mysqli', get_class($db->getConnection()));
    }
}
