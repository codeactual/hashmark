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
     * @var boolean     If true, incr()/decr() will create the named scalar
     *                  if it doesn't exist.
     */
    protected $_createScalarIfNotExists = false;

    /**
     * Set the value of a scalar identified by name.
     *
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

            $stmt = $this->_db->query($sql, array($value, $scalarName));
            
            if (!$stmt->rowCount()) {
                return false;
            }

            unset($stmt);
            
            $sql = 'INSERT INTO ~samples '
                 . '(`value`, `start`, `end`) '
                 . 'VALUES (?, UTC_TIMESTAMP(), UTC_TIMESTAMP())';

            $scalarId = $this->getModule('Core')->getScalarIdByName($scalarName);

            $partition = $this->getModule('Partition');
            $stmt = $partition->queryCurrent($scalarId, $sql, array($value));
        } else {
            $sql = "UPDATE {$this->_dbName}`scalars` "
                 . 'SET `value` = ?, '
                 . '`last_inline_change` = UTC_TIMESTAMP() '
                 . 'WHERE `name` = ?';

            $stmt = $this->_db->query($sql, array($value, $scalarName));
        }

        return (1 == $stmt->rowCount());
    }
    
    /**
     * Get the value of a scalar identified by name or ID.
     *
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

        $rows = $this->_db->fetchAll($sql, array($scalarNameOrId));

        if (!$rows) {
            return false;
        }
        
        return $rows[0]['value'];
    }
    
    /**
     * Increment a scalar identified by name.
     *
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

        if ($this->_createScalarIfNotExists) {
            $core = $this->getModule('Core');

            $scalarId = $core->getScalarIdByName($scalarName);
            if (!$scalarId) {
                $fields = array('name' => $scalarName, 'type' => 'decimal',
                                'value' => $amount,
                                'description' => 'Auto-created by client'); 
                $scalarId = $core->createScalar($fields);
                if (!$scalarId) {
                    throw new Exception("Scalar '{$scalarName}' was not auto-created",
                                        HASHMARK_EXCEPTION_SQL);
                }

                if ($newSample) {
                    $sql = 'INSERT INTO ~samples '
                         . '(`value`, `start`, `end`) '
                         . "VALUES ({$amount}, UTC_TIMESTAMP(), UTC_TIMESTAMP())";
    
                    $partition = $this->getModule('Partition');
                    $partition->queryCurrent($scalarId, $sql);
                }

                return true;
            }
        }

        $dbHelperConfig = Hashmark::getConfig('DbHelper');

        $sum = 'CONVERT(`value`, DECIMAL'
             . $dbHelperConfig['decimal_sql_width'] . ') + '
             . $this->escape($amount);
       
        if ($newSample) {
            $sql = "UPDATE {$this->_dbName}`scalars` "
                 . "SET `value` = {$sum}, "
                 . '`last_sample_change` = UTC_TIMESTAMP(), '
                 . '`last_inline_change` = UTC_TIMESTAMP() '
                 . 'WHERE `name` = ? '
                 . 'AND `type` = "decimal"';

            $stmt = $this->_db->query($sql, array($scalarName));
        
            if (!$stmt->rowCount()) {
                return false;
            }

            unset($stmt);

            $currentScalarValue = "SELECT `value` FROM {$this->_dbName}`scalars` WHERE `name` = ? LIMIT 1";

            $sql = 'INSERT INTO ~samples '
                 . '(`value`, `start`, `end`) '
                 . "VALUES (({$currentScalarValue}), UTC_TIMESTAMP(), UTC_TIMESTAMP())";

            $scalarId = $this->getModule('Core')->getScalarIdByName($scalarName);

            $partition = $this->getModule('Partition');
            $stmt = $partition->queryCurrent($scalarId, $sql, array($scalarName));
        } else {
            $sql = "UPDATE {$this->_dbName}`scalars` "
                 . "SET `value` = {$sum}, "
                 . '`last_inline_change` = UTC_TIMESTAMP() '
                 . 'WHERE `name` = ? '
                 . 'AND `type` = "decimal"';

            $stmt = $this->_db->query($sql, array($scalarName));
        }

        return (1 == $stmt->rowCount());
    }
    
    /**
     * Decrement a scalar identified by name.
     *
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

    /**
     * Public write access to $_createScalarIfNotExists.
     *
     * @param boolean   $newValue
     * @return void
     */
    public function createScalarIfNotExists($newValue)
    {
        $this->_createScalarIfNotExists = $newValue;
    }
}
