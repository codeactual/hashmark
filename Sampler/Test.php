<?php
// vim: fenc=utf-8:ft=php:ai:si:ts=4:sw=4:et:

/**
 * Hashmark_Sampler_Test
 *
 * @filesource
 * @link        http://code.google.com/p/hashmark/
 * @link        http://framework.zend.com/manual/en/coding-standard.html
 * @link        http://phpdoc.org/tutorial.php
 * @copyright   Copyright (c) 2008-2009, Code Actual LLC
 * @license     http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package     Hashmark
 * @subpackage  Hashmark_Sampler
 * @version     $Id$
 */

/**
 * Class for unit tests.
 *
 * @package     Hashmark
 * @subpackage  Hashmark_Sampler
 */
class Hashmark_Sampler_Test extends Hashmark_Sampler
{
    /**
     * @see Abstract parent signature docs.
     */
    public static function getName()
    {
        return 'Test';
    }

    /**
     * @see Abstract parent signature docs.
     */
    public static function getDescription()
    {
        return 'Unit test sampler';
    }

    /**
     * @see Abstract parent signature docs.
     */
    public static function run($params = array())
    {
        return '1234';
    }
}
