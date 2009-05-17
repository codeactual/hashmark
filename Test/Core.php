<?php
// vim: fenc=utf-8:ft=php:ai:si:ts=4:sw=4:et:

/**
 * Hashmark_TestCase_Core
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
 * @package     Hashmark-Test
 * @subpackage  Base
 */
class Hashmark_TestCase_Core extends Hashmark_TestCase
{
    /**
     * Resources needed for most tests.
     *
     * @return void
     */
    protected function setUp()
    {
        parent::setUp();
        
        $this->_core = Hashmark::getModule('Core', '', $this->_db);
    }
    
    /**
     * @test
     * @group Core
     * @group createsAgentAndGetsById
     * @group getAgentById
     * @group createAgent
     */
    public function createsAgentAndGetsById()
    {
        // False positive check.
        $this->assertFalse($this->_core->getAgentById(''));
        $this->assertFalse($this->_core->getAgentById(0));
        $this->assertFalse($this->_core->getAgentById(-1));

        $expectedName = self::randomString();
        $expectedId = $this->_core->createAgent($expectedName);

        $agent = $this->_core->getAgentById($expectedId);
        $this->assertEquals($expectedId, $agent['id']);
        $this->assertEquals($expectedName, $agent['name']);
    }
    
    /**
     * @test
     * @group Core
     * @group createsAgentAndGetsByName
     * @group getAgentByName
     * @group createAgent
     */
    public function createsAgentAndGetsByName()
    {
        // False positive check.
        $this->assertFalse($this->_core->getAgentByName(''));
        $this->assertFalse($this->_core->getAgentByName(0));
        $this->assertFalse($this->_core->getAgentByName(-1));

        $expectedName = self::randomString();
        $expectedId = $this->_core->createAgent($expectedName);

        $agent = $this->_core->getAgentByName($expectedName);
        $this->assertEquals($expectedId, $agent['id']);
        $this->assertEquals($expectedName, $agent['name']);
    }

    /**
     * @test
     * @group Core
     * @group deletesAgent
     * @group deleteAgent
     * @group createAgent
     * @group getAgentById
     */
    public function deletesAgent()
    {
        $expectedId = $this->_core->createAgent(self::randomString());
        $this->assertTrue($this->_core->deleteAgent($expectedId));
        $this->assertFalse($this->_core->getAgentById($expectedId));
    }
    
    /**
     * @test
     * @group Core
     * @group createsScalarAgentAndGetsById
     * @group getScalarAgentById
     * @group createScalarAgent
     */
    public function createsScalarAgentAndGetsById()
    {
        // False positive check.
        $this->assertFalse($this->_core->getScalarAgentById(''));
        $this->assertFalse($this->_core->getScalarAgentById(0));
        $this->assertFalse($this->_core->getScalarAgentById(-1));

        $agentId = $this->_core->createAgent(self::randomString());

        $scalar = array();
        $scalar['name'] = self::randomString();
        $scalar['type'] = 'decimal';
        $scalarId = $this->_core->createScalar($scalar);
        
        // Rely on some defaults. 
        $frequency = 0;
        $scalarAgentId = $this->_core->createScalarAgent($scalarId, $agentId, $frequency);
        $scalarAgent = $this->_core->getScalarAgentById($scalarAgentId);
        $this->assertEquals($scalarAgentId, $scalarAgent['id']);
        $this->assertEquals(HASHMARK_DATETIME_EMPTY, $scalarAgent['start']);
        $this->assertTrue(empty($scalarAgent['error']));
        $this->assertEquals($frequency, $scalarAgent['frequency']);
        $this->assertEquals('Unscheduled', $scalarAgent['status']);
        $this->assertEquals('', $scalarAgent['config']);
        $this->assertEquals(HASHMARK_DATETIME_EMPTY, $scalarAgent['start']);

        // Define all fields.
        $frequency = 0;
        $status = 'Running';
        $config = array('c' => 3, 'a' => 1, 'b' => 2);
        $start = gmdate(HASHMARK_DATETIME_FORMAT);
        $scalarAgentId = $this->_core->createScalarAgent($scalarId, $agentId, $frequency,
                                                         $status, $config, $start);
        $scalarAgent = $this->_core->getScalarAgentById($scalarAgentId);
        $this->assertEquals($scalarAgentId, $scalarAgent['id']);
        $this->assertEquals($start, $scalarAgent['start']);
        $this->assertTrue(empty($scalarAgent['error']));
        $this->assertEquals($frequency, $scalarAgent['frequency']);
        $this->assertEquals($status, $scalarAgent['status']);
        $this->assertEquals($config, $scalarAgent['config']);
        $this->assertEquals($start, $scalarAgent['start']);
    }
    
