<?php
// vim: fenc=utf-8:ft=php:ai:si:ts=4:sw=4:et:

/**
 * Hashmark_Analyst_BasicDecimal
 *
 * @filesource
 * @copyright   Copyright (c) 2008-2011 David Smith
 * @license     http://www.opensource.org/licenses/mit-license.php MIT License
 * @package     Hashmark
 * @subpackage  Hashmark_Analyst
 * @version     $Id$
*/

/**
 * Suite outputs raw sample values/dates, aggregates, moving aggregates,
 * successive changes, etc.
 *
 * @package     Hashmark
 * @subpackage  Hashmark_Analyst
 */
class Hashmark_Analyst_BasicDecimal extends Hashmark_Analyst
{
    /**
     * @var Hashmark_Partition_*    Instance created in initModule().
     */
    protected $_partition;

    /**
     * @var int     Current MySQL div_precision_increment setting.
     */
    protected $_divPrecisionIncr;

    /**
     * Aggregation options.
     *
     * @var Array   Function names.
     */
    protected static $_aggFunctions = array('AVG', 'SUM', 'COUNT', 'MAX', 'MIN',
                                            'STDDEV_POP', 'STDDEV_SAMP', 'VAR_POP', 'VAR_SAMP');
    
    /**
     * Aggregation options w/ DISTINCT support.
     *
     * @var Array   Function names.
     */
    protected static $_distinctFunctions = array('AVG', 'SUM', 'COUNT', 'MAX', 'MIN');
    
    /**
     * Time interval codes mapped to their DATE_FORMAT() format strings.
     *
     *      -   Weeks start on Monday.
     *
     * @var Array   Keys = interval codes, values = format strings.
     */
    protected static $_intervalDbFormats = array('h' => '%Y%m%d%H', 'd' => '%Y%m%d',
                                                 'w' => '%x%v', 'm' => '%Y%m', 'y' => '%Y');
    
    /**
     * Time interval codes mapped to their date() format strings.
     *
     *      -   Weeks start on Monday.
     *
     * @var Array   Keys = interval codes, values = format strings.
     */
    protected static $_intervalPhpFormats = array('h' => 'YmdH', 'd' => 'Ymd',
                                                  'w' => 'oW', 'm' => 'Ym', 'y' => 'Y');
    
    /**
     * Time formats/functions used to collect aggegrates of recurrence groups, 
     * ex. Tuesdays, Junes, or 1st days of the month.
     *
     *      -   'z' provides (DAYOFYEAR - 1)
     *
     * @var Array   Keys = functions, values = date() format strings.
     */
    protected static $_recurFormats = array('HOUR' => 'G', 'DAYOFMONTH' => 'j',
                                            'DAYOFYEAR' => 'z', 'MONTH' => 'n');

    /**
     * Called by Hashmark::getModule() to inject dependencies.
     *
     * @param mixed                 $db         Connection object/resource.
     * @param string                $dbName     Database selection, unquoted. [optional]
     * @param Hashmark_Partition    $partition  Initialized instance.
     * @return boolean  False if module could not be initialized and is unusable.
     *                  Hashmark::getModule() will also then return false.
     */
    public function initModule($db, $partition)
    {
        parent::initModule($db);

        $this->_partition = $partition;

        $dbHelperConfig = Hashmark::getConfig('DbHelper');
        $this->_divPrecisionIncr = $dbHelperConfig['div_precision_increment'];

        return true;
    }
    
    /**
     * Public access to $_intervalDbFormats values by interval code.
     *
     * @param string    $interval    Code, ex. 'h' for hour.
     * @return string   Format string; false if code unrecognized.
     */
    public static function getIntervalDbFormat($interval)
    {
        if (isset(self::$_intervalDbFormats[$interval])) {
            return self::$_intervalDbFormats[$interval];
        }

        return false;
    }
    
    /**
     * Public access to $_intervalPhpFormats values by interval code.
     *
     * @param string    $interval    Code, ex. 'h' for hour.
     * @return string   Format string; false if code unrecognized.
     */
    public static function getIntervalPhpFormat($interval)
    {
        if (isset(self::$_intervalPhpFormats[$interval])) {
            return self::$_intervalPhpFormats[$interval];
        }

        return false;
    }
    
    /**
     * Public access to $_aggFunctions.
     *
     * @return Array
     */
    public static function getAggFunctions()
    {
        return self::$_aggFunctions;
    }
    
    /**
     * Public access to $_distinctFunctions.
     *
     * @return Array
     */
    public static function getDistinctFunctions()
    {
        return self::$_distinctFunctions;
    }
    
    /**
     * Public access to $_recurFormats keys.
     *
     * @return Array
     */
    public static function getRecurFunctions()
    {
        return array_keys(self::$_recurFormats);
    }
    
    /**
     * Public access to $_recurFormats.
     *
     * @return Array
     */
    public static function getRecurFormats()
    {
        return self::$_recurFormats;
    }
   
