<?php
// vim: fenc=utf-8:ft=php:ai:si:ts=4:sw=4:et:

/**
 * Hashmark_TestCase_Hashmark
 *
 * @filesource
 * @link        http://code.google.com/p/hashmark/
 * @link        http://framework.zend.com/manual/en/coding-standard.html
 * @link        http://phpdoc.org/tutorial.php
 * @copyright   Copyright (c) 2008-2009, Code Actual LLC
 * @license     http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package     Hashmark-Test
 * @version     $Id$
*/

/**
 * @package     Hashmark-Test
 */
class Hashmark_TestCase_Hashmark extends Hashmark_TestCase
{
    /**
     * @test
     * @group Hashmark
     * @group getsModules
     * @group getModule
     */
    public function getsModules()
    {
        $module = 'Agent'; 

        foreach (glob(dirname(__FILE__) . '/../' . $module . '/*.php') as $typeFile) {
            $type = basename($typeFile, '.php');

            $inst = Hashmark::getModule($module, $type);

            $this->assertEquals('Hashmark_' . $module . '_' . $type, get_class($inst));
        }
    }
    
    /**
     * @test
     * @group Hashmark
     * @group configFileOverridesDefaults
     * @group getModule
     * @group getBaseConfig
     */
    public function configFileOverridesDefaults()
    {
        $inst = Hashmark::getModule('Test', 'FakeExternalType');
        $baseConfig = $inst->getBaseConfig();
        $this->assertEquals('overwritten', $baseConfig['override_me']);
    }

    /**
     * @test
     * @group Hashmark
     * @group loadsModuleConfigs
     * @group getModule
     * @group getBaseConfig
     * @group getTypeConfig
     */
    public function loadsModuleConfigs()
    {
        $test = Hashmark::getModule('Test', 'FakeModuleType');
        $baseConfig = $test->getBaseConfig();
        $typeConfig = $test->getTypeConfig();
        $this->assertEquals('Test', $baseConfig['base']);
        $this->assertEquals('FakeModuleType', $typeConfig['type']);
    }

    /**
     * @test
     * @group Hashmark
     * @group loadsExternalModuleType
     * @group getModule
     * @group getTypeConfig
     */
    public function loadsExternalModuleType()
    {
        $inst = Hashmark::getModule('Test', 'FakeExternalType');
        $this->assertTrue(is_subclass_of($inst, 'Hashmark_Test'));

        $typeConfig = $inst->getTypeConfig();
        $this->assertEquals(4400, $typeConfig['test_key']);
    }
}
