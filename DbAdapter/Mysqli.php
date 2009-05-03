<?php
// vim: fenc=utf-8:ft=php:ai:si:ts=4:sw=4:et:

/**
 * Hashmark_DbAdapter_Mysqli
 *
 * @filesource
 * @link        http://code.google.com/p/hashmark/
 * @link        http://framework.zend.com/manual/en/coding-standard.html
 * @link        http://phpdoc.org/tutorial.php
 * @copyright   Copyright (c) 2008-2009, Code Actual LLC
 * @license     http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package     Hashmark
 * @subpackage  DbAdapter
 * @version     $Id$
*/

/**
 * Wrapper for Zend Framework MySQLi adapter.
 *
 *  -   Adds $_connection setter.
 *
 * @package     Hashmark
 * @subpackage  DbAdapter
 */

class Hashmark_DbAdapter_Mysqli extends Zend_Db_Adapter_Mysqli
{
    /**
     * Set the adapter's connection object or resource.
     *
     * Allows Hashmark to use a client application's existing connection.
     *
     * @param object|resource|null $connection
     * @return Zend_Db_Adapter_Abstract Fluent interface
     */
    public function setConnection($connection)
    {
        $this->_connection = $connection;
        return $this;
    }
}