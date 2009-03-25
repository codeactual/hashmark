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
     * @access protected
     * @var mixed Database connection object/resource.
     */
    protected $_db;
    
    /**
     * @access protected
     * @var string  Database selection in quoted form `<name>` w/ trailing period.
     */
    protected $_dbName;

    /**
     * @access protected
     * @var Hashmark_DbHelper_*    Instance created in initModule().
     */
    protected $_dbHelper;
    
    /**
     * @static
     * @access protected
     * @var Array   SQL templates indexed by module base name, then template name
     *              which is usually the associated function's name.
     */
    protected static $_sql;

    /**
     * @access protected
     * @param mixed     $db         Connection object/resource.
     * @param string    $dbName     Database selection, unquoted. [optional]
     * @return boolean  False if module could not be initialized and is unusable.
     *                  Hashmark::getModule() will also then return false.
     */
    public function initModule($db, $dbName = '')
    {
        if (!$db) {
            return false;
        }
        
        if ($dbName) {
            $this->_dbName = "`{$dbName}`.";
        } else {
            $this->_dbName = '';
        }

        $this->_db = $db;
        $this->_dbHelper = Hashmark::getModule('DbHelper', HASHMARK_DBHELPER_DEFAULT_TYPE);

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
     * @access public
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
     * @access public
     * @return mixed
     */
    public function getDb()
    {
        return $this->_db;
    }
    
    /**
     * Public access to $_db.
     *
     * @access public
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
     * @access public
     * @return Hashmark_DbHelper_*
     */
    public function getDbHelper()
    {
        return $this->_dbHelper;
    }

    /**
     * Return a Hashmark module instance (of the same type as this one)
     *
     * @access protected
     * @param string    $name   Module name, ex. 'Core', 'Cron, 'Client.
     * @return mixed    New instance.
     */
    public function getModule($name)
    {
        return Hashmark::getModule($name, '', $this->_db, $this->getDbName());
    }
}
