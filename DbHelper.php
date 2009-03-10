<?php
// vim: fenc=utf-8:ft=php:ai:si:ts=4:sw=4:et:

/**
 * Hashmark_DbHelper
 *
 * @filesource
 * @link        http://code.google.com/p/hashmark/
 * @link        http://framework.zend.com/manual/en/coding-standard.html
 * @link        http://phpdoc.org/tutorial.php
 * @copyright   Copyright (c) 2008-2009, Code Actual LLC
 * @license     http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package     Hashmark
 * @subpackage  Base
 * @version     $Id: DbHelper.php 296 2009-02-13 05:03:11Z david $
*/

/**
 * Base class for MySQL extension wrappers.
 *
 * Implementations live in DbHelper/. Each defines methods wrapping functions
 * like mysql_affected_rows().
 *
 * @abstract
 * @package     Hashmark
 * @subpackage  Base
 */
abstract class Hashmark_DbHelper extends Hashmark_Module
{
    /**
     * Open a database connection.
     *
     *      -   Mainly for the Cron and unit test classes.
     *
     * @access protected
     * @param string    $profileName
     * @return mixed    New database connection object/resource.
     */
    abstract protected function _openDb($profileName);

    /**
     * Close a database connection.
     *
     *      -   Mainly for the Cron and unit test classes.
     *
     * @access public
     * @param mixed  $link      Database connection object/resource.
     * @return boolean
     */
    abstract public function closeDb($link);
    
    /**
     * Returns the last connection error string.
     *
     * @access public
     * @param mixed  $link      Database connection object/resource.
     * @return string
     */
    abstract public function openError($link);
    
    /**
     * Returns the last query error string.
     *
     * @access public
     * @param mixed  $link      Database connection object/resource.
     * @return string
     */
    abstract public function error($link);
    
    /**
     * Returns the last query error number.
     *
     * @access public
     * @param mixed  $link      Database connection object/resource.
     * @return int
     */
    abstract public function errno($link);
    
    /**
     * Escapes values, ex. for template macro expansions.
     *
     * @access public
     * @param mixed     $link   Database connection object/resource.
     * @param mixed     $value  String-representable value.
     * @param boolean   $quote  If true, value will be single-quoted.
     * @return string
     * @throws  Exception   If any parameter cannot be converted to a string;
     *                      if $link is invalid.
     */
    abstract public function escape($link, $value, $quote = true);

    /**
     * Pass-through wrapper for mysql_num_rows(), etc.
     *
     * @access public
     * @param mixed     $res    Result object/resource.
     * @return int  Return row count.
     */
    abstract public function numRows($res);
    
    /**
     * Pass-through wrapper for mysql_free_result(), etc.
     *
     * @access public
     * @param mixed     $res    Result object/resource.
     * @return boolean  True on success.
     */
    abstract public function freeResult($res);
    
    /**
     * Pass-through wrapper for mysql_fetch_assoc(), etc.
     *
     * @access public
     * @param mixed     $res    Result object/resource.
     * @return Array    One result row as associative Array.
     */
    abstract public function fetchAssoc($res);

    /**
     * Pass-through wrapper for mysql_fetch_row(), etc.
     *
     * @access public
     * @param mixed     $res    Result object/resource.
     * @return Array    One result row as numeric Array.
     */
    abstract public function fetchRow($res);
    
    /**
     * Pass-through wrapper for mysql_affected_rows(), etc.
     *
     * @access public
     * @param mixed     $link   Database cnnection object/resource.
     * @return int  Affected row count.
     */
    abstract public function affectedRows($link);

    /**
     * Pass-through wrapper for mysql_insert_id(), etc.
     *
     * @access public
     * @param mixed     $link   Database cnnection object/resource.
     * @return int  Connection's last inserted row ID.
     */
    abstract public function insertId($link);

