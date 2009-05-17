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
     * Add new row to `samples` and update `scalars` statistics.
     *
     * @param int       $scalarId
     * @param string    $value
     * @param mixed     $start  UNIX timestamp or DATETIME string.
     * @param mixed     $end    UNIX timestamp or DATETIME string.
     * @return boolean  True on success.
     * @throws Exception On query error.
     */
    public function createSample($scalarId, $value, $start, $end)
    {
        $start = Hashmark_Util::toDatetime($start);
        $end = Hashmark_Util::toDatetime($end);

        // `sample_count` seeds AUTO_INCREMENT `id` values in sample partitions
        $sql = "UPDATE {$this->_dbName}`scalars` "
             . 'SET `value` = ?, '
             . '`last_agent_change` = ?, '
             . '`sample_count` = `sample_count` + 1 '
             . 'WHERE `id` = ?';

        $this->_db->query($sql, array($value, $end, $scalarId));
        
        $sql = 'INSERT INTO ~samples '
             . '(`value`, `start`, `end`) '
             . 'VALUES (?, ?, ?)';
        
        // queryAtDate() instead of queryCurrent() so unit tests can
        // create backdated samples.
        $bind = array($value, $start, $end);
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
     * Update an scalar's agent status and error message.
     *
     * @param int       $id         `agents_scalars`.`id`
     * @param string    $status     Ex. 'Scheduled'.
     * @param string    $error      Optional error message.
     * @param mixed     $lastrun    Optional UNIX timestamp or DATETIME string.
     * @return boolean  True on success.
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

        $sql = 'SELECT `map`.`id`, `agent_id`, `scalar_id`, `config`, `status`, `name` '
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
}
