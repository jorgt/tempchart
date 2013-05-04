<?php
/**
 * V8
 *
 * @author      Jorg Thuijls <jorg.thuijls@gmail.com>
 * @copyright   2013 Jorg Thuijls
 * @license     MIT License
 * @version     0.1
 * @package     Core
 *
 * MIT LICENSE
 *
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the
 * "Software"), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish,
 * distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to
 * the following conditions:
 *
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
 * LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
 * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
 * WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */
namespace Core {

    /**
     * Holds all configuration
     * 
     * Has the ability add variables from ini files, and to hold variables
     * stored at runtime. Since it's static, Config will be used throughout to 
     * hold app wide variables. 
     */
    final class Config {

        /**
         * Container for variables
         * @var type Array
         */
        private static $vars = array();

        private function __construct() {
            throw \ErrorException('Config is ment to stay static');
        }

        public static function load($_config = null) {
            if (is_null($_config)) {
                $_config = '../settings.php';
            }

            self::parseSettingsFile($_config);
            if (!self::checkInitialSettingsFile()) {
                throw new \Core\ConfigException('Settings file integrity check failed. Please check the file.', E_USER_ERROR);
            }
        }

        /**
         * 
         * @param type $_var
         * @param type $_val
         */
        public static function set($_var, $_val) {
            $_val = self::fixVal($_var, $_val);
            if (isset(self::$vars[$_var])) {
                if (is_array(self::$vars[$_var]) && is_array($_val)) {
                    self::$vars[$_var] = array_merge($_val, self::$vars[$_var]);
                    return true;
                }
            }
            self::$vars[$_var] = $_val;
            return true;
        }

        private static function fixVal($_var, &$_val) {
            if ($_val == "true") {
                $_val = true;
                return true;
            } elseif ($_val == "false") {
                $_val = false;
                return false;
            }
            if (preg_match('/^path(.*)$/i', $_var) && strpos($_val, _BASE) === false) {
                $_val = str_replace('/', DS, $_val);
                $_val = realpath(_BASE . DS . $_val);
            }
            return $_val;
        }

        public static function get($_var) {
            return (isset(self::$vars[$_var])) ? self::$vars[$_var] : null;
        }

        public static function all() {
            return self::$vars;
        }

        public static function __callStatic($_name, $_arguments) {
            // getting
            if (sizeof($_arguments) === 1) {
                if (array_key_exists($_name, self::$vars)) {
                    $vals = self::get($_name);
                    return (!isset($vals[$_arguments[0]])) ? null : $vals[$_arguments[0]];
                }
                return null;
            } elseif (sizeof($_arguments) > 1) {
                if (is_array($_arguments[1])) {
                    throw new Exception('To many layers of arrays');
                }
                self::$vars[$_name][$_arguments[0]] = self::fixVal($_arguments[0], $_arguments[1]);
            }
            return null;
        }

        private static function parseSettingsFile($_file) {
            if (realpath($_file) === false) {
                throw new \ErrorException('Please provide a valid config file', E_USER_ERROR);
            }
            $ini = parse_ini_file(realpath($_file), true);
            ;
            self::$vars = array_merge(self::fixValRecursive($ini), self::$vars);
        }

        private static function fixValRecursive(array &$_var) {
            foreach ($_var as $key => &$val) {
                if (is_array($val)) {
                    self::fixValRecursive($val);
                } else {
                    self::fixVal($key, $val);
                }
            }
            return $_var;
        }

        private static function checkInitialSettingsFile() {
            $intregrity = array(
                'installation' => array('url'),
                'folders' => array('path_public', 'path_core', 'path_app', 'path_databases', 'path_templates'));

            foreach ($intregrity as $k => $v) {
                if (!in_array($k, array_keys(self::$vars))) {
                    return false;
                }

                foreach ($v as $i) {
                    if (!in_array($i, array_keys(self::$vars[$k]))) {
                        return false;
                    }
                }
            }
            return true;
        }

    }

}