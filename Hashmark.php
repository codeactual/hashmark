<?php
// vim: fenc=utf-8:ft=php:ai:si:ts=4:sw=4:et:

/**
 * Hashmark
 *
 * @filesource
 * @copyright   Copyright (c) 2008-2011 David Smith
 * @license     http://www.opensource.org/licenses/mit-license.php MIT License
 * @package     Hashmark
 * @version     $Id$
*/

/**
 * Avoid repeated dirname().
 */
define('HASHMARK_ROOT_DIR', dirname(__FILE__));

/**
 * Auto-load ZF components if not already embedded in ZF dependent app.
 */
if (!class_exists('Zend_Loader_Autoloader')) {
    require_once HASHMARK_ROOT_DIR . '/Zend/Loader/Autoloader.php';
    Zend_Loader_Autoloader::getInstance();
}

// In case a script is invoked from a non-root dir
// (since ZF uses relative paths for dependencies).
$includePath = ini_get('include_path');
if (false === strpos($includePath, HASHMARK_ROOT_DIR)) {
    ini_set('include_path', $includePath  . ':' . HASHMARK_ROOT_DIR);
}
unset($includePath);

/**
 * Minimal set required by most modules creaed by getModule().
 */
require_once HASHMARK_ROOT_DIR . '/Module.php';
require_once HASHMARK_ROOT_DIR . '/Module/DbDependent.php';
require_once HASHMARK_ROOT_DIR . '/Util.php';

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
     * Factory for instances of Client, Core, Cron, Agent, etc.
     *
     *      -   Autoloads required scripts and config files (if they exist)
     *          based on naming conventions.
     *      -   To inject dependencies $a and $b use:
     *          Hashmark::getModule($base, $type, $a, $b);
     *          Dependency list must match classes initModule() definition.
     *
     * @param string    $base   Base class, ex. 'Agent' in Agent.php.
     * @param string    $type   Optional class subtype, ex. 'ScalarValue'
     *                          in Agent/ScalarValue.php.
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
        
        $baseConfig = self::getConfig($base);
        $typeConfig = null;
        
        // Look for a fallback default module type.
        if (!$type && !empty($baseConfig['default_type'])) {
            $type = $baseConfig['default_type'];
        }
        
        $moduleId = $base . $type;
        
        // Cache miss: load base/type classes and configs.
        if (!isset($moduleCache[$moduleId])) {
            $baseClassFile = HASHMARK_ROOT_DIR . "/{$base}.php";
            if (is_readable($baseClassFile)) {
                require_once $baseClassFile;
            }

            if ($type) {
                // Apply external module paths defined in the base config file.
                $modulePaths = array(HASHMARK_ROOT_DIR . "/{$base}");
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
                                                     self::getConfig($base, $type),
                                                     $cache);
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
     * @param string    $base   Ex. 'Agent', optional base class in Agent.php.
     * @param string    $type   Ex. 'ScalarValue', implementation in Agent/ScalarValue.php.
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
        
        if (!isset($defaultConfigCache)) {
            $defaultConfigFile = HASHMARK_ROOT_DIR . '/Config/Hashmark.php';
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

            $baseConfigFile = HASHMARK_ROOT_DIR . "/Config/{$base}.php";

            if (is_readable($baseConfigFile)) {
                require_once $baseConfigFile;   // May update $config
            }
                
            // Cache this default+overrides config set.
            $baseConfigCache[$base] = $config;
        }
                    
        // Apply external module paths defined in the base config file.
        $configPaths = array(HASHMARK_ROOT_DIR . "/Config/{$base}");
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
