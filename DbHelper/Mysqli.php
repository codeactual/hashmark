<?php
// vim: fenc=utf-8:ft=php:ai:si:ts=4:sw=4:et:

/**
 * Hashmark_DbHelper_Mysqli
 *
 * @filesource
 * @link        http://code.google.com/p/hashmark/
 * @link        http://framework.zend.com/manual/en/coding-standard.html
 * @link        http://phpdoc.org/tutorial.php
 * @copyright   Copyright (c) 2008-2009, Code Actual LLC
 * @license     http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package     Hashmark
 * @subpackage  Hashmark_DbHelper
 * @version     $Id: Mysqli.php 294 2009-02-13 03:48:59Z david $
*/

/**
 * mysqli extension wrapper.
 *
 * @package     Hashmark
 * @subpackage  Hashmark_DbHelper
 */
class Hashmark_DbHelper_Mysqli extends Hashmark_DbHelper
{
    /**
     * @see Abstract parent signature docs.
     */
    protected function _openDb($profileName)
    {
        $profile = $this->_baseConfig['profile'][$profileName];

        if (isset($profile['sock'])) {
            return new mysqli($profile['host'],
                              $profile['user'],
                              $profile['pass'],
                              $profile['name'],
                              $profile['port'],
                              $profile['sock']);
        } else {
        }
            return new mysqli($profile['host'],
                              $profile['user'],
                              $profile['pass'],
                              $profile['name'],
                              $profile['port']);
    }
    
    /**
     * @see Abstract parent signature docs.
     */
    public function closeDb($link)
    {
        return $link->close();
    }
    
    /**
     * @see Abstract parent signature docs.
     */
    public function openError($link)
    {
        return mysqli_connect_error();
    }
    
    /**
     * @see Abstract parent signature docs.
     */
    public function error($link)
    {
        return $link->error;
    }
    
    /**
     * @see Abstract parent signature docs.
     */
    public function errno($link)
    {
        return $link->errno;
    }
    
    /**
     * @see Abstract parent signature docs.
     */
    public function escape($link, $value, $quote = true)
    {
        if (!$link || !($link instanceof mysqli)) {
            throw new Exception('DB link object/resource is invalid.', HASHMARK_EXCEPTION_VALIDATION);
        }

        if (is_string($value)) {
            if ($quote) {
                return '\'' . $link->real_escape_string($value) . '\'';
            }
        } else if (!is_int($value) && !is_float($value) && (!is_object($value) || !method_exists($value, '__toString'))) {
            throw new Exception('All query() arguments must be strings, numeric or implement __toString().', HASHMARK_EXCEPTION_SQL);
        }

        return $link->real_escape_string($value);
    }
    
    /**
     * @see Abstract parent signature docs.
     */
    public function numRows($res)
    {
        return $res->num_rows;
    }
    
    /**
     * @see Abstract parent signature docs.
     */
    public function freeResult($res)
    {
        return $res->free();
    }
    
    /**
     * @see Abstract parent signature docs.
     */
    public function fetchAssoc($res)
    {
        return $res->fetch_assoc();
    }

    /**
     * @see Abstract parent signature docs.
     */
    public function fetchRow($res)
    {
        return $res->fetch_row();
    }
    
    /**
     * @see Abstract parent signature docs.
     */
    public function affectedRows($link)
    {
        return $link->affected_rows;
    }

    /**
     * @see Abstract parent signature docs.
     */
    public function insertId($link)
    {
        return $link->insert_id;
    }
    
    /**
     * @see Abstract parent signature docs.
     */
    public function rawQuery($link, $sql)
    {
        if (!$link || !($link instanceof mysqli)) {
            throw new Exception('DB link object/resource is invalid.', HASHMARK_EXCEPTION_VALIDATION);
        }

        return $link->query($sql);
    }
}
