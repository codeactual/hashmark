<?php
// vim: fenc=utf-8:ft=php:ai:si:ts=4:sw=4:et:

/**
 * Hashmark_TestCase_Module
 *
 * @filesource
 * @link        http://code.google.com/p/hashmark/
 * @link        http://framework.zend.com/manual/en/coding-standard.html
 * @link        http://phpdoc.org/tutorial.php
 * @copyright   Copyright (c) 2008-2011 David Smith
 * @license     http://www.opensource.org/licenses/mit-license.php MIT License
 * @package     Hashmark-Test
 * @subpackage  Base
 * @version     $Id$
*/

/**
 * @package     Hashmark-Test
 * @subpackage  Base
 */
class Hashmark_TestCase_Module extends Hashmark_TestCase
{
    /**
     * @test
     * @group Module
     * @group initsBaseAndTypeProps
     * @group getModule
     * @group getBase
     * @group getBaseConfig
     * @group getType
     * @group getTypeConfig
     */
    public function initsBaseAndTypeProps()
    {
        $expectedBase = 'Test';
        $expectedType = 'FakeModuleType';
        $inst = Hashmark::getModule($expectedBase, $expectedType);

        $this->assertEquals($expectedBase, $inst->getBase());
        $baseConfig = $inst->getBaseConfig();
        $this->assertEquals($baseConfig['base'], $expectedBase);
        
        $this->assertEquals($expectedType, $inst->getType());
        $typeConfig = $inst->getTypeConfig();
        $this->assertEquals($typeConfig['type'], $expectedType);
    }
}
