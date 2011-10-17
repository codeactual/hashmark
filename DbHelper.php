<?php
// vim: fenc=utf-8:ft=php:ai:si:ts=4:sw=4:et:

/**
 * Hashmark_DbHelper
 *
 * @filesource
 * @link        http://code.google.com/p/hashmark/
 * @link        http://framework.zend.com/manual/en/coding-standard.html
 * @link        http://phpdoc.org/tutorial.php
 * @copyright   Copyright (c) 2008-2011 David Smith
 * @license     http://www.opensource.org/licenses/mit-license.php MIT License
 * @package     Hashmark
 * @subpackage  Base
 * @version     $Id$
*/

/**
 * Factory for database adapters, prepared statements, etc.
 *
 * @package     Hashmark
 * @subpackage  Base
 */
class Hashmark_DbHelper extends Hashmark_Module
{
    /**
     * Factory for Zend adapter wrappers.
     *
     * @param string    $name       'Mysqli' or 'Pdo'.
     * @param Array     $config     Constructor argument
     * @return Zend_Db_Adapter_*    New instance.
     * return 
     */
    public function getAdapter($name, $config)
    {
        require_once HASHMARK_ROOT_DIR . "/DbAdapter/{$name}.php";
        $className = "Hashmark_DbAdapter_{$name}";
        return new $className($config);
    }

    /**
     * Configure new database adapter that will open a connection on-demand.
     *
     *      -   Mainly for the Cron and unit test classes.
     *
     * @param string    $profileName
     * @return Zend_Db_Adapter_*    New instance.
     * @throws Exception If profile or adapter name is unrecognized.
     */
    public function openDb($profileName)
    {
        $allowedAdapters = array('Mysqli', 'Pdo');

        if (defined('HASHMARK_TEST_MODE')) {
            $profileName = 'unittest';
        }

        if (!isset($this->_baseConfig['profile'][$profileName])) {
            throw new Exception("Database profile '{$profileName}' is not available.");
        }

        if (!in_array($this->_baseConfig['profile'][$profileName]['adapter'], $allowedAdapters)) {
            throw new Exception("Database adapter '{$this->_baseConfig['profile'][$profileName]['adapter']}' is not allowed.");
        }

        return $this->getAdapter($this->_baseConfig['profile'][$profileName]['adapter'],
                                  $this->_baseConfig['profile'][$profileName]['params']);
    }
    
    /**
     * Reuse an existing connection in a new adapter.
     *
     * @param mixed     $link           Database connection object/resource.
     * @param string    $adapterName    Ex. 'Mysqli'
     * @return Zend_Db_Adapter_*        New instance.
     * @throws Exception If adapter name is unrecognized.
     */
    public function reuseDb($link, $adapterName)
    {
        $config = array('host' => '', 'username' => '', 'password' => '', 'dbname' => '');
        $dbAdapter = $this->getAdapter($adapterName, $config);
        $dbAdapter->setConnection($link);
        return $dbAdapter;
    }
}
