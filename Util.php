<?php
// vim: fenc=utf-8:ft=php:ai:si:ts=4:sw=4:et:

/**
 * Hashmark_Util
 *
 * @filesource
 * @link        http://code.google.com/p/hashmark/
 * @link        http://framework.zend.com/manual/en/coding-standard.html
 * @link        http://phpdoc.org/tutorial.php
 * @copyright   Copyright (c) 2008-2011 David Smith
 * @license     http://www.opensource.org/licenses/mit-license.php MIT License
 * @package     Hashmark
 * @version     $Id$
*/

/**
 * MySQL DATETIME data type constants.
 */
define('HASHMARK_DATETIME_FORMAT', 'Y-m-d H:i:s');
define('HASHMARK_DATETIME_PREG_PATTERN', '/^[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}$/');
define('HASHMARK_DATETIME_EMPTY', '0000-00-00 00:00:00');
define('HASHMARK_DATETIME_MIN', '1000-01-01 00:00:00');
define('HASHMARK_DATETIME_MAX', '9999-12-31 23:59:59');

/**
 * Mix of static utility methods.
 *
 * @package     Hashmark
 * @subpackage  Base
 */
class Hashmark_Util
{
    /**
     * Normalize date+time strings and UNIX timestamps to DATETIME format.
     *
     * @param mixed     $str
     * @return mixed    Normalized string; otherwise false.
     */
    public static function toDatetime($str)
    {
        switch (strlen(strval($str))) {
            case 10:    // UNIX timestamp.
                return gmdate(HASHMARK_DATETIME_FORMAT, $str);
            case 14:    // YYYYMMDDHHMMSS
                $new = preg_replace('/^([0-9]{4})([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})$/', '\\1-\\2-\\3 \\4:\\5:\\6', $str);
                return ($new == $str ? false : $new);
            default:    // YYYY-MM-DD HH:MM:SS
                if (preg_match(HASHMARK_DATETIME_PREG_PATTERN, $str)) {
                    return $str;
                }
                return false;
        }
    }

    /**
     * Reverse comparator based on string length.
     * 
     * @param string $a
     * @param string $b
     * @return int
     */
    public static function sortByStrlenReverse($a, $b)
    {
        $aLen = strlen($a);
        $bLen = strlen($b);

        if ($aLen < $bLen) {
            return 1;
        } else if ($aLen > $bLen) {
            return -1;
        }

        return 0;
    }

    /**
     * Return a pseudorandom SHA1 hash.
     *
     * @return string
     */
    public static function randomSha1()
    {
        return sha1(uniqid(mt_rand(), true));
    }
}
