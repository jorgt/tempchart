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

    class User {

        static private $_singleton;
        //ENCRYPTION CENTER
        private $domain_code = '';
        private $today_ts = '';
        private $today_m = '';
        private $error_message = NULL;
        private $users = '';
        private $num_1 = '';
        private $num_2 = '';
        private $num_3 = '';
        private $username = '';
        private $expire = 86400; // in seconds. 60 * 60 * 24 = one day.
        private $key = 'AAdasdf3e3fra';
        private $domain_code = 'hosanna'; //Alpha Numeric and no space
        private $random_num_1 = 211;  //Pick a random number between 1 to 500
        private $random_num_2 = 765;  //Pick a random number between 500 to 1000
        private $random_num_3 = 3;  //Pick a random number between 1 to 3

        protected function __construct() {
            parent::__construct();
            debug("Instantiating!");
        }

        static function singleton() {
            if (!is_object(self::$_singleton)) {
                self::$_singleton = new self();
            }

            return self::$_singleton;
        }

        function set_domain_code($d) {
            $this->domain_code = $d;
        }

        function set_today_ts($t) {
            $this->today_ts = $t;
        }

        function set_today_m($t) {
            $this->today_m = $t;
        }

        function set_error_message($t) {
            $this->error_message = $t;
        }

        function set_num_1($t) {
            $this->num_1 = $t;
        }

        function set_num_2($t) {
            $this->num_2 = $t;
        }

        function set_num_3($t) {
            $this->num_3 = $t;
        }

        function set_username($t) {
            $this->username = $t;
        }

        function set_expire($t) {
            $this->expire = $t;
        }

        function verify_settings() {
            $verified = TRUE;

            //Num 1 between 1 - 500
            if ($this->num_1 < 1 || $this->num_1 > 500)
                $verified = FALSE;
            elseif ($this->num_2 < 500 || $this->num_2 > 1000)
                $verified = FALSE;
            elseif ($this->num_3 < 1 || $this->num_3 > 5)
                $verified = FALSE;

            return $verified;
        }

        function encryption_key($user) {
            //Encryption Key One
            $key_uid = $this->user_encryption($user);

            //Encrption Key Two
            $key_cid = $this->code_encryption($key_uid);

            //Set Keys

            setcookie($this->domain_code . '_uid', $key_uid, time() + $this->expire);
            setcookie($this->domain_code . '_cid', $key_cid, time() + $this->expire);
        }

        function user_encryption($user) {
            //Array of Characters
            return hash("sha1", $user);
        }

        function code_encryption($key_cid, $encrypt = 1) {
            if ($encrypt == 1) {
                $key_code = preg_replace('/([^0-9+])/', '', $key_cid);

                switch ($this->num_3) {
                    case 1:
                        $key_code = floor((($key_code + $this->num_2 + (($this->num_1 * 2) * $this->num_2)) / $this->num_1) / $this->num_2);
                        break;
                    case 2:
                        $key_code = ceil(((($this->num_2 + $this->num_1) * $this->num_1 + $key_code + $this->num_2 - (10 * $this->num_1)) / ($this->num_1 * 50)) / 100000000);
                        break;
                    case 3:
                        $key_code = floor((((($key_code - $this->num_2 + (($this->num_1 * 3) * $this->num_2)) + $this->num_1) / $this->num_2)) / 100000000);
                        break;
                }

                $key_code = substr($key_code, 0, 10);

                return $key_code;
            }
        }

        function check_login($username, $password) {
            //Check Login
            $stmt = self::prepare("SELECT * FROM " . TBL_USER .
                            " WHERE username = :username" .
                            " AND   password = :password");

            $stmt->bindParam(":username", strip_tags($username), PDO::PARAM_STR);
            $stmt->bindParam(":password", strip_tags($password), PDO::PARAM_STR);

            try {
                $result = $stmt->execute();
                $user = $stmt->fetch(PDO::FETCH_OBJ);
            } catch (PDOException $e) {
                trigger($e);
            }
            if (is_object($user)) {
                self::update(TBL_USER, array('session_id' => session_id()), array('username' => strip_tags($username)));
                return true;
            } else {
                return false;
            }
        }

        function verify_login($key_uid, $key_cid) {
            //Check Login
            if ($key_cid = $this->code_encryption($key_uid)) {
                //Validate Username Is True
                try {
                    $result = self::select(TBL_USER, array('username' => $_SESSION['username'],
                                ' AND session_id' => session_id()));
                } catch (PDOException $e) {
                    trigger($e);
                }
                if ($result !== false) {
                    debug("login verified.");
                    return true;
                } else {
                    debug("login not verified.");
                    return false;
                }
            }

            return FALSE;
        }

        function error_login() {
            if (isset($this->error_message)) {
                return $this->error_message;
            }
        }

        function cleanse_input($input) {
            //Trim
            $input = trim($input);

            if (get_magic_quotes_gpc() == 1) {
                //Null
            } else {
                //Escape Codes
                $input = addslashes($input);
            }

            //If Html Entities
            $input = htmlentities($input);

            return $input;
        }

        public function login() {
            $today_ts = strtotime("now");
            $today_m = date('n', $today_ts);
            $pass_login = FALSE;

            $this->set_domain_code($domain_code);
            $this->set_today_ts($today_ts);
            $this->set_today_m($today_m);
            $this->set_num_1($random_num_1);
            $this->set_num_2($random_num_2);
            $this->set_num_3($random_num_3);

            //Verify
            if (!$this->verify_settings()) {
                trigger('<strong>Invalid Admin Settings for Login Script</strong><br />
			Check your settings and retry logging in');
                exit();
            }

            //Logged In
            if (isset($_COOKIE[$domain_code . '_uid']) && $_COOKIE[$domain_code . '_uid'] != '' && isset($_COOKIE[$domain_code . '_cid']) && $_COOKIE[$domain_code . '_cid'] != '') {

                $key_uid = $this->cleanse_input($_COOKIE[$domain_code . '_uid']);
                $key_cid = $this->cleanse_input($_COOKIE[$domain_code . '_cid']);

                if (!$this->verify_login($key_uid, $key_cid)) {
                    $this->set_error_message('Login has expired');
                } else {
                    $pass_login = TRUE;
                }
            }

            //Verify Logged In Credentials
            if (!$pass_login) {
                debug("failed the cookie check: not already logged in.");
                $need_login = TRUE;
                //Trying To Login
                if (isset($_POST['login'])) {
                    //Verify Login
                    $this->_user = $this->cleanse_input($_POST['username']);
                    $this->_pass = $this->cleanse_input($_POST['password']);

                    session_start();
                    if (DEBUG !== true)
                        $_SESSION['logins'] = $_SESSION['logins'] + 1;
                    else
                        $_SESSION['logins'] = 0;

                    if (!isset($_SESSION['logins']) || $_SESSION['logins'] > 2) {

                        $this->set_error_message('To many failed attempts. Please wait 1 minute.');
                        $need_login = TRUE;
                        if (time() - $_SESSION['time'] > 60) {
                            $_SESSION['logins'] = 0;
                        }
                    } else {
                        $_SESSION['time'] = time();
                        //Check Login
                        if ($this->check_login($this->_user, $this->_pass)) {
                            //Encode
                            $this->encryption_key($this->_user);

                            $need_login = FALSE;
                            $_SESSION['username'] = $this->_user;
                        } else {
                            $this->set_error_message('Invalid login username and password');
                            $need_login = TRUE;
                        }
                    }
                }

                //Login Page
                if ($need_login) {
                    // display login
                    exit();
                }
            }
        }

        public function logout() {
            //self::update(TBL_USER, array('session_id' => ''), array('username' => $this->username));

            setcookie($domain_code . '_uid');
            setcookie($domain_code . '_cid');
            //redirect('/');
        }

    }

}