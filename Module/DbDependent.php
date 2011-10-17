<?php
// vim: fenc=utf-8:ft=php:ai:si:ts=4:sw=4:et:

/**
 * Hashmark_Module_DbDependent
 *
 * @filesource
 * @copyright   Copyright (c) 2008-2011 David Smith
 * @license     http://www.opensource.org/licenses/mit-license.php MIT License
 * @package     Hashmark
 * @subpackage  Hashmark_Module
 * @version     $Id$
*/

require_once dirname(__FILE__) . '/../DbHelper.php';

/**
 * Database-dependent module.
 *
 * Automates SQL template/helper loading and exposes DB properties.
 *
 * @package     Hashmark
 * @subpackage  Hashmark_Module
 */
abstract class Hashmark_Module_DbDependent extends Hashmark_Module
{
    /**
     * @var Zend_Db_Adapter_*   Current instance.
     */
    protected $_db;
    
    /**
     * @var string  Database selection in quoted form `<name>` w/ trailing period.
     */
    protected $_dbName;
    
    /**
     * @var Array   SQL templates indexed by module base name, then template name
     *              which is usually the associated function's name.
     */
    protected static $_sql;

    /**
     * @param mixed     $db         Connection object/resource.
     * @return boolean  False if module could not be initialized and is unusable.
     *                  Hashmark::getModule() will also then return false.
     */
    public function initModule($db)
    {
        if (!$db) {
            return false;
        }

        $class = get_class($this);

        $this->_db = $db;

        // Load SQL templates.
        if ($this->_type) {
            $templateFile = HASHMARK_ROOT_DIR . "/Sql/{$this->_base}/{$this->_type}.php";
        } else {
            $templateFile = HASHMARK_ROOT_DIR . "/Sql/{$this->_base}.php";
        }
        if (!isset(self::$_sql[$this->_base]) && is_readable($templateFile)) {
            require $templateFile;
            self::$_sql[$this->_base] = $sql;
        }

        return true;
    }

    /**
     * Public access to $_sql by key.
     *
     * @param string    Template name, usually a function name.
     * @return string   Template SQL; otherwise false.
     */
    public function getSql($name)
    {
        if (isset(self::$_sql[$this->_base][$name])) {
            return self::$_sql[$this->_base][$name];
        }
        return false;
    }

    /**
     * Public access to $_db.
     *
     * @return Zend_Db_Adapter_*    Current instance.
     */
    public function getDb()
    {
        return $this->_db;
    }
    
    /**
     * Public access to $_dbName.
     *
     * @param boolean   $clean  If true, back-quotes and period are removed.
     * @return mixed
     */
    public function getDbName($clean = true)
    {
        if ($clean) {
            return str_replace('`', '', str_replace('.', '', $this->_dbName));
        }

        return $this->_dbName;
    }

    /**
     * Public write access to $_dbName.
     *
     * @param string    $dbName
     * @return void
     */
    public function setDbName($dbName)
    {
        if ($dbName) {
            $this->_dbName = "`{$dbName}`.";
        } else {
            $this->_dbName = '';
        }
    }
    
    /**
     * Escape strings without quoting them, ex. SQL function names.
     *
     * @param string $value     Raw string.
     * @return string           Escaped string.
     */
    public function escape($value)
    {
        // Use quote() for native escaping but trim quotes.
        return preg_replace('/(^\'|\'$)/', '', $this->_db->quote($value));
    }

    /**
     * Expands :name and @name macros. Escapes/quotes mapped values.
     *
     * @param string    $sql   Statement with 0 or more macros.
     * @param Array     $map        Assoc. array that maps macro names to their values.
     *      Use ':' macro prefix to escape and quote the value (if string).
     *      Use '@' macro prefix to only escape it.
     *      Prefix is required.
     *      Each value is escaped and quoted (if string).
     * @return string
     * @throws  Exception if any parameter cannot be converted to a string;
     *          if query does not produce a valid result object/resource;
     *          if a macro is missing a valid prefix.
     */
    public function expandSql($sql, $map)
    {
        // Ex. prevent macro :name from being being expanded before :nameSuffix.
        uksort($map, array('Hashmark_Util', 'sortByStrlenReverse'));

        foreach ($map as $macro => $value) {
            if (':' == $macro[0]) {
                $map[$macro] = $this->_db->quote($value);
            } else if ('@' == $macro[0]) {
                $map[$macro] = $this->escape($value);
            } else {
                throw new Exception("Macro '{$macro}' does not have a valid prefix character.",
                                    HASHMARK_EXCEPTION_VALIDATION);
            }
        }
        return str_replace(array_keys($map), array_values($map), $sql);
    }

    /**
     * Return a Hashmark module instance (of the same type as this one)
     *
     * @param string    $name   Module name, ex. 'Core', 'Client', 'Agent'.
     * @param string    $type   Ex. 'ScalarValue', implementation in Agent/ScalarValue.php.
     * @return mixed    New instance.
     */
    public function getModule($name, $type = '')
    {
        $args = func_get_args();

        // Ensure this omission gets passed to Hashmark::getModule().
        if (!$type) {
            array_splice($args, 1, 0, '');
        }

        // Use the current module's DB object/resource.
        if (isset($args[2])) {
            array_splice($args, 2, 0, array($this->_db));
        } else {
            $args[] = $this->_db;
        }

        $inst = call_user_func_array(array('Hashmark', 'getModule'), $args);
        $inst->setDbName($this->getDbName());
        return $inst;
    }
}
