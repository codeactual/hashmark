<?php
// vim: fenc=utf-8?=php???=4?=4?:

/**
 * Shared SQL query templates.
 *
 *      -   :name macros will be escaped and quoted, ex. DATETIMEs.
 *      -   @name macros will not be escaped nor quoted, ex. SQL functions.
 *
 * @filesource
 * @link        http://code.google.com/p/hashmark/
 * @link        http://framework.zend.com/manual/en/coding-standard.html
 * @link        http://phpdoc.org/tutorial.php
 * @copyright   Copyright (c) 2008-2009, Code Actual LLC
 * @license     http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package     Hashmark
 * @subpackage  Sql
 * @version     $Id: Core.php 273 2009-02-04 13:47:39Z david $
*/

$sql = array();

$sql['getJobById'] = 'SELECT * '
                   . 'FROM `jobs` '
                   . 'WHERE `id` = ?';

$sql['createScalar'] = 'INSERT INTO `scalars` '
                     . '(`name`, `value`, `type`, `description`, `sampler_frequency`, '
                     . '`sampler_start`, `sampler_handler`, `sampler_status`) '
                     . 'VALUES (?, ?, ?, ?, ?, ?, ?, ?)';

$sql['getScalarById'] = 'SELECT * '
                      . 'FROM `scalars` '
                      . 'WHERE `id` = ?';

$sql['getScalarByName'] = 'SELECT * '
                        . 'FROM `scalars` '
                        . 'WHERE `name` = ? '
                        . 'LIMIT 1';

$sql['getScalarType'] = 'SELECT `type` '
                      . 'FROM `scalars` '
                      . 'WHERE `id` = ?';

$sql['getScalarSampleCount'] = 'SELECT `sample_count` '
                             . 'FROM `scalars` '
                             . 'WHERE `id` = ?';

$sql['getScalarIdByName'] = 'SELECT `id` '
                          . 'FROM `scalars` '
                          . 'WHERE `name` = ?';

$sql['scalarHasCategory'] = 'SELECT `scalar_id` '
                          . 'FROM `categories_scalars` '
                          . 'WHERE `category_id` = ? '
                          . 'AND `scalar_id` = ?';

$sql['setScalarCategory'] = 'REPLACE INTO `categories_scalars` '
                          . '(`category_id`, `scalar_id`) '
                          . 'VALUES (?, ?)';

$sql['unsetScalarCategory'] = 'DELETE FROM `categories_scalars` '
                            . 'WHERE `category_id` = ? '
                            . 'AND `scalar_id` = ?';

$sql['deleteScalar'] = 'DELETE FROM `scalars` WHERE `id` = ?';

$sql['createCategory'] = 'INSERT INTO `categories` '
                       . '(`name`, `description`) '
                       . 'VALUES (?, ?)';

$sql['getCategoryById'] = 'SELECT * '
                        . 'FROM `categories` '
                        . 'WHERE `id` = ?';

$sql['getCategoryByName'] = 'SELECT * '
                          . 'FROM `categories` '
                          . 'WHERE `name` = ? '
                          . 'LIMIT 1';

$sql['deleteCategory'] = 'DELETE FROM `categories` WHERE `id` = ?';

$sql['createMilestone'] = 'INSERT INTO `milestones` '
                        . '(`name`, `when`) '
                        . 'VALUES (?, ?)';

$sql['getMilestoneById'] = 'SELECT * '
                        . 'FROM `milestones` '
                        . 'WHERE `id` = ?';

$sql['getMilestoneByName'] = 'SELECT * '
                          . 'FROM `milestones` '
                          . 'WHERE `name` = ? '
                          . 'LIMIT 1';

$sql['updateMilestone'] = 'UPDATE `milestones` '
                        . 'SET `name` = ?, '
                        . '`when` = ? '
                        . 'WHERE `id` = ?';

$sql['milestoneHasCategory'] = 'SELECT `milestone_id` '
                             . 'FROM `categories_milestones` '
                             . 'WHERE `category_id` = ? '
                             . 'AND `milestone_id` = ?';

$sql['setMilestoneCategory'] = 'REPLACE INTO `categories_milestones` '
                             . '(`category_id`, `milestone_id`) '
                             . 'VALUES (?, ?)';

$sql['unsetMilestoneCategory'] = 'DELETE FROM `categories_milestones` '
                               . 'WHERE `category_id` = ? '
                               . 'AND `milestone_id` = ?';

$sql['deleteMilestone'] = 'DELETE FROM `milestones` WHERE `id` = ?';
