<?php
// vim: fenc=utf-8:ft=php:ai:si:ts=4:sw=4:et:

/**
 * Hashmark_Client
 *
 * @filesource
 * @link        http://code.google.com/p/hashmark/
 * @link        http://framework.zend.com/manual/en/coding-standard.html
 * @link        http://phpdoc.org/tutorial.php
 * @copyright   Copyright (c) 2008-2009, Code Actual LLC
 * @license     http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package     Hashmark
 * @subpackage  Base
 * @version     $Id: Client.php 296 2009-02-13 05:03:11Z david $
*/

/**
 * Interface for client applications.
 *
 * Enables scalar value reads/writes, sample writes, using scalar names as keys.
 *
 * @package     Hashmark
 * @subpackage  Base
 */
class Hashmark_Client extends Hashmark_Module_DbDependent
{
    /**
     * Set the value of a scalar identified by name.
     *
     * @access public
     * @param string    $scalarName
     * @param mixed     $value      Integers/floats OK if precision if PHP can accurately
     *                              convert to string. If not, supply as string.
     * @param boolean   $newSample  If true, a new sample partition row is inserted
     *                              `scalars` is updated.
     * @return boolean  True on success.
     * @throws Exception On query error or non-string $scalarName.
     */
    public function set($scalarName, $value, $newSample = false)
    {
        if (!is_string($scalarName)) {
            throw new Exception('Cannot look up scalar with a non-string name.',
                                HASHMARK_EXCEPTION_VALIDATION);
        }

        if ($newSample) {
            $sql = $this->getSql(__FUNCTION__ . ':updateScalarForNewSample');
            $this->_dbHelper->query($this->_db, $sql, $value, $scalarName);
            
            if (!$this->_dbHelper->affectedRows($this->_db)) {
                return false;
            }
            
            $partition = $this->getModule('Partition');
            
            $sql = $this->getSql(__FUNCTION__ . ':insertSample');
            $scalarId = $this->getModule('Core')->getScalarIdByName($scalarName);
            $partition->query($scalarId, $sql, $value);
        } else {
            $sql = $this->getSql(__FUNCTION__ . ':updateScalar');
            $this->_dbHelper->query($this->_db, $sql, $value, $scalarName);
        }

        return (1 == $this->_dbHelper->affectedRows($this->_db));
    }
    
    /**
     * Get the value of a scalar identified by name.
     *
     * @access public
     * @param string    $scalarName
     * @return mixed    Scalar value; null on miss.
     * @throws Exception On query error or non-string $scalarName.
     */
    public function get($scalarName)
    {
        if (!is_string($scalarName)) {
            throw new Exception('Cannot look up scalar with a non-string name.',
                                HASHMARK_EXCEPTION_VALIDATION);
        }

        $res = $this->_dbHelper->query($this->_db, $this->getSql(__FUNCTION__), $scalarName);

        if (!$this->_dbHelper->numRows($res)) {
            return false;
        }
        
        $scalar = $this->_dbHelper->fetchAssoc($res);
        $this->_dbHelper->freeResult($res);
        
        return $scalar['value'];
    }
    
    /**
     * Increment a scalar identified by name.
     *
     * @access public
     * @param string    $name
     * @param string    $amount
     * @param boolean   $newSample  If true, a new sample partition row is inserted
     *                              `scalars` is updated.
     * @return boolean  True on success.
     * @throws Exception On query error or non-string $scalarName.
     */
    public function incr($scalarName, $amount = '1', $newSample = false)
    {
        if (!is_string($scalarName)) {
            throw new Exception('Cannot look up scalar with a non-string name.',
                                HASHMARK_EXCEPTION_VALIDATION);
        }

        if (!preg_match('/[0-9.-]+/', $amount)) {
            throw new Exception('Cannot increment/decrement a scalar an invalid amount.',
                                HASHMARK_EXCEPTION_VALIDATION);
        }

        $values = array(':name' => $scalarName, '@amount' => $amount);
       
        if ($newSample) {
            $sql = $this->getSql(__FUNCTION__ . ':updateScalarForNewSample');
            $this->_dbHelper->query($this->_db, $sql, $values);
        
            if (!$this->_dbHelper->affectedRows($this->_db)) {
                return false;
            }

            $partition = $this->getModule('Partition');

            $sql = $this->getSql(__FUNCTION__ . ':insertSample');
            $scalarId = $this->getModule('Core')->getScalarIdByName($scalarName);
            $partition->query($scalarId, $sql, $scalarName);
        } else {
            $sql = $this->getSql(__FUNCTION__ . ':updateScalar');
            $this->_dbHelper->query($this->_db, $sql, $values);
        }

        return (1 == $this->_dbHelper->affectedRows($this->_db));
    }
    
    /**
     * Decrement a scalar identified by name.
     *
     * @access public
     * @param string    $name
     * @param string    $amount     Unsigned.
     * @param boolean   $newSample  If true, a new sample partition row is inserted
     *                              `scalars` is updated.
     * @return boolean  True on success.
     * @throws Exception On query error or non-string $scalarName.
     */
    public function decr($scalarName, $amount = '1', $newSample = false)
    {
        return $this->incr($scalarName, "-({$amount})", $newSample);
    }
}
