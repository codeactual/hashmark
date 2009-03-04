<?php
// vim: fenc=utf-8:ft=php:ai:si:ts=4:sw=4:et:

/**
 * Hashmark_TestCase_Cache
 *
 * @filesource
 * @link        http://code.google.com/p/hashmark/
 * @link        http://framework.zend.com/manual/en/coding-standard.html
 * @link        http://phpdoc.org/tutorial.php
 * @copyright   Copyright (c) 2008-2009, Code Actual LLC
 * @license     http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package     Hashmark-Test
 * @subpackage  Base
 * @version     $Id: Cache.php 290 2009-02-11 04:55:11Z david $
*/

/**
 * @abstract
 * @package     Hashmark-Test
 * @subpackage  Base
 */
abstract class Hashmark_TestCase_Cache extends Hashmark_TestCase
{
    /**
     * @test
     * @group Cache
     * @group setsGetsAndRemovesWithoutGroup
     * @group set
     * @group get
     * @group remove
     */
    public function setsGetsAndRemovesWithoutGroup()
    {
        $cache = Hashmark::getModule('Cache', $this->_type);
        $expectedKey = self::randomString();
        $expectedValue = self::randomString();

        $this->assertTrue($cache->set($expectedKey, $expectedValue));
        $this->assertEquals($expectedValue, $cache->get($expectedKey));
        
        $expectedValue = self::randomString();
        
        $this->assertTrue($cache->set($expectedKey, $expectedValue));
        $this->assertEquals($expectedValue, $cache->get($expectedKey));
        
        $this->assertTrue($cache->remove($expectedKey));
        $this->assertFalse($cache->get($expectedKey));
    }

    /**
     * @test
     * @group Cache
     * @group setsGetsAndRemovesWithGroup
     * @group set
     * @group get
     * @group remove
     */
    public function setsGetsAndRemovesWithGroup()
    {
        $cache = Hashmark::getModule('Cache', $this->_type);
        $expectedKeys = array(self::randomString(), self::randomString());
        $expectedGroup = self::randomString();
       
        foreach ($expectedKeys as $expectedKey) {
            $expectedValue = self::randomString();

            $this->assertTrue($cache->set($expectedKey, $expectedValue, $expectedGroup));
            $this->assertFalse($cache->get($expectedKey));  // False positive check.
            $this->assertEquals($expectedValue, $cache->get($expectedKey, $expectedGroup));

            $expectedValue = self::randomString();

            $this->assertTrue($cache->set($expectedKey, $expectedValue, $expectedGroup));
            $this->assertFalse($cache->get($expectedKey));  // False positive check.
            $this->assertEquals($expectedValue, $cache->get($expectedKey, $expectedGroup));

            $this->assertTrue($cache->remove($expectedKey, $expectedGroup));
            $this->assertFalse($cache->get($expectedKey, $expectedGroup));
        }
    }
    
    /**
     * @test
     * @group Cache
     * @group invalidatesGroupMembers
     * @group set
     * @group get
     * @group remove
     */
    public function invalidatesGroupMembers()
    {
        $cache = Hashmark::getModule('Cache', $this->_type);
        $affectedKeys = array(self::randomString(), self::randomString());
        $affectedGroup = self::randomString();

        // Assert this key/group is intact after $affectedGroup invalidation.
        $unaffectedKey = self::randomString();
        $unaffectedValue = self::randomString();
        $unaffectedGroup = self::randomString();
        $this->assertTrue($cache->set($unaffectedKey, $unaffectedValue, $unaffectedGroup));
        $this->assertEquals($unaffectedValue, $cache->get($unaffectedKey, $unaffectedGroup));
        
        foreach ($affectedKeys as $affectedKey) {
            $affectedValue = self::randomString();

            $this->assertTrue($cache->set($affectedKey, $affectedValue, $affectedGroup));
            $this->assertEquals($affectedValue, $cache->get($affectedKey, $affectedGroup));
        }
        
        $this->assertTrue($cache->removeGroup($affectedGroup));
        
        foreach ($affectedKeys as $affectedKey) {
            $this->assertFalse($cache->get($affectedKey, $affectedGroup));
        }
        
        $this->assertEquals($unaffectedValue, $cache->get($unaffectedKey, $unaffectedGroup));
    }
}
