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
/**
 * Description of user
 *
 * @author jthuijls
 */

namespace Core\Helpers {

    use \Core\database\databases as db,
        \Core\Databases\Collection as model;

    class User {

        private $dbname = null;
        private $name = null;

        public function __construct() {
            $this->dbname = sha1('users');
            db::register($this->dbname);
            db::get($this->dbname);
        }

        public function exists() {
            return ($this->user !== false || is_null($this->user));
        }

        public function getName() {
            return $this->name;
        }

        public function isLoggedIn() {
            sec_session_start();
            // Check if all session variables are set

            if (isset($_SESSION['user_id'], $_SESSION['username'], $_SESSION['login_string'])) {
                $user_id = $_SESSION['user_id'];
                $login_string = $_SESSION['login_string'];
                $ip_address = $_SERVER['REMOTE_ADDR']; // Get the IP address of the user. 
                $user_browser = $_SERVER['HTTP_USER_AGENT']; // Get the user-agent string of the user.
                $stmt = db::get()->prepare("SELECT password FROM users WHERE id = ? LIMIT 1");
                if ($stmt !== false) {
                    $stmt->bindParam(1, $user_id, \PDO::PARAM_INT); // Bind "$user_id" to parameter.
                    $stmt->execute(); // Execute the prepared query.
                    $res = $stmt->fetch(\PDO::FETCH_OBJ);
                    if ($res !== false) { // If the user exists
                        $login_check = strtoupper(hash('sha512', strtoupper($res->password) . $ip_address . $user_browser));
                        if ($login_check === $login_string) {
                            $this->name = $_SESSION['username'];
                            return true;
                        }
                    }
                }
            }
            return false;
        }

        public function logout() {
            $this->sec_session_start();
            $_SESSION = array();
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
        }

    }

}
?>
