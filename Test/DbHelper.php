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
 * @version     $Id: DbHelper.php 296 2009-02-13 05:03:11Z david $
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
     * @group opensAndClosesDbConnection
     * @group openDb
     * @group closeDb
     */
    public function opensAndClosesDbConnection()
    {
        $dbHelper = Hashmark::getModule('DbHelper', HASHMARK_DBHELPER_DEFAULT_TYPE);
        $db = $dbHelper->openDb('unittest');
        $this->assertFalse(empty($db));
        $this->assertTrue($dbHelper->closeDb($db));
    }

    /**
     * @test
     * @group DbHelper
     * @group throwsOnQueryError
     * @group query
     */
    public function throwsOnQueryError()
    {
        $dbHelper = Hashmark::getModule('DbHelper', HASHMARK_DBHELPER_DEFAULT_TYPE);
        $thrown = false;

        try {
            $dbHelper->query($db, 'SELECT NOW_TYPO()');
        } catch (Exception $e) {
            $thrown = true;
        }

        $this->assertTrue($thrown);
    }

    /**
     * @test
     * @group DbHelper
     * @group throwsOnMissingLink
     * @group rawQuery
     * @group escape
     */
    public function throwsOnMissingLink()
    {
        $dbHelper = Hashmark::getModule('DbHelper', HASHMARK_DBHELPER_DEFAULT_TYPE);

        $thrown = false;
        try {
            $dbHelper->rawQuery(1, 'SELECT NOW_TYPO()');
        } catch (Exception $e) {
            $thrown = true;
        }
        $this->assertTrue($thrown);
        
        $thrown = false;
        try {
            $dbHelper->escape(1, 'SELECT NOW()');
        } catch (Exception $e) {
            $thrown = true;
        }
        $this->assertTrue($thrown);
    }

    /**
     * @test
     * @group DbHelper
     * @group throwsOnInvalidMacros
     * @group expandMacros
     */
    public function throwsOnInvalidMacros()
    {
        $dbHelper = Hashmark::getModule('DbHelper', HASHMARK_DBHELPER_DEFAULT_TYPE);
        $db = $dbHelper->openDb('unittest');
        $template = 'SELECT * FROM `@table` WHERE `id` = :id OR `name` = :name';

        $values = array('@table' => 'sca"lars', '%id' => 2, ':name' => 'two\'s');
        $thrown = false;
        try {
            $dbHelper->expandMacros($db, $template, array($values));
        } catch (Exception $e) {
            $thrown = true;
        }
        $this->assertTrue($thrown);
        
        $values = array('@table' => 'sca"lars', ':id' => 2, '!name' => 'two\'s');
        $thrown = false;
        try {
            $dbHelper->expandMacros($db, $template, array($values));
        } catch (Exception $e) {
            $thrown = true;
        }
        $this->assertTrue($thrown);
        
        $values = array('@table' => 'sca"lars', ':id' => 2, ':name' => 'two\'s');
        $thrown = false;
        try {
            $dbHelper->expandMacros($db, $template, array($values));
        } catch (Exception $e) {
            $thrown = true;
        }
        $this->assertFalse($thrown);
    }
    
    /**
     * @test
     * @group DbHelper
     * @group exposesQueryErrorString
     * @group error
     * @group query
     */
    public function exposesQueryErrorString()
    {
        $dbHelper = Hashmark::getModule('DbHelper', HASHMARK_DBHELPER_DEFAULT_TYPE);
        $db = $dbHelper->openDb('unittest');
        try {
            $dbHelper->query($db, 'SELECT NOW_TYPO()');
        } catch (Exception $e) {
        }
        $error = $dbHelper->error($db);
        $this->assertTrue(false !== strpos($error, 'does not exist'));
    }
    
    /**
     * @test
     * @group DbHelper
     * @group exposesQueryErrorNum
     * @group errno
     * @group query
     */
    public function exposesQueryErrorNum()
    {
        $dbHelper = Hashmark::getModule('DbHelper', HASHMARK_DBHELPER_DEFAULT_TYPE);
        $db = $dbHelper->openDb('unittest');
        try {
            $dbHelper->query($db, 'SELECT NOW_TYPO()');
        } catch (Exception $e) {
        }
        $errno = $dbHelper->errno($db);
        $this->assertEquals('1305', $errno);
    }
    
    /**
     * @test
     * @group DbHelper
     * @group escapesValues
     * @group escape
     */
    public function escapesValues()
    {
        $dbHelper = Hashmark::getModule('DbHelper', HASHMARK_DBHELPER_DEFAULT_TYPE);
        $db = $dbHelper->openDb('unittest');

        // Boolean parameter disables auto-quoting.
        $this->assertEquals('scalarName', $dbHelper->escape($db, 'scalarName', false));
        $this->assertEquals('scalar\\\'Name', $dbHelper->escape($db, 'scalar\'Name', false));
        $this->assertEquals('scalar\"Name', $dbHelper->escape($db, 'scalar"Name', false));
    }
    
    /**
     * @test
     * @group DbHelper
     * @group escapesAndQuotesValues
     * @group escape
     */
    public function escapesAndQuotesValues()
    {
        $dbHelper = Hashmark::getModule('DbHelper', HASHMARK_DBHELPER_DEFAULT_TYPE);
        $db = $dbHelper->openDb('unittest');
        $this->assertEquals('\'scalarName\'', $dbHelper->escape($db, 'scalarName'));
        $this->assertEquals('\'scalar\\\'Name\'', $dbHelper->escape($db, 'scalar\'Name'));
        $this->assertEquals('\'scalar\"Name\'', $dbHelper->escape($db, 'scalar"Name'));
    }
    
    /**
     * @test
     * @group DbHelper
     * @group executesRawQuery
     * @group rawQuery
     * @group error
     * @group errno
     */
    public function executesRawQuery()
    {
        $dbHelper = Hashmark::getModule('DbHelper', HASHMARK_DBHELPER_DEFAULT_TYPE);
        $db = $dbHelper->openDb('unittest');
        try {
            $dbHelper->rawQuery($db, 'SELECT NOW()');
        } catch (Exception $e) {
        }
        $errno = $dbHelper->errno($db);
        $this->assertTrue(empty($errno));
        $error = $dbHelper->error($db);
        $this->assertTrue(empty($error));
    }

    /**
     * @test
     * @group DbHelper
     * @group expandsNamedAndVariableLengthListMacros
     * @group expandMacros
     */
    public function expandsNamedAndVariableLengthListMacros()
    {
        $dbHelper = Hashmark::getModule('DbHelper', HASHMARK_DBHELPER_DEFAULT_TYPE);
        $db = $dbHelper->openDb('unittest');

        $template = 'SELECT * FROM `@table` WHERE `id` = :id OR `name` = :name';
        $values = array('@table' => 'sca"lars', ':id' => 2, ':name' => 'two\'s');
        $expectedSql = 'SELECT * FROM `sca\"lars` WHERE `id` = 2 OR `name` = \'two\\\'s\'';
        $actualSql = $dbHelper->expandMacros($db, $template, array($values));
        $this->assertEquals($expectedSql, $actualSql);
        
        $template = 'SELECT * FROM `scalars` WHERE `id` = ? OR `name` = ?';
        $expectedSql = 'SELECT * FROM `scalars` WHERE `id` = 3 OR `name` = \'thr\"ee\'';
        $values = array(3, 'thr"ee');
        $actualSql = $dbHelper->expandMacros($db, $template, $values);
        $this->assertEquals($expectedSql, $actualSql);
    }
}
