<?php
// vim: fenc=utf-8:ft=php:ai:si:ts=4:sw=4:et:

/**
 * Hashmark_Analyst_BasicDecimal
 *
 * @filesource
 * @link        http://code.google.com/p/hashmark/
 * @link        http://framework.zend.com/manual/en/coding-standard.html
 * @link        http://phpdoc.org/tutorial.php
 * @copyright   Copyright (c) 2008-2009, Code Actual LLC
 * @license     http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package     Hashmark
 * @subpackage  Hashmark_Analyst
 * @version     $Id: BasicDecimal.php 300 2009-02-13 05:51:17Z david $
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

        $dbHelperConfig = $this->_dbHelper->getBaseConfig();
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

            $this->_dbHelper->query($this->_db, $sql);
        }
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
        $values = array(':scalarId' => $scalarId, ':start' => $start, ':end' => $end);

        $sql = $this->getSql(__FUNCTION__)
             . ($limit ? 'LIMIT ' . intval($limit) : '');
       
        $res = $this->query($sql, $values);

        if (!$res || !$this->_dbHelper->numRows($res)) {
            return false;
        }
    
        $samples = array();

        while ($sample = $this->_dbHelper->fetchAssoc($res)) {
            $samples[] = $sample;
        }

        $this->_dbHelper->freeResult($res);

        return $samples;
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
        
        $values = array(':scalarId' => $scalarId, ':start' => $start, ':end' => $end,
                        ':format' => $format);
        
        $sql = $this->getSql(__FUNCTION__)
             . ($limit ? 'LIMIT ' . intval($limit) : '');
        
        $res = $this->query($sql, $values);

        if (!$res || !$this->_dbHelper->numRows($res)) {
            return false;
        }
    
        $samples = array();

        while ($sample = $this->_dbHelper->fetchAssoc($res)) {
            $samples[] = $sample;
        }

        $this->_dbHelper->freeResult($res);

        return $samples;
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

        $values = array(':scalarId' => $scalarId, ':start' => $start, ':end' => $end,
                        '@aggFunc' => $aggFunc, '@distinct' => $distinct);
        
        $res = $this->query($this->getSql(__FUNCTION__), $values);

        if (!$res || !$this->_dbHelper->numRows($res)) {
            return false;
        }
    
        $aggregate = $this->_dbHelper->fetchAssoc($res);

        $this->_dbHelper->freeResult($res);
            
        return $aggregate['y'];
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

        $values = array(':scalarId' => $scalarId, ':start' => $start, ':end' => $end,
                        ':format' => $format, '@aggFunc' => $aggFunc, '@distinct' => $distinct);
        
        $res = $this->query($this->getSql(__FUNCTION__), $values);

        if (!$res || !$this->_dbHelper->numRows($res)) {
            return false;
        }
    
        $aggregates = array();

        while ($aggregate = $this->_dbHelper->fetchAssoc($res)) {
            $aggregates[] = $aggregate;
        }

        $this->_dbHelper->freeResult($res);
        
        return $aggregates;
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

        // @valuesAggAtIntervalCols sets which temporary table columns are filled by
        // $sql['valuesAggAtInterval'] results. See: Sql/BasicDecimal.php.
        $values = array(':scalarId' => $scalarId, ':start' => $start, ':end' => $end,
                        ':format' => $format, '@aggFuncOuter' => $aggFuncOuter,
                        '@distinctOuter' => $distinctOuter, '@aggFuncInner' => $aggFuncInner,
                        '@distinctInner' => $distinctInner, '@valuesAggAtIntervalCols' => '`grp`, `y2`');
        
        // Use str_replace() to resolve @aggFunc and @distinct macro conflicts.
        $sql = array();
        $sql['valuesAggAtInterval'] = str_replace(array('aggFunc', 'distinct'),
                                                  array('aggFuncInner', 'distinctInner'),
                                                  $this->getSql('valuesAggAtInterval'));
        $sql['valuesNestedAggAtInterval'] = str_replace(array('aggFunc', 'distinct'),
                                                        array('aggFuncOuter', 'distinctOuter'),
                                                        $this->getSql(__FUNCTION__));

        $res = $this->query($sql, $values);

        if (!$res || !$this->_dbHelper->numRows($res)) {
            return false;
        }
        
        $aggregate = $this->_dbHelper->fetchAssoc($res);

        $this->_dbHelper->freeResult($res);

        return $aggregate['y2'];
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

        $values = array(':scalarId' => $scalarId, ':start' => $start, ':end' => $end,
                        '@recurFunc' => $recurFunc, '@aggFunc' => $aggFunc, '@distinct' => $distinct);
        
        $res = $this->query($this->getSql(__FUNCTION__), $values);

        if (!$res || !$this->_dbHelper->numRows($res)) {
            return false;
        }
    
        $aggregates = array();

        while ($row = $this->_dbHelper->fetchAssoc($res)) {
            $aggregates[] = $row;
        }

        $this->_dbHelper->freeResult($res);

        return $aggregates;
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
        $values = array(':scalarId' => $scalarId, ':start' => $start, ':end' => $end);

        $sql = $this->getSql(__FUNCTION__)
             . ($limit ? 'LIMIT ' . intval($limit) : '');
       
        $res = $this->query($sql, $values);

        if (!$res || !$this->_dbHelper->numRows($res)) {
            return false;
        }
    
        $samples = array();

        while ($sample = $this->_dbHelper->fetchAssoc($res)) {
            $samples[] = $sample;
        }

        $this->_dbHelper->freeResult($res);

        return $samples;
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

        // @valuesAtInterval sets which temporary table columns are filled by
        // $sql['valuesAtInterval'] results. See: Sql/BasicDecimal.php.
        $values = array(':scalarId' => $scalarId, ':start' => $start, ':end' => $end,
                        ':format' => $format, '@valuesAtIntervalCols' => '`x`, `y`');
        
        $sql = array();
        $sql['valuesAtInterval'] = $this->getSql('valuesAtInterval')
                                 . 'ORDER BY `s1`.`end` DESC ';
        $sql['changesAtInterval'] = $this->getSql(__FUNCTION__)
                                  . ($limit ? 'LIMIT ' . intval($limit) : '');

        $res = $this->query($sql, $values);
        
        if (!$res || !$this->_dbHelper->numRows($res)) {
            return false;
        }
    
        $samples = array();

        while ($sample = $this->_dbHelper->fetchAssoc($res)) {
            $samples[] = $sample;
        }

        $this->_dbHelper->freeResult($res);
            
        return $samples;
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

        $values = array(':scalarId' => $scalarId, ':start' => $start, ':end' => $end,
                        '@aggFunc' => $aggFunc, '@distinct' => $distinct);
        
        $res = $this->query($this->getSql(__FUNCTION__), $values);

        if (!$res || !$this->_dbHelper->numRows($res)) {
            return false;
        }
    
        $aggregate = $this->_dbHelper->fetchAssoc($res);

        $this->_dbHelper->freeResult($res);

        return $aggregate['y'];
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
        
        // @changesCols sets which temporary table columns are filled by
        // $sql['changes'] results. See: Sql/BasicDecimal.php.
        $values = array(':scalarId' => $scalarId, ':start' => $start, ':end' => $end,
                        ':format' => $format, '@aggFunc' => $aggFunc, '@distinct' => $distinct,
                        '@changesCols' => '`x`, `y`, `y2`, `id`');
        
        $sql = array();
        $sql['changes'] = $this->getSql('changes');
        $sql['changesAggAtInterval'] = $this->getSql(__FUNCTION__);

        $res = $this->query($sql, $values);

        if (!$res || !$this->_dbHelper->numRows($res)) {
            return false;
        }

        $aggregates = array();

        while ($aggregate = $this->_dbHelper->fetchAssoc($res)) {
            $aggregates[] = $aggregate;
        }

        $this->_dbHelper->freeResult($res);

        return $aggregates;
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

        // @changesCols and @changesAggAtIntervalCols set which temporary table
        // columns are filled by their respective $sql statement results.
        // See: Sql/BasicDecimal.php.
        $values = array(':scalarId' => $scalarId, ':start' => $start, ':end' => $end,
                        '@aggFuncOuter' => $aggFuncOuter, '@distinctOuter' => $distinctOuter,
                        '@aggFuncInner' => $aggFuncInner, '@distinctInner' => $distinctInner,
                        ':format' => $format, '@changesCols' => '`x`, `y`, `y2`, `id`',
                        '@changesAggAtIntervalCols' => '`grp`, `y`');
        
        // Use str_replace() to resolve @aggFunc and @distinct macro conflicts.
        $sql = array();
        $sql['changes'] = $this->getSql('changes');
        $sql['changesAggAtInterval'] = str_replace(array('aggFunc', 'distinct'),
                                                  array('aggFuncInner', 'distinctInner'),
                                                  $this->getSql('changesAggAtInterval'));
        $sql['changesNestedAggAtInterval'] = str_replace(array('aggFunc', 'distinct'),
                                                        array('aggFuncOuter', 'distinctOuter'),
                                                        $this->getSql(__FUNCTION__));
        
        $res = $this->query($sql, $values);

        if (!$res || !$this->_dbHelper->numRows($res)) {
            return false;
        }
    
        $aggregate = $this->_dbHelper->fetchAssoc($res);

        $this->_dbHelper->freeResult($res);

        return $aggregate['y'];
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
        
        $values = array(':scalarId' => $scalarId, ':start' => $start, ':end' => $end,
                        '@recurFunc' => $recurFunc, '@aggFunc' => $aggFunc, '@distinct' => $distinct);

        $res = $this->query($this->getSql(__FUNCTION__), $values);

        if (!$res || !$this->_dbHelper->numRows($res)) {
            return false;
        }
    
        $aggregates = array();

        while ($aggregate = $this->_dbHelper->fetchAssoc($res)) {
            $aggregates[] = $aggregate;
        }

        $this->_dbHelper->freeResult($res);

        return $aggregates;
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
        $values = array(':scalarId' => $scalarId, ':start' => $start, ':end' => $end);

        $sql = $this->getSql(__FUNCTION__)
             . 'ORDER BY `y` ' . ($descOrder ? 'DESC' : 'ASC')
             . ($limit ? ' LIMIT ' . intval($limit) : '');
       
        $res = $this->query($sql, $values);

        if (!$res || !$this->_dbHelper->numRows($res)) {
            return false;
        }
    
        $samples = array();

        while ($sample = $this->_dbHelper->fetchAssoc($res)) {
            $samples[] = $sample;
        }

        $this->_dbHelper->freeResult($res);

        return $samples;
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
        
        $values = array(':scalarId' => $scalarId, ':start' => $start, ':end' => $end,
                        '@aggFunc' => $aggFunc, '@distinct' => $distinct);
        
        $sql = $this->getSql(__FUNCTION__)
             . ($limit ? ' LIMIT ' . intval($limit) : '');
       
        $res = $this->query($sql, $values);

        if (!$res || !$this->_dbHelper->numRows($res)) {
            return false;
        }
    
        $samples = array();

        while ($sample = $this->_dbHelper->fetchAssoc($res)) {
            $samples[] = $sample;
        }

        $this->_dbHelper->freeResult($res);

        return $samples;
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
        
        // @movingCols sets which temporary table columns are filled by
        // $sql['moving'] results. See: Sql/BasicDecimal.php.
        $values = array(':scalarId' => $scalarId, ':start' => $start, ':end' => $end,
                        ':format' => $format, '@aggFunc' => $aggFunc, '@distinct' => $distinct,
                        '@movingCols' => '`x`, `y`, `y2`');
        
        $sql = array();
        $sql['moving'] = $this->getSql('moving');
        $sql['movingAtInterval'] = $this->getSql(__FUNCTION__)
                                 . ($limit ? 'LIMIT ' . intval($limit) : '');

        $res = $this->query($sql, $values);
        
        if (!$res || !$this->_dbHelper->numRows($res)) {
            return false;
        }
    
        $samples = array();

        while ($sample = $this->_dbHelper->fetchAssoc($res)) {
            $samples[] = $sample;
        }

        $this->_dbHelper->freeResult($res);
            
        return $samples;
    }
}
