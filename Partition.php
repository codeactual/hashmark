<?php
// vim: fenc=utf-8:ft=php:ai:si:ts=4:sw=4:et:

/**
 * Hashmark_Partition
 *
 * @filesource
 * @link        http://code.google.com/p/hashmark/
 * @link        http://framework.zend.com/manual/en/coding-standard.html
 * @link        http://phpdoc.org/tutorial.php
 * @copyright   Copyright (c) 2008-2011 David Smith
 * @license     http://www.opensource.org/licenses/mit-license.php MIT License
 * @package     Hashmark
 * @subpackage  Base
 * @version     $Id$
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

        if (!($output = $this->_cache->load($cacheKey, 'schema'))) {
            $name = $this->_db->quoteIdentifier($name);

            $sql = "SHOW CREATE TABLE {$this->_dbName}{$name}";

            $rows = $this->_db->fetchAll($sql);

            if (!$rows) {
                return false;
            }

            $output = preg_replace('/( AUTO_INCREMENT=\d+)|( COMMENT.\'.*\')/', '', $rows[0]['Create Table']);

            $this->_cache->save($output, $cacheKey, 'schema');
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

        if (!($exists = $this->_cache->load($cacheKey, 'tablelist'))) {
            $dbName = ($this->_dbName ? '"' . $this->getDbName() . '"' : 'DATABASE()');

            $sql = 'SELECT `TABLE_NAME` '
                 . 'FROM `INFORMATION_SCHEMA`.`TABLES` '
                 . 'WHERE `TABLE_NAME` = ? '
                 . "AND `TABLE_SCHEMA` = {$dbName}";

            $exists = (1 == count($this->_db->fetchAll($sql, array($name))));

            $this->_cache->save($exists, $cacheKey, 'tablelist');
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
        $cacheKey = __FUNCTION__
                  . str_replace('%', '___', $expr);

        if (!($tables = $this->_cache->load($cacheKey, 'tablelist'))) {
            $dbName = ($this->_dbName ? '"' . $this->getDbName() . '"' : 'DATABASE()');
            $expr = $this->_db->quote($expr);

            $sql = 'SELECT `TABLE_NAME` '
                 . 'FROM `INFORMATION_SCHEMA`.`TABLES` '
                 . "WHERE `TABLE_NAME` LIKE {$expr} "
                 . "AND `TABLE_SCHEMA` = {$dbName}";

            $rows = $this->_db->fetchAll($sql);

            if (!$rows) {
                return false;
            }

            $tables = array();
            foreach ($rows as $row) {
                $tables[] = $row['TABLE_NAME'];
            }

            $this->_cache->save($tables, $cacheKey, 'tablelist');
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
            $name[$key] = $this->_dbName . $this->_db->quoteIdentifier($value);
        }

        $sql = 'DROP TABLE IF EXISTS ' . implode(',', $name);

        $this->_db->query($sql);
        
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

        $rows = $this->_db->fetchAll($sql, array($name));

        if (!$rows) {
            return false;
        }

        return $rows[0];
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

        $rows = $this->_db->fetchAll($sql);

        if (!$rows) {
            return false;
        }

        return $rows;
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
            return ($status['ENGINE'] == 'MRG_MYISAM');
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

        // Add leading zeroes.
        foreach (array('month', 'day') as $field) {
            $start[$field] = $start[$field] < 10 ? '0' . $start[$field] : $start[$field];
            $end[$field] = $end[$field] < 10 ? '0' . $end[$field] : $end[$field];
        }

        
        // Reduce LIKE scope if possible.
        $datePatternFields = array('year', 'month');
        if ('d' == $this->_baseConfig['interval']) {
            $datePatternFields[] = 'day';
        }
        $approxDateId = '';
        foreach ($datePatternFields as $field) {
            if ($start[$field] == $end[$field]) {
                $approxDateId .= $start[$field];
            } else {
                $approxDateId .= '%';
                break;
            }
        }
        // Add a day wildcard (if needed) when patterns narrowed to month.
        if ('%' != substr($approxDateId, -1) && 'month' == $field) {
            $approxDateId .= '%';
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
     *                              if null/default, current timestamp used.
     * @param string    $interval   Interval code.
     * @return string   Full table name, not just unique part.
     * @see Config/Partition.php for default value and options.
     */
    public function getIntervalTableName($scalarId, $time = null, $interval = '')
    {
        if (is_null($time)) {
            $time = time();
        } else if (is_string($time)) {
            $time = strtotime($time);
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

        $sql = "CREATE TABLE IF NOT EXISTS {$this->_dbName}`{$name}` "
             . "{$definition} AUTO_INCREMENT=" . max(1, $sampleCount);

        $this->_db->query($sql);
        
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

        // Default DATETIME comments are used in Cron/gcMergeTables.php.
        if (!$comment) {
            $comment = gmdate('Y-m-d H:i:s');
        }

        $union = implode(',', $unionTableNames);

        $sql = "CREATE TABLE IF NOT EXISTS {$this->_dbName}`{$mergeTableName}` {$definition} "
             . "ENGINE=MERGE UNION=({$union}) INSERT_METHOD=NO COMMENT='{$comment}'";

        $this->_db->query($sql);
        
        $this->_cache->removeGroup('tablelist');
        
        return $mergeTableName;
    }
    
    /**
     * Partition-aware wrapper for Hashmark_DbHelper_*::query().
     *
     *      -   Only queries most recent partition.
     *
     * @param int       $scalarId
     * @param string    $sql
     * @param Array     $bind       Values bound to statement placeholders.
     * @return mixed    Result object/resource.
     * @throws Exception On query error.
     */
    public function queryCurrent($scalarId, $sql, $bind = array())
    {
        return $this->queryAtDate($scalarId, $sql, null, $bind);
    }
    
    /**
     * Partition-aware wrapper for Hashmark_DbHelper_*::query().
     *
     * @param int       $scalarId
     * @param string    $sql
     * @param string    $date       UNIX timestamp or DATETIME string;
     *                              if null, current timestamp used.
     * @param Array     $bind       Values bound to statement placeholders.
     * @return mixed    Result object/resource.
     * @throws Exception On query error.
     */
    public function queryAtDate($scalarId, $sql, $date, $bind = array())
    {
        $tableName = $this->getIntervalTableName($scalarId, $date);
        $isWrite = preg_match('/^\s*insert|replace/i', $sql);
        $tableExists = $this->tableExists($tableName);

        if (!$tableExists && $isWrite) {
            $type = $this->getModule('Core')->getScalarType($scalarId);
            $this->createTable($scalarId, $tableName, $type);
            $tableExists = true;
        }

        if (!$tableExists) {
            return false;
        }

        $sql = str_replace('~samples', $this->_dbName . '`' . $tableName . '`', $sql);
        
        return $this->_db->query($sql, $bind);
    }
    
    /**
     * Partition-aware wrapper for Hashmark_DbHelper_*::query().
     *
     *      -   Queries a merge table satisfying the given range.
     *
     * @param int       $scalarId
     * @param string    $start      DATETIME string.
     * @param string    $end        DATETIME string.
     * @param string    $sql
     * @param Array     $bind       Values bound to statement placeholders.
     * @return Zend_Db_Statement_*      New instance.
     * @throws Exception On query error.
     */
    public function queryInRange($scalarId, $start, $end, $sql, $bind = array())
    {
        $tableName = $this->getAnyTableWithRange($scalarId, $start, $end);
        if (!$tableName) {
            return false;
        }

        $sql = str_replace('~samples', $this->_dbName . '`' . $tableName . '`', $sql);
        
        return $this->_db->query($sql, $bind);
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
        $this->_db->query($sql);

        $sql = "INSERT INTO {$this->_dbName}`{$name}` SELECT * FROM {$this->_dbName}`{$src}`";
        $this->_db->query($sql);

        return $name;
    }

    /**
     * Create a temporary table from a SELECT statement and existing definition.
     *
     * @param string    $src        Name of table providing a definition.
     * @param string    $columns    Columns populated by $sql, ex.: `x`, `y`
     * @param string    $selectSql  Pre-escaped statement.
     * @param Array     $values     Hashmark_Module_DbDependent::expandSql() compatible macros.
     *
     *                              If $selectSql uses the ~samples macro, these
     *                              values are required: :scalarId, :start, :end
     * @return string   Destination temporary table name.
     */
    public function createTempFromQuery($src, $columns, $selectSql, $values = array(), $scalarId = '', $start = '', $end = '')
    {
        $name = 'samples_tmp_' . Hashmark_Util::randomSha1();

        $createSql = "CREATE TEMPORARY TABLE IF NOT EXISTS {$this->_dbName}`{$name}` LIKE `{$src}`";
        $this->_db->query($createSql);

        $insertSql = "INSERT INTO {$this->_dbName}`{$name}` ({$columns}) {$selectSql}";
        if (false === strpos($insertSql, '~samples')) {
            $this->_db->query($insertSql, $values);
        } else {
            $this->queryInRange($scalarId, $start, $end, $insertSql, $values);
        }

        return $name;
    }
    /**
     * Add new row to `samples` and update `scalars` statistics.
     *
     * @param int       $scalarId
     * @param string    $value
     * @param mixed     $end    UNIX timestamp or DATETIME string.
     * @return boolean  True on success.
     * @throws Exception On query error.
     */
    public function createSample($scalarId, $value, $end)
    {
        $end = Hashmark_Util::toDatetime($end);

        // `sample_count` seeds AUTO_INCREMENT `id` values in sample partitions
        $sql = "UPDATE {$this->_dbName}`scalars` "
             . 'SET `value` = ?, '
             . '`last_agent_change` = ?, '
             . '`sample_count` = `sample_count` + 1 '
             . 'WHERE `id` = ?';

        $this->_db->query($sql, array($value, $end, $scalarId));
        
        $sql = 'INSERT INTO ~samples '
             . '(`value`, `end`) '
             . 'VALUES (?, ?)';
        
        // queryAtDate() instead of queryCurrent() so unit tests can
        // create backdated samples.
        $bind = array($value, $end);
        $stmt = $this->queryAtDate($scalarId, $sql, $end, $bind);

        return (1 == $stmt->rowCount());
    }

    /**
     * Return all fields from a scalar's latest sample in the current partition.
     *
     * @param int       $scalarId
     * @return Array    Assoc. of fields; otherwise false.
     * @throws Exception On query error.
     */
    public function getLatestSample($scalarId)
    {
        $sql = 'SELECT * FROM ~samples ORDER BY `id` DESC LIMIT 1';

        $stmt = $this->queryCurrent($scalarId, $sql);

        $rows = $stmt->fetchAll();
        if (!$rows) {
            return false;
        }

        return $rows[0];
    }
}
