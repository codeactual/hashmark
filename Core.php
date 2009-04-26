<?php
// vim: fenc=utf-8:ft=php:ai:si:ts=4:sw=4:et:

/**
 * Hashmark_Core
 *
 * @filesource
 * @link        http://code.google.com/p/hashmark/
 * @link        http://framework.zend.com/manual/en/coding-standard.html
 * @link        http://phpdoc.org/tutorial.php
 * @copyright   Copyright (c) 2008-2009, Code Actual LLC
 * @license     http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package     Hashmark
 * @subpackage  Base
 * @version     $Id$
*/

/**
 * Core behaviors supporting front-ends.
 *
 * CRUD operations for scalars, jobs, categories, etc.
 *
 * @package     Hashmark
 * @subpackage  Base
 */
class Hashmark_Core extends Hashmark_Module_DbDependent
{
    /**
     * Return all valid `scalars`.`type` ENUM values.
     *
     * @return Array
     */
    public static function getValidScalarTypes()
    {
        return array('decimal', 'string');
    }

    /**
     * Return all valid `scalars`.`sampler_status` ENUM values.
     *
     * @return Array
     */
    public static function getValidSampleStatuses()
    {
        return array('Unscheduled', 'Scheduled', 'Running');
    }
    
    /**
     * Return all `jobs` fields associated with an ID.
     *
     * @param int       $id
     * @return Array    Assoc. of fields; otherwise false.
     * @throws Exception On query error.
     */
    public function getJobById($id)
    {
        $sql = 'SELECT * '
             . "FROM {$this->_dbName}`jobs` "
             . 'WHERE `id` = ?';

        $rows = $this->_db->fetchAll($sql, array($id));
        
        if (!$rows) {
            return false;
        }

        return $rows[0];
    }

    /**
     * Add `scalars` row.
     *
     * @param Array     $fields     Assoc. scalar properties.
     *
     *      Required:
     *
     *      'name'
     *      'type':                 See getValidScalarTypes() for options.
     *
     *      Optional:
     *
     *      'value':                Initial value.
     *      'description'
     *      'sampler_frequency':    Recurrence interval in minutes.
     *      'sampler_start':        Earliest possible sampling as UNIX timestamp or DATETIME string.
     *      'sampler_status':       Ex. 'Scheduled'
     *      'sampler_name`:         Hashmark_Sampler_* implementation name, ex. 'SomeFeatureUsage`.
     *                              It would refer to class Hashmark_Sampler_SomeFeatureUsage defined
     *                              in Sampler/SomeFeatureUsage.php.
     * @return int      Inserted row ID.
     * @throws Exception    On query error; if 'name'/'type' are empty/missing;
     *                      if 'type' is invalid.
     */
    public function createScalar($fields)
    {
        if (empty($fields['name']) || empty($fields['type'])) {
            throw new Exception('Scalar name and type are required.', HASHMARK_EXCEPTION_VALIDATION);
        }

        $fields['name'] = trim($fields['name']);
        
        if (!$fields['name']) {
            throw new Exception('Scalar name cannot be empty.', HASHMARK_EXCEPTION_VALIDATION);
        }
        
        if (!in_array($fields['type'], $this->getModule('Core')->getValidScalarTypes())) {
            throw new Exception('Cannot create table of unrecognized type: ' . $fields['type'], HASHMARK_EXCEPTION_VALIDATION);
        }

        $fields['value'] = isset($fields['value']) ? $fields['value'] : '';
        $fields['description'] = isset($fields['description']) ? $fields['description'] : '';
        $fields['sampler_frequency'] = isset($fields['sampler_frequency']) ? $fields['sampler_frequency'] : '';
        $fields['sampler_start'] = isset($fields['sampler_start']) ? $fields['sampler_start'] : HASHMARK_DATETIME_EMPTY;
        $fields['sampler_name'] = isset($fields['sampler_name']) ? $fields['sampler_name'] : '';
        $fields['sampler_status'] = isset($fields['sampler_status']) ? $fields['sampler_status'] : 'Unscheduled';
        
        if (is_int($fields['sampler_start'])) {
            $fields['sampler_start'] = gmdate(HASHMARK_DATETIME_FORMAT, $fields['sampler_start']);
        }

        $sql = "INSERT INTO {$this->_dbName}`scalars` "
             . '(`name`, `value`, `type`, `description`, `sampler_frequency`, '
             . '`sampler_start`, `sampler_name`, `sampler_status`) '
             . 'VALUES (?, ?, ?, ?, ?, ?, ?, ?)';
                                  
        $args = array($fields['name'], $fields['value'], $fields['type'],
                      $fields['description'],$fields['sampler_frequency'],
                      $fields['sampler_start'],$fields['sampler_name'],
                      $fields['sampler_status']);

        $this->_db->query($sql, $args);
        
        $scalarId = $this->_db->lastInsertId();
        
        $this->_cache->removeGroup('scalar_' . $scalarId);

        return $scalarId;
    }
    