    /**
     * Public access to $_recurFormats values by function name.
     *
     * @param string    $recurFunc  Ex. 'DAYOFMONTH'.
     * @return string   date() format string; false if $recurFunc unrecognized.
     */
    public static function getRecurFormat($recurFunc)
    {
        if (isset(self::$_recurFormats[$recurFunc])) {
            return self::$_recurFormats[$recurFunc];
        }

        return false;
    }
    
    /**
     * Set MySQL div_precision_increment variable.
     *
     * @param int   $incr   New value.
     * @return void
     * @throws Exception On query error.
     */
    public function setDivPrecisionIncr($incr)
    {
        if ($incr != $this->_divPrecisionIncr) {
            $this->_divPrecisionIncr = intval($incr);

            $sql = "SET div_precision_increment = {$this->_divPrecisionIncr}";

            $this->_db->query($sql);
        }
    }
    
    /**
     * Execute one or more SQL statements.
     *
     *  -   Results from each statement, except the last, are stored in a temp. table.
     *  -   Subsequent statements can use those intermediate results via temp.
     *      table macros in format: ~STATEMENT_ID
     *  -   Supports 1 temp. table self-join per statement.
     * 
     * @param int       $scalarId
     * @param mixed     $stmts      Keys = statement ID, ex. 'valuesAtInterval',
     *                              Values = assoc. arrays of statement options.
     *
     *      Example:
     *
     *      array('statement ID #1' => array('sql' => 'SELECT ...',
     *                                       'macros' => array('@a' => 'SUM',
     *                                                         '@b' => 'MIN'),
     *                                       'bind' => array(44, 10, 4),
     *                                       'tmpcol' => '`x`, `y`'),
     *            'statement ID #2' => array('sql' => 'SELECT ...',
     *                                       'macros' => array('@a' => 'COUNT',
     *                                                         '@b' => 'MAX'),
     *                                       'bind' => array(10),
     *                                       'tmpcol' => '`x`, `y`'),
     *            ...)
     *
     *      macros:
     *
     *          Expanded w/ Hashmark_Module_DbDependent::expandSql().
     *          Primarily used for SQL function names.
     *
     *      values:
     *
     *          Prepared statement '?' bind parameters.
     *
     *      tmpcol:
     *
     *          Required if that statement's results will be used,
     *          via statement macros like ~STATEMENT_ID in a later statement.
     *          It defines which `samples_analyst_temp` columns will be
     *          inserted by the associated statement, and in what order,
     *          via direct use in an INSERT INTO ... SELECT.
     *
     * @return Array        Found rows as associative arrays.
     * @throws Exception    If $stmts does not contain any statements;
     *                      if a statement tries to reopen a temp. table twice.
     */
    public function multiQuery($scalarId, $start, $end, $stmts)
    {
        /**
         * Temporary tables created.
         *
         * Keys = statement IDs, ex. 'valuesAtInterval', values = table names.
         */
        $tempTables = array();

        /**
         * Temporary table copies created.
         *
         * Keys = statement IDs, ex. 'valuesAtInterval', values = table names.
         */
        $tempTableCopies = array();

        if (is_array($stmts)) {
            $stmtIds = array_keys($stmts);
        } else {
            throw new Exception('No SQL statements to execute.', HASHMARK_EXCEPTION_VALIDATION);
        }
            
        $stmtCount = count($stmtIds);

        for ($stmtNum = 0; $stmtNum < $stmtCount; $stmtNum++) {
            $stmtId = $stmtIds[$stmtNum];
            $currentStmt = $stmts[$stmtId];
            $bind = (isset($currentStmt['bind']) ? $currentStmt['bind'] : array());

            // Expand '@key'  macros.
            if (isset($currentStmt['macros'])) {
                $currentStmt['sql']= $this->expandSql($currentStmt['sql'], $currentStmt['macros']);
            }

            // Temp. tables available, created from prior queries.
            foreach ($tempTables as $tmpStmtId => $tmpTable) {
                // Count references in current statement to this temp. statement.
                $stmtMacroCount = substr_count($currentStmt['sql'], '~' . $tmpStmtId);

                // Support up to 2 statement references, ex. for self-joins.
                if ($stmtMacroCount > 2) {
                    throw new Exception("Statement {$stmtId} cannot use macro {$tmpStmtId} more than twice.",
                                        HASHMARK_EXCEPTION_VALIDATION);
                }

                for ($uses = 0; $uses < $stmtMacroCount; $uses++) {
                    // Avoid "ERROR 1137: Can't reopen table" by creating a temp table copy
                    // for the second reference.
                    if (1 == $uses) {
                        if (!isset($tempTableCopies[$tmpStmtId])) {
                            $tempTableCopies[$tmpStmtId] = $this->_partition->copyToTemp($tempTables[$tmpStmtId]);
                        }

                        // Use the copy's name instead.
                        $tmpTable = $tempTableCopies[$tmpStmtId];
                    }

                    // Expand the statement ID macro with the temp. table name.
                    $currentStmt['sql'] = preg_replace('/\~'. $tmpStmtId . '/',
                                                      $this->_dbName . '`'. $tmpTable . '`',
                                                      $currentStmt['sql'],
                                                      1);
                }
            }
                
            // Last statement in sequence.
            if ($stmtNum == ($stmtCount - 1)) {
                if (false === strpos($currentStmt['sql'], '~samples')) {
                    $res = $this->_db->query($currentStmt['sql'], $bind);
                } else {
                    $res = $this->_partition->queryInRange($scalarId,
                                                           $start,
                                                           $end,
                                                           $currentStmt['sql'],
                                                           $bind);
                }
            } else {
                $tempTables[$stmtId] = $this->_partition->createTempFromQuery('samples_analyst_temp',
                                                                             $currentStmt['tmpcol'],
                                                                             $currentStmt['sql'],
                                                                             $bind,
                                                                             $scalarId,
                                                                             $start,
                                                                             $end);

                // Ex. prevent table macro ~changes from being being expanded before
                // ~changesAtInterval.
                uksort($tempTables, array('Hashmark_Util', 'sortByStrlenReverse'));
            }
        }

        $rows = $res->fetchAll();

        if ($tempTables || $tempTableCopies) {
            $this->_partition->dropTable(array_merge($tempTables, $tempTableCopies));
        }

        return $rows;
    }

