<?php
// vim: fenc=utf-8:ft=php:ai:si:ts=4:sw=4:et:

/**
 * Hashmark_Cron
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
 * Support class for Cron/Tool/ scripts.
 *
 * Mostly of methods too specialized to fit in Hashmark_Core. Expect this class
 * to morph/split as the cron script suite expands and matures.
 * 
 * @package     Hashmark
 * @subpackage  Base
 */
class Hashmark_Cron extends Hashmark_Module_DbDependent
{
    /**
     * Add new row to `jobs`.
     *
     * @return int  Inserted row ID.
     * @throws Exception On query error.
     */
    public function startJob()
    {
        $sql = "INSERT INTO {$this->_dbName}`jobs` "
             . '(`start`) '
             . 'VALUES (UTC_TIMESTAMP())';

        $this->_db->query($sql);
        
        return $this->_db->lastInsertId();
    }
    
    /**
     * Register the end of a job.
     *
     * @param int       $id
     * @return boolean  True on success.
     * @throws Exception On query error.
     */
    public function endJob($id)
    {
        $sql = "UPDATE {$this->_dbName}`jobs` "
             . 'SET `end` = UTC_TIMESTAMP() '
             . 'WHERE `id` = ?';

        $stmt = $this->_db->query($sql, array($id));
        
        return (1 == $stmt->rowCount());
    }

    /**
     * Add new row to `samples`.
     *
     * @param int       $scalarId
     * @param int       $jobId 
     * @param string    $value
     * @param mixed     $start  UNIX timestamp or DATETIME string.
     * @param mixed     $end    UNIX timestamp or DATETIME string.
     * @return boolean  True on success.
     * @throws Exception On query error.
     */
    public function createSample($scalarId, $jobId, $value, $start, $end)
    {
        $start = Hashmark_Util::toDatetime($start);
        $end = Hashmark_Util::toDatetime($end);

        /**
         *      -   Synchronize value from latest Cron-obtained value.
         *      -   Record time of sync.
         *      -   Reset any past sampler error to flag successful write.
         *      -   Flip sampler status from "Running" back to "Scheduled".
         *      -   Increment the count used to seed sample partition table AUTO_INCREMENT
         *          values for `id`.
         */
        $sql = "UPDATE {$this->_dbName}`scalars` "
             . 'SET `value` = ?, '
             . '`last_sample_change` = ?, '
             . '`sampler_error` = "", '
             . '`sampler_status` = "Scheduled", '
             . '`sample_count` = `sample_count` + 1 '
             . 'WHERE `id` = ?';

        $this->_db->query($sql, array($value, $end, $scalarId));
        
        $sql = 'INSERT INTO ~samples '
             . '(`job_id`, `value`, `start`, `end`) '
             . 'VALUES (?, ?, ?, ?)';
        
        // queryAtDate() instead of queryCurrent() so unit tests can
        // create backdated samples.
        $bind = array($jobId, $value, $start, $end);
        $stmt = $this->getModule('Partition')->queryAtDate($scalarId, $sql, $end, $bind);

        return (1 == $stmt->rowCount());
    }

    /**
     * Return all fields from a scalar's latest sample in the current partition.
     *
     * @param int       $scalarId
     * @return Array    Assoc. of fields; otherwise false.
     * @throws Exception On query error.
     */
    public function getLatestSample($scalarId)
    {
        $sql = 'SELECT * FROM ~samples ORDER BY `id` DESC LIMIT 1';

        $stmt = $this->getModule('Partition')->queryCurrent($scalarId, $sql);

        $rows = $stmt->fetchAll();
        if (!$rows) {
            return false;
        }

        return $rows[0];
    }

    /**
     * Update a scalar's sampler status/error message.
     *
     * @param int       $scalarId
     * @param string    $status     New `scalars`.`sampler_status` ENUM value, ex. 'Scheduled'.
     * @param string    $error      New `scalars`.`sampler_error` message.
     * @return boolean  True on success.
     * @throws Exception On query error.
     */
    public function setSamplerStatus($scalarId, $status, $error = '')
    {
        if ($error) {
            // Expecting error strings that exceed column length, ex. Exception
            // messages with debugging info.
            mb_internal_encoding('UTF-8');
            $error = mb_substr($error, 0, 255);
        }

        $sql = "UPDATE {$this->_dbName}`scalars` "
             . 'SET `sampler_status` = ?, '
             . '`sampler_error` = ? '
             . 'WHERE `id` = ?';

        $stmt = $this->_db->query($sql, array($status, $error, $scalarId));
        
        return (1 == $stmt->rowCount());
    }
    
    /**
     * Find scalar samplers which are due right now.
     *  
     *   -  Due = Based on their frequency and last update, or have never ran.
     *   -  Only returns `scalars` fields necessary for resampling: `id`,
     *      `sampler_name`, `sampler_status`
     *
     * @return Array    Assoc. of fields; otherwise false.
     * @throws Exception On query error.
     */
    public function getScheduledSamplers()
    {
        // "Running" means it's scheduled but the last run didn't finish.
        $statusMatch = '(`sampler_status` IN ("Scheduled", "Running"))';
        // Recurrence interval has been reached.
        $isDue = '(`last_sample_change` + INTERVAL `sampler_frequency` MINUTE <= UTC_TIMESTAMP())';
        // Start date/time has been reached or none was specified.
        $canStart = '(`sampler_start` = ? OR `sampler_start` <= UTC_TIMESTAMP())';
        $hasNeverFinished = '`last_sample_change` = ?';

        $sql = 'SELECT `id`, `sampler_name`, `sampler_status` '
             . "FROM {$this->_dbName}`scalars` "
             . "WHERE {$statusMatch} "
             . "AND ({$isDue} OR {$hasNeverFinished}) "
             . "AND {$canStart} "
             . 'AND `sampler_name` != ""';

        $rows = $this->_db->fetchAll($sql, array(HASHMARK_DATETIME_EMPTY, HASHMARK_DATETIME_EMPTY));

        if (!$rows) {
            return false;
        }

        return $rows;
    }
}
