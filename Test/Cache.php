<?php
// vim: fenc=utf-8:ft=php:ai:si:ts=4:sw=4:et:

/**
 * Hashmark_TestCase_Cache
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
class Hashmark_TestCase_Cache extends Hashmark_TestCase
{
    /**
     * @test
     * @group Cache
     * @group isConfigured
     */
    public function isConfigured()
    {
        $this->assertTrue(Hashmark::getModule('Cache')->isConfigured());
    }

    /**
     * @test
     * @group Cache
     * @group persists
     * @group save
     * @group load
     * @group remove
     */
    public function persists()
    {
        $cache = Hashmark::getModule('Cache');
        $expectedKey = self::randomString();
        $expectedValue = self::randomString();

        // Verify write.
        $this->assertTrue($cache->save($expectedValue, $expectedKey));
        $this->assertEquals($expectedValue, $cache->load($expectedKey));
        
        $expectedValue = self::randomString();
        
        // Verify overwrite.
        $this->assertTrue($cache->save($expectedValue, $expectedKey));
        $this->assertEquals($expectedValue, $cache->load($expectedKey));
        
        // Verify delete.
        $this->assertTrue($cache->remove($expectedKey));
        $this->assertFalse($cache->load($expectedKey));
    }

    /**
     * @test
     * @group Cache
     * @group persistsUsingGroupKey
     * @group save
     * @group load
     * @group remove
     */
    public function persistsUsingGroupKey()
    {
        $cache = Hashmark::getModule('Cache');
        
        // Two keys will share group.
        $expectedKeys = array(self::randomString(), self::randomString());
        $expectedGroup = self::randomString();
       
        foreach ($expectedKeys as $expectedKey) {
            $expectedValue = self::randomString();

            // Verify write.
            $this->assertTrue($cache->save($expectedValue, $expectedKey, $expectedGroup));
            $this->assertFalse($cache->load($expectedKey));  // False positive check.
            $this->assertEquals($expectedValue, $cache->load($expectedKey, $expectedGroup));

            $expectedValue = self::randomString();

            // Verify overwrite.
            $this->assertTrue($cache->save($expectedValue, $expectedKey, $expectedGroup));
            $this->assertFalse($cache->load($expectedKey));  // False positive check.
            $this->assertEquals($expectedValue, $cache->load($expectedKey, $expectedGroup));

            // Verify delete.
            $this->assertTrue($cache->remove($expectedKey, $expectedGroup));
            $this->assertFalse($cache->load($expectedKey, $expectedGroup));
        }
    }
    
    /**
     * @test
     * @group Cache
     * @group removesWholeGroup
     * @group save
     * @group load
     * @group removeGroup
     */
    public function removesWholeGroup()
    {
        $cache = Hashmark::getModule('Cache');
        
        // Assert these are unaffected by the operations on subject keys/values/groups.
        $unaffectedKey = self::randomString();
        $unaffectedValue = self::randomString();
        $unaffectedGroup = self::randomString();
        $this->assertTrue($cache->save($unaffectedValue, $unaffectedKey, $unaffectedGroup));
        $this->assertEquals($unaffectedValue, $cache->load($unaffectedKey, $unaffectedGroup));
        
        // Two keys will share group.
        $subjectKeys = array(self::randomString(), self::randomString());
        $subjectGroup = self::randomString();

        // Write to subject key/group.
        foreach ($subjectKeys as $subjectKey) {
            $subjectValue = self::randomString();
            $this->assertTrue($cache->save($subjectValue, $subjectKey, $subjectGroup));
            $this->assertEquals($subjectValue, $cache->load($subjectKey, $subjectGroup));
        }
        
        // Remove subject group.
        $this->assertTrue($cache->removeGroup($subjectGroup));
        foreach ($subjectKeys as $subjectKey) {
            $this->assertFalse($cache->load($subjectKey, $subjectGroup));
        }
        
        // Verify other group not touched.
        $this->assertEquals($unaffectedValue, $cache->load($unaffectedKey, $unaffectedGroup));
    }
}
