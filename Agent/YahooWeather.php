<?php
// vim: fenc=utf-8:ft=php:ai:si:ts=4:sw=4:et:

/**
 * Hashmark_Agent_YahooWeather
 *
 * @filesource
 * @link        http://code.google.com/p/hashmark/
 * @link        http://framework.zend.com/manual/en/coding-standard.html
 * @link        http://phpdoc.org/tutorial.php
 * @copyright   Copyright (c) 2008-2009, Code Actual LLC
 * @license     http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package     Hashmark
 * @subpackage  Agent
 * @version     $Id$
 */

/**
 * Scrapes Seattle temperature from an RSS feed.
 * 
 * Example code.
 *
 * @package     Hashmark
 * @subpackage  Agent
 */
class Hashmark_Agent_YahooWeather implements Hashmark_Agent
{
    /**
     * @see Parent/interface signature docs.
     */
    public static function getName()
    {
        return 'Seattle temperature';
    }

    /**
     * @see Parent/interface signature docs.
     */
    public static function getDescription()
    {
        return 'From RSS feed. In Fahrenheit.';
    }

    /**
     * @see Parent/interface signature docs.
     */
    public static function run($agent = array())
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
