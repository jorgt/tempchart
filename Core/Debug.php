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

    class Debug {

        private static $file;
        private static $level;
        private static $toFile;
        private static $toScreen;
        private static $collect;
        private static $collection = array();

        public static function initialize($_on, $_lvl, $_collect, $_toFile, $_toScreen, $_path) {
            define('DEBUG', $_on);
            self::$toFile = $_toFile;
            self::$toScreen = $_toScreen;
            self::$collect = $_collect;
            self::$level = $_lvl;

            if (DEBUG && $_toFile) {
                self::initializeFile($_path);
            }
        }

        private static function initializeFile($_path) {
            $date = date('YmdHis');
            self::$file = $_path . DS . $date . '_' . str_replace('/', '_', $_GET['url']) . '.txt';
            if (self::$toFile) {
                $path = realpath(__DIR__ . '/../');
                if (defined('AJAX') && AJAX) {
                    self::toFile('AJAX CALL. Current level: ' . self::$level . ', Installed: ' . $path);
                } else {
                    self::toFile('APPLICATION LOG. Current level: ' . self::$level . ', Installed: ' . $path);
                }
            }
        }

        private static function message($_str) {
            if (!self::$collect && self::$toScreen && defined('AJAX') && !AJAX) {
                echo str_replace('\n', '', $_str);
            }
        }

        private static function collect($_str) {
            if (self::$collect) {
                self::$collection[] = $_str;
            }
        }

        public static function dump() {
            if (DEBUG && self::$collect && self::$toScreen && sizeof(self::$collection > 0) && defined('AJAX') && !AJAX) {
                foreach (self::$collection as $line) {
                    echo str_replace('\n', '', $line);
                }
            }
        }

        private static function toFile($_str = null) {
            if (DEBUG && self::$toFile) {
                if (is_null($_str)) {
                    $line = PHP_EOL;
                } else {
                    $_str = str_replace('\n', PHP_EOL, $_str);
                    $line = date('Y-m-d H:i:s') . ' ' . html_entity_decode(strip_tags($_str)) . PHP_EOL;
                }
                file_put_contents(self::$file, $line, FILE_APPEND);
            }
        }

        public static function Debug($_str, $_lvl) {
            if (defined('DEBUG') && DEBUG && $_lvl <= self::$level) {
                $stack = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
                $single = @$stack[1];
                $class = @$stack[2]['class'] . @$stack[2]['type'] . @$stack[2]['function'];
                $path = pathinfo($single["file"]);
                $file = strtoupper($path["basename"]);
                $style = ($_lvl === 1) ? 'style="color:blue"' : "";
                ob_start();
                echo "<pre $style><b>[" . $file . " ON LINE " . $single["line"] . "] $class():</b> ";
                if (is_array($_str)) {
                    print_r($_str);
                } elseif (is_object($_str)) {
                    print_r((array) $_str);
                } else {
                    echo $_str;
                }
                echo '</pre>';
                $echo = ob_get_clean();
                self::collect($echo);
                self::message($echo);
                self::toFile($_str);
            }
        }

    }

}
