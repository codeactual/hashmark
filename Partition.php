<?php
// vim: fenc=utf-8:ft=php:ai:si:ts=4:sw=4:et:

/**
 * Hashmark_Partition
 *
 * @filesource
 * @link        http://code.google.com/p/hashmark/
 * @link        http://framework.zend.com/manual/en/coding-standard.html
 * @link        http://phpdoc.org/tutorial.php
 * @copyright   Copyright (c) 2008-2009, Code Actual LLC
 * @license     http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package     Hashmark
 * @subpackage  Base
 * @version     $Id: Partition.php 300 2009-02-13 05:51:17Z david $
*/

/**
 * Sample partition management and querying.
 *
 * Defines partitioning scheme and misc. helpers like tableExists().
 *
 * @package     Hashmark
 * @subpackage  Base
 */
class Hashmark_Partition extends Hashmark_Module_DbDependent
{
    /**
     * Return the SHOW CREATE TABLE output for $name.
     *  
     * @param string    $name
     * @return string
     * @throws Exception On query error.
     */
    public function getTableDefinition($name)
    {
        $cacheKey = __FUNCTION__ . $name;

        if (!($output = $this->_cache->get($cacheKey, 'schema'))) {
            $values = array('@name' => $name);

            $sql = "SHOW CREATE TABLE {$this->_dbName}`@name`";

            $res = $this->_dbHelper->query($this->_db, $sql, $values);

            if (!$this->_dbHelper->numRows($res)) {
                return false;
            }

            $row = $this->_dbHelper->fetchRow($res);

            $output = preg_replace('/( AUTO_INCREMENT=\d+)|( COMMENT.\'.*\')/', '', $row[1]);

            $this->_cache->set($cacheKey, $output, 'schema');
        }

        return $output;
    }

    /**
     * Confirm a samples table exists.
     *  
     * @param string    $name   Table name.
     * @return boolean  True if exists.
     * @throws Exception On query error.
     */
    public function tableExists($name)
    {
        $cacheKey = __FUNCTION__ . $name;

        if (!($exists = $this->_cache->get($cacheKey, 'tablelist'))) {
            $dbName = ($this->_dbName ? '"' . $this->getDbName() . '"' : 'DATABASE()');

            $sql = 'SELECT `TABLE_NAME` '
                 . 'FROM `INFORMATION_SCHEMA`.`TABLES` '
                 . 'WHERE `TABLE_NAME` = ? '
                 . "AND `TABLE_SCHEMA` = {$dbName}";

            $res = $this->_dbHelper->query($this->_db, $sql, $name);

            $exists = (1 == $this->_dbHelper->numRows($res));

            $this->_cache->set($cacheKey, $exists, 'tablelist');
        }

        return $exists;
    }
    
    /**
     * Find tables LIKE the given expression.
     *  
     * @param string    $expr
     * @return Array    Table names; otherwise false.
     * @throws Exception On query error.
     */
    public function getTablesLike($expr)
    {
        $cacheKey = __FUNCTION__ . $expr;

        if (!($tables = $this->_cache->get($cacheKey, 'tablelist'))) {
            $dbName = ($this->_dbName ? 'FROM `' . $this->getDbName() . '`' : '');
            $sql = "SHOW TABLES {$dbName} LIKE ?";

            $res = $this->_dbHelper->query($this->_db, $sql, $expr);

            if (!$this->_dbHelper->numRows($res)) {
                return false;
            }

            $tables = array();

            while ($row = $this->_dbHelper->fetchRow($res)) {
                $tables[] = $row[0];
            }

            $this->_dbHelper->freeResult($res);
            
            $this->_cache->set($cacheKey, $tables, 'tablelist');
        }

        return $tables;
    }
    
    /**
     * Drop a samples table.
     *  
     * @param mixed    $name   Table name or Array of them.
     * @return void
     * @throws Exception On query error.
     */
    public function dropTable($name)
    {
        if (!is_array($name)) {
            $name = array($name);
        }

        foreach ($name as $key => $value) {
            $name[$key] = $this->_dbName . '`' . $this->_dbHelper->escape($this->_db, $value, false) . '`';
        }

        $values = array('@list' => implode(',', $name));

        $sql = 'DROP TABLE IF EXISTS @list';

        $this->_dbHelper->query($this->_db, $sql, $values);

        $this->_cache->removeGroup('tablelist');
    }
    