    /**
     * Pass-through wrapper for mysql_query(), etc.
     *
     * @access public
     * @param mixed     $link   Database connection object/resource.
     * @param string    $sql
     * @return mixed    Query result object/resource.
     * @throws Exception If $link is invalid.
     */
    abstract public function rawQuery($link, $sql);

    /**
     * Public wrapper for _openDb().
     *
     * @access public
     * @param string    $profileName
     * @return mixed    New database connection object/resource.
     * @throws  Exception   If profile is not found in configs.
     */
    public function openDb($profileName)
    {
        if (defined('HASHMARK_TEST_MODE')) {
            $profileName = 'unittest';
        }

        if (!isset($this->_baseConfig['profile'][$profileName])) {
            throw new Exception("Connection profile '{$profileName}' was not found.");
        }

        return $this->_openDb($profileName);
    }

    /**
     * Expands ?, :name and @name macros. Escapes/quotes matching values.
     *
     * @access public
     * @param mixed     $link       Database connection object/resource.
     * @param string    $template   Statement with 0 or more macros.
     * @param Array     Two options:
     *      -   Assoc. Array that maps macro names to their values.
     *          Use ':' macro prefix to escape and quote the value (if string).
     *          Use '@' macro prefix to only escape it.
     *          Prefix is required.
     *      -   Variable-length list of arguments. One string-representable
     *          argument for every $template macro.
     *      Each value is escaped and quoted (if string).
     * @return mixed    Query result object/resource.
     * @throws  Exception if any parameter cannot be converted to a string;
     *          if query does not produce a valid result object/resource;
     *          if a macro is missing a valid prefix;
     *          if $link is invalid.
     */
    public function expandMacros($link, $template, $queryArgs)
    {
        $numArgs = count($queryArgs);

        // Use named-macro-to-value mapping.
        if (1 == $numArgs && is_array($queryArgs[0])) {
            $cleanValues = $queryArgs[0];

            // Ex. prevent macro :name from being being expanded before :nameSuffix.
            uksort($cleanValues, array('Hashmark_Util', 'sortByStrlenReverse'));

            foreach ($cleanValues as $macro => $value) {
                if (':' == $macro[0]) {
                    $cleanValues[$macro] = $this->escape($link, $value);
                } else if ('@' == $macro[0]) {
                    $cleanValues[$macro] = $this->escape($link, $value, false);
                } else {
                    throw new Exception("Macro '{$macro}' does not have a valid prefix character.", HASHMARK_EXCEPTION_VALIDATION);
                }
            }
            $sql = str_replace(array_keys($cleanValues), array_values($cleanValues), $template);

        // Use variable length list of '?' substitution values.
        } else {
            for ($num = 0; $num < $numArgs; $num++) {
                $queryArgs[$num] = $this->escape($link, $queryArgs[$num]);
                $template = preg_replace('/\?/', $queryArgs[$num], $template, 1);
            }
            $sql = $template;
        }

        return $sql;
    }
    
    /**
     * Query wrapper which accepts expandMacros() compatible templates and macro lists.
     *
     * @access public
     * @param mixed     $link       Database connection object/resource.
     * @param string    $template   Statement with 0 or more macros.
     * @param mixed     ...         See $queryArgs parameter of expandMacros().
     * @return mixed    Query result object/resource.
     * @throws  Exception if any parameter cannot be converted to a string;
     *          if query does not produce a valid result object/resource;
     *          if a macro is missing a valid prefix;
     *          if $link is invalid.
     */
    public function query($link, $template)
    {
        $numArgs = func_num_args();

        if (2 == $numArgs) {
            $sql = $template;
        } else {
            $queryArgs = func_get_args();
            $sql = $this->expandMacros($link, $template, array_slice($queryArgs, 2));
        }
        
        if (!($result = $this->rawQuery($link, $sql))) {
            $error = $this->error($link);
            $errno = $this->errno($link);
            throw new Exception("SQL error({$errno}): {$error} from query: {$sql}", HASHMARK_EXCEPTION_SQL);
        }

        return $result;
    }
}
