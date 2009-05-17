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
 * CRUD operations for scalars, milestones, categories, etc.
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
     * Add `scalars` row.
     *
     * @param Array     $fields     Assoc. scalar properties.
     *
     *      Required:
     *
     *      'name'
     *      'type':         See getValidScalarTypes() for options.
     *
     *      Optional:
     *
     *      'value':         Initial value.
     *      'description'
     *
     * @return int      Inserted row ID.
     * @throws Exception    On query error; if 'name'/'type' are empty/missing;
     *                      if 'type' is invalid.
     */
    public function createScalar($fields)
    {
        if (empty($fields['name']) || empty($fields['type'])) {
            throw new Exception('Scalar name and type are required.',
                                HASHMARK_EXCEPTION_VALIDATION);
        }

        $fields['name'] = trim($fields['name']);
        
        if (!$fields['name']) {
            throw new Exception('Scalar name cannot be empty.',
                                HASHMARK_EXCEPTION_VALIDATION);
        }
        
        if (!in_array($fields['type'], $this->getModule('Core')->getValidScalarTypes())) {
            throw new Exception('Cannot create table of unrecognized type: ' . $fields['type'],
                                HASHMARK_EXCEPTION_VALIDATION);
        }

        $fields['value'] = isset($fields['value']) ? $fields['value'] : '';
        $fields['description'] = isset($fields['description']) ? $fields['description'] : '';

        $sql = "INSERT INTO {$this->_dbName}`scalars` "
             . '(`name`, `value`, `type`, `description`) '
             . 'VALUES (?, ?, ?, ?)';
                                  
        $args = array($fields['name'], $fields['value'], $fields['type'], $fields['description']);

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
     * Return valid `agents_scalars`.`status` ENUM values.
     *
     * @return Array
     */
    public static function getValidScalarAgentStatuses()
    {
        return array('Unscheduled', 'Scheduled', 'Running');
    }

    /**
     * Add new row to `agents`.
     *
     * @param string    $name   Ex. 'ScalarValue` to identify
     *                          class Hashmark_Agent_ScalarValue.
     * @return int      Inserted row ID.
     * @throws Exception On query error.
     */
    public function createAgent($name)
    {
        $sql = "INSERT INTO {$this->_dbName}`agents` "
             . '(`name`) '
             . 'VALUES (?)';

        $this->_db->query($sql, array($name));
        
        return $this->_db->lastInsertId();
    }
    
    /**
     * Return all `agents` fields associated with an ID.
     *
     * @param int       $id
     * @return Array    Assoc. of fields; otherwise false.
     * @throws Exception On query error.
     */
    public function getAgentById($id)
    {
        $sql = 'SELECT * '
             . "FROM {$this->_dbName}`agents` "
             . 'WHERE `id` = ?';

        $rows = $this->_db->fetchAll($sql, array($id));

        if (!$rows) {
            return false;
        }

        return $rows[0];
    }
    
    /**
     * Return all `agents` fields associated with a name.
     *
     * @param string    $name
     * @return Array    Assoc. of fields; otherwise false.
     * @throws Exception On query error.
     */
    public function getAgentByName($name)
    {
        $sql = 'SELECT * '
             . "FROM {$this->_dbName}`agents` "
             . 'WHERE `name` = ? '
             . 'LIMIT 1';

        $rows = $this->_db->fetchAll($sql, array($name));
        
        if (!$rows) {
            return false;
        }

        return $rows[0];
    }
    
    /**
     * Delete row from `agents`.
     *
     * @param int       $id
     * @return boolean  True on success.
     * @throws Exception On query error.
     */
    public function deleteAgent($id)
    {
        $sql = 'DELETE FROM `agents` WHERE `id` = ?';

        $stmt = $this->_db->query($sql, array($id));
        
        return (1 == $stmt->rowCount());
    }

    /**
     * Add new row to `agents_scalars`.
     *
     * @param int       $scalarId
     * @param int       $agentId
     * @param int       $frequency  Recurrence interval in minutes.
     * @param Array     $config     Will be passed to agent class whenever ran.
     * @param string    $status     Ex. 'Scheduled'
     * @param string    $start      Earliest possible sampling as UNIX timestamp
     *                              or DATETIME string.
     * @return int  Inserted row ID.
     * @see Hashmark_Core::getValidScalarAgentStatuses() for $status options.
     * @throws Exception On query error.
     */
    public function createScalarAgent($scalarId, $agentId, $frequency, $status = 'Unscheduled', $config = '', $start = HASHMARK_DATETIME_EMPTY)
    {
        if ('' !== $config) {
            $config = base64_encode(serialize($config));
        }
        
        if (is_int($start)) {
            $start = gmdate(HASHMARK_DATETIME_FORMAT, $start);
        }

        $sql = "INSERT INTO {$this->_dbName}`agents_scalars` "
             . '(`scalar_id`, `agent_id`, `frequency`, `config`, `status`, `start`) '
             . 'VALUES (?, ?, ?, ?, ?, ?)';

        $this->_db->query($sql, array($scalarId, $agentId, $frequency, $config, $status, $start));
        
        return $this->_db->lastInsertId();
    }
    
    /**
     * Return all `agents_scalars` fields associated with an ID.
     *
     * @param int       $id
     * @return Array    Assoc. of fields; otherwise false.
     * @throws Exception On query error.
     */
    public function getScalarAgentById($id)
    {
        $sql = 'SELECT * '
             . "FROM {$this->_dbName}`agents_scalars` "
             . 'WHERE `id` = ?';

        $rows = $this->_db->fetchAll($sql, array($id));

        if (!$rows) {
            return false;
        }

        if ('' !== $rows[0]['config']) {
            $rows[0]['config'] = unserialize(base64_decode($rows[0]['config']));
        }

        return $rows[0];
    }
    
    /**
     * Delete row from `agents_scalars`.
     *
     * @param int       $id
     * @return boolean  True on success.
     * @throws Exception On query error.
     */
    public function deleteScalarAgent($id)
    {
        $sql = 'DELETE FROM `agents_scalars` WHERE `id` = ?';

        $stmt = $this->_db->query($sql, array($id));
        
        return (1 == $stmt->rowCount());
    }

    /**
     * Update an scalar's agent status and error message.
     *
     * @param int       $id         `agents_scalars`.`id`
     * @param string    $status     Ex. 'Scheduled'.
     * @param string    $error      Optional error message.
     * @param mixed     $lastrun    Optional UNIX timestamp or DATETIME string.
     * @return boolean  True on success.
     * @see Hashmark_Core::getValidScalarAgentStatuses() for $status options.
     * @throws Exception On query error.
     */
    public function setScalarAgentStatus($id, $status, $error = '', $lastrun = '')
    {
        if ($lastrun) {
            if (is_int($lastrun)) {
                $lastrun = gmdate(HASHMARK_DATETIME_FORMAT, $lastrun);
            }

            $sql = "UPDATE {$this->_dbName}`agents_scalars` "
                 . 'SET `status` = ?, '
                 . '`error` = ?, '
                 . '`lastrun` = ? '
                 . 'WHERE `id` = ?';
    
            $stmt = $this->_db->query($sql, array($status, $error, $lastrun, $id));
        } else {
            $sql = "UPDATE {$this->_dbName}`agents_scalars` "
                 . 'SET `status` = ?, '
                 . '`error` = ? '
                 . 'WHERE `id` = ?';
    
            $stmt = $this->_db->query($sql, array($status, $error, $id));
        }
        
        return (1 == $stmt->rowCount());
    }
    
    /**
     * Find scalar agents which are due to run right now.
     *  
     *   -  Due: Based on frequency, last run time, and if they've
     *      successfully ran before.
     *
     * @return Array    Assoc. of fields; otherwise false.
     * @throws Exception On query error.
     */
    public function getScheduledAgents()
    {
        // "Running" means it's scheduled but the last run didn't finish.
        $statusMatch = '(`status` IN ("Scheduled", "Running"))';
        // Recurrence interval has been reached.
        $isDue = '(`lastrun` + INTERVAL `frequency` MINUTE <= UTC_TIMESTAMP())';
        // Start date/time has been reached or none was specified.
        $canStart = '(`start` = ? OR `start` <= UTC_TIMESTAMP())';
        $hasNeverFinished = '`lastrun` = ?';

        $sql = 'SELECT `map`.`id`, `agent_id`, `scalar_id`, `config`, `status`, `name`, `error` '
             . "FROM {$this->_dbName}`agents_scalars` AS `map` "
             . "JOIN {$this->_dbName}`agents` AS `agent` ON `map`.`agent_id` = `agent`.`id` "
             . "WHERE {$statusMatch} "
             . "AND {$canStart} "
             . "AND ({$isDue} OR {$hasNeverFinished}) ";

        $rows = $this->_db->fetchAll($sql, array(HASHMARK_DATETIME_EMPTY, HASHMARK_DATETIME_EMPTY));

        if (!$rows) {
            return false;
        }

        return $rows;
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
