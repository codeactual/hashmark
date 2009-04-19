<?php
// vim: fenc=utf-8:ft=php:ai:si:ts=4:sw=4:et:

/**
 * Hashmark_Module_DbDependent
 *
 * @filesource
 * @link        http://code.google.com/p/hashmark/
 * @link        http://framework.zend.com/manual/en/coding-standard.html
 * @link        http://phpdoc.org/tutorial.php
 * @copyright   Copyright (c) 2008-2009, Code Actual LLC
 * @license     http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package     Hashmark
 * @subpackage  Hashmark_Module
 * @version     $Id: DbDependent.php 296 2009-02-13 05:03:11Z david $
*/

/**
 * Database-dependent module.
 *
 * Automates SQL template/helper loading and exposes DB properties.
 *
 * @abstract
 * @package     Hashmark
 * @subpackage  Hashmark_Module
 */
abstract class Hashmark_Module_DbDependent extends Hashmark_Module
{
    /**
     * @var mixed Database connection object/resource.
     */
    protected $_db;
    
    /**
     * @var string  Database selection in quoted form `<name>` w/ trailing period.
     */
    protected $_dbName;

    /**
     * @var Hashmark_DbHelper_*    Instance created in initModule().
     */
    protected $_dbHelper;
    
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
        $this->_dbHelper = Hashmark::getModule('DbHelper');

        if (!$this->_dbHelper) {
            return false;
        }

        // Load SQL templates.
        if ($this->_type) {
            $templateFile = dirname(__FILE__) . "/../Sql/{$this->_base}/{$this->_type}.php";
        } else {
            $templateFile = dirname(__FILE__) . "/../Sql/{$this->_base}.php";
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
     * @return mixed
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
     * Public access to $_dbHelper.
     *
     * @return Hashmark_DbHelper_*
     */
    public function getDbHelper()
    {
        return $this->_dbHelper;
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
     * Return a Hashmark module instance (of the same type as this one)
     *
     * @param string    $name   Module name, ex. 'Core', 'Cron, 'Client.
     * @param string    $type   Ex. 'Mysql', implementation in Core/Mysql.php.
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