    /**
     * @test
     * @group Core
     * @group deletesScalarAgent
     * @group deleteScalarAgent
     * @group createScalarAgent
     * @group getAgentById
     */
    public function deletesScalarAgent()
    {
        $agentId = $this->_core->createAgent(self::randomString());

        $scalar = array();
        $scalar['name'] = self::randomString();
        $scalar['type'] = 'string';
        $scalarId = $this->_core->createScalar($scalar);

        $expectedId = $this->_core->createScalarAgent($scalarId, $agentId, 0);

        $this->assertTrue($this->_core->deleteScalarAgent($expectedId));
        $this->assertFalse($this->_core->getScalarAgentById($expectedId));
    }
    
    /**
     * @test
     * @group Core
     * @group createsScalarAndGetsById
     * @group getScalarById
     * @group createScalar
     */
    public function createsScalarAndGetsById()
    {
        // False positive check.
        $this->assertFalse($this->_core->getScalarById(''));
        $this->assertFalse($this->_core->getScalarById(0));
        $this->assertFalse($this->_core->getScalarById(-1));

        foreach (self::provideScalarTypesAndValues() as $data) {
            list($type, $value) = $data;

            $expectedFields = array();
            $expectedFields['name'] = self::randomString();
            $expectedFields['type'] = $type;
            $expectedFields['value'] = $value;
            $expectedFields['description'] = self::randomString();

            $expectedId = $this->_core->createScalar($expectedFields);

            $scalar = $this->_core->getScalarById($expectedId);

            $this->assertEquals($expectedId, $scalar['id']);
            $this->assertEquals($expectedFields['name'], $scalar['name']);
            $this->assertEquals($expectedFields['type'], $scalar['type']);
            $this->assertEquals($expectedFields['value'], $scalar['value']);
            $this->assertEquals($expectedFields['description'], $scalar['description']);
            $this->assertEquals(HASHMARK_DATETIME_EMPTY, $scalar['last_inline_change']);
            $this->assertEquals(HASHMARK_DATETIME_EMPTY, $scalar['last_agent_change']);
            $this->assertEquals(0, $scalar['sample_count']);
        }
    }
    
    /**
     * @test
     * @group Core
     * @group createsScalarAndGetsByName
     * @group getScalarByName
     * @group createScalar
     */
    public function createsScalarAndGetsByName()
    {
        foreach (self::provideScalarTypesAndValues() as $data) {
            list($type, $value) = $data;

            $expectedFields = array();
            $expectedFields['name'] = self::randomString();
            $expectedFields['type'] = $type;
            $expectedFields['value'] = $value;
            $expectedFields['description'] = self::randomString();

            $expectedId = $this->_core->createScalar($expectedFields);

            $scalar = $this->_core->getScalarByName($expectedFields['name']);

            $this->assertEquals($expectedId, $scalar['id']);
            $this->assertEquals($expectedFields['name'], $scalar['name']);
            $this->assertEquals($expectedFields['type'], $scalar['type']);
            $this->assertEquals($expectedFields['value'], $scalar['value']);
            $this->assertEquals($expectedFields['description'], $scalar['description']);
            $this->assertEquals(HASHMARK_DATETIME_EMPTY, $scalar['last_inline_change']);
            $this->assertEquals(HASHMARK_DATETIME_EMPTY, $scalar['last_agent_change']);
            $this->assertEquals(0, $scalar['sample_count']);
        }
    }
    
    /**
     * @test
     * @group Core
     * @group getsScalarType
     * @group getScalarType
     * @group createScalar
     */
    public function getsScalarType()
    {
        foreach ($this->_core->getValidScalarTypes() as $expectedType) {
            $expectedFields = array();
            $expectedFields['name'] = self::randomString();
            $expectedFields['type'] = $expectedType;

            $expectedId = $this->_core->createScalar($expectedFields);

            $actualType = $this->_core->getScalarType($expectedId);

            $this->assertEquals($expectedType, $actualType);
        }
    }
    