    /**
     * Finds samples in range/limit.
     *
     * @param int       $scalarId
     * @param int       $limit  Result set size limit.
     * @param string    $start  Inclusive DATETIME range boundary.
     * @param string    $end    Inclusive DATETIME range boundary.
     * @return Array    Time/value pair assoc. arrays; otherwise false.
     *                  Each pair: 'x' = time, 'y' = value.
     * @throws Exception On query error.
     */
    public function values($scalarId, $limit, $start, $end)
    {
        $sql = $this->getSql(__FUNCTION__)
             . ($limit ? 'LIMIT ' . intval($limit) : '');
       
        $bind = array($start, $end); 
        $stmt = $this->_partition->queryInRange($scalarId, $start, $end, $sql, $bind);

        $rows = $stmt->fetchAll();
        if (!$rows) {
            return false;
        }
    
        return $rows;
    }
    
    /**
     * Finds the most recent sample from each time interval.
     *
     *      -   Use these to pre-validate parameters:
     *          $interval: getIntervalDbFormat()
     *
     * @param int       $scalarId
     * @param int       $limit      Result set size limit.
     * @param string    $start      Inclusive DATETIME range boundary.
     * @param string    $end        Inclusive DATETIME range boundary.
     * @param string    $interval   Interval code.
     * @return Array    Time/value pair assoc. arrays,
     *                  each pair: 'x' = time, 'y' = value. False on error.
     * @see Hashmark_Analyst_BasicDecimal::$_intervalFormats for $interval codes.
     * @throws Exception On query error; on invalid $interval.
     */
    public function valuesAtInterval($scalarId, $limit, $start, $end, $interval)
    {
        if (!($format = $this->getIntervalDbFormat($interval))) {
            throw new Exception('Invalid interval: ' . $interval, HASHMARK_EXCEPTION_VALIDATION);
        }
        
        $sql = $this->getSql(__FUNCTION__)
             . ($limit ? 'LIMIT ' . intval($limit) : '');
        
        $bind = array($format, $format, $start, $end, $start, $end);
        $stmt = $this->_partition->queryInRange($scalarId, $start, $end, $sql, $bind);

        $rows = $stmt->fetchAll();
        if (!$rows) {
            return false;
        }
    
        return $rows;
    }

    /**
     * Finds the aggregate of samples.
     *
     *      -   Use these to pre-validate parameters:
     *          $aggFunc: getAggFunctions()
     *          $distinct: getDistinctFunctions()
     *
     * @param int       $scalarId
     * @param string    $start      Inclusive DATETIME range boundary.
     * @param string    $end        Inclusive DATETIME range boundary.
     * @param string    $aggFunc    GROUP BY function, ex. 'SUM'.
     * @param boolean   $distinct   Apply DISTINCT constraint.
     * @return string   Aggregate value; otherwise false.
     * @see Hashmark_Analyst_BasicDecimal::$_aggFunctions for $aggFunc options.
     * @see Hashmark_Analyst_BasicDecimal::$_distinctFunctions for $distinct availability.
     * @throws Exception On query error.
     */
    public function valuesAgg($scalarId, $start, $end, $aggFunc, $distinct)
    {
        $this->setDivPrecisionIncr(0);

        $distinct = $distinct ? 'DISTINCT ' : '';

        $sql = $this->expandSql($this->getSql(__FUNCTION__),
                                array('@aggFunc' => $aggFunc,
                                      '@distinct' => $distinct));

        $bind = array($start, $end);
        $stmt = $this->_partition->queryInRange($scalarId, $start, $end, $sql, $bind);

        $rows = $stmt->fetchAll();
        if (!$rows) {
            return false;
        }
    
        return $rows[0]['y'];
    }

