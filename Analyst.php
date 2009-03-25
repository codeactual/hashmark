<?php
// vim: fenc=utf-8:ft=php:ai:si:ts=4:sw=4:et:

/**
 * Hashmark_Analyst
 *
 * @filesource
 * @link        http://code.google.com/p/hashmark/
 * @link        http://framework.zend.com/manual/en/coding-standard.html
 * @link        http://phpdoc.org/tutorial.php
 * @copyright   Copyright (c) 2008-2009, Code Actual LLC
 * @license     http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package     Hashmark
 * @subpackage  Base
 * @version     $Id: Analyst.php 296 2009-02-13 05:03:11Z david $
*/

/**
 * Base class for sample analysers suites.
 *
 * Implementations live in Analyst/. Each suite queries sample partitions
 * of one or more scalars to extract an analytical result, ex. simple
 * aggregates or raw value/date lists.
 * 
 * @abstract
 * @package     Hashmark
 * @subpackage  Base
 */
abstract class Hashmark_Analyst extends Hashmark_Module_DbDependent
{
    /**
     * @access protected
     * @var Hashmark_Cache_*    Instance created in initModule().
     */
    protected $_cache;
    
    /**
     * Called by Hashmark::getModule() to inject dependencies.
     *
     * @access public
     * @param mixed     $db     Connection object/resource.
     * @return boolean  False if module could not be initialized and is unusable.
     *                  Hashmark::getModule() will also then return false.
     */
    public function initModule($db)
    {
        parent::initModule($db);

        $this->_cache = Hashmark::getModule('Cache', HASHMARK_CACHE_DEFAULT_TYPE);

        return true;
    }

    /**
     * Execute one or more SQL statements.
     *
     *      -   Results from each statement, except the last, is stored in a
     *          temporary table.
     *      -   Subsequent statements can use those intermediate results via
     *          temp. table macros.
     *      -   Supports 1 temp. table self-join per statement.
     * 
     * @access public
     * @param mixed     $sql        One statement or an Array of them with unique
     *                              labels as keys. Labels are used for temp. table
     *                              macros in the format: ~name.
     * @param Array     $values     DbHelper::query() compatible named macros.
     * @return mixed    Query result object/resource; false if no partition tables
     *                  exist in the date rannge defined in $values.
     * @throws Exception    If $sql does not contain any statements;
     *                      if a statement tries to reopen a temporary table more
     *                      than once.
     */
    public function query($sql, $values)
    {
        // Both Arrays: keys = labels; values = table names.
        $tempTables = array();
        $copyTables = array();

        if ($sql && is_string($sql)) {
            $labels = array('anon');
            $sql = array('anon' => $sql);
        } else if ($sql && is_array($sql)) {
            $labels = array_keys($sql);
        } else {
            throw new Exception('No SQL statement to execute.', HASHMARK_EXCEPTION_VALIDATION);
        }
            
        $labelsCount = count($labels);

        for ($labelNum = 0; $labelNum < $labelsCount; $labelNum++) {
            $label = $labels[$labelNum];
            $currentSql = $sql[$label];

            // Expand temp. table macros.
            foreach ($tempTables as $tableLabel => $table) {
                $macros = substr_count($currentSql, '~' . $tableLabel);
                if ($macros > 2) {
                    throw new Exception("Statement {$label} cannot use macro {$tableLabel} more than twice.", HASHMARK_EXCEPTION_VALIDATION);
                }

                $regex = '/\~'. $tableLabel . '/';
                for ($replacements = 0; $replacements < $macros; $replacements++) {
                    // Avoid "ERROR 1137: Can't reopen table" by creating a copy,
                    // ex. for self-joins.
                    if (1 == $replacements) {
                        if (!isset($copyTables[$tableLabel])) {
                            $copyTables[$tableLabel] = $this->_partition->copyToTemp($tempTables[$tableLabel]);
                        }
                        $table = $copyTables[$tableLabel];
                    }
                    $currentSql = preg_replace($regex, $this->_dbName . '`'. $table . '`', $currentSql, 1);
                }
            }
                
            // Last statement in sequence.
            if ($labelNum == ($labelsCount - 1)) {
                if (false === strpos($currentSql, '~samples')) {
                    $res = $this->_dbHelper->query($this->_db, $currentSql, $values);;
                } else {
                    $res = $this->_partition->queryInRange($values[':scalarId'],
                                                           $values[':start'],
                                                           $values[':end'],
                                                           $currentSql, $values);
                }
            } else {
                $tempTables[$label] = $this->_partition->createTempFromQuery('samples_analyst_temp',
                                                                             $values['@' . $label . 'Cols'],
                                                                             $currentSql, $values);

                // Ex. prevent table macro ~changes from being being expanded before
                // ~changesAtInterval.
                uksort($tempTables, array('Hashmark_Util', 'sortByStrlenReverse'));
            }
        }

        if ($tempTables || $copyTables) {
            $this->_partition->dropTable(array_merge($tempTables, $copyTables));
        }

        return $res;
    }
}