    /**
     * @test
     * @group Core
     * @group getsScalarSampleCount
     * @group getScalarSampleCount
     * @group createScalar
     */
    public function getsScalarSampleCount()
    {
        $expectedFields = array();
        $expectedFields['name'] = self::randomString();
        $expectedFields['type'] = 'decimal';

        $expectedId = $this->_core->createScalar($expectedFields);

        $actualCount = $this->_core->getScalarSampleCount($expectedId);

        $this->assertEquals('0', $actualCount);
    }
    
    /**
     * @test
     * @group Core
     * @group getsScalarIdByName
     * @group getScalarIdByName
     * @group createScalar
     */
    public function getsScalarIdByName()
    {
        $expectedFields = array();
        $expectedFields['name'] = self::randomString();
        $expectedFields['type'] = 'decimal';

        $expectedId = $this->_core->createScalar($expectedFields);

        $this->assertEquals($expectedId, $this->_core->getScalarIdByName($expectedFields['name']));
    }

    /**
     * @test
     * @group Core
     * @group changesScalarCategory
     * @group scalarHasCategory
     * @group setScalarCategory
     * @group unsetScalarCategory
     * @group createScalar
     * @group createCategory
     * @group getValidScalarTypes
     */
    public function changesScalarCategory()
    {
        $scalar = array();
        
        foreach ($this->_core->getValidScalarTypes() as $type) {
            $scalar['name'] = self::randomString();
            $scalar['type'] = $type;

            $scalarId = $this->_core->createScalar($scalar);

            $categoryName = self::randomString();
            $categoryDescript = self::randomString();

            $categoryId = $this->_core->createCategory($categoryName, $categoryDescript);

            $this->assertTrue($this->_core->setScalarCategory($scalarId, $categoryId));
            $this->assertTrue($this->_core->scalarHasCategory($scalarId, $categoryId));

            $this->assertTrue($this->_core->unsetScalarCategory($scalarId, $categoryId));
            $this->assertFalse($this->_core->scalarHasCategory($scalarId, $categoryId));
        }
    }

    /**
     * @test
     * @group Core
     * @group deletesScalar
     * @group deleteScalar
     * @group createScalar
     * @group getScalarById
     * @group getValidScalarTypes
     */
    public function deletesScalar()
    {
        $expectedFields = array();
        
        foreach ($this->_core->getValidScalarTypes() as $type) {
            $expectedFields['name'] = self::randomString();
            $expectedFields['type'] = $type;

            $expectedId = $this->_core->createScalar($expectedFields);
            $this->assertTrue($this->_core->deleteScalar($expectedId));

            $this->assertFalse($this->_core->getScalarById($expectedId));
        }
    }

    /**
     * @test
     * @group Core
     * @group getsCategoryById
     * @group categories
     * @group getCategoryById
     * @group createCategory
     */
    public function getsCategoryById()
    {
        // False positive check.
        $this->assertFalse($this->_core->getCategoryById(''));
        $this->assertFalse($this->_core->getCategoryById(0));
        $this->assertFalse($this->_core->getCategoryById(-1));

        $expectedName = self::randomString();
        $expectedDescript = self::randomString();

        $expectedId = $this->_core->createCategory($expectedName, $expectedDescript);

        $category = $this->_core->getCategoryById($expectedId);

        $this->assertEquals($expectedId, $category['id']);
        $this->assertEquals($expectedName, $category['name']);
        $this->assertEquals($expectedDescript, $category['description']);
    }

    /**
     * @test
     * @group Core
     * @group getsCategoryByName
     * @group categories
     * @group getCategoryByName
     * @group createCategory
     */
    public function getsCategoryByName()
    {
        // False positive check.
        $nonExistentName = self::randomString();
        $this->assertFalse($this->_core->getCategoryByName($nonExistentName));

        $expectedName = self::randomString();
        $expectedDescript = self::randomString();

        $expectedId = $this->_core->createCategory($expectedName, $expectedDescript);

        $category = $this->_core->getCategoryByName($expectedName);

        $this->assertEquals($expectedId, $category['id']);
        $this->assertEquals($expectedName, $category['name']);
        $this->assertEquals($expectedDescript, $category['description']);
    }

    /**
     * @test
     * @group Core
     * @group deletesCategory
     * @group categories
     * @group deleteCategory
     * @group createCategory
     * @group getCategoryById
     */
    public function deletesCategory()
    {
        $expectedName = self::randomString();
        $expectedDescript = self::randomString();

        $expectedId = $this->_core->createCategory($expectedName, $expectedDescript);
        $this->assertTrue($this->_core->deleteCategory($expectedId));

        $category = $this->_core->getCategoryById($expectedId);
        $this->assertFalse($category);
    }
    