    /**
     * Finds the aggregate of samples in each interval.
     *
     *      -   Use these to pre-validate parameters:
     *          $interval: getIntervalDbFormat()
     *          $aggFunc: getAggFunctions()
     *          $distinct: getDistinctFunctions()
     *
     * @param int       $scalarId
     * @param string    $start      Inclusive DATETIME range boundary.
     * @param string    $end        Inclusive DATETIME range boundary.
     * @param string    $interval   Interval code.
     * @param string    $aggFunc    GROUP BY function, ex. 'SUM'.
     * @param boolean   $distinct   Apply DISTINCT constraint.
     * @return Array    Time/value pair assoc. arrays; otherwise false.
     *                  Each pair: 'x' = time group, 'y' = aggregate value.
     * @see Hashmark_Analyst_BasicDecimal::$_intervalFormats for $interval codes.
     * @see Hashmark_Analyst_BasicDecimal::$_aggFunctions for $aggFunc options.
     * @see Hashmark_Analyst_BasicDecimal::$_distinctFunctions for $distinct availability.
     * @throws Exception On query error; on invalid $interval.
     */
    public function valuesAggAtInterval($scalarId, $start, $end, $interval, $aggFunc, $distinct)
    {
        if (!($format = $this->getIntervalDbFormat($interval))) {
            throw new Exception('Invalid interval: ' . $interval, HASHMARK_EXCEPTION_VALIDATION);
        }
        
        $this->setDivPrecisionIncr(0);
        
        $distinct = $distinct ? 'DISTINCT ' : '';
        
        $sql = $this->expandSql($this->getSql(__FUNCTION__),
                                array('@aggFunc' => $aggFunc,
                                      '@distinct' => $distinct));

        $bind = array($format, $start, $end);
        $stmt = $this->_partition->queryInRange($scalarId, $start, $end, $sql, $bind);

        $rows = $stmt->fetchAll();
        if (!$rows) {
            return false;
        }
    
        return $rows;
    }
    
    /**
     * Finds the aggregate of interval aggregates, i.e. aggregate of valuesAggAtInterval().
     * 
     *      -   Ex. MAX(AVG())-type queries.
     *      -   Use these to pre-validate parameters:
     *          $interval: getIntervalDbFormat()
     *          $aggFunc*: getAggFunctions()
     *          $distinct*: getDistinctFunctions()
     *
     * @param int       $scalarId
     * @param string    $start          Inclusive DATETIME range boundary.
     * @param string    $end            Inclusive DATETIME range boundary.
     * @param string    $interval       Interval code.
     * @param string    $aggFuncOuter   GROUP BY function, ex. 'SUM'.
     * @param boolean   $distinctOuter  Apply DISTINCT constraint.
     * @param string    $aggFuncInner   GROUP BY function, ex. 'SUM'.
     * @param boolean   $distinctInner  Apply DISTINCT constraint.
     * @return string   Aggregate value; otherwise false.
     * @see Hashmark_Analyst_BasicDecimal::$_intervalFormats for $interval codes.
     * @see Hashmark_Analyst_BasicDecimal::$_aggFunctions for $aggFunc* options.
     * @see Hashmark_Analyst_BasicDecimal::$_distinctFunctions for $distinct* availability.
     * @throws Exception On query error; on invalid $interval.
     */
    public function valuesNestedAggAtInterval($scalarId, $start, $end, $interval, $aggFuncOuter, $distinctOuter, $aggFuncInner, $distinctInner)
    {
        if (!($format = $this->getIntervalDbFormat($interval))) {
            throw new Exception('Invalid interval: ' . $interval, HASHMARK_EXCEPTION_VALIDATION);
        }
        
        $this->setDivPrecisionIncr(0);
        
        $distinctOuter = $distinctOuter ? 'DISTINCT ' : '';
        $distinctInner = $distinctInner ? 'DISTINCT ' : '';
                                
        $querySet = array('valuesAggAtInterval' => array(),
                          __FUNCTION__ => array());

        $querySet['valuesAggAtInterval']['sql'] = $this->getSql('valuesAggAtInterval');
        $querySet['valuesAggAtInterval']['macros'] = array('@aggFunc' => $aggFuncInner,
                                                           '@distinct' => $distinctInner);
        $querySet['valuesAggAtInterval']['bind'] = array($format, $start, $end);

        // Set which temporary table columns are filled this query's results.
        $querySet['valuesAggAtInterval']['tmpcol'] = '`grp`, `y2`';

        $querySet[__FUNCTION__]['sql'] = $this->getSql(__FUNCTION__);
        $querySet[__FUNCTION__]['macros'] = array('@aggFunc' => $aggFuncOuter,
                                                  '@distinct' => $distinctOuter);

        $rows = $this->multiQuery($scalarId, $start, $end, $querySet);

        if (!$rows) {
            return false;
        }
    
        return $rows[0]['y2'];
    }
    