    /**
     * Return all `scalars` fields associated with an ID.
     *
     * @param int       $id
     * @return Array    Assoc. of fields; otherwise false.
     * @throws Exception On query error.
     */
    public function getScalarById($id)
    {
        $sql = 'SELECT * '
             . "FROM {$this->_dbName}`scalars` "
             . 'WHERE `id` = ?';

        $rows = $this->_db->fetchAll($sql, array($id));

        if (!$rows) {
            return false;
        }

        return $rows[0];
    }
    
    /**
     * Return all `scalars` fields associated with a name.
     *
     * @param string    $name
     * @return Array    Assoc. of fields; otherwise false.
     * @throws Exception On query error.
     */
    public function getScalarByName($name)
    {
        $sql = 'SELECT * '
             . "FROM {$this->_dbName}`scalars` "
             . 'WHERE `name` = ? '
             . 'LIMIT 1';

        $rows = $this->_db->fetchAll($sql, array($name));
        
        if (!$rows) {
            return false;
        }

        return $rows[0];
    }
    
    /**
     * Return `scalars`.`type` associated with an ID.
     *
     * @param int       $id
     * @return string   See Hashmark_Core::getValidScalarTypes() for possible values.
     * @throws Exception On query error.
     */
    public function getScalarType($id)
    {
        $sql = 'SELECT `type` '
             . "FROM {$this->_dbName}`scalars` "
             . 'WHERE `id` = ?';

        $rows = $this->_db->fetchAll($sql, array($id));

        if (!$rows) {
            return false;
        }

        return $rows[0]['type'];
    }
    
    /**
     * Return `scalars`.`sample_count` associated with an ID.
     *
     * @param int       $id
     * @return string
     * @throws Exception On query error.
     */
    public function getScalarSampleCount($id)
    {
        $sql = 'SELECT `sample_count` '
             . "FROM {$this->_dbName}`scalars` "
             . 'WHERE `id` = ?';

        $rows = $this->_db->fetchAll($sql, array($id));

        if (!$rows) {
            return false;
        }

        return $rows[0]['sample_count'];
    }
    
    /**
     * Return the `id` associated with a scalar name.
     *
     * @param string    $name
     * @return string
     * @throws Exception On query error.
     */
    public function getScalarIdByName($name)
    {
        $cacheKey = __FUNCTION__ . $name;
        
        if (!($output = $this->_cache->load($cacheKey, 'schema'))) {
            $sql = 'SELECT `id` '
                 . "FROM {$this->_dbName}`scalars` "
                 . 'WHERE `name` = ?';

            $rows = $this->_db->fetchAll($sql, array($name));

            if (!$rows) {
                return false;
            }

            $output = $rows[0]['id'];
            $this->_cache->save($output, $cacheKey, 'scalar_' . $output);
        }

        return $output;
    }
    
    /**
     * Verify a `categories_scalars` row/relationship exists.
     *
     * @param int       $scalarId
     * @param int       $categoryId
     * @return boolean  True on success.
     * @throws Exception On query error.
     */
    public function scalarHasCategory($scalarId, $categoryId)
    {
        $sql = 'SELECT `scalar_id` '
             . 'FROM `categories_scalars` '
             . 'WHERE `category_id` = ? '
             . 'AND `scalar_id` = ?';

        $stmt = $this->_db->query($sql, array($categoryId, $scalarId));
        
        return (1 == $stmt->rowCount());
    }
    