    /**
     * Return the INFORMATON_SCHEMA fields for $name.
     *  
     * @param string    $name
     * @return void
     * @throws Exception On query error.
     */
    public function getTableInfo($name)
    {
        $dbName = ($this->_dbName ? '"' . $this->getDbName() . '"' : 'DATABASE()');

        $sql = 'SELECT * FROM `INFORMATION_SCHEMA`.`TABLES` '
             . 'WHERE `TABLE_NAME` = ? '
             . "AND `TABLE_SCHEMA` = {$dbName}";

        $res = $this->_dbHelper->query($this->_db, $sql, $name);

        if (!$this->_dbHelper->numRows($res)) {
            return false;
        }

        $row = $this->_dbHelper->fetchAssoc($res);
        $this->_dbHelper->freeResult($res);

        return $row;
    }
    
    /**
     * Return all merge table names and comments.
     *
     * @return Array    Each element is assoc. w/ keys 'TABLE_NAME' and
     *                  'TABLE_COMMENT'.
     * @throws Exception On query error.
     */
    public function getAllMergeTables()
    {
        $dbName = ($this->_dbName ? '"' . $this->getDbName() . '"' : 'DATABASE()');

        $sql = 'SELECT `TABLE_NAME`, `TABLE_COMMENT` FROM `INFORMATION_SCHEMA`.`TABLES` '
             . "WHERE SUBSTR(`TABLE_NAME`, 1, 12) = '{$this->_baseConfig['mergetable_prefix']}' "
             . "AND `TABLE_SCHEMA` = {$dbName}";

        $res = $this->_dbHelper->query($this->_db, $sql);

        if (!$this->_dbHelper->numRows($res)) {
            return false;
        }

        $tables = array();

        while ($scalar = $this->_dbHelper->fetchAssoc($res)) {
            $tables[] = $scalar;
        }

        $this->_dbHelper->freeResult($res);

        return $tables;
    }

    /**
     * Return the SHOW CREATE TABLE output for `samples_<$type>`.
     *  
     * @param string    $type   See Hashmark_Core::getValidScalarTypes() for options.
     * @return string
     * @throws Exception On query error; if $type invalid.
     */
    public function getPartitionDefinition($type)
    {
        if (!in_array($type, $this->getModule('Core')->getValidScalarTypes())) {
            throw new Exception('Cannot create table of unrecognized type: ' . $type, HASHMARK_EXCEPTION_VALIDATION);
        }
       
        return $this->getTableDefinition('samples_' . $type);
    }
    
    /**
     * Confirm a MERGE table appears operational.
     *  
     * @param string    $name   Table name.
     * @return boolean  True if operational; null if table not found.
     * @throws Exception On query error.
     */
    public function checkMergeTableStatus($name)
    {
        $status = $this->getTableInfo($name);
        
        if ($status) {
            return ($status['ENGINE'] == 'MRG_MyISAM');
        }

        return null;
    }
    
    /**
     * Return the merge table's last-modified UTC timestamp.
     *  
     * @param string    $name   Table name.
     * @return string   DATETIME string.
     * @throws Exception On query error.
     */
    public function getMergeTableCreatedDate($name)
    {
        $status = $this->getTableInfo($name);

        if ($status) {
            return $status['TABLE_COMMENT'];
        }

        return false;
    }
    
    /**
     * Build a merge table name based on scalar ID and range.
     *  
     * @param int       $scalarId
     * @param string    $start              DATETIME string.
     * @param string    $end                DATETIME string.
     * @return string   New merge table name.
     */
    public function getMergeTableName($scalarId, $start, $end)
    {
        // $start/$end ex. '20081123'
        $start = preg_replace('/^(\d{4})-(\d{2})-(\d{2}).*$/', '\1\2\3', $start);
        $end = preg_replace('/^(\d{4})-(\d{2})-(\d{2}).*$/', '\1\2\3', $end);
        
        return "{$this->_baseConfig['mergetable_prefix']}{$scalarId}_{$start}_{$end}";
    }
    
