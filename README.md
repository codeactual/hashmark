# hashmark

hashmark is a MySQL [time-series](http://en.wikipedia.org/wiki/Time_series) database and PHP library for data point insertion and analytic queries.

## Features

* Numeric and string data types.
* PHP client library for collecting data points in preexisting apps.
* Custom scripts for analysis and periodic data point collection.
* SQL macros allowing queries to reference intermediate results from prior statements.
* Configurable date-based partitioning.
* Cache and database adapters provided by bundled Zend Framework 1.x components.
* High unit test coverage.

## Analytics

### Support

* MySQL aggregate functions: `AVG`, `SUM`, `COUNT`, `MAX`, `MIN`, `STDDEV_POP`, `STDDEV_SAMP`, `VAR_POP`, `VAR_SAMP`
* MySQL aggregate functions eligible for DISTINCT selection: `AVG`,`'SUM`, `COUNT`, `MAX`, `MIN`
* Time intervals for aggregates: hour, day, week, month, year
* MySQL time functions for aggregates of recurrence groups (e.g. "1st of the month"): `HOUR`, `DAYOFMONTH`, `DAYOFYEAR`, `MONTH`

### Methods

<code>[multiQuery](https://github.com/codeactual/hashmark/blob/90bcc5083d2c326b167392b8fd8427e36803fc92/Analyst/BasicDecimal.php#205)($scalarId, $start, $end, $stmts)</code>

> Perform multiple queries using macros to reference prior intermediate result sets. Internally supports many of the functions below.

<code>[values](https://github.com/codeactual/hashmark/blob/90bcc5083d2c326b167392b8fd8427e36803fc92/Analyst/BasicDecimal.php#351)($scalarId, $limit, $start, $end)</code>

> Return samples within a date range.

<code>[valuesAtInterval](https://github.com/codeactual/hashmark/blob/90bcc5083d2c326b167392b8fd8427e36803fc92/Analyst/BasicDecimal.php#379)($scalarId, $limit, $start, $end, $interval)</code>

> Return the most recent sample from each interval within a date range.

<code>[valuesAgg](https://github.com/codeactual/hashmark/blob/90bcc5083d2c326b167392b8fd8427e36803fc92/Analyst/BasicDecimal.php#415)($scalarId, $start, $end, $aggFunc, $distinct)</code>

> E.g. return **"average value between date X and Y" or **"volume of distinct values between date X and Y."**

<code>[valuesAggAtInterval](https://github.com/codeactual/hashmark/blob/90bcc5083d2c326b167392b8fd8427e36803fc92/Analyst/BasicDecimal.php#453)($scalarId, $start, $end, $interval, $aggFunc, $distinct)</code>

> Similar to `valuesAgg` except that results are grouped into a given interval, e.g.  **"average weekly value between date X and Y."**

<code>[valuesNestedAggAtInterval](https://github.com/codeactual/hashmark/blob/90bcc5083d2c326b167392b8fd8427e36803fc92/Analyst/BasicDecimal.php#499)($scalarId, $start, $end, $interval, $aggFuncOuter, $distinctOuter, $aggFuncInner, $distinctInner)</code>

> Aggregate values returned by `valuesAggAtInterval`, e.g. **"average weekly high between date X and Y."**

<code>[valuesAggAtRecurrence](https://github.com/codeactual/hashmark/blob/90bcc5083d2c326b167392b8fd8427e36803fc92/Analyst/BasicDecimal.php#557)($scalarId, $start, $end, $recurFunc, $aggFunc, $distinct)</code>

> E.g. **"peak value in the 8-9am hour between date X and Y."**

<code>[changes](https://github.com/codeactual/hashmark/blob/90bcc5083d2c326b167392b8fd8427e36803fc92/Analyst/BasicDecimal.php#601)($scalarId, $limit, $start, $end)</code>

> Return from a date range each sample's date, value, and change in value from the prior sample.

<code>[changesAtInterval](https://github.com/codeactual/hashmark/blob/90bcc5083d2c326b167392b8fd8427e36803fc92/Analyst/BasicDecimal.php#628)($scalarId, $limit, $start, $end, $interval)</code>

> Similar to `changes` except that `valuesAtInterval` provides the source data, e.g. **"weekly value and its change (week-over-week) between date X and Y."**

<code>[changesAgg](https://github.com/codeactual/hashmark/blob/90bcc5083d2c326b167392b8fd8427e36803fc92/Analyst/BasicDecimal.php#674)($scalarId, $start, $end, $aggFunc, $distinct)</code>

> E.g. **"peak value change between date X and Y."**

<code>[changesAggAtInterval](https://github.com/codeactual/hashmark/blob/90bcc5083d2c326b167392b8fd8427e36803fc92/Analyst/BasicDecimal.php#712)($scalarId, $start, $end, $interval, $aggFunc, $distinct)</code>

> Similar to `changesAgg` except that `changes` provides the source data, e.g. **"weekly peak value change (week-over-week) between date X and Y."**

<code>[changesNestedAggAtInterval](https://github.com/codeactual/hashmark/blob/90bcc5083d2c326b167392b8fd8427e36803fc92/Analyst/BasicDecimal.php#766)($scalarId, $start, $end, $interval, $aggFuncOuter, $distinctOuter, $aggFuncInner, $distinctInner)</code>

> Aggregate values returned by `changesAggAtInterval`, e.g. **"average of weekly peak value changes (week-over-week) between date X and Y."**

<code>[changesAggAtRecurrence](https://github.com/codeactual/hashmark/blob/90bcc5083d2c326b167392b8fd8427e36803fc92/Analyst/BasicDecimal.php#831)($scalarId, $start, $end, $recurFunc, $aggFunc, $distinct)</code>

> E.g. **"peak value change on Black Friday between year X and year Y."**

<code>[frequency](https://github.com/codeactual/hashmark/blob/90bcc5083d2c326b167392b8fd8427e36803fc92/Analyst/BasicDecimal.php#874)($scalarId, $limit, $start, $end, $descOrder)</code>

> Return unique values and their frequency between date X and Y.

<code>[moving](https://github.com/codeactual/hashmark/blob/90bcc5083d2c326b167392b8fd8427e36803fc92/Analyst/BasicDecimal.php#903)($scalarId, $limit, $start, $end, $aggFunc, $distinct)</code>

> Return from a date range each sample's date, value, and the aggregate value at sample-time. E.g. **"values and their moving averages between date X and Y."**

<code>[movingAtInterval](https://github.com/codeactual/hashmark/blob/90bcc5083d2c326b167392b8fd8427e36803fc92/Analyst/BasicDecimal.php#946)($scalarId, $limit, $start, $end, $interval, $aggFunc, $distinct)</code>

> Similar to `valuesAtInterval` except that `moving` provides the data source, e.g. **"the last value and its moving average from each week between date X and Y."**

## Example Code

### Quick Background

Main database tables:

* <code>[scalars](https://github.com/codeactual/hashmark/blob/b24734f75552189b82611cd927e745ebe70ef4b8/Sql/Schema/hashmark.sql#L181)</code>: Metadata and current value of a named string or number, e.g. "featureX:optOut".
* <code>[samples_decimal](https://github.com/codeactual/hashmark/blob/b24734f75552189b82611cd927e745ebe70ef4b8/Sql/Schema/hashmark.sql#L147)</code>: Historical values of a numeric data points in `scalars`.
* <code>[samples_string](https://github.com/codeactual/hashmark/blob/b24734f75552189b82611cd927e745ebe70ef4b8/Sql/Schema/hashmark.sql#L131)</code>: Historical values of a string data points in `scalars`.

### Client

<code>[Hashmark_Client](https://github.com/codeactual/hashmark/blob/b24734f75552189b82611cd927e745ebe70ef4b8/Client.php#L23)</code> supplies methods for updating a current value (in `scalars`) and adding a historical sample (in `samples_decimal` or `samples_string`).

* <code>[incr](https://github.com/codeactual/hashmark/blob/b24734f75552189b82611cd927e745ebe70ef4b8/Client.php#L126)</code>($name, $amount = 1, $newSample = false)
* <code>[decr](https://github.com/codeactual/hashmark/blob/b24734f75552189b82611cd927e745ebe70ef4b8/Client.php#L219)</code>($name, $amount = 1, $newSample = false)
* <code>[set](https://github.com/codeactual/hashmark/blob/b24734f75552189b82611cd927e745ebe70ef4b8/Client.php#L42)</code>($name, $amount, $newSample = false)
* <code>[get](https://github.com/codeactual/hashmark/blob/b24734f75552189b82611cd927e745ebe70ef4b8/Client.php#L90)</code>($name)

``` php
<?php
if ($userOptedOutOfFeatureX) {
 $client->incr('featureX:optOut', 1, true);
}
```

To enable drop-in client calls to work without any prior setup, e.g. if "featureX:optOut" above did not yet exist, use `$client->createScalarIfNotExists(true)`.

### Agent

Each script is just a class that implements the small <code>[Hashmark_Agent](https://github.com/codeactual/hashmark/blob/b24734f75552189b82611cd927e745ebe70ef4b8/Agent.php#L21)</code> interface.

The [Agent/StockPrice.php](https://github.com/codeactual/hashmark/blob/b24734f75552189b82611cd927e745ebe70ef4b8/Agent/StockPrice.php#L23) demo fetches AAPL's price from Google Finance and creates a historical data point.

<code>[Cron/runAgents.php](https://github.com/codeactual/hashmark/blob/b24734f75552189b82611cd927e745ebe70ef4b8/Cron/runAgents.php)</code> normally runs each agent on a configured schedule, but a manual run might look like:

``` php
<?php
$agent = Hashmark::getModule('Agent', 'StockPrice');
$price = $agent->run($scalarId);

$partition = Hashmark::getModule(Partition, '', $db);
$partition->createSample($scalarId, $price, time());
```

### Create a Scalar

``` php
<?php
$core = Hashmark::getModule('Core', '', $db);

$scalarFields = array();
$scalarFields['name'] = 'featureX:optOut';
$scalarFields['type'] = 'decimal';
$scalarFields['value'] = 0;  // Initial value.
$scalarFields['description'] = 'Opt-out requests for featureX.';

$scalarId = $core->createScalar($scalarFields);

$savedScalarFields = $core->getScalarById($scalarId);
$savedScalarFields = $core->getScalarByName('featureX:optOut');
```
### Create a Category

``` php
<?php
$categoryId = $core->createCategory('Feature Trackers');
if (!$core->scalarHasCategory($scalarId, $categoryId)) {
   $core->addScalarCategory($scalarId, $categoryId);
}
```
### Create a Milestone

``` php
<?php
$milestoneId = $core->createMilestone('featureX initial release');
$core->setMilestoneCategory($milestoneId, $releaseCategoryId);
```
### Query

``` php
<?php
$analyst = Hashmark::getModule('Analyst', 'BasicDecimal', $db);

$sampleDateMin = '2012-01-01 00:00:00';
$sampleDateMax = '2012-02-01 00:00:00';

$limit = 10;

// Returns first 10 samples: their dates, values, and running/cumulative totals
$analyst->moving($scalarId, $limit, $sampleDateMin, $sampleDateMax, 'SUM');
// Now only distinct values affect aggregates
$analyst->moving($scalarId, $limit, $sampleDateMin, $sampleDateMax, 'SUM', true);

// Returns first 10 samples: their dates and values
$analyst->values($scalarId, $limit, $sampleDateMin, $sampleDateMax);

// Returns first 10 samples: their dates and values
$analyst->values($scalarId, $limit, $sampleDateMin, $sampleDateMax);

// Returns first 10 samples: their dates, values, and difference from prior sample
$analyst->changes($scalarId, $limit, $sampleDateMin, $sampleDateMax);
```

## Requirements

Most recently tested with PHP 5.4.0beta1, PHPUnit 3.6.0RC4, and MySQL 5.5.16.

* PHP 5.2+
* MySQL 5.1+
* PDO or MySQL Improved
* apc, xcache or [memcache](http://pecl.php.net/package/memcache)

For tests:

* PHPUnit 3+
* [bcmath](http://php.net/manual/en/book.bc.php)

## Installation

*  `CREATE DATABASE hashmark DEFAULT CHARACTER SET utf8 DEFAULT COLLATE utf8_unicode_ci;`
* Import [Sql/Schema/hashmark.sql](https://github.com/codeactual/hashmark/blob/b24734f75552189b82611cd927e745ebe70ef4b8/Sql/Schema/hashmark.sql#L115)
* Optionally repeat 1 and 2 for a separate unit test DB.

### Database Configuration

Hashmark uses Zend Framework's database component. Refer to the ZF [guide](http://framework.zend.com/manual/1.11/en/zend.db.adapter.html) for option values. Example:

``` php
<?php
$config['DbHelper']['profile']['unittest'] = array(
 'adapter' => 'Mysqli',
 'params' => array(
   'host' => '127.0.0.1',
   'port' => 5516,
   'dbname' => 'hashmark_test',
   'username' => 'msandbox',
   'password' => 'msandbox'
 )
);
```

[Config/Hashmark-dist.php](https://github.com/codeactual/hashmark/blob/b24734f75552189b82611cd927e745ebe70ef4b8/Config/Hashmark-dist.php) only includes a database config profile for cron scripts and unit tests. Normally the client app will supply its own connection instance. For example:

``` php
<?php
$this->hashmark = Hashmark::getModule('Client', '', $db);
...
$this->hashmark->incr('featureX:optOut', 1, true);
```

### Cache Configuration

Hashmark also uses Zend Framework's cache component. Refer to the ZF [guide](http://framework.zend.com/manual/1.11/en/zend.cache.backends.html) for option values.

Using Memcache as an example, you might update `$config['cache`'] in [Config/Hashmark-dist.php](https://github.com/codeactual/hashmark/blob/b24734f75552189b82611cd927e745ebe70ef4b8/Config/Hashmark-dist.php#L23):

``` php
$config['Cache'] = array(
 'backEndName' => 'Memcached',
 'frontEndOpts' => array(),
 'backEndOpts' => array(
   'servers' => array(
     array('host' => 'localhost', 'port' => 11211)
   )
 )
);
```

### Other Configuration

See [Config/Hashmark-dist.php](https://github.com/codeactual/hashmark/blob/b24734f75552189b82611cd927e745ebe70ef4b8/Config/Hashmark-dist.php) comments.

### Verify

```
$ php -f Test/Install.php
pass: Connected to DB with 'cron' profile in Config/DbHelper.php
pass: Found all Hashmark tables with 'cron' profile in Config/DbHelper.php
pass: Connected to DB with 'unittest' profile in Config/DbHelper.php
pass: Found all Hashmark tables with 'unittest' profile in Config/DbHelper.php
pass: Loaded Hashmark_BcMath module.
pass: Loaded Hashmark_Cache module.
pass: Loaded Hashmark_Client module.
pass: Loaded Hashmark_Core module.
pass: Loaded Hashmark_DbHelper module.
pass: Loaded Hashmark_Partition module.
pass: Loaded Hashmark_Agent_YahooWeather module.
pass: Loaded Hashmark_Test_FakeModuleType module.
pass: Built samples_1234_20111000 partition name with 'm' setting in Config/Partition.php.
```

## Schema

* <code>[agents](https://github.com/codeactual/hashmark/blob/b24734f75552189b82611cd927e745ebe70ef4b8/Sql/Schema/hashmark.sql#L22)</code>: Available [Agent](https://github.com/codeactual/hashmark/blob/b24734f75552189b82611cd927e745ebe70ef4b8/Agent/) classes.
* <code>[agents_scalars](https://github.com/codeactual/hashmark/blob/b24734f75552189b82611cd927e745ebe70ef4b8/Sql/Schema/hashmark.sql#L37)</code>: Agent's schedules and last-run metadata.
* <code>[categories](https://github.com/codeactual/hashmark/blob/b24734f75552189b82611cd927e745ebe70ef4b8/Sql/Schema/hashmark.sql#L63)</code>: Groups to support front-end browsing, searches, visualization, etc.
* <code>[categories_milestones](https://github.com/codeactual/hashmark/blob/b24734f75552189b82611cd927e745ebe70ef4b8/Sql/Schema/hashmark.sql#L79)</code>: For example, to link category "ShoppingCart" with milestone "site release 2.1.2".
* <code>[categories_scalars(https://github.com/codeactual/hashmark/blob/b24734f75552189b82611cd927e745ebe70ef4b8/Sql/Schema/hashmark.sql#L97)</code>: For example, to link category "ShoppingCart" with data point "featureX:optOut".
* <code>[milestones](https://github.com/codeactual/hashmark/blob/b24734f75552189b82611cd927e745ebe70ef4b8/Sql/Schema/hashmark.sql#L115)</code>: Events to correlate with scalar histories, e.g. to visualize "featureX:optOut" changes across site releases that tweak "featureX".
* <code>[samples_analyst_temp](https://github.com/codeactual/hashmark/blob/b24734f75552189b82611cd927e745ebe70ef4b8/Sql/Schema/hashmark.sql#L163)</code>: When Hashmark creates temporary tables to hold intermediate aggregates, it copies this table's definition.
* <code>[samples_decimal](https://github.com/codeactual/hashmark/blob/b24734f75552189b82611cd927e745ebe70ef4b8/Sql/Schema/hashmark.sql#L147)</code> and <code>[samples_string](https://github.com/codeactual/hashmark/blob/b24734f75552189b82611cd927e745ebe70ef4b8/Sql/Schema/hashmark.sql#L131)</code>: Identical except for one column. Hashmark copies their definitions when creating new partitions. `id` auto-increment values are seeded from the associated scalar's `sample_count` column.
* <code>[scalars](https://github.com/codeactual/hashmark/blob/b24734f75552189b82611cd927e745ebe70ef4b8/Sql/Schema/hashmark.sql#L181)</code>: The table holds columns that define each data point's type (string or decimal), current value, and other metadata.

## File Layout

### Naming Convention

Zend Framework's style is followed pretty closely. Parent classes, some abstract, live in the root directory. Child classes live in directories named after their parents. Class names predictable indicate ancestors, e.g. [Hashmark_Analyst_BasicDecimal`, and file names mirror the class name's last part, e.g. Analyst/BasicDecimal.php.

```
Analyst/
 BasicDecimal.php
Analyst.php
...
Agent/
 YahooWeather.php
 ...
Agent.php
...
```

### Classes

* [Analyst.php](https://github.com/codeactual/hashmark/blob/b24734f75552189b82611cd927e745ebe70ef4b8/Analyst.php): Abstract base. For example, implementation [BasicDecimal.php](https://github.com/codeactual/hashmark/blob/b24734f75552189b82611cd927e745ebe70ef4b8/Analyst/BasicDecimal.php) performs list and statistical queries.
* [Cache.php](https://github.com/codeactual/hashmark/blob/b24734f75552189b82611cd927e745ebe70ef4b8/Cache.php): Zend_Cache wrapper that adds namespaces.
* [Client.php](https://github.com/codeactual/hashmark/blob/b24734f75552189b82611cd927e745ebe70ef4b8/Client.php): Input API for client apps to update scalars and add historical data points.
* [Core.php](https://github.com/codeactual/hashmark/blob/b24734f75552189b82611cd927e745ebe70ef4b8/Core.php): Internal API to manage scalars, categories, milestones, etc.
* [DbHelper.php](https://github.com/codeactual/hashmark/blob/b24734f75552189b82611cd927e745ebe70ef4b8/DbHelper.php): Abstract base for Zend_Db adapter wrappers.
* [Hashmark.php](https://github.com/codeactual/hashmark/blob/b24734f75552189b82611cd927e745ebe70ef4b8/Hashmark.php): Defines the `getModule()` factory.
* [Module.php](https://github.com/codeactual/hashmark/blob/b24734f75552189b82611cd927e745ebe70ef4b8/Module.php): Abstract base for classes produced by factory <code>[Hashmark::getModule()](https://github.com/codeactual/hashmark/blob/b24734f75552189b82611cd927e745ebe70ef4b8/Hashmark.php#L76).
* [Partition.php](https://github.com/codeactual/hashmark/blob/b24734f75552189b82611cd927e745ebe70ef4b8/Partition.php): Management and querying of MyISAM and MRG_MyISAM tables holding scalars' historical values.
* [Agent.php](https://github.com/codeactual/hashmark/blob/b24734f75552189b82611cd927e745ebe70ef4b8/Agent.php): Interface relied upon by [Cron/runAgents.php](https://github.com/codeactual/hashmark/blob/b24734f75552189b82611cd927e745ebe70ef4b8/Cron/runAgents.php).
* [Util.php](https://github.com/codeactual/hashmark/blob/b24734f75552189b82611cd927e745ebe70ef4b8/Util.php): Static/stateless helper class with methods like `randomSha1()`.

### Tests

Most test-related files live under `Test/`, but a few like `Config/Test.php` live outside so cases can cover code relying on naming conventions.

### Sql/Analyst/

Contains SQL templates. For example, [Sql/Analyst/BasicDecimal.php](https://github.com/codeactual/hashmark/tree/07c4dc972b180418d62bee49ee382d88cf07dc8f/Sql/Analyst/BasicDecimal.php) templates allow [Analyst/BasicDecimal.php]((https://github.com/codeactual/hashmark/blob/b24734f75552189b82611cd927e745ebe70ef4b8/Analyst/BasicDecimal.php) ) to reuse and combine statements as intermediate results toward final aggregates.

## Cron Scripts

* <code>[gcMergeTables.php](https://github.com/codeactual/hashmark/blob/b24734f75552189b82611cd927e745ebe70ef4b8/Cron/gcMergeTables.php)</code>: Drops merge tables based on hard limits defined in <code>[Config/Cron.php](https://github.com/codeactual/hashmark/blob/b24734f75552189b82611cd927e745ebe70ef4b8/Config/Hashmark-dist.php#L27)</code>.
* <code>[gcUnitTestTables.php](https://github.com/codeactual/hashmark/blob/b24734f75552189b82611cd927e745ebe70ef4b8/Cron/gcUnitTestTables.php)</code>: Drops test-created tables and runs `FLUSH TABLES`.
* <code>[runAgents.php](https://github.com/codeactual/hashmark/blob/b24734f75552189b82611cd927e745ebe70ef4b8/Cron/runAgents.php)</code>: Finds and runs all agent scripts due for execution based on their configured frequency.

## Tests

### Running

**First**: `php -f Test/Analyst/BasicDecimal/Tool/writeProviderData.php` which Test/Analyst/BasicDecimal/Data/provider.php. The `BasicDecimal` suite relies on a `bcmath` and a series of generators in `Test/Analyst/BasicDecimal/Tool/` to provide calculate a comprehensive set of expected test results.

* Run suites for all modules: `phpunit  [--group name] Test/AllTests.php`
* Run a specific module's suite: `phpunit [--group name] Test/[module]/AllTests.php`