    /**
     * Add `categories_scalars` row.
     *
     *      -   scalarHasCategory(scalar_id, category_id) available for validation.
     *
     * @param int       $scalarId
     * @param int       $categoryId
     * @return boolean  True on success.
     * @throws Exception On query error.
     */
    public function setScalarCategory($scalarId, $categoryId)
    {
        $sql = "REPLACE INTO {$this->_dbName}`categories_scalars` "
             . '(`category_id`, `scalar_id`) '
             . 'VALUES (?, ?)';

        $stmt = $this->_db->query($sql, array($categoryId, $scalarId));
        
        return (1 == $stmt->rowCount());
    }
    
    /**
     * Delete `categories_scalars` row.
     *
     *      -   scalarHasCategory(scalar_id, category_id) available for validation.
     *
     * @param int   $scalarId
     * @param int   $categoryId
     * @return boolean  True on success.
     * @throws Exception On query error.
     */
    public function unsetScalarCategory($scalarId, $categoryId)
    {
        $sql = 'DELETE FROM `categories_scalars` '
             . 'WHERE `category_id` = ? '
             . 'AND `scalar_id` = ?';

        $stmt = $this->_db->query($sql, array($categoryId, $scalarId));
        
        return (1 == $stmt->rowCount());
    }
    
    /**
     * Delete row from `scalars`.
     *
     * @param int       $id
     * @return boolean  True on success.
     * @throws Exception On query error.
     */
    public function deleteScalar($id)
    {
        $sql = 'DELETE FROM `scalars` WHERE `id` = ?';

        $stmt = $this->_db->query($sql, array($id));
        
        return (1 == $stmt->rowCount());
    }

    /**
     * Add new row to `categories`.
     *
     * @param string    $name
     * @param string    $description    Optional.
     * @return int      Inserted row ID.
     * @throws Exception On query error.
     */
    public function createCategory($name, $description = '')
    {
        $sql = "INSERT INTO {$this->_dbName}`categories` "
             . '(`name`, `description`) '
             . 'VALUES (?, ?)';

        $this->_db->query($sql, array($name, $description));
        
        return $this->_db->lastInsertId();
    }
    
    /**
     * Return all `categories` fields associated with an ID.
     *
     * @param int       $id
     * @return Array    Assoc. of fields; otherwise false.
     * @throws Exception On query error.
     */
    public function getCategoryById($id)
    {
        $sql = 'SELECT * '
             . "FROM {$this->_dbName}`categories` "
             . 'WHERE `id` = ?';

        $rows = $this->_db->fetchAll($sql, array($id));
        
        if (!$rows) {
            return false;
        }

        return $rows[0];
    }
    
    /**
     * Return all `categories` fields associated with a name.
     *
     * @param string    $name
     * @return Array    Assoc. of fields; otherwise false.
     * @throws Exception On query error.
     */
    public function getCategoryByName($name)
    {
        $sql = 'SELECT * '
             . "FROM {$this->_dbName}`categories` "
             . 'WHERE `name` = ? '
             . 'LIMIT 1';

        $rows = $this->_db->fetchAll($sql, array($name));

        if (!$rows) {
            return false;
        }

        return $rows[0];
    }
    
    /**
     * Delete row from `categories`.
     *
     * @param int       $id
     * @return boolean  True on success.
     * @throws Exception On query error.
     */
    public function deleteCategory($id)
    {
        $sql = 'DELETE FROM `categories` WHERE `id` = ?';

        $stmt = $this->_db->query($sql, array($id));
        
        return (1 == $stmt->rowCount());
    }

    /**
     * Add new row to `milestones`.
     *
     * @param string    $name
     * @param mixed     $when   UNIX timestamp or DATETIME string.
     * @return int      Inserted row ID.
     * @throws Exception On query error.
     */
    public function createMilestone($name, $when)
    {
        $when = Hashmark_Util::toDatetime($when);

        $sql = "INSERT INTO {$this->_dbName}`milestones` "
             . '(`name`, `when`) '
             . 'VALUES (?, ?)';

        $this->_db->query($sql, array($name, $when));
        
        return $this->_db->lastInsertId();
    }
    
