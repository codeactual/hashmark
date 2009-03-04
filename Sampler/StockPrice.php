<?php
// vim: fenc=utf-8:ft=php:ai:si:ts=4:sw=4:et:

/**
 * Hashmark_Sampler_StockPrice
 *
 * @filesource
 * @link        http://code.google.com/p/hashmark/
 * @link        http://framework.zend.com/manual/en/coding-standard.html
 * @link        http://phpdoc.org/tutorial.php
 * @copyright   Copyright (c) 2008-2009, Code Actual LLC
 * @license     http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package     Hashmark
 * @subpackage  Hashmark_Sampler
 * @version     $Id: StockPrice.php 263 2009-02-03 11:22:57Z david $
 */

/**
 * Scrapes Apple NASDAQ price.
 * 
 * Example code.
 *
 * @package     Hashmark
 * @subpackage  Hashmark_Sampler
 */
class Hashmark_Sampler_StockPrice extends Hashmark_Sampler
{
    /**
     * @see Abstract parent signature docs.
     */
    public static function getName()
    {
        return 'Apple, Inc. stock price';
    }

    /**
     * @see Abstract parent signature docs.
     */
    public static function getDescription()
    {
        return 'From Google Finance.';
    }

    /**
     * @see Abstract parent signature docs.
     */
    public static function run($scalarId)
    {
        $json = file_get_contents('http://www.google.com/finance/info?client=ig&q=AAPL');
        if (!$json) {
            return null;
        }

        $fields = json_decode(substr($json, 4));
        if (!isset($fields[0]->l)) {
            return null;
        }

        return $fields[0]->l;
    }
}