    /**
     * @test
     * @group Core
     * @group getsMilestoneById
     * @group milestones
     * @group getMilestoneById
     * @group createMilestone
     */
    public function getsMilestoneById()
    {
        // False positive check.
        $this->assertFalse($this->_core->getMilestoneById(''));
        $this->assertFalse($this->_core->getMilestoneById(0));
        $this->assertFalse($this->_core->getMilestoneById(-1));

        $expectedName = self::randomString();
        $expectedWhen = gmdate(HASHMARK_DATETIME_FORMAT);

        $expectedId = $this->_core->createMilestone($expectedName, $expectedWhen);

        $milestone = $this->_core->getMilestoneById($expectedId);

        $this->assertEquals($expectedId, $milestone['id']);
        $this->assertEquals($expectedName, $milestone['name']);
        $this->assertEquals($expectedWhen, $milestone['when']);
    }
   
    /**
     * @test
     * @group Core
     * @group getsMilestoneByName
     * @group milestones
     * @group getMilestoneByName
     * @group createMilestone
     */
    public function getsMilestoneByName()
    {
        // False positive check.
        $this->assertFalse($this->_core->getMilestoneByName(''));
        $this->assertFalse($this->_core->getMilestoneByName(self::randomString()));

        $expectedName = self::randomString();
        $expectedWhen = gmdate(HASHMARK_DATETIME_FORMAT);

        $expectedId = $this->_core->createMilestone($expectedName, $expectedWhen);

        $milestone = $this->_core->getMilestoneByName($expectedName);

        $this->assertEquals($expectedId, $milestone['id']);
        $this->assertEquals($expectedName, $milestone['name']);
        $this->assertEquals($expectedWhen, $milestone['when']);
    }

    /**
     * @test
     * @group Core
     * @group setsMilestone
     * @group milestones
     * @group updateMilestone
     * @group createMilestone
     * @group getMilestoneById
     */
    public function updatesMilestone()
    {
        $time = time();

        $originalName = self::randomString();
        $originalWhen = gmdate(HASHMARK_DATETIME_FORMAT, $time);

        $expectedId = $this->_core->createMilestone($originalName, $originalWhen);
        
        $newName = self::randomString();
        $newWhen = gmdate(HASHMARK_DATETIME_FORMAT, $time + 60);

        $this->assertTrue($this->_core->updateMilestone($expectedId, $newName, $newWhen));

        $milestone = $this->_core->getMilestoneById($expectedId);

        $this->assertEquals($expectedId, $milestone['id']);
        $this->assertEquals($newName, $milestone['name']);
        $this->assertEquals($newWhen, $milestone['when']);
    }
    
    /**
     * @test
     * @group Core
     * @group changesMilestoneCategory
     * @group milestones
     * @group milestoneHasCategory
     * @group setMilestoneCategory
     * @group unsetMilestoneCategory
     * @group createMilestone
     * @group createCategory
     */
    public function changesMilestoneCategory()
    {
        $milestoneName = self::randomString();
        $milestoneWhen = gmdate(HASHMARK_DATETIME_FORMAT);

        $milestoneId = $this->_core->createMilestone($milestoneName, $milestoneWhen);
        
        $categoryName = self::randomString();
        $categoryDescript = self::randomString();

        $categoryId = $this->_core->createCategory($categoryName, $categoryDescript);

        $this->assertTrue($this->_core->setMilestoneCategory($milestoneId, $categoryId));
        $this->assertTrue($this->_core->milestoneHasCategory($milestoneId, $categoryId));
        
        $this->assertTrue($this->_core->unsetMilestoneCategory($milestoneId, $categoryId));
        $this->assertFalse($this->_core->milestoneHasCategory($milestoneId, $categoryId));
    }

    /**
     * @test
     * @group Core
     * @group deletesMilestone
     * @group categories
     * @group deleteMilestone
     * @group createMilestone
     * @group getMilestoneById
     */
    public function deletesMilestone()
    {
        $expectedName = self::randomString();
        $expectedWhen = gmdate(HASHMARK_DATETIME_FORMAT);

        $expectedId = $this->_core->createMilestone($expectedName, $expectedWhen);
        $this->assertTrue($this->_core->deleteMilestone($expectedId));

        $this->assertFalse($this->_core->getMilestoneById($expectedId));
    }
}
