<?php
// vim: fenc=utf-8:ft=php:ai:si:ts=4:sw=4:et:

/**
 * Hashmark
 *
 * @filesource
 * @link        http://code.google.com/p/hashmark/
 * @link        http://framework.zend.com/manual/en/coding-standard.html
 * @link        http://phpdoc.org/tutorial.php
 * @copyright   Copyright (c) 2008-2009, Code Actual LLC
 * @license     http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package     Hashmark
 * @version     $Id: Hashmark.php 291 2009-02-11 16:30:32Z david $
*/

/**
 * Minimal set required by most modules creaed by getModule().
 */
require_once dirname(__FILE__) . '/Config/Hashmark.php';
require_once dirname(__FILE__) . '/Module.php';
require_once dirname(__FILE__) . '/Module/DbDependent.php';
require_once dirname(__FILE__) . '/Util.php';

/**
 * Exception codes used in Hashmark_Module implementations.
 */
define('HASHMARK_EXCEPTION_VALIDATION', 1);
define('HASHMARK_EXCEPTION_SQL', 2);

/**
 * Universal helpers for accessing Hashmark modules.
 *
 * Central Hashmark class inside the library, client apps, and front-ends.
 *
 * @package     Hashmark
 */
class Hashmark
{
    /**
     * Factory for Hashmark_Module implementations: Client, Core, Cron, etc.
     *
     *      -   Autoloads required scripts and config files (if they exist)
     *          based on naming conventions.
     *      -   To inject dependencies $a and $b use:
     *          Hashmark::getModule($base, $type, $a, $b);
     *          Dependency list must match classes initModule() definition.
     *
     * @param string    $base   Ex. 'Core', optional base class in Core.php.
     * @param string    $type   Ex. 'Mysql', implementation in Core/Mysql.php.
     * @param mixed     ...     Variable-length list of arguments passed on to
     *                          instance's initModule().
     * @return mixed    New instance; false if instance's initModule()
     *                  returns false.
     * @throws Exception    If class file for $type is unreadable.
     */
    public static function getModule($base, $type = '')
    {
        static $moduleCache = array();

        // Skip class/config file loading if possible.
        $modCacheKey = $base . $type;
        if (!isset($moduleCache[$modCacheKey])) {
            static $baseConfigCache = array();
            static $typeConfigCache = array();

            $dirname = dirname(__FILE__);

            // Use $config from the config file, if present.
            if (!isset($baseConfigCache[$base])) {
                $baseConfigFile = $dirname . "/Config/{$base}.php";
                if (is_readable($baseConfigFile)) {
                    unset($config);
                    require_once $baseConfigFile;
                    if (isset($config)) {
                        $baseConfigCache[$base] = $config;
                    } else {
                        $baseConfigCache[$base] = false;
                    }
                }

                $baseClassFile = $dirname . "/{$base}.php";
                if (!is_readable($baseClassFile)) {
                    throw new Exception("File not found for class Hashmark_{$base}.");
                }
                require_once $baseClassFile;
            }

            // Check if $baseClassFile defined a constant for the default type.
            if (!$type) {
                $defaultType = 'HASHMARK_' . strtoupper($base) . '_DEFAULT_TYPE';
                if (defined($defaultType)) {
                    $type = constant($defaultType);
                }
            }

            if ($type) {
                // Use $config from the config file, if present.
                if (!isset($typeConfigCache[$type])) {
                    
                    // Apply external config paths defined in the base config file.
                    $configPaths = array("{$dirname}/Config");
                    if (!empty($baseConfigCache[$base]['ext_config_paths'])) {
                        $configPaths = array_merge($baseConfigCache[$base]['ext_config_paths'], $configPaths);
                    }
                    
                    foreach ($configPaths as $path) {
                        $typeConfigFile = "{$path}/{$base}/{$type}.php";
                        if (is_readable($typeConfigFile)) {
                            unset($config);
                            require_once $typeConfigFile;
                            if (isset($config)) {
                                $typeConfigCache[$type] = $config;
                                break;
                            } else {
                                $typeConfigCache[$type] = false;
                            }
                        }
                    }

                    // Apply external module paths defined in the base config file.
                    $modulePaths = array($dirname);
                    if (!empty($baseConfigCache[$base]['ext_module_paths'])) {
                        $modulePaths = array_merge($baseConfigCache[$base]['ext_module_paths'], $modulePaths);
                    }

                    foreach ($modulePaths as $path) {
                        $typeClassFile = "{$path}/{$base}/{$type}.php";
                        if (is_readable($typeClassFile)) {
                            require_once $typeClassFile;
                            break;
                        }
                        $typeClassFile = '';
                    }
                    
                    if (!$typeClassFile) {
                        throw new Exception("File not found for class Hashmark_{$base}_{$type}.");
                    }
                }

                $instClass = "Hashmark_{$base}_{$type}";
            } else {
                $instClass = "Hashmark_{$base}";
            }

            $baseConfig = isset($baseConfigCache[$base]) ? $baseConfigCache[$base] : false;
            $typeConfig = isset($typeConfigCache[$type]) ? $typeConfigCache[$type] : false;

            if ('Cache' == $base) {
                $cache = null;
            } else {
                $cache = self::getModule('Cache');
            }

            $moduleCache[$modCacheKey] = new $instClass($base, $baseConfig, $type,
                                                        $typeConfig, $cache);
        }
        
        $inst = clone $moduleCache[$modCacheKey];
        
        // Inject variable dependencies.
        $initArgs = func_get_args();
        if (method_exists($inst, 'initModule')) {
            if (count($initArgs) > 2) {
                $initOk = call_user_func_array(array($inst, 'initModule'), array_slice($initArgs, 2));
            } else {
                $initOk = $inst->initModule();
            }

            if (!$initOk) {
                return false;
            }
        }

        return $inst;
    }
}
