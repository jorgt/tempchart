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
namespace Core\Helpers {

    class Cookie {
        //put your code here

        const Session = null;
        const OneDay = 86400;
        const SevenDays = 604800;
        const ThirtyDays = 2592000;
        const SixMonths = 15811200;
        const OneYear = 31536000;
        const Lifetime = -1; // 2030-01-01 00:00:00

        private $name;
        private static $cookies = array();

        public static function factory($_name) {
            if (!isset(self::$cookies[$_name])) {
                self::$cookies[$_name] = new Cookie($_name);
            }

            return self::$cookies[$_name];
        }

        private function __construct($_name) {
            $this->name = $_name;
            $params = session_get_cookie_params();
            session_set_cookie_params($params["lifetime"], $params["path"], $params["domain"], false, true);
        }

        /**
         * Returns true if there is a cookie with this name.
         *
         * @param string $name
         * @return bool
         */
        public function exists() {
            return isset($_COOKIE[$this->name]);
        }

        /**
         * Returns true if there no cookie with this name or it's empty, or 0,
         * or a few other things. Check http://php.net/empty for a full list.
         *
         * @param string $name
         * @return bool
         */
        public function isEmpty() {
            return empty($_COOKIE[$this->name]);
        }

        /**
         * Get the value of the given cookie. If the cookie does not exist the value
         * of $default will be returned.
         *
         * @param string $name
         * @param string $default
         * @return mixed
         */
        public function get($var) {
            return (isset($_COOKIE[$this->name][$var]) ? $_COOKIE[$this->name][$var] : false);
        }

        /**
         * Set a cookie. Silently does nothing if headers have already been sent.
         *
         * @param string $name
         * @param string $value
         * @param mixed $expiry
         * @param string $path
         * @param string $domain
         * @return bool
         */
        public function set($var, $value, $expiry = self::ThirtyDays, $httpOnly = true) {
            $retval = false;
            if (!headers_sent()) {
                if ($expiry === -1)
                    $expiry = 1893456000; // Lifetime = 2030-01-01 00:00:00
                elseif (is_numeric($expiry))
                    $expiry += time();
                else
                    $expiry = strtotime($expiry);

                $retval = @setcookie("$this->name[$var]", $value, $expiry, false, false, false, $httpOnly);
                if ($retval)
                    $_COOKIE[$this->name][$var] = $value;
            }
            return $retval;
        }

        /**
         * Delete a cookie.
         *
         * @param string $name
         * @param string $path
         * @param string $domain
         * @param bool $remove_from_global Set to true to remove this cookie from this request.
         * @return bool
         */
        public function delete($remove_from_global = true) {
            $retval = false;
            if (!headers_sent() && $this->exists()) {
                foreach ($_COOKIE[$this->name] as $k => $v) {
                    $retval = setcookie("$this->name[$k]", '', time() - 3600);

                    if ($remove_from_global)
                        unset($_COOKIE[$this->name]);
                }
            }
            return $retval;
        }

        public function serialize() {
            $details = array();
            if ($this->exists()) {
                $details['content'] = $_COOKIE[$this->name];
                $details['meta'] = session_get_cookie_params();
                return serialize($details);
            }
        }

        public function restore($_string) {
            $d = unserialize($_string);
            if (is_array($d) && isset($d['content']) && isset($d['meta'])) {
                session_set_cookie_params($d['meta']["lifetime"], $d['meta']["path"], $d['meta']["domain"], $d['meta']['secure'], $d['meta']['httponly']);
                foreach ($d['content'] as $k => $v) {
                    $this->set($k, $v);
                }
            }
        }

    }

}
?>