    /**
     * Find the narrowest merge table which encompasses a given range.
     *
     * @param int       $scalarId
     * @param string    $start  DATETIME string.
     * @param string    $end    DATETIME string.
     * @return string   Table name; otherwise false.
     * @throws Exception On query error.
     */
    public function getMergeTableWithRange($scalarId, $start, $end)
    {
        $start = date_parse($start);
        if (!$start || $start['warning_count'] || $start['error_count']) {
            return false;
        }

        $end = date_parse($end);
        if (!$end || $end['warning_count'] || $end['error_count']) {
            return false;
        }

        foreach (array('month', 'day') as $field) {
            $start[$field] = $start[$field] < 10 ? '0' . $start[$field] : $start[$field];
            $end[$field] = $end[$field] < 10 ? '0' . $end[$field] : $end[$field];
        }

        // Reduce LIKE scope if possible.
        $approxDateId = '';
        if ($start['year'] == $end['year']) {
            $approxDateId .= $start['year'];
        }
        
        $candidates = $this->getTablesLike("{$this->_baseConfig['mergetable_prefix']}{$scalarId}_{$approxDateId}%_{$approxDateId}%");
        if (!$candidates) {
            return false;
        }

        $startDate = $start['year'] . $start['month'] . $start['day'];
        $endDate = $end['year'] . $end['month'] . $end['day'];

        // Keys = table names, values = table's date coverage magnitude (for sorting)
        $tables = array();

        foreach ($candidates as $table) {
            $matches = array();
            if (preg_match('/_(\d{8})_(\d{8})$/', $table, $matches) && count($matches) == 3) {
                list(, $start, $end) = $matches;
                if ($start <= $startDate && $end >= $endDate) {
                    // Union's scope later used in final asort().
                    $tables[$table] = $end - $start;
                }
            }
        }

        if (!$tables) {
            return false;
        }

        // Sort by coverage and return narrowest partition.
        asort($tables);
        $tables = array_keys($tables);
        return $tables[0];
    }
    
    /**
     * Find samples tables from an inclusive date range.
     *  
     * @param int       $scalarId
     * @param string    $start  DATETIME string.
     * @param string    $end    DATETIME string.
     * @return Array    Table names sorted by date DESC; otherwise false.
     * @throws Exception On query error.
     */
    public function getTablesInRange($scalarId, $start, $end)
    {
        $start = date_parse($start);
        if (!$start || $start['warning_count'] || $start['error_count']) {
            return false;
        }

        $end = date_parse($end);
        if (!$end || $end['warning_count'] || $end['error_count']) {
            return false;
        }

        foreach (array('month', 'day') as $field) {
            $start[$field] = $start[$field] < 10 ? '0' . $start[$field] : $start[$field];
            $end[$field] = $end[$field] < 10 ? '0' . $end[$field] : $end[$field];
        }

        // Reduce LIKE scope if possible.
        $approxDateId = '';
        foreach (array('year', 'month', 'day') as $field) {
            if ($start[$field] == $end[$field]) {
                $approxDateId .= $start[$field];
            } else {
                $approxDateId .= '%';
                break;
            }
        }
        
        $candidates = $this->getTablesLike("samples_{$scalarId}_{$approxDateId}");
        if (!$candidates) {
            return false;
        }

        $startMonth = $start['year'] . $start['month'];
        $startDate = $startMonth . $start['day'];

        $endMonth = $end['year'] . $end['month'];
        $endDate = $endMonth . $end['day'];

        $tablesInRange = array();
        foreach ($candidates as $table) {
            // Break apart the table name so we can use monthy and daily tables
            // together, ex. if we changed partitioning over time.
            $yyyymmdd = str_replace("samples_{$scalarId}_", '', $table);
            $yyyymm = substr($yyyymmdd, 0, 6);
            $isMonthlyTable = (substr($yyyymmdd, -2) == '00');

            if ($isMonthlyTable) {
                $candidateInRange = ($yyyymm >= $startMonth && $yyyymm <= $endMonth);
            } else {
                $candidateInRange = ($yyyymmdd >= $startDate && $yyyymmdd <= $endDate);
            }

            if ($candidateInRange) {
                $tablesInRange[] = $table;
            }
        }

        // First tables in merge union are also scanned first.
        rsort($tablesInRange);

        return $tablesInRange;
    }
        
    /**
     * Find a table which encompasses a given range. Create a merge table if needed.
     *
     *      -   Wrapper for getTablesInRange() and getMergeTableWithRange().
     *
     * @param int       $scalarId
     * @param string    $start  DATETIME string.
     * @param string    $end    DATETIME string.
     * @return string   Table name; otherwise false.
     * @throws Exception On query error.
     */
    public function getAnyTableWithRange($scalarId, $start, $end)
    {
        $tablesInRange = $this->getTablesInRange($scalarId, $start, $end);
        if (!$tablesInRange) {
            return false;
        }

        $tableCount = count($tablesInRange);

        // Exact match whose scope equals or exceeds $start/$end.
        if (1 == $tableCount) {
            return $tablesInRange[0];
        }

        // Resort to a union with partial coverage.
        // Ex. merge table name: samples_mrg_4501_20090622_20091022
        $start = preg_replace('/^(\d{4})-(\d{2})-(\d{2}).*$/', '\1\2\3', $start);
        $end = preg_replace('/^(\d{4})-(\d{2})-(\d{2}).*$/', '\1\2\3', $end);
        $mergeTableName = "{$this->_baseConfig['mergetable_prefix']}{$scalarId}_{$start}_{$end}";

        if ($this->tableExists($mergeTableName)) {
            return $mergeTableName;
        }

        return $this->createMergeTable($scalarId, $start, $end, $tablesInRange);
    }

