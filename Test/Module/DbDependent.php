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
 * @version     $Id: DbDependent.php 297 2009-02-13 05:04:37Z david $
*/

/**
 * @package     Hashmark-Test
 * @subpackage  Hashmark_Module
 */
class Hashmark_TestCase_Module_DbDependent extends Hashmark_TestCase_Module
{
    /**
     * @test
     * @group Module
     * @group getsDbProps
     * @group getModule
     * @group getDb
     * @group getDbHelper
     */
    public function getsDbProps()
    {
        $moduleNames = array('BasicDecimal', 'Client', 'Core', 'Cron', 'Partition');
        
        foreach ($moduleNames as $module) {
            // Find all type scripts under BasicDecimal/, Client/, etc.
            foreach (glob(dirname(__FILE__) . '/../../' . $module . '/*.php') as $typeFile) {
                $type = basename($typeFile, '.php');
    
                $dbHelper = Hashmark::getModule('DbHelper', $type);
                $db = $dbHelper->openDb('unittest');
    
                $inst = Hashmark::getModule($module, $type, $db);
    
                $this->assertEquals('Hashmark_' . $module . '_' . $type, get_class($inst));
                $this->assertEquals($db, $inst->getDb());
                $this->assertEquals($dbHelper, $inst->getDbHelper());

                $dbHelper->closeDb($db);
            }
        }
    }
    
    /**
     * @test
     * @group Module
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
        $this->assertEquals($inst->getDbHelper(), $relatedInst->getDbHelper());
    }
}
