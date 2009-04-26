<?php
// vim: fenc=utf-8:ft=php:ai:si:ts=4:sw=4:et:

/**
 * Hashmark_Sampler_YahooWeather
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
 * Scrapes Seattle temperature from an RSS feed.
 * 
 * Example code.
 *
 * @package     Hashmark
 * @subpackage  Hashmark_Sampler
 */
class Hashmark_Sampler_YahooWeather extends Hashmark_Sampler
{
    /**
     * @see Abstract parent signature docs.
     */
    public static function getName()
    {
        return 'Seattle temperature';
    }

    /**
     * @see Abstract parent signature docs.
     */
    public static function getDescription()
    {
        return 'From RSS feed. In Fahrenheit.';
    }

    /**
     * @see Abstract parent signature docs.
     */
    public static function run($params = array())
    {
        $xml = file_get_contents('http://weather.yahooapis.com/forecastrss?p=98103&u=f');
        if (!$xml) {
            return null;
        }

        $matches = array();
        if (!preg_match('/<yweather:condition.*temp="(\d+)"/', $xml, $matches)) {
            return null;
        }

        return $matches[1];
    }
}
