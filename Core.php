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
 * @version     $Id: Core.php 296 2009-02-13 05:03:11Z david $
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
     * @static
     * @access public
     * @return Array
     */
    public static function getValidScalarTypes()
    {
        return array('decimal', 'string');
    }

    /**
     * Return all valid `scalars`.`sampler_status` ENUM values.
     *
     * @static
     * @access public
     * @return Array
     */
    public static function getValidSampleStatuses()
    {
        return array('Unscheduled', 'Scheduled', 'Running');
    }
    
    /**
     * Return all `jobs` fields associated with an ID.
     *
     * @access public
     * @param int       $id
     * @return Array    Assoc. of fields; otherwise false.
     * @throws Exception On query error.
     */
    public function getJobById($id)
    {
        $sql = 'SELECT * '
             . 'FROM `jobs` '
             . 'WHERE `id` = ?';

        $res = $this->_dbHelper->query($this->_db, $sql, $id);

        if (!$this->_dbHelper->numRows($res)) {
            return false;
        }

        $job = $this->_dbHelper->fetchAssoc($res);
        $this->_dbHelper->freeResult($res);

        return $job;
    }

    /**
     * Add `scalars` row.
     *
     * @access public
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
     *      'sampler_handler`:      Hashmark_Sampler_* implementation name, ex. 'SomeFeatureUsage`.
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
        $fields['sampler_handler'] = isset($fields['sampler_handler']) ? $fields['sampler_handler'] : '';
        $fields['sampler_status'] = isset($fields['sampler_status']) ? $fields['sampler_status'] : 'Unscheduled';
        
        if (is_int($fields['sampler_start'])) {
            $fields['sampler_start'] = gmdate(HASHMARK_DATETIME_FORMAT, $fields['sampler_start']);
        }

        $sql = 'INSERT INTO `scalars` '
             . '(`name`, `value`, `type`, `description`, `sampler_frequency`, '
             . '`sampler_start`, `sampler_handler`, `sampler_status`) '
             . 'VALUES (?, ?, ?, ?, ?, ?, ?, ?)';

        $this->_dbHelper->query($this->_db, $sql, $fields['name'],
                                $fields['value'], $fields['type'],
                                $fields['description'],$fields['sampler_frequency'],
                                $fields['sampler_start'],$fields['sampler_handler'],
                                $fields['sampler_status']);
        
        return $this->_dbHelper->insertId($this->_db);
    }
    
    /**
     * Return all `scalars` fields associated with an ID.
     *
     * @access public
     * @param int       $id
     * @return Array    Assoc. of fields; otherwise false.
     * @throws Exception On query error.
     */
    public function getScalarById($id)
    {
        $sql = 'SELECT * '
             . 'FROM `scalars` '
             . 'WHERE `id` = ?';

        $res = $this->_dbHelper->query($this->_db, $sql, $id);

        if (!$this->_dbHelper->numRows($res)) {
            return false;
        }

        $scalar = $this->_dbHelper->fetchAssoc($res);
        $this->_dbHelper->freeResult($res);

        return $scalar;
    }
    
    /**
     * Return all `scalars` fields associated with a name.
     *
     * @access public
     * @param string    $name
     * @return Array    Assoc. of fields; otherwise false.
     * @throws Exception On query error.
     */
    public function getScalarByName($name)
    {
        $sql = 'SELECT * '
             . 'FROM `scalars` '
             . 'WHERE `name` = ? '
             . 'LIMIT 1';

        $res = $this->_dbHelper->query($this->_db, $sql, $name);

        if (!$this->_dbHelper->numRows($res)) {
            return false;
        }

        $scalar = $this->_dbHelper->fetchAssoc($res);
        $this->_dbHelper->freeResult($res);
        
        return $scalar;
    }
    
    /**
     * Return `scalars`.`type` associated with an ID.
     *
     * @access public
     * @param int       $id
     * @return string   See Hashmark_Core::getValidScalarTypes() for possible values.
     * @throws Exception On query error.
     */
    public function getScalarType($id)
    {
        $sql = 'SELECT `type` '
             . 'FROM `scalars` '
             . 'WHERE `id` = ?';

        $res = $this->_dbHelper->query($this->_db, $sql, $id);

        if (!$this->_dbHelper->numRows($res)) {
            return false;
        }

        $scalar = $this->_dbHelper->fetchAssoc($res);
        $this->_dbHelper->freeResult($res);

        return $scalar['type'];
    }
    
    /**
     * Return `scalars`.`sample_count` associated with an ID.
     *
     * @access public
     * @param int       $id
     * @return string
     * @throws Exception On query error.
     */
    public function getScalarSampleCount($id)
    {
        $sql = 'SELECT `sample_count` '
             . 'FROM `scalars` '
             . 'WHERE `id` = ?';

        $res = $this->_dbHelper->query($this->_db, $sql, $id);

        if (!$this->_dbHelper->numRows($res)) {
            return false;
        }

        $scalar = $this->_dbHelper->fetchAssoc($res);
        $this->_dbHelper->freeResult($res);

        return $scalar['sample_count'];
    }
    
    /**
     * Return the `id` associated with a scalar name.
     *
     * @access public
     * @param string    $name
     * @return string
     * @throws Exception On query error.
     */
    public function getScalarIdByName($name)
    {
        $sql = 'SELECT `id` '
             . 'FROM `scalars` '
             . 'WHERE `name` = ?';

        $res = $this->_dbHelper->query($this->_db, $sql, $name);

        if (!$this->_dbHelper->numRows($res)) {
            return false;
        }

        $scalar = $this->_dbHelper->fetchAssoc($res);
        $this->_dbHelper->freeResult($res);

        return $scalar['id'];
    }
    
    /**
     * Verify a `categories_scalars` row/relationship exists.
     *
     * @access public
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

        $res = $this->_dbHelper->query($this->_db, $sql, $categoryId, $scalarId);
        
        $exists = (1 == $this->_dbHelper->numRows($res));
        $this->_dbHelper->freeResult($res);

        return $exists;
    }
    
    /**
     * Add `categories_scalars` row.
     *
     *      -   scalarHasCategory(scalar_id, category_id) available for validation.
     *
     * @access public
     * @param int       $scalarId
     * @param int       $categoryId
     * @return boolean  True on success.
     * @throws Exception On query error.
     */
    public function setScalarCategory($scalarId, $categoryId)
    {
        $sql = 'REPLACE INTO `categories_scalars` '
             . '(`category_id`, `scalar_id`) '
             . 'VALUES (?, ?)';

        $this->_dbHelper->query($this->_db, $sql, $categoryId, $scalarId);
        
        return (1 == $this->_dbHelper->affectedRows($this->_db));
    }
    
    /**
     * Delete `categories_scalars` row.
     *
     *      -   scalarHasCategory(scalar_id, category_id) available for validation.
     *
     * @access public
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

        $this->_dbHelper->query($this->_db, $sql, $categoryId, $scalarId);
        
        return (1 == $this->_dbHelper->affectedRows($this->_db));
    }
    
    /**
     * Delete row from `scalars`.
     *
     * @access public
     * @param int       $id
     * @return boolean  True on success.
     * @throws Exception On query error.
     */
    public function deleteScalar($id)
    {
        $sql = 'DELETE FROM `scalars` WHERE `id` = ?';

        $this->_dbHelper->query($this->_db, $sql, $id);

        return (1 == $this->_dbHelper->affectedRows($this->_db));
    }

    /**
     * Add new row to `categories`.
     *
     * @access public
     * @param string    $name
     * @param string    $description    Optional.
     * @return int      Inserted row ID.
     * @throws Exception On query error.
     */
    public function createCategory($name, $description = '')
    {
        $sql = 'INSERT INTO `categories` '
             . '(`name`, `description`) '
             . 'VALUES (?, ?)';

        $this->_dbHelper->query($this->_db, $sql, $name, $description);

        return $this->_dbHelper->insertId($this->_db);
    }
    
    /**
     * Return all `categories` fields associated with an ID.
     *
     * @access public
     * @param int       $id
     * @return Array    Assoc. of fields; otherwise false.
     * @throws Exception On query error.
     */
    public function getCategoryById($id)
    {
        $sql = 'SELECT * '
             . 'FROM `categories` '
             . 'WHERE `id` = ?';

        $res = $this->_dbHelper->query($this->_db, $sql, $id);

        if (!$this->_dbHelper->numRows($res)) {
            return false;
        }

        $category = $this->_dbHelper->fetchAssoc($res);
        $this->_dbHelper->freeResult($res);

        return $category;
    }
    
    /**
     * Return all `categories` fields associated with a name.
     *
     * @access public
     * @param string    $name
     * @return Array    Assoc. of fields; otherwise false.
     * @throws Exception On query error.
     */
    public function getCategoryByName($name)
    {
        $sql = 'SELECT * '
             . 'FROM `categories` '
             . 'WHERE `name` = ? '
             . 'LIMIT 1';

        $res = $this->_dbHelper->query($this->_db, $sql, $name);

        if (!$this->_dbHelper->numRows($res)) {
            return false;
        }

        $category = $this->_dbHelper->fetchAssoc($res);
        $this->_dbHelper->freeResult($res);
        
        return $category;
    }
    
    /**
     * Delete row from `categories`.
     *
     * @access public
     * @param int       $id
     * @return boolean  True on success.
     * @throws Exception On query error.
     */
    public function deleteCategory($id)
    {
        $sql = 'DELETE FROM `categories` WHERE `id` = ?';

        $this->_dbHelper->query($this->_db, $sql, $id);

        return (1 == $this->_dbHelper->affectedRows($this->_db));
    }

    /**
     * Add new row to `milestones`.
     *
     * @access public
     * @param string    $name
     * @param mixed     $when   UNIX timestamp or DATETIME string.
     * @return int      Inserted row ID.
     * @throws Exception On query error.
     */
    public function createMilestone($name, $when)
    {
        $when = Hashmark_Util::toDatetime($when);

        $sql = 'INSERT INTO `milestones` '
             . '(`name`, `when`) '
             . 'VALUES (?, ?)';

        $this->_dbHelper->query($this->_db, $sql, $name, $when);

        return $this->_dbHelper->insertId($this->_db);
    }
    
    /**
     * Return all `milestones` fields associated with an ID.
     *
     * @access public
     * @param int       $id
     * @return Array    Assoc. of fields; otherwise false.
     * @throws Exception On query error.
     */
    public function getMilestoneById($id)
    {
        $sql = 'SELECT * '
             . 'FROM `milestones` '
             . 'WHERE `id` = ?';

        $res = $this->_dbHelper->query($this->_db, $sql, $id);

        if (!$this->_dbHelper->numRows($res)) {
            return false;
        }

        $milestone = $this->_dbHelper->fetchAssoc($res);
        $this->_dbHelper->freeResult($res);

        return $milestone;
    }
    
    /**
     * Return all `milestones` fields associated with a name.
     *
     * @access public
     * @param string    $name
     * @return Array    Assoc. of fields; otherwise false.
     * @throws Exception On query error.
     */
    public function getMilestoneByName($name)
    {
        $sql = 'SELECT * '
             . 'FROM `milestones` '
             . 'WHERE `name` = ? '
             . 'LIMIT 1';

        $res = $this->_dbHelper->query($this->_db, $sql, $name);

        if (!$this->_dbHelper->numRows($res)) {
            return false;
        }

        $milestone = $this->_dbHelper->fetchAssoc($res);
        $this->_dbHelper->freeResult($res);

        return $milestone;
    }
    
    /**
     * Update a milestone's fields.
     *
     * @access public
     * @param int       $id
     * @param string    $name
     * @param mixed     $when   UNIX timestamp or DATETIME string.
     * @return boolean  True on success.
     * @throws Exception On query error.
     */
    public function updateMilestone($id, $name, $when)
    {
        $when = Hashmark_Util::toDatetime($when);

        $sql = 'UPDATE `milestones` '
             . 'SET `name` = ?, '
             . '`when` = ? '
             . 'WHERE `id` = ?';

        $this->_dbHelper->query($this->_db, $sql, $name, $when, $id);
        
        return (1 == $this->_dbHelper->affectedRows($this->_db));
    }
    
    /**
     * Verify a `categories_milestones` row/relationship exists.
     *
     * @access public
     * @param int       $milestoneId
     * @param int       $categoryId
     * @return boolean  True on success.
     * @throws Exception On query error.
     */
    public function milestoneHasCategory($milestoneId, $categoryId)
    {
        $sql = 'SELECT `milestone_id` '
             . 'FROM `categories_milestones` '
             . 'WHERE `category_id` = ? '
             . 'AND `milestone_id` = ?';

        $res = $this->_dbHelper->query($this->_db, $sql, $categoryId, $milestoneId);
        
        $exists = (1 == $this->_dbHelper->numRows($res));
        $this->_dbHelper->freeResult($res);

        return $exists;
    }
    
    /**
     * Add `categories_milestones` row.
     *
     *      -   milestoneHasCategory(milestone_id, category_id) available for validation.
     *
     * @access public
     * @param int       $milestoneId
     * @param int       $categoryId
     * @return boolean  True on success.
     * @throws Exception On query error.
     */
    public function setMilestoneCategory($milestoneId, $categoryId)
    {
        $sql = 'REPLACE INTO `categories_milestones` '
             . '(`category_id`, `milestone_id`) '
             . 'VALUES (?, ?)';

        $this->_dbHelper->query($this->_db, $sql, $categoryId, $milestoneId);
        
        return (1 == $this->_dbHelper->affectedRows($this->_db));
    }
    
    /**
     * Delete `categories_milestones` row.
     *
     *      -   milestoneHasCategory(milestone_id, category_id) available for validation.
     *
     * @access public
     * @param int   $milestoneId
     * @param int   $categoryId
     * @return boolean  True on success.
     * @throws Exception On query error.
     */
    public function unsetMilestoneCategory($milestoneId, $categoryId)
    {
        $sql = 'DELETE FROM `categories_milestones` '
             . 'WHERE `category_id` = ? '
             . 'AND `milestone_id` = ?';

        $this->_dbHelper->query($this->_db, $sql, $categoryId, $milestoneId);
        
        return (1 == $this->_dbHelper->affectedRows($this->_db));
    }
    
    /**
     * Delete row from `milestones`.
     *
     * @access public
     * @param int       $id
     * @return boolean  True on success.
     * @throws Exception On query error.
     */
    public function deleteMilestone($id)
    {
        $sql = 'DELETE FROM `milestones` WHERE `id` = ?';

        $this->_dbHelper->query($this->_db, $sql, $id);

        return (1 == $this->_dbHelper->affectedRows($this->_db));
    }
}
