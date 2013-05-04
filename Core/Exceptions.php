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

    abstract class Exceptions {

        private static $STARTED = false;
        private static $LVL;

        private function __construct() {
            
        }

        public static function initiate($_lvl = 2) {
            set_error_handler(array('\Core\exceptions', 'err_handler'));
            set_exception_handler(array('\Core\exceptions', 'exc_handler'));
            register_shutdown_function(array('\Core\exceptions', 'capture_shutdown'));
            self::$LVL = $_lvl;
        }

        public static function err_handler($errno, $errstr, $errfile, $errline, $errcontext) {
            $l = error_reporting();
            if ($errno <= $l) {
                $exit = false;
                switch ($errno) {
                    case E_ERROR:
                    case E_USER_ERROR:
                        $type = 'Fatal Error';
                        $exit = true;
                        break;
                    case E_USER_WARNING:
                    case E_WARNING:
                        $type = 'Warning';
                        break;
                    case E_USER_NOTICE:
                    case E_NOTICE:
                    case @E_STRICT:
                        $type = 'Notice';
                        break;
                    case @E_RECOVERABLE_ERROR:
                        $type = 'Catchable';
                        break;
                    default:
                        $type = 'Unknown Error';
                        $exit = true;
                        break;
                }
                $exception = new \ErrorException($type . ': ' . $errstr, 0, $errno, $errfile, $errline);
                self::exc_handler($exception);
                if ($exit)
                    exit();

                return false;
            }
        }

        static function exc_handler(\Exception $ex) {
            self::$STARTED = true;
            if (DEBUG) {
                $stack = $ex->getTrace();
                $lvl = 3;
                //rsort($stack);
                $str = '<code>';
                $str .= '<b style="color:red">' . $ex->getMessage() . '</b>\n';
                $class = (self::$LVL >= $lvl) ? ' style="color:red"' : '';
                foreach ($stack as $curr) {
                    $c = (isset($curr['class'])) ? '<b>' . $curr['class'] . '::</b>' : '';
                    $str .= '<span' . $class . '><br>&nbsp;&nbsp;Trown in function <b>' . $c . $curr['function'] . '</b>';
                    $str .= (isset($curr['line'])) ? ' on line ' . $curr['line'] : '';
                    $str .= (isset($curr['file'])) ? ' of file ' . $curr['file'] : '';
                    $str .= '</span>\n';

                    if (self::$LVL >= $lvl) {
                        $str .= '<br>';
                        if (isset($curr['file'])) {
                            foreach (@array_slice(@file(@$curr['file']), @$curr['line'] - 4, 7, true) as $l => $v) {
                                $v = @str_replace("\t", '&nbsp;', $v);
                                $str .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . $v.'\n';
                            }
                        }
                    }
                }
                $str .= '</code>';
                debug($str);
            } elseif (AJAX) {
                $error = array('error' => array(
                        'message' => $ex->getMessage(),
                        'stack' => $ex->getTrace()));
                die(json_encode($error));
                exit;
            } else {
                $str = 'Oh no!<br>' . $ex->getMessage();
                die($str);
            }
        }

        /**
         * On shutdown, one last error is captured. This one will only show up
         * if debugging is set to TRUE and error output has not already been 
         * started. 
         * @return boolean
         */
        public static function capture_shutdown() {
            if (!self::$STARTED) {
                $error = error_get_last();
                if ($error && DEBUG) {
                    ## IF YOU WANT TO CLEAR ALL BUFFER, UNCOMMENT NEXT LINE:
                    ob_end_clean();
                    // Display content $error variable
                    echo '<pre>';
                    print_r($error);
                    echo '</pre>';
                    die();
                } else {
                    return true;
                }
            }
        }

    }

}    