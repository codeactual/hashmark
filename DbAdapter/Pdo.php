<?php
// vim: fenc=utf-8:ft=php:ai:si:ts=4:sw=4:et:

/**
 * Hashmark_DbAdapter_Pdo
 *
 * @filesource
 * @copyright   Copyright (c) 2008-2011 David Smith
 * @license     http://www.opensource.org/licenses/mit-license.php MIT License
 * @package     Hashmark
 * @subpackage  DbAdapter
 * @version     $Id$
*/

/**
 * Wrapper for Zend Framework PDO MySQL adapter.
 *
 *  -   Adds $_connection setter.
 *
 * @package     Hashmark
 * @subpackage  DbAdapter
 */
class Hashmark_DbAdapter_Pdo extends Zend_Db_Adapter_Pdo_Mysql
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