    /**
     * Finds the aggregate of samples in each recurrence group.
     *
     *      -   Ex. AVG() of Fridays or 8:00 AMs.
     *      -   Use these to pre-validate parameters:
     *          $recurFunc: getRecurFunctions()
     *          $aggFunc: getAggFunctions()
     *          $distinct: getDistinctFunctions()
     *
     * @param int       $scalarId
     * @param string    $start          Inclusive DATETIME range boundary.
     * @param string    $end            Inclusive DATETIME range boundary.
     * @param string    $recurFunc      Recurrence function, ex. 'DAYOFMONTH'
     * @param string    $aggFunc        GROUP BY function, ex. 'SUM'.
     * @param boolean   $distinct       Apply DISTINCT constraint.
     * @return Array    Time/value pair assoc. arrays; otherwise false.
     *                  Each pair: 'x' = time group, 'y' = aggregate value.
     * @see Hashmark_Analyst_BasicDecimal::$_recurFormats for $recurFunc options.
     * @see Hashmark_Analyst_BasicDecimal::$_aggFunctions for $aggFunc options.
     * @see Hashmark_Analyst_BasicDecimal::$_distinctFunctions for $distinct availability.
     * @throws Exception On query error.
     */
    public function valuesAggAtRecurrence($scalarId, $start, $end, $recurFunc, $aggFunc, $distinct)
    {
        $this->setDivPrecisionIncr(0);

        $distinct = $distinct ? 'DISTINCT ' : '';

        $sql = $this->expandSql($this->getSql(__FUNCTION__),
                                array('@recurFunc' => $recurFunc,
                                      '@aggFunc' => $aggFunc,
                                      '@distinct' => $distinct));

        $bind = array($start, $end);
        $stmt = $this->_partition->queryInRange($scalarId, $start, $end, $sql, $bind);

        $rows = $stmt->fetchAll();
        if (!$rows) {
            return false;
        }
    
        return $rows;
    }
   
    /**
     * Finds value changes between successive samples.
     *
     * @param int       $scalarId
     * @param int       $limit  Result set size limit.
     * @param string    $start  Inclusive DATETIME range boundary.
     * @param string    $end    Inclusive DATETIME range boundary.
     * @return Array    Time/value pair assoc. arrays; otherwise false.
     *                  Each pair: 'x' = time, 'y' = value.
     * @throws Exception On query error.
     */
    public function changes($scalarId, $limit, $start, $end)
    {
        $sql = $this->getSql(__FUNCTION__)
             . ($limit ? 'LIMIT ' . intval($limit) : '');
       
        $bind = array($start, $end, $start, $end);
        $stmt = $this->_partition->queryInRange($scalarId, $start, $end, $sql, $bind);

        $rows = $stmt->fetchAll();
        if (!$rows) {
            return false;
        }
    
        return $rows;
    }
    
    /**
     * Finds value changes between successive interval samples,
     * i.e. changes between valuesAtInterval() results.
     *
     *      -   Use these to pre-validate parameters:
     *          $interval: getIntervalDbFormat()
     *
     * @param int       $scalarId
     * @param int       $limit  Result set size limit.
     * @param string    $start  Inclusive DATETIME range boundary.
     * @param string    $end    Inclusive DATETIME range boundary.
     * @param string    $interval       Interval code.
     * @return Array    Time/value pair assoc. arrays,
     *                  each pair: 'x' = time, 'y' = value. False on error.
     * @throws Exception On query error; on invalid $interval.
     */
    public function changesAtInterval($scalarId, $limit, $start, $end, $interval)
    {
        if (!($format = $this->getIntervalDbFormat($interval))) {
            throw new Exception('Invalid interval: ' . $interval, HASHMARK_EXCEPTION_VALIDATION);
        }

        $querySet = array('valuesAtInterval' => array(),
                          __FUNCTION__ => array());

        $querySet['valuesAtInterval']['sql'] = $this->getSql('valuesAtInterval')
                                             . 'ORDER BY `s1`.`end` DESC ';
        $querySet['valuesAtInterval']['bind'] = array($format, $format,
                                                      $start, $end, $start, $end);
        
        // Set which temporary table columns are filled this query's results.
        $querySet['valuesAtInterval']['tmpcol'] = '`x`, `y`';

        $querySet[__FUNCTION__]['sql'] = $this->getSql(__FUNCTION__)
                                       . ($limit ? 'LIMIT ' . intval($limit) : '');
        $querySet[__FUNCTION__]['bind'] = array($format);

        $rows = $this->multiQuery($scalarId, $start, $end, $querySet);

        if (!$rows) {
            return false;
        }
    
        return $rows;
    }
    