    /**
     * Return all `milestones` fields associated with an ID.
     *
     * @param int       $id
     * @return Array    Assoc. of fields; otherwise false.
     * @throws Exception On query error.
     */
    public function getMilestoneById($id)
    {
        $sql = 'SELECT * '
             . "FROM {$this->_dbName}`milestones` "
             . 'WHERE `id` = ?';

        $rows = $this->_db->fetchAll($sql, array($id));
        
        if (!$rows) {
            return false;
        }

        return $rows[0];
    }
    
    /**
     * Return all `milestones` fields associated with a name.
     *
     * @param string    $name
     * @return Array    Assoc. of fields; otherwise false.
     * @throws Exception On query error.
     */
    public function getMilestoneByName($name)
    {
        $sql = 'SELECT * '
             . "FROM {$this->_dbName}`milestones` "
             . 'WHERE `name` = ? '
             . 'LIMIT 1';

        $rows = $this->_db->fetchAll($sql, array($name));

        if (!$rows) {
            return false;
        }

        return $rows[0];
    }
    
    /**
     * Update a milestone's fields.
     *
     * @param int       $id
     * @param string    $name
     * @param mixed     $when   UNIX timestamp or DATETIME string.
     * @return boolean  True on success.
     * @throws Exception On query error.
     */
    public function updateMilestone($id, $name, $when)
    {
        $when = Hashmark_Util::toDatetime($when);

        $sql = "UPDATE {$this->_dbName}`milestones` "
             . 'SET `name` = ?, '
             . '`when` = ? '
             . 'WHERE `id` = ?';

        $stmt = $this->_db->query($sql, array($name, $when, $id));
        
        return (1 == $stmt->rowCount());
    }
    
    /**
     * Verify a `categories_milestones` row/relationship exists.
     *
     * @param int       $milestoneId
     * @param int       $categoryId
     * @return boolean  True on success.
     * @throws Exception On query error.
     */
    public function milestoneHasCategory($milestoneId, $categoryId)
    {
        $sql = 'SELECT `milestone_id` '
             . "FROM {$this->_dbName}`categories_milestones` "
             . 'WHERE `category_id` = ? '
             . 'AND `milestone_id` = ?';

        $stmt = $this->_db->query($sql, array($categoryId, $milestoneId));
        
        return (1 == count($stmt->fetchAll()));
    }
    
    /**
     * Add `categories_milestones` row.
     *
     *      -   milestoneHasCategory(milestone_id, category_id) available for validation.
     *
     * @param int       $milestoneId
     * @param int       $categoryId
     * @return boolean  True on success.
     * @throws Exception On query error.
     */
    public function setMilestoneCategory($milestoneId, $categoryId)
    {
        $sql = "REPLACE INTO {$this->_dbName}`categories_milestones` "
             . '(`category_id`, `milestone_id`) '
             . 'VALUES (?, ?)';

        $stmt = $this->_db->query($sql, array($categoryId, $milestoneId));
        
        return (1 == $stmt->rowCount());
    }
    
    /**
     * Delete `categories_milestones` row.
     *
     *      -   milestoneHasCategory(milestone_id, category_id) available for validation.
     *
     * @param int   $milestoneId
     * @param int   $categoryId
     * @return boolean  True on success.
     * @throws Exception On query error.
     */
    public function unsetMilestoneCategory($milestoneId, $categoryId)
    {
        $sql = "DELETE FROM {$this->_dbName}`categories_milestones` "
             . 'WHERE `category_id` = ? '
             . 'AND `milestone_id` = ?';

        $stmt = $this->_db->query($sql, array($categoryId, $milestoneId));
        
        return (1 == $stmt->rowCount());
    }
    
    /**
     * Delete row from `milestones`.
     *
     * @param int       $id
     * @return boolean  True on success.
     * @throws Exception On query error.
     */
    public function deleteMilestone($id)
    {
        $sql = "DELETE FROM {$this->_dbName}`milestones` WHERE `id` = ?";

        $stmt = $this->_db->query($sql, array($id));
        
        return (1 == $stmt->rowCount());
    }
}
