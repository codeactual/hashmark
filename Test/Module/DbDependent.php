<?php
// vim: fenc=utf-8:ft=php:ai:si:ts=4:sw=4:et:

/**
 * Hashmark_TestCase_Module_DbDependent
 *
 * @filesource
 * @link        http://code.google.com/p/hashmark/
 * @link        http://framework.zend.com/manual/en/coding-standard.html
 * @link        http://phpdoc.org/tutorial.php
 * @copyright   Copyright (c) 2008-2009, Code Actual LLC
 * @license     http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package     Hashmark-Test
 * @subpackage  Hashmark_Module
 * @version     $Id$
*/

/**
 * @package     Hashmark-Test
 * @subpackage  Hashmark_Module
 */
class Hashmark_TestCase_Module_DbDependent extends Hashmark_TestCase_Module
{
    /**
     * @test
     * @group DbDependent
     * @group getsDbProps
     * @group setDbName
     * @group getModule
     * @group getDb
     */
    public function getsDbProps()
    {
        $moduleNames = array('Client' => '', 'Core' => '', 'Cron' => '', 'Partition' => '');
        
        $dbHelper = Hashmark::getModule('DbHelper');
        $dbHelperConfig = $dbHelper->getBaseConfig();
        
        foreach ($moduleNames as $module => $type) {
            $db = $dbHelper->openDb('unittest');

            $inst = Hashmark::getModule($module, $type, $db);
            $inst->setDbName('customDbName');

            $this->assertEquals('Hashmark_' . $module . ($type ? "_{$type}" : ''),
                                get_class($inst));
            $this->assertEquals($db, $inst->getDb());
            $this->assertEquals('customDbName',
                                $inst->getDbName());

            $db->closeConnection();
        }
    }
    
    /**
     * @test
     * @group DbDependent
     * @group getsModuleUsingSameDbProperties
     * @group getModule
     * @group getType
     */
    public function getsModuleUsingSameDbProperties()
    {
        $expectedBase = 'Client';
        
        $db = Hashmark::getModule('DbHelper')->openDb('unittest');
        $inst = Hashmark::getModule($expectedBase, '', $db);

        $relatedInst = $inst->getModule('Core');

        $this->assertEquals($inst->getDb(), $relatedInst->getDb());
        $this->assertEquals($inst->getDbName(), $relatedInst->getDbName());
    }
    
    /**
     * @test
     * @group DbDependent
     * @group setsDbName
     * @group getModule
     * @group setDbName
     */
    public function setsDbName()
    {
        $expectedDbName = self::randomString();
        $inst = Hashmark::getModule('Client', '', $this->_db);
        $inst->setDbName($expectedDbName);
        $this->assertEquals($expectedDbName, $inst->getDbName());
    }

    /**
     * @test
     * @group DbDependent
     * @group throwsOnInvalidMacros
     * @group expandSql
     */
    public function throwsOnInvalidMacros()
    {
        $mod = Hashmark::getModule('Core', '', $this->_db);
        $sql = 'SELECT * FROM `@table` WHERE `id` = :id OR `name` = :name';

        $values = array('@table' => 'sca"lars', '%id' => 2, ':name' => 'two\'s');
        $thrown = false;
        try {
            $mod->expandSql($sql, $values);
        } catch (Exception $e) {
            $thrown = true;
        }
        $this->assertTrue($thrown);
        
        $values = array('@table' => 'sca"lars', ':id' => 2, '!name' => 'two\'s');
        $thrown = false;
        try {
            $mod->expandSql($sql, $values);
        } catch (Exception $e) {
            $thrown = true;
        }
        $this->assertTrue($thrown);
        
        $values = array('@table' => 'sca"lars', ':id' => 2, ':name' => 'two\'s');
        $thrown = false;
        try {
            $mod->expandSql($sql, $values);
        } catch (Exception $e) {
            $thrown = true;
        }
        $this->assertFalse($thrown);
    }
    
    /**
     * @test
     * @group DbDependent
     * @group escapesValuesWithoutQuoting
     * @group escape
     */
    public function escapesValuesWithoutQuoting()
    {
        $mod = Hashmark::getModule('Core', '', $this->_db);
        $this->assertEquals('scalarName', $mod->escape('scalarName'));
        $this->assertEquals('scalar\\\'Name', $mod->escape('scalar\'Name'));
        $this->assertEquals('scalar\"Name', $mod->escape('scalar"Name'));
    }
    
    /**
     * @test
     * @group DbDependent
     * @group expandsNamedMacros
     * @group expandSql
     */
    public function expandsNamedMacros()
    {
        $mod = Hashmark::getModule('Core', '', $this->_db);

        // Verify that ':' prefixed macros get escaped/quoted;
        // that '@' prefixed get only escaped.
        $sql = 'SELECT * FROM `@table` WHERE `id` = :id OR `name` = :name';
        $values = array('@table' => 'sca"lars', ':id' => 2, ':name' => 'two\'s');
        $expectedSql = 'SELECT * FROM `sca\"lars` WHERE `id` = 2 OR `name` = \'two\\\'s\'';
        $actualSql = $mod->expandSql($sql, $values);
        $this->assertEquals($expectedSql, $actualSql);
    }
}