    /**
     * Finds aggregate of value changes between successive interval samples.
     *
     *      -   Use these to pre-validate parameters:
     *          $aggFunc: getAggFunctions()
     *          $distinct: getDistinctFunctions()
     *
     * @param int       $scalarId
     * @param string    $start      Inclusive DATETIME range boundary.
     * @param string    $end        Inclusive DATETIME range boundary.
     * @param string    $aggFunc    GROUP BY function, ex. 'SUM'.
     * @param boolean   $distinct   Apply DISTINCT constraint.
     * @return string   Aggregate value; otherwise false.
     * @see Hashmark_Analyst_BasicDecimal::$_aggFunctions for $aggFunc options.
     * @see Hashmark_Analyst_BasicDecimal::$_distinctFunctions for $distinct availability.
     * @throws Exception On query error.
     */
    public function changesAgg($scalarId, $start, $end, $aggFunc, $distinct)
    {
        $this->setDivPrecisionIncr(0);

        $distinct = $distinct ? 'DISTINCT ' : '';

        $sql = $this->expandSql($this->getSql(__FUNCTION__),
                                array('@aggFunc' => $aggFunc,
                                      '@distinct' => $distinct));

        $bind = array($start, $end, $start, $end);
        $stmt = $this->_partition->queryInRange($scalarId, $start, $end, $sql, $bind);

        $rows = $stmt->fetchAll();
        if (!$rows) {
            return false;
        }
    
        return $rows[0]['y'];
    }
    
    /**
     * Finds aggregate of value changes in each interval.
     *
     *      -   Use these to pre-validate parameters:
     *          $interval: getIntervalDbFormat()
     *          $aggFunc: getAggFunctions()
     *          $distinct: getDistinctFunctions()
     *
     * @param int       $scalarId
     * @param string    $start      Inclusive DATETIME range boundary.
     * @param string    $end        Inclusive DATETIME range boundary.
     * @param string    $interval   Interval code.
     * @param string    $aggFunc    GROUP BY function, ex. 'SUM'.
     * @param boolean   $distinct   Apply DISTINCT constraint.
     * @param Array     Time/value pair assoc. arrays; otherwise false.
     *                  Each pair: 'x' = time group, 'y' = aggregate value.
     * @see Hashmark_Analyst_BasicDecimal::$_intervalFormats for $interval codes.
     * @see Hashmark_Analyst_BasicDecimal::$_aggFunctions for $aggFunc options.
     * @see Hashmark_Analyst_BasicDecimal::$_distinctFunctions for $distinct availability.
     * @throws Exception On query error; on invalid $interval.
     */
    public function changesAggAtInterval($scalarId, $start, $end, $interval, $aggFunc, $distinct)
    {
        if (!($format = $this->getIntervalDbFormat($interval))) {
            throw new Exception('Invalid interval: ' . $interval, HASHMARK_EXCEPTION_VALIDATION);
        }
        
        $this->setDivPrecisionIncr(0);
        
        $distinct = $distinct ? 'DISTINCT ' : '';
        
        $querySet = array('changes' => array(),
                          __FUNCTION__ => array());

        $querySet['changes']['sql'] = $this->getSql('changes');
        $querySet['changes']['bind'] = array($start, $end, $start, $end);
                        
        // Set which temporary table columns are filled this query's results.
        $querySet['changes']['tmpcol'] = '`x`, `y`, `y2`, `id`';

        $querySet[__FUNCTION__]['sql'] = $this->getSql(__FUNCTION__);
        $querySet[__FUNCTION__]['macros'] = array('@aggFunc' => $aggFunc,
                                                  '@distinct' => $distinct);
        $querySet[__FUNCTION__]['bind'] = array($format, $format);

        $rows = $this->multiQuery($scalarId, $start, $end, $querySet);

        if (!$rows) {
            return false;
        }
    
        return $rows;
    }
    
