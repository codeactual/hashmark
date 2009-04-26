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
     * Configure new database adapter that will open a connection on-demand.
     *
     *      -   Mainly for the Cron and unit test classes.
     *
     * @param string    $profileName
     * @return Zend_Db_Adapter_*    Current instance; otherwise false.
     */
    public function openDb($profileName)
    {
        if (defined('HASHMARK_TEST_MODE')) {
            $profileName = 'unittest';
        }

        if (!isset($this->_baseConfig['profile'][$profileName])) {
            return false;
        }

        $className = 'Zend_Db_Adapter_' . $this->_baseConfig['profile'][$profileName]['adapter'];
        return new $className($this->_baseConfig['profile'][$profileName]['params']);
    }
    
    /**
     * Reuse an existing connection in a new adapter.
     *
     * @param mixed     $link           Database connection object/resource.
     * @param string    $adapterName    Ex. 'Mysqli'
     * @return Zend_Db_Adapter_*    Current instance; otherwise false.
     */
    public function reuseDb($link, $adapterName)
    {
        $className = 'Zend_Db_Adapter_' . $adapterName;
        $config = array('host' => '', 'username' => '', 'password' => '', 'dbname' => '');
        $dbAdapter = new $className($config);
        $dbAdapter->setConnection($link);

        return $dbAdapter;
    }
}