    /**
     * Get the name of the samples table that should be used now.
     *
     * @param int       $scalarId
     * @param mixed     $time       UNIX timestamp or DATETIME string;
     *                              if unspecified, current timestamp used.
     * @param string    $interval   Interval code.
     * @return string   Full table name, not just unique part.
     * @see Config/Partition.php for default value and options.
     */
    public function getIntervalTableName($scalarId, $time = null, $interval = '')
    {
        if (is_null($time)) {
            $time = time();
        } else if (is_string($time)) {
            $time = strtotime($time . ' UTC');
        }

        if (!$interval) {
            $interval = $this->_baseConfig['interval'];
        }

        switch ($interval) {
            case 'd':
                $timeId = gmdate('Ymd', $time);         // Daily partitions.
                break;
            case 'm':
                $timeId = gmdate('Ym', $time) . '00';   // Monthly parttions.
                break;
            default:
                return false;
        }

        return "samples_{$scalarId}_{$timeId}";
    }
    
    /**
     * Create a samples table.
     *  
     * @param int       $scalarId
     * @param string    $name       Table name.
     * @param string    $type       See Hashmark_Core::getValidScalarTypes() for options.
     * @return void
     * @throws Exception On query error; if $type invalid.
     */
    public function createTable($scalarId, $name, $type)
    {
        // Copy the table definition from the partition model, ex. `samples_decimal`.
        $definition = $this->getPartitionDefinition($type);
        $definition = preg_replace('/\n|CREATE TABLE `samples_' . $type . '`/', '', $definition);
        
        // Seed the new partition's AUTO_INCREMENT. Allows us to avoid checking
        // the scalar's sample count for every new sample row.
        $sampleCount = $this->getModule('Core')->getScalarSampleCount($scalarId);

        $sql = "CREATE TABLE IF NOT EXISTS {$this->_dbName}`{$name}` {$definition} AUTO_INCREMENT=" . max(1, $sampleCount);

        $this->_dbHelper->query($this->_db, $sql);
        
        $this->_cache->removeGroup('tablelist');
    }

    /**
     * Create a merge table of samples partitions.
     *  
     * @param int       $scalarId
     * @param string    $start              DATETIME string.
     * @param string    $end                DATETIME string.
     * @param Array     $unionTableNames    Table names.
     * @param string    $comment            Optional merge table COMMENT value.
     *                                      Defaults to current DATETIME.
     * @return string   New merge table name.
     * @throws Exception On query error.
     */
    public function createMergeTable($scalarId, $start, $end, $unionTableNames, $comment = '')
    {
        $mergeTableName = $this->getMergeTableName($scalarId, $start, $end);

        $type = $this->getModule('Core')->getScalarType($scalarId);

        $definition = $this->getPartitionDefinition($type);
        $definition = preg_replace('/\n|CREATE TABLE `samples_' . $type . '`|(ENGINE.*$)/', '', $definition);

        // Default DATETIME comments are used in Cron/Tool/gcMergeTables.php.
        if (!$comment) {
            $comment = gmdate('Y-m-d H:i:s');
        }

        $union = implode(',', $unionTableNames);

        $sql = "CREATE TABLE IF NOT EXISTS {$this->_dbName}`{$mergeTableName}` {$definition} "
             . "ENGINE=MERGE UNION=({$union}) INSERT_METHOD=NO COMMENT='{$comment}'";

        $this->_dbHelper->query($this->_db, $sql);
        
        $this->_cache->removeGroup('tablelist');
        
        return $mergeTableName;
    }
    
    /**
     * Partition-aware wrapper for Hashmark_DbHelper_*::query().
     *
     *      -   Only queries most recent partition.
     *
     * @param int       $scalarId
     * @param string    $template
     * @param mixed     ...         String or integer argument for every $template macro.
     * @return mixed    Result object/resource.
     * @throws Exception On query error.
     */
    public function query($scalarId, $template)
    {
        $tableName = $this->getIntervalTableName($scalarId);

        if (!$this->tableExists($tableName)) {
            $type = $this->getModule('Core')->getScalarType($scalarId);
            $this->createTable($scalarId, $tableName, $type);
        }
        
        // Reorganize $args for final call into:
        // query($this->_db, SQL w/expanded partition macro, ... list of template variables)
        $args = func_get_args();
        $args = array_slice($args, 2);
        array_unshift($args, $this->_db, str_replace('~samples', $this->_dbName . '`' . $tableName . '`', $template));
        
        return call_user_func_array(array($this->_dbHelper, 'query'), $args);
    }
    