    /**
     * Finds the aggregate of interval aggregates, i.e. aggregate of changesAggAtInterval().
     * 
     *      -   Ex. MAX(AVG())-type queries.
     *      -   Use these to pre-validate parameters:
     *          $interval: getIntervalDbFormat()
     *          $aggFunc*: getAggFunctions()
     *          $distinct*: getDistinctFunctions()
     *
     * @param int       $scalarId
     * @param string    $start          Inclusive DATETIME range boundary.
     * @param string    $end            Inclusive DATETIME range boundary.
     * @param string    $interval       Interval code.
     * @param string    $aggFuncOuter   GROUP BY function, ex. 'SUM'.
     * @param boolean   $distinctOuter  Apply DISTINCT constraint.
     * @param string    $aggFuncInner   GROUP BY function, ex. 'SUM'.
     * @param boolean   $distinctInner  Apply DISTINCT constraint.
     * @return string   Aggregate value; otherwise false.
     * @see Hashmark_Analyst_BasicDecimal::$_intervalFormats for $interval codes.
     * @see Hashmark_Analyst_BasicDecimal::$_aggFunctions for $aggFunc* options.
     * @see Hashmark_Analyst_BasicDecimal::$_distinctFunctions for $distinct* availability.
     * @throws Exception On query error; on invalid $interval.
     */
    public function changesNestedAggAtInterval($scalarId, $start, $end, $interval, $aggFuncOuter, $distinctOuter, $aggFuncInner, $distinctInner)
    {
        if (!($format = $this->getIntervalDbFormat($interval))) {
            throw new Exception('Invalid interval: ' . $interval, HASHMARK_EXCEPTION_VALIDATION);
        }
        
        $this->setDivPrecisionIncr(0);
        
        $distinctOuter = $distinctOuter ? 'DISTINCT ' : '';
        $distinctInner = $distinctInner ? 'DISTINCT ' : '';

        $querySet = array('changes' => array(),
                          'changesAggAtInterval' => array(),
                          __FUNCTION__ => array());
        
        $querySet['changes']['sql'] = $this->getSql('changes');
        $querySet['changes']['bind'] = array($start, $end, $start, $end);

        // Set which temporary table columns are filled this query's results.
        $querySet['changes']['tmpcol'] = '`x`, `y`, `y2`, `id`';

        $querySet['changesAggAtInterval']['sql'] = $this->getSql('changesAggAtInterval');
        $querySet['changesAggAtInterval']['macros'] = array('@aggFunc' => $aggFuncInner,
                                                            '@distinct' => $distinctInner);
        $querySet['changesAggAtInterval']['bind'] = array($format, $format);
        
        // Set which temporary table columns are filled this query's results.
        $querySet['changesAggAtInterval']['tmpcol'] = '`grp`, `y`';

        $querySet[__FUNCTION__]['sql'] = $this->getSql(__FUNCTION__);
        $querySet[__FUNCTION__]['macros'] = array('@aggFunc' => $aggFuncOuter,
                                                  '@distinct' => $distinctOuter);

        $rows = $this->multiQuery($scalarId, $start, $end, $querySet);

        if (!$rows) {
            return false;
        }
    
        return $rows[0]['y'];
    }
    
    /**
     * Finds the aggregate of value changes in each recurrence group.
     *
     *      -   Use these to pre-validate parameters:
     *          $recurFunc: getRecurFunctions()
     *          $aggFunc: getAggFunctions()
     *          $distinct: getDistinctFunctions()
     *
     * @param int       $scalarId
     * @param string    $start          Inclusive DATETIME range boundary.
     * @param string    $end            Inclusive DATETIME range boundary.
     * @param string    $recurFunc      Recurrence function, ex. 'DAYOFMONTH'
     * @param string    $aggFunc        GROUP BY function, ex. 'SUM'.
     * @param boolean   $distinct       Apply DISTINCT constraint.
     * @return Array    Time/value pair assoc. arrays; otherwise false.
     *                  Each pair: 'x' = time group, 'y' = aggregate value.
     * @see Hashmark_Analyst_BasicDecimal::$_recurFormats for $recurFunc options.
     * @see Hashmark_Analyst_BasicDecimal::$_aggFunctions for $aggFunc options.
     * @see Hashmark_Analyst_BasicDecimal::$_distinctFunctions for $distinct availability.
     * @throws Exception On query error.
     */
    public function changesAggAtRecurrence($scalarId, $start, $end, $recurFunc, $aggFunc, $distinct)
    {
        $this->setDivPrecisionIncr(0);
       
        $distinct = $distinct ? 'DISTINCT ' : '';
        
        $sql = $this->expandSql($this->getSql(__FUNCTION__),
                                array('@recurFunc' => $recurFunc,
                                      '@aggFunc' => $aggFunc,
                                      '@distinct' => $distinct));

        $bind = array($start, $end, $start, $end);
        $stmt = $this->_partition->queryInRange($scalarId, $start, $end, $sql, $bind);

        $rows = $stmt->fetchAll();
        if (!$rows) {
            return false;
        }
    
        return $rows;
    }
    
    /**
     * Finds value occurrence counts, i.e. frequency/popularity.
     *
     * @param int       $scalarId
     * @param int       $limit      Result set size limit.
     * @param string    $start      Inclusive DATETIME range boundary.
     * @param string    $end        Inclusive DATETIME range boundary.
     * @param boolean   $descOrder  Apply descending order.
     * @return Array    Time/value pair assoc. arrays; otherwise false.
     *                  Each pair: 'x' = value, 'y' = frequency.
     * @throws Exception On query error.
     */
    public function frequency($scalarId, $limit, $start, $end, $descOrder)
    {
        $sql = $this->getSql(__FUNCTION__)
             . 'ORDER BY `y` ' . ($descOrder ? 'DESC' : 'ASC')
             . ($limit ? ' LIMIT ' . intval($limit) : '');
       
        $bind = array($start, $end);
        $stmt = $this->_partition->queryInRange($scalarId, $start, $end, $sql, $bind);

        $rows = $stmt->fetchAll();
        if (!$rows) {
            return false;
        }
    
        return $rows;
    }
    
