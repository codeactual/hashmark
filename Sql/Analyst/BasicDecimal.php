<?php
// vim: fenc=utf-8:ft=php:ai:si:ts=4:sw=4:et:

/**
 * Shared SQL query templates.
 *
 *      -   Generally $sql keys match module method names,
 *          ex. $sql['myMethod'] is used in Hashmark_MyModule::myMethod().
 *      -   Prefer established macro names so values can be shared, ex. :start and :end.
 *      -   :name macros will be escaped and quoted, ex. DATETIMEs.
 *      -   @name macros will not be escaped nor quoted, ex. SQL functions.
 *      -   ~name table name macros will be backtick-quoted.
 *      -   To set the destination columns for the INSERT INTO ... SELECT
 *          that populates a temp. table with macro named ~exampleTemp~,
 *          define macro @exampleTempCols, ex. w/ value: `x`, `y`
 *      -   ROUND() used to avoid truncation warnings.
 *
 * @filesource
 * @link        http://code.google.com/p/hashmark/
 * @link        http://framework.zend.com/manual/en/coding-standard.html
 * @link        http://phpdoc.org/tutorial.php
 * @copyright   Copyright (c) 2008-2009, Code Actual LLC
 * @license     http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package     Hashmark
 * @subpackage  Sql
 * @version     $Id: BasicDecimal.php 296 2009-02-13 05:03:11Z david $
*/

$sql = array();
        
$sql['values'] = 'SELECT `end` AS `x`, '
               . '`value` AS `y` '
               . 'FROM ~samples '
               . 'WHERE `end` >= :start '
               . 'AND `end` <= :end ';

/**
 *  -   Self-join allows us to pull the most recent row from inside each
 *      interval to reprsent the whole.
 *  -   Duplicate the start/end conditions in the self-join
 *      in order to narrow `s2` scan.
 */
$sql['valuesAtInterval'] = 'SELECT `s1`.`end` AS `x`, '
                         . '`s1`.`value` AS `y` '
                         . 'FROM ~samples AS `s1` '
                         . 'LEFT JOIN ~samples AS `s2` '
                         . 'ON DATE_FORMAT(`s1`.`end`, :format) = DATE_FORMAT(`s2`.`end`, :format) '
                         . 'AND `s1`.`end` < `s2`.`end` '
                         . 'AND `s2`.`end` >= :start '
                         . 'AND `s2`.`end` <= :end '
                         . 'WHERE `s1`.`end` >= :start '
                         . 'AND `s1`.`end` <= :end '
                         . 'AND `s2`.`end` IS NULL ';

$sql['valuesAgg'] = 'SELECT ROUND(@aggFunc(@distinct`value`), ' . HASHMARK_DECIMAL_RIGHTWIDTH . ') AS `y` '
                  . 'FROM ~samples '
                  . 'WHERE `end` >= :start '
                  . 'AND `end` <= :end ';

$sql['valuesAggAtInterval'] = 'SELECT DATE_FORMAT(`end`, :format) AS `x`, '
                            . 'ROUND(@aggFunc(@distinct`value`), '. HASHMARK_DECIMAL_RIGHTWIDTH . ') AS `y` '
                            . 'FROM ~samples '
                            . 'WHERE `end` >= :start '
                            . 'AND `end` <= :end '
                            . 'GROUP BY `x` ';
        
$sql['valuesNestedAggAtInterval'] = 'SELECT ROUND(@aggFunc(@distinct`y2`), ' . HASHMARK_DECIMAL_RIGHTWIDTH . ') AS `y2` '
                                  . 'FROM ~valuesAggAtInterval ';

$sql['valuesAggAtRecurrence'] = 'SELECT @recurFunc(`end`) AS `x`, '
                              . 'ROUND(@aggFunc(@distinct`value`), ' . HASHMARK_DECIMAL_RIGHTWIDTH . ') AS `y` '
                              . 'FROM ~samples '
                              . 'WHERE `end` >= :start '
                              . 'AND `end` <= :end '
                              . 'GROUP BY @recurFunc(`end`) ';

/**
 *  -   Duplicate the start/end conditions in the self-join
 *      in order to narrow `s2` scan.
 */
$sql['changes'] = 'SELECT `s1`.`end` AS `x`, '
                . '`s1`.`value` AS `y`, '
                . '(`s1`.`value` - `s2`.`value`) AS `y2`, '
                . '`s1`.`id` '
                . 'FROM ~samples AS `s1` '
                . 'INNER JOIN ~samples AS `s2` '
                . 'ON `s1`.`id` - 1 = `s2`.`id` '
                . 'AND `s2`.`end` >= :start '
                . 'AND `s2`.`end` <= :end '
                . 'WHERE `s1`.`end` >= :start '
                . 'AND `s1`.`end` <= :end ';