    /**
     * Partition-aware wrapper for Hashmark_DbHelper_*::query().
     *
     *      -   Only queries one partition identified by $date.
     *
     * @param int       $scalarId
     * @param string    $template
     * @param string    $date UNIX timestamp or DATETIME string.
     * @param mixed     ...         String or integer argument for every $template macro.
     * @return mixed    Result object/resource.
     * @throws Exception On query error.
     */
    public function queryAtDate($scalarId, $template, $date)
    {
        $tableName = $this->getIntervalTableName($scalarId, $date);

        if (!$this->tableExists($tableName)) {
            $type = $this->getModule('Core')->getScalarType($scalarId);
            $this->createTable($scalarId, $tableName, $type);
        }

        // Reorganize $args for final call into:
        // query($this->_db, SQL w/expanded partition macro, ... list of template variables)
        $args = func_get_args();
        $args = array_slice($args, 3);
        array_unshift($args, $this->_db, str_replace('~samples', $this->_dbName . '`' . $tableName . '`', $template));
        
        return call_user_func_array(array($this->_dbHelper, 'query'), $args);
    }
    
    /**
     * Partition-aware wrapper for Hashmark_DbHelper_*::query().
     *
     *      -   Queries a merge table satisfying the given range.
     *
     * @param int       $scalarId
     * @param string    $start          DATETIME string.
     * @param string    $end            DATETIME string.
     * @param string    $template
     * @param mixed     ...         String or integer argument for every $template macro.
     * @return mixed    Query result object/resource; false if no partition tables
     *                  exist in the date rannge defined in $values.
     * @throws Exception On query error.
     */
    public function queryInRange($scalarId, $start, $end, $template)
    {
        $tableName = $this->getAnyTableWithRange($scalarId, $start, $end);
        if (!$tableName) {
            $args = func_get_args();
            return false;
        }
        
        // Reorganize $args for final call into:
        // query($this->_db, SQL w/expanded partition macro, ... list of template variables)
        $args = func_get_args();
        $args = array_slice($args, 4);
        array_unshift($args, $this->_db, str_replace('~samples', $this->_dbName . '`' . $tableName . '`', $template));
        
        return call_user_func_array(array($this->_dbHelper, 'query'), $args);
    }

    /**
     * Copy a table's definition and data into a temporary table.
     *
     * @param string    $src    Source table name.
     * @return string   Destination temporary table name.
     */
    public function copyToTemp($src)
    {
        $name = 'samples_tmpcpy_' . Hashmark_Util::randomSha1();

        $sql = "CREATE TEMPORARY TABLE IF NOT EXISTS {$this->_dbName}`{$name}` LIKE {$this->_dbName}`{$src}`";
        $this->_dbHelper->query($this->_db, $sql);

        $sql = "INSERT INTO {$this->_dbName}`{$name}` SELECT * FROM {$this->_dbName}`{$src}`";
        $this->_dbHelper->query($this->_db, $sql);

        return $name;
    }

    /**
     * Create a temporary table from a SELECT statement and existing definition.
     *
     * @param string    $src        Name of table providing a definition.
     * @param string    $columns    Columns populated by $sql, ex.: `x`, `y`
     * @param string    $selectSql  Pre-escaped statement.
     * @param Array     $values     DbHelper::query() compatible named macros.
     *
     *                              If $selectSql uses the ~samples macro, these
     *                              values are required: :scalarId, :start, :end
     *      
     * @return string   Destination temporary table name.
     */
    public function createTempFromQuery($src, $columns, $selectSql, $values)
    {
        $name = 'samples_tmp_' . Hashmark_Util::randomSha1();

        $createSql = "CREATE TEMPORARY TABLE IF NOT EXISTS {$this->_dbName}`{$name}` LIKE `{$src}`";
        $this->_dbHelper->query($this->_db, $createSql);

        $insertSql = "INSERT INTO {$this->_dbName}`{$name}` ({$columns}) {$selectSql}";
        if (false === strpos($insertSql, '~samples')) {
            $this->_dbHelper->query($this->_db, $insertSql, $values);
        } else {
            $this->queryInRange($values[':scalarId'], $values[':start'], $values[':end'], $insertSql, $values);
        }

        return $name;
    }
}
