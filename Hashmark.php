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
     * @throws Exception    If class file for $type is unreadable;
     *                      if $config in Config/Hashmark.php is missing.
     */
    public static function getModule($base, $type = '')
    {
        /**
         * Previously configured module instances. Clones will be returned
         * using cached objects as sources.
         *
         * Indexed by $base.$type values.
         */
        static $moduleCache = array();
        
        $dirname = dirname(__FILE__);

        $baseConfig = self::getConfig($base);
        $typeConfig = null;
        
        // Look for a fallback default module type.
        if (!$type && !empty($baseConfig['default_type'])) {
            $type = $baseConfig['default_type'];
        }
        
        $moduleId = $base . $type;
        
        // Cache miss: load base/type classes and configs.
        if (!isset($moduleCache[$moduleId])) {
            $baseClassFile = $dirname . "/{$base}.php";
            if (!is_readable($baseClassFile)) {
                throw new Exception("File not found for class Hashmark_{$base}.");
            }
            require_once $baseClassFile;

            if ($type) {
                // Apply external module paths defined in the base config file.
                $modulePaths = array("{$dirname}/{$base}");
                if (!empty($baseConfig['ext_module_paths'])) {
                    $modulePaths = array_merge($baseConfig['ext_module_paths'], $modulePaths);
                }

                // Use the first class found with matching name.
                foreach ($modulePaths as $path) {
                    $typeClassFile = "{$path}/{$type}.php";
                    if (is_readable($typeClassFile)) {
                        require_once $typeClassFile;
                        break;
                    }
                    $typeClassFile = '';
                }

                if (!$typeClassFile) {
                    throw new Exception("File not found for class Hashmark_{$base}_{$type}.");
                }

                $instClass = "Hashmark_{$base}_{$type}";
            } else {
                $instClass = "Hashmark_{$base}";
            }

            // Auto-inject a cache object into every module.
            if ('Cache' == $base) {
                $cache = null;
            } else {
                $cache = self::getModule('Cache');
            }

            $moduleCache[$moduleId] = new $instClass($base, $baseConfig, $type,
                                               self::getConfig($base, $type), $cache);
        }
        
        $inst = clone $moduleCache[$moduleId];
        
        // Inject variable argument list into instance's initModule(), if exists.
        $initArgs = func_get_args();
        if (method_exists($inst, 'initModule')) {
            if (count($initArgs) > 2) {
                $initOk = call_user_func_array(array($inst, 'initModule'), array_slice($initArgs, 2));
            } else {
                $initOk = $inst->initModule();
            }

            // Module logic indicates the instance is unusable, ex. dependencies
            // are missing or misconfigured.
            if (!$initOk) {
                return false;
            }
        }

        return $inst;
    }

    /**
     * Returns a base module's config value(s).
     *
     *      -   Optionally filtered by module type and specific config key.
     *      -   Starts with default configs defined in Config/Hashmark.php,
     *          then applies overrides defined in optional files discovered
     *          with standard Hashmark naming conventions inside Config/.
     * 
     * @param string    $base   Ex. 'Core', optional base class in Core.php.
     * @param string    $type   Ex. 'Mysql', implementation in Core/Mysql.php. Optional.
     * @param string    $key    If key exists in the config array, only the
     *                          associated value is returned, rather than whole
     *                          array. Optional.
     * @return mixed        The filtered value identified by non-empty $base,
     *                      $type, and $key values; null if not found.
     * @throws Exception    If class file for $type is unreadable;
     *                      if $config in Config/Hashmark.php is missing.
     */
    public static function getConfig($base, $type = '', $key = '')
    {
        /**
         * Holds $config from Config/Hashmark.php.
         * These defaults can be overriden by base/type config files.
         */
        static $defaultConfigCache;

        /**
         * Defaults and optional overrides from base config files.
         * Indexed by base names.
         */
        static $baseConfigCache = array();
        
        /**
         * Defaults and optional overrides from type config files.
         * Indexed by <base>_<type>.
         */
        static $typeConfigCache = array();
        
        $dirname = dirname(__FILE__);

        if (!isset($defaultConfigCache)) {
            $defaultConfigFile = $dirname . '/Config/Hashmark.php';
            if (!is_readable($defaultConfigFile)) {
                throw new Exception("Default config file missing: {$defaultConfigFile}.");
            }

            require_once $defaultConfigFile;

            if (!isset($config)) {
                throw new Exception("Default config values missing: {$defaultConfigFile}.");
            }
            
            $defaultConfigCache = $config;
        }

        if (!isset($baseConfigCache[$base])) {
            // Apply default configs. Allow base-specific config file to
            // override the values.
            if (isset($defaultConfigCache[$base])) {
                $config = $defaultConfigCache[$base];
            } else {
                $config = array();
            }

            $baseConfigFile = $dirname . "/Config/{$base}.php";

            if (is_readable($baseConfigFile)) {
                require_once $baseConfigFile;   // May update $config
            }
                
            // Cache this default+overrides config set.
            $baseConfigCache[$base] = $config;
        }
                    
        // Apply external module paths defined in the base config file.
        $configPaths = array("{$dirname}/Config/{$base}");
        if (!empty($baseConfigCache[$base]['ext_config_paths'])) {
            $configPaths = array_merge($baseConfigCache[$base]['ext_config_paths'], $configPaths);
        }

        if ($type) {
            $typeId = "{$base}_{$type}";

            if (!isset($typeConfigCache[$typeId])) {
                // Apply default configs. Allow type-specific config file to
                // override the values.
                if (isset($defaultConfigCache[$typeId])) {
                    $config = $defaultConfigCache[$typeId];
                } else {
                    $config = array();
                }

                // Use the first class found with matching name.
                foreach ($configPaths as $path) {
                    $typeConfigFile = "{$path}/{$type}.php";
                    if (is_readable($typeConfigFile)) {
                        require_once $typeConfigFile;   // May update $config
                        break;
                    }
                }
            
                // Cache this default+overrides config set.
                $typeConfigCache[$typeId] = $config;
            }

            // Return a specific config value, if exists.
            if ($key) {
                if (isset($typeConfigCache[$typeId][$key])) {
                    return $typeConfigCache[$typeId][$key];
                } else {
                    return null;
                }
            }

            return $typeConfigCache[$typeId];
        }

        // Return a specific config value, if exists.
        if ($key) {
            if (isset($baseConfigCache[$base][$key])) {
                return $baseConfigCache[$base][$key];
            } else {
                return null;
            }
        }
                
        // Returns all config values for the module, ex. $config['DbHelper']
        // defined in Config/Hashmark.php.
        return $baseConfigCache[$base];
    }
}
