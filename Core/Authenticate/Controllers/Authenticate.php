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
 * @todo create a full authentication experience as a Core module. Copy from old feature
 * try to keep it lean, no default HTML stuff echoed or returned. Ppl can worry about that 
 * themselves. 
 */

namespace Core\Api\Controllers {

    use \Core\Databases\Collection as model,
        \Core\Databases\Database as db;

    class authenticate extends \Core\Controllers\NonAuthorized {

        public function __construct($_req, $_res, $_vars) {
            parent::__construct($_req, $_res, $_vars);
            $this->dbname = sha1('users');
            db::register($this->dbname);
            db::get($this->dbname);

            $userTable = db::table();
            $attemptsTable = db::table();

            $userTable->id = db::newTableField('INT', true);
            $userTable->name = db::newTableField('VARCHAR');
            $userTable->password = db::newTableField('VARCHAR');
            $userTable->salt = db::newTableField('VARCHAR');
            $userTable->session = db::newTableField('VARCHAR', false, false);

            $attemptsTable->id = db::newTableField('INTEGER');
            $attemptsTable->time = db::newTableField('INTEGER');

            if (!db::createTable('users', $userTable) || !db::createTable('attempts', $attemptsTable)) {
                die('Could not create user tables');
            };
        }

        public function login() {
            
        }

        public function logout() {
            sec_session_start();
            $_SESSION = array();
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $p["path"], $p["domain"], $p["secure"], $p["httponly"]);

            $this->response()->send(json_encode(true));
        }

        public function verify() {
            $model = model::get('users');
            $user = $model->getSingle(array('name' => $this->request()->post->name));
            $_password = hash('sha512', strtoupper($this->request()->post->password) . 'saltysalt');

            sec_session_start();

            if (($user !== false) && $user->id > 0) {
                // We check if the account is locked from too many login attempts
                if ($this->checkBrute($user->id)) {
                    $result = 2;
                } else {
                    $user_id = preg_replace("/[^0-9]+/", "", $user->id);
                    if (strtoupper($user->password) === strtoupper($_password)) {
                        // Password is correct!
                        $ip_address = $_SERVER['REMOTE_ADDR']; // Get the IP address of the user. 
                        $user_browser = $_SERVER['HTTP_USER_AGENT']; // Get the user-agent string of the user.
                        $_SESSION['user_id'] = $user_id;
                        $username = preg_replace("/[^a-zA-Z0-9_\-]+/", "", $user->name);
                        $_SESSION['username'] = $username;
                        $_SESSION['login_string'] = strtoupper(hash('sha512', strtoupper($_password) . $ip_address . $user_browser));
                        // Login successful.
                        $result = 0;
                    } else {
                        // Password is not correct
                        // We record this attempt in the database
                        $now = time();
                        db::get()->query("INSERT INTO attempts (id, time) VALUES ('$user_id', '$now')");
                        $result = 3;
                    }
                }
            } else {
                // No user exists. 
                $result = 1;
            }

            $this->response()->send(json_encode($result));
        }

        private function checkBrute() {
            // All login attempts are counted from the past 2 hours. 
            $valid_attempts = time() - (2 * 60 * 60);
            $stmt = db::get()->prepare("SELECT COUNT(time) FROM attempts WHERE id = ? AND time > '$valid_attempts'");
            if ($stmt !== false) {
                $stmt->bindParam(1, $_id, \PDO::PARAM_INT);
                $stmt->execute();
                $result = $stmt->fetch();
                // If there has been more than 5 failed logins
                if ($result->{"COUNT(time)"} > 5) {
                    return true;
                } else {
                    return false;
                }
            }
        }

    }

}
?>