    /**
     * Finds moving aggregrate of values, i.e. running/cumulative aggregate.
     *
     *      -   Use these to pre-validate parameters:
     *          $aggFunc: getAggFunctions()
     *          $distinct: getDistinctFunctions()
     *
     * @param int       $scalarId
     * @param int       $limit      Result set size limit.
     * @param string    $start      Inclusive DATETIME range boundary.
     * @param string    $end        Inclusive DATETIME range boundary.
     * @param string    $aggFunc    GROUP BY function, ex. 'SUM'.
     * @param boolean   $distinct   Apply DISTINCT constraint.
     * @return Array    Time/value pair assoc. arrays; otherwise false.
     *                  Each pair: 'x' = time, 'y' = aggregate.
     * @see Hashmark_Analyst_BasicDecimal::$_aggFunctions for $aggFunc* options.
     * @see Hashmark_Analyst_BasicDecimal::$_distinctFunctions for $distinct availability.
     * @throws Exception On query error.
     */
    public function moving($scalarId, $limit, $start, $end, $aggFunc, $distinct)
    {
        $this->setDivPrecisionIncr(0);

        $distinct = $distinct ? 'DISTINCT ' : '';
        
        $sql = $this->getSql(__FUNCTION__)
             . ($limit ? ' LIMIT ' . intval($limit) : '');
       
        $sql = $this->expandSql($this->getSql(__FUNCTION__),
                                array('@aggFunc' => $aggFunc,
                                      '@distinct' => $distinct));

        $bind = array($start, $end, $start, $end);
        $stmt = $this->_partition->queryInRange($scalarId, $start, $end, $sql, $bind);

        $rows = $stmt->fetchAll();
        if (!$rows) {
            return false;
        }
    
        return $rows;
    }
    
    /**
     * Finds the most recent moving aggregate from each time interval,
     * i.e. like valuesAtInterval() but source data is a moving()
     * result set.
     *
     *      -   Use these to pre-validate parameters:
     *          $interval: getIntervalDbFormat()
     *          $aggFunc: getAggFunctions()
     *          $distinct: getDistinctFunctions()
     *
     * @param int       $scalarId
     * @param int       $limit      Result set size limit.
     * @param string    $start      Inclusive DATETIME range boundary.
     * @param string    $end        Inclusive DATETIME range boundary.
     * @param string    $interval   Interval code.
     * @param string    $aggFunc    GROUP BY function, ex. 'SUM'.
     * @param boolean   $distinct   Apply DISTINCT constraint.
     * @return Array    Time/value pair assoc. arrays; otherwise false.
     *                  Each pair: 'x' = time, 'y' = aggegrate.
     * @see Hashmark_Analyst_BasicDecimal::$_intervalFormats for $interval codes.
     * @see Hashmark_Analyst_BasicDecimal::$_aggFunctions for $aggFunc* options.
     * @see Hashmark_Analyst_BasicDecimal::$_distinctFunctions for $distinct availability.
     * @throws Exception On query error; on invalid $interval.
     */
    public function movingAtInterval($scalarId, $limit, $start, $end, $interval, $aggFunc, $distinct)
    {
        if (!($format = $this->getIntervalDbFormat($interval))) {
            throw new Exception('Invalid interval: ' . $interval, HASHMARK_EXCEPTION_VALIDATION);
        }
        
        $distinct = $distinct ? 'DISTINCT ' : '';
        
        $this->setDivPrecisionIncr(0);
        
        $querySet = array('moving' => array(),
                          __FUNCTION__ => array());

        $querySet['moving']['sql'] = $this->getSql('moving');
        $querySet['moving']['macros'] = array('@aggFunc' => $aggFunc,
                                              '@distinct' => $distinct);
        $querySet['moving']['bind'] = array($start, $end, $start, $end);

        // Set which temporary table columns are filled this query's results.
        $querySet['moving']['tmpcol'] = '`x`, `y`, `y2`';

        $querySet[__FUNCTION__]['sql'] = $this->getSql(__FUNCTION__)
                                       . ($limit ? 'LIMIT ' . intval($limit) : '');
        $querySet[__FUNCTION__]['macros'] = array('@aggFunc' => $aggFunc,
                                                  '@distinct' => $distinct);
        $querySet[__FUNCTION__]['bind'] = array($format, $format, $format);

        $rows = $this->multiQuery($scalarId, $start, $end, $querySet);

        if (!$rows) {
            return false;
        }
    
        return $rows;
    }
}
