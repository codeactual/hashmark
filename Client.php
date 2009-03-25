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
            $sql = "UPDATE {$this->_dbName}`scalars` "
                 . 'SET `value` = ?, '
                 . '`last_sample_change` = UTC_TIMESTAMP(), '
                 . '`last_inline_change` = UTC_TIMESTAMP() '
                 . 'WHERE `name` = ?';

            $this->_dbHelper->query($this->_db, $sql, $value, $scalarName);
            
            if (!$this->_dbHelper->affectedRows($this->_db)) {
                return false;
            }
            
            $sql = 'INSERT INTO ~samples '
                 . '(`value`, `start`, `end`) '
                 . 'VALUES (?, UTC_TIMESTAMP(), UTC_TIMESTAMP())';

            $scalarId = $this->getModule('Core')->getScalarIdByName($scalarName);

            $partition = $this->getModule('Partition');
            $partition->query($scalarId, $sql, $value);
        } else {
            $sql = "UPDATE {$this->_dbName}`scalars` "
                 . 'SET `value` = ?, '
                 . '`last_inline_change` = UTC_TIMESTAMP() '
                 . 'WHERE `name` = ?';

            $this->_dbHelper->query($this->_db, $sql, $value, $scalarName);
        }

        return (1 == $this->_dbHelper->affectedRows($this->_db));
    }
    
    /**
     * Get the value of a scalar identified by name or ID.
     *
     * @access public
     * @param mixed     $scalarNameOrId     Uses string/int type check.
     * @return mixed    Scalar value; null on miss.
     * @throws Exception On query error or non-string $scalarName.
     */
    public function get($scalarNameOrId)
    {
        if (is_string($scalarNameOrId)) {
            $sql = 'SELECT `value` '
                 . "FROM {$this->_dbName}`scalars` "
                 . 'WHERE `name` = ? '
                 . 'LIMIT 1';
        } else if (is_int($scalarNameOrId)) {
            $sql = 'SELECT `value` '
                 . "FROM {$this->_dbName}`scalars` "
                 . 'WHERE `id` = ? '
                 . 'LIMIT 1';
        } else {
            throw new Exception('Cannot look up scalar with a non-string name.',
                                HASHMARK_EXCEPTION_VALIDATION);
        }

        $res = $this->_dbHelper->query($this->_db, $sql, $scalarNameOrId);

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
        $sum = 'CONVERT(`value`, DECIMAL' . HASHMARK_DECIMAL_SQLWIDTH . ') + @amount';
       
        if ($newSample) {
            $sql = "UPDATE {$this->_dbName}`scalars` "
                 . "SET `value` = {$sum}, "
                 . '`last_sample_change` = UTC_TIMESTAMP(), '
                 . '`last_inline_change` = UTC_TIMESTAMP() '
                 . 'WHERE `name` = :name '
                 . 'AND `type` = "decimal"';

            $this->_dbHelper->query($this->_db, $sql, $values);
        
            if (!$this->_dbHelper->affectedRows($this->_db)) {
                return false;
            }

            $currentScalarValue = 'SELECT `value` FROM `scalars` WHERE `name` = ? LIMIT 1';

            $sql = 'INSERT INTO ~samples '
                 . '(`value`, `start`, `end`) '
                 . "VALUES (({$currentScalarValue}), UTC_TIMESTAMP(), UTC_TIMESTAMP())";

            $scalarId = $this->getModule('Core')->getScalarIdByName($scalarName);

            $partition = $this->getModule('Partition');
            $partition->query($scalarId, $sql, $scalarName);
        } else {
            $sql = "UPDATE {$this->_dbName}`scalars` "
                 . "SET `value` = {$sum}, "
                 . '`last_inline_change` = UTC_TIMESTAMP() '
                 . 'WHERE `name` = :name '
                 . 'AND `type` = "decimal"';

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
