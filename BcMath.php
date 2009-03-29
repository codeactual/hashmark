<?php
// vim: fenc=utf-8:ft=php:ai:si:ts=4:sw=4:et:

/**
 * Hashmark_BcMath
 *
 * @filesource
 * @link        http://code.google.com/p/hashmark/
 * @link        http://framework.zend.com/manual/en/coding-standard.html
 * @link        http://phpdoc.org/tutorial.php
 * @copyright   Copyright (c) 2008-2009, Code Actual LLC
 * @license     http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package     Hashmark-Test
 * @version     $Id: BcMath.php 296 2009-02-13 05:03:11Z david $
 */

/**
 * Load constants, ex. HASHMARK_DECIMAL_RIGHTWIDTH.
 */
require_once dirname(__FILE__) . '/Hashmark.php';

/**
 * bcmath extension wrappers.
 *
 *      -   Ex. Test/BasicDecimal/Tool/calc* scripts use it to generate
 *          expected aggregates.
 *
 * @package     Hashmark_Test
 */
class Hashmark_BcMath
{
    /**
     * Return a value rounded to match MySQL's treatment of "exact-value
     * numbers": round "away from zero".
     *
     *      -   Works around PHP round()'s handling of border values:
     *              round(0.29155, 4) = 0.2915
     *              round(0.29255, 4) = 0.2925
     *              round(0.29355, 4) = 0.2936
     *              round(0.29455, 4) = 0.2946
     *      -   Assumes value's digits past decimal point can survive
     *          intval() without precision loss.
     *      -   Uses explicit casts to emphasize conversions.
     *
     * @param string    $value
     * @return string
     */
    public static function round($value, $precision = HASHMARK_DECIMAL_RIGHTWIDTH)
    {
        // Ex. $value = '1000000000000000.29255000', $precision = 4
        if (false === ($pointPos = strrpos($value, '.'))) {
            return $value;
        }
        
        // Ex. '29255000'
        $decimal = substr($value, $pointPos + 1);

        // Ex. '29255'
        $decimal = preg_replace('/0+$/', '', $decimal);
        $length = strlen($decimal);

        // Border decimals whose scale we need to reduce.
        if ($length > $precision && '5' === $decimal[$length - 1]) {
            $decimal = (int) $decimal;
            $lostDigitCount = $length - strlen((string) $decimal);

            // Round away from zero. Ex. '292551'
            $decimal++;

            $decimal = (string) $decimal;

            // Recover precision lost in intval().
            if ($lostDigitCount && '0' === $value[$pointPos + 1]) {
                $decimal = implode('', array_fill(0, $lostDigitCount, '0')) . $decimal;
            }
        }

        // Ex. '1000000000000000'
        $whole = substr($value, 0, $pointPos);

        // Ex. '0.2926'
        $decimal = (string) round('0.' . $decimal, $precision);
        
        bcscale($precision);

        if ('-' === $value[0]) {
            $decimal = '-' . $decimal;
        }
        
        $rounded =  bcadd($whole, $decimal);

        // Add back sign if lost in bcadd(), ex. when $whole is '-0'.
        if ('-' !== $rounded[0] && '-' === $value[0]) {
            $rounded = '-' . $rounded;
        }

        return $rounded;
    }

    /**
     * Return sum based on bcadd().
     *
     * @param Array     $values
     * @return string
     * @throws  Exception if $values is not a populated Array.
     */
    public static function sum($values)
    {
        if (!is_array($values) || empty($values)) {
            throw new Exception('sum() requires a populated Array.', HASHMARK_EXCEPTION_VALIDATION);
        }

        bcscale(HASHMARK_DECIMAL_RIGHTWIDTH);

        $result = '';

        foreach ($values as $v) {
            $result = bcadd($result, $v);
        }

        return $result;
    }

    /**
     * Return average based on bcdiv(), sum() and round().
     *
     * @param Array     $values
     * @return string
     * @throws  Exception if $values is not a populated Array.
     */
    public static function avg($values)
    {
        if (!is_array($values) || empty($values)) {
            throw new Exception('avg() requires a populated Array.', HASHMARK_EXCEPTION_VALIDATION);
        }

        // Can't be nested as one-liner in bcdiv() due to separate bcscale().
        $sum = self::sum($values);

        // Increase bcscale() to provide extra precision for rounding.
        bcscale(HASHMARK_DECIMAL_RIGHTWIDTH * 2);

        return self::round(bcdiv($sum, count($values)));
    }

    /**
     * Return maximum based on bccomp().
     *
     * @param Array     $values
     * @return string   Maximum value.
     * @throws  Exception if $values is not a populated Array.
     */
    public static function max($values)
    {
        if (!is_array($values) || empty($values)) {
            throw new Exception('max() requires a populated Array.', HASHMARK_EXCEPTION_VALIDATION);
        }

        bcscale(HASHMARK_DECIMAL_RIGHTWIDTH);

        usort($values, 'bccomp');
        return $values[count($values) - 1];
    }

    /**
     * Return minimum based on bccomp().
     *
     * @param Array     $values
     * @return string   Minimum value.
     * @throws  Exception if $values is not a populated Array.
     */
    public static function min($values)
    {
        if (!is_array($values) || empty($values)) {
            throw new Exception('min() requires a populated Array.', HASHMARK_EXCEPTION_VALIDATION);
        }

        bcscale(HASHMARK_DECIMAL_RIGHTWIDTH );

        usort($values, 'bccomp');
        return $values[0];
    }
}
