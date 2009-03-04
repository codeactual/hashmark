<?php
// vim: fenc=utf-8:ft=php:ai:si:ts=4:sw=4:et:

/**
 * Hashmark_DbHelper_Mysql
 *
 * @filesource
 * @link        http://code.google.com/p/hashmark/
 * @link        http://framework.zend.com/manual/en/coding-standard.html
 * @link        http://phpdoc.org/tutorial.php
 * @copyright   Copyright (c) 2008-2009, Code Actual LLC
 * @license     http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package     Hashmark
 * @subpackage  Hashmark_DbHelper
 * @version     $Id: Mysql.php 294 2009-02-13 03:48:59Z david $
*/

/**
 * mysql extension wrapper.
 *
 * @package     Hashmark
 * @subpackage  Hashmark_DbHelper
 */
class Hashmark_DbHelper_Mysql extends Hashmark_DbHelper
{
    /**
     * @see Abstract parent signature docs.
     */
    protected function _openDb($profileName)
    {
        $profile = $this->_baseConfig['profile'][$profileName];

        if (isset($profile['sock'])) {
            $host = ':' . $profile['sock'];
        } else {
            $host = $profile['host'];
            if ($profile['port']) {
                $host .= ':' . $profile['port'];
            }
        }

        $link = mysql_connect($host, $profile['user'], $profile['pass']);

        if ($link && $profile['name']) {
            mysql_select_db($profile['name'], $link);
        }

        return $link;
    }

    /**
     * @see Abstract parent signature docs.
     */
    public function closeDb($link)
    {
        return mysql_close($link);
    }
    
    /**
     * @see Abstract parent signature docs.
     */
    public function openError($link)
    {
        return mysql_error($link);
    }
    
    /**
     * @see Abstract parent signature docs.
     */
    public function error($link)
    {
        return mysql_error($link);
    }
    
    /**
     * @see Abstract parent signature docs.
     */
    public function errno($link)
    {
        return mysql_errno($link);
    }

    /**
     * @see Abstract parent signature docs.
     */
    public function escape($link, $value, $quote = true)
    {
        if (!$link || !is_resource($link)) {
            throw new Exception('DB link object/resource is invalid.', HASHMARK_EXCEPTION_VALIDATION);
        }

        if (is_string($value)) {
            if ($quote) {
                return '\'' . mysql_real_escape_string($value, $link) . '\'';
            }
        } else if (!is_int($value) && !is_float($value) && (!is_object($value) || !method_exists($value, '__toString'))) {
            throw new Exception('All query() arguments must be strings, numeric or implement __toString().', HASHMARK_EXCEPTION_SQL);
        }

        return mysql_real_escape_string($value);
    }
    
    /**
     * @see Abstract parent signature docs.
     */
    public function numRows($res)
    {
        return mysql_num_rows($res);
    }
    
    /**
     * @see Abstract parent signature docs.
     */
    public function freeResult($res)
    {
        return mysql_free_result($res);
    }
    
    /**
     * @see Abstract parent signature docs.
     */
    public function fetchAssoc($res)
    {
        return mysql_fetch_assoc($res);
    }

    /**
     * @see Abstract parent signature docs.
     */
    public function fetchRow($res)
    {
        return mysql_fetch_row($res);
    }
    
    /**
     * @see Abstract parent signature docs.
     */
    public function affectedRows($link)
    {
        return mysql_affected_rows($link);
    }

    /**
     * @see Abstract parent signature docs.
     */
    public function insertId($link)
    {
        return mysql_insert_id($link);
    }

    /**
     * @see Abstract parent signature docs.
     */
    public function rawQuery($link, $sql)
    {
        if (!$link || !is_resource($link)) {
            throw new Exception('DB link object/resource is invalid.', HASHMARK_EXCEPTION_VALIDATION);
        }

        return mysql_query($sql, $link);
    }
}
