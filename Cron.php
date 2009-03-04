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
 * @version     $Id: Cron.php 296 2009-02-13 05:03:11Z david $
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
     * @access public
     * @return int  Inserted row ID.
     * @throws Exception On query error.
     */
    public function startJob()
    {
        $this->_dbHelper->query($this->_db, $this->getSql(__FUNCTION__));
        
        if (1 == $this->_dbHelper->affectedRows($this->_db)) {
            return $this->_dbHelper->insertId($this->_db);
        }

        return false;
    }
    
    /**
     * Register the end of a job.
     *
     * @access public
     * @param int       $id
     * @return boolean  True on success.
     * @throws Exception On query error.
     */
    public function endJob($id)
    {
        $this->_dbHelper->query($this->_db, $this->getSql(__FUNCTION__), $id);
        
        return (1 == $this->_dbHelper->affectedRows($this->_db));
    }

    /**
     * Add new row to `samples`.
     *
     * @access public
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

        $this->_dbHelper->query($this->_db, $this->getSql(__FUNCTION__ . ':updateScalar'),
                                $value, $end, $scalarId);
        
        // queryAtDate() instead of query() so unit tests can
        // create backdated samples.
        $this->getModule('Partition')->queryAtDate($scalarId, $this->getSql(__FUNCTION__ . ':insertSample'),
                                                   $end, $jobId, $value, $start, $end);

        if (1 == $this->_dbHelper->affectedRows($this->_db)) {
            return true;
        }
        
        return false;
    }

    /**
     * Return all fields from a scalar's latest sample in the current partition.
     *
     * @access public
     * @param int       $scalarId
     * @return Array    Assoc. of fields; otherwise false.
     * @throws Exception On query error.
     */
    public function getLatestSample($scalarId)
    {
        $res = $this->getModule('Partition')->query($scalarId, $this->getSql(__FUNCTION__));

        if (!$this->_dbHelper->numRows($res)) {
            return false;
        }

        return $this->_dbHelper->fetchAssoc($res);
    }

    /**
     * Update a scalar's sampler status/error message.
     *
     * @access public
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

        $this->_dbHelper->query($this->_db, $this->getSql(__FUNCTION__), $status, $error, $scalarId);
        
        return (1 == $this->_dbHelper->affectedRows($this->_db));
    }
    
    /**
     * Find scalar samplers which are due right now.
     *  
     *   -  Due = Based on their frequency and last update, or have never ran.
     *   -  Only returns `scalars` fields necessary for resampling: `id`,
     *      `sampler_handler`, `sampler_status`
     *
     * @access public
     * @return Array    Assoc. of fields; otherwise false.
     * @throws Exception On query error.
     */
    public function getScheduledSamplers()
    {
        $res = $this->_dbHelper->query($this->_db, $this->getSql(__FUNCTION__));

        if (!$this->_dbHelper->numRows($res)) {
            return false;
        }

        $samplers = array();

        while ($scalar = $this->_dbHelper->fetchAssoc($res)) {
            $samplers[] = $scalar;
        }

        $this->_dbHelper->freeResult($res);

        return $samplers;
    }
}
