<?php
// vim: fenc=utf-8:ft=php:ai:si:ts=4:sw=4:et:

/**
 * Hashmark_Agent_Test
 *
 * @filesource
 * @copyright   Copyright (c) 2008-2011 David Smith
 * @license     http://www.opensource.org/licenses/mit-license.php MIT License
 * @package     Hashmark
 * @subpackage  Agent
 * @version     $Id$
 */

/**
 * Class for unit tests.
 *
 * @package     Hashmark
 * @subpackage  Agent
 */
class Hashmark_Agent_Test implements Hashmark_Agent
{
    /**
     * @see Parent/interface signature docs.
     */
    public static function getName()
    {
        return 'Test';
    }

    /**
     * @see Parent/interface signature docs.
     */
    public static function getDescription()
    {
        return 'Unit test agent';
    }

    /**
     * @see Parent/interface signature docs.
     */
    public static function run(&$agent)
    {
        return '1234';
    }
}