$sql['changesAtInterval'] = 'SELECT DATE_FORMAT(`s1`.`x`, :format) AS `x`, '
                          . '`s1`.`y` AS `y`, '
                          . '(`s1`.`y` - `s2`.`y`) AS `y2` '
                          . 'FROM ~valuesAtInterval AS `s1` '
                          . 'INNER JOIN ~valuesAtInterval AS `s2` '
                          . 'ON `s1`.`id` + 1 = `s2`.`id` ';
        
/**
 *  -   Duplicate the start/end conditions in the self-join
 *      in order to narrow `s2` scan.
 */
$sql['changesAgg'] = 'SELECT ROUND(@aggFunc(@distinct(`s1`.`value` - `s2`.`value`)), ' . HASHMARK_DECIMAL_RIGHTWIDTH . ') AS `y` '
                   . 'FROM ~samples AS `s1` '
                   . 'INNER JOIN ~samples AS `s2` '
                   . 'ON `s1`.`id` - 1 = `s2`.`id` '
                   . 'AND `s2`.`end` >= :start '
                   . 'AND `s2`.`end` <= :end '
                   . 'WHERE `s1`.`end` >= :start '
                   . 'AND `s1`.`end` <= :end ';
        
$sql['changesAggAtInterval'] = 'SELECT DATE_FORMAT(`x`, :format) AS `x`, '
                             . 'ROUND(@aggFunc(@distinct`y2`), ' . HASHMARK_DECIMAL_RIGHTWIDTH . ') AS `y` '
                             . 'FROM ~changes '
                             . 'GROUP BY DATE_FORMAT(`x`, :format) ';
        
$sql['changesNestedAggAtInterval'] = 'SELECT ROUND(@aggFunc(@distinct`y`), ' . HASHMARK_DECIMAL_RIGHTWIDTH . ') AS `y` '
                                   . 'FROM ~changesAggAtInterval ';

$sql['changesAggAtRecurrence'] = 'SELECT @recurFunc(`s1`.`end`) AS `x`, '
                               . 'ROUND(@aggFunc(@distinct(`s1`.`value` - `s2`.`value`)), ' . HASHMARK_DECIMAL_RIGHTWIDTH . ') AS `y` '
                               . 'FROM ~samples AS `s1` '
                               . 'INNER JOIN ~samples AS `s2` '
                               . 'ON `s1`.`id` - 1 = `s2`.`id` '
                               . 'AND `s2`.`end` >= :start '
                               . 'AND `s2`.`end` <= :end '
                               . 'WHERE `s1`.`end` >= :start '
                               . 'AND `s1`.`end` <= :end '
                               . 'GROUP BY @recurFunc(`s1`.`end`) ';

$sql['frequency'] = 'SELECT `value` AS `x`, '
                  . 'COUNT(*) AS `y` '
                  . 'FROM ~samples '
                  . 'WHERE `end` >= :start '
                  . 'AND `end` <= :end '
                  . 'GROUP BY `x` ';
        
/**
 *  -   Self-join allows us to aggregate inner sample values inserted
 *      on/before the current outer sample.
 *  -   Duplicate the start/end conditions in the self-join
 *      in order to narrow `s2` scan.
 */
 $sql['moving'] = 'SELECT `s1`.`end` AS `x`, '
                . '`s1`.`value` AS `y`, '
                . 'ROUND(@aggFunc(@distinct`s2`.`value`), ' . HASHMARK_DECIMAL_RIGHTWIDTH . ') AS `y2` '
                . 'FROM ~samples AS `s1` '
                . 'INNER JOIN ~samples AS `s2` '
                . 'ON `s1`.`id` >= `s2`.`id` '
                . 'AND `s2`.`end` >= :start '
                . 'AND `s2`.`end` <= :end '
                . 'WHERE `s1`.`end` >= :start '
                . 'AND `s1`.`end` <= :end '
                . 'GROUP BY `s1`.`id` ';
        
/**
 *  -   Self-join allows us to aggregate inner sample values inserted
 *      on/before the current outer sample.
 */
 $sql['movingAtInterval'] = 'SELECT DATE_FORMAT(`s1`.`x`, :format) AS `x`, '
                          . '`s1`.`y` AS `y`, '
                          . '`s1`.`y2` AS `y2` '
                          . 'FROM ~moving AS `s1` '
                          . 'LEFT JOIN ~moving AS `s2` '
                          . 'ON DATE_FORMAT(`s1`.`x`, :format) = DATE_FORMAT(`s2`.`x`, :format) '
                          . 'AND `s1`.`x` < `s2`.`x` '
                          . 'WHERE `s2`.`x` IS NULL ';
