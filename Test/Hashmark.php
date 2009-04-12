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
 * @version     $Id: Hashmark.php 290 2009-02-11 04:55:11Z david $
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
        $module = 'Sampler'; 

        foreach (glob(dirname(__FILE__) . '/../' . $module . '/*.php') as $typeFile) {
            $type = basename($typeFile, '.php');

            $inst = Hashmark::getModule($module, $type);

            $this->assertEquals('Hashmark_' . $module . '_' . $type, get_class($inst));
        }
    }

    /**
     * @test
     * @group Hashmark
     * @group loadsModuleConfigs
     * @group getModule
     */
    public function loadsModuleConfigs()
    {
        $this->assertFalse(defined('HASHMARK_TEST_CONFIG_NAME'));
        $this->assertFalse(defined('HASHMARK_TEST_FAKEMODULETYPE_CONFIG_NAME'));
        Hashmark::getModule('Test', 'FakeModuleType');
        $this->assertTrue(defined('HASHMARK_TEST_CONFIG_NAME'));
        $this->assertTrue(defined('HASHMARK_TEST_FAKEMODULETYPE_CONFIG_NAME'));
        $this->assertEquals('HASHMARK_TEST_CONFIG_VALUE', HASHMARK_TEST_CONFIG_NAME);
        $this->assertEquals('HASHMARK_TEST_FAKEMODULETYPE_CONFIG_VALUE', HASHMARK_TEST_FAKEMODULETYPE_CONFIG_NAME);
    }

    /**
     * @test
     * @group Hashmark
     * @group loadsExternalModuleType
     * @group getModule
     */
    public function loadsExternalModuleType()
    {
        $inst = Hashmark::getModule('Test', 'FakeExternalType');
        $this->assertTrue(is_subclass_of($inst, 'Hashmark_Test_FakeModuleType'));

        $typeConfig = $inst->getTypeConfig();
        $this->assertEquals(4400, $typeConfig['test_key']);
    }
}
