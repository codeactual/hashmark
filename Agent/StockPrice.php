<?php
// vim: fenc=utf-8:ft=php:ai:si:ts=4:sw=4:et:

/**
 * Hashmark_Agent_StockPrice
 *
 * @filesource
 * @copyright   Copyright (c) 2008-2011 David Smith
 * @license     http://www.opensource.org/licenses/mit-license.php MIT License
 * @package     Hashmark
 * @subpackage  Agent
 * @version     $Id$
 */

/**
 * Scrapes Apple NASDAQ price.
 * 
 * Example code.
 *
 * @package     Hashmark
 * @subpackage  Agent
 */
class Hashmark_Agent_StockPrice implements Hashmark_Agent
{
    /**
     * @see Parent/interface signature docs.
     */
    public static function getName()
    {
        return 'Apple, Inc. stock price';
    }

    /**
     * @see Parent/interface signature docs.
     */
    public static function getDescription()
    {
        return 'From Google Finance.';
    }

    /**
     * @see Parent/interface signature docs.
     */
    public static function run(&$agent)
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
