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
namespace Core\Databases {

    final class Database {

        private static $db = null;
        private static $registered = array();

        private function __construct() {
            
        }

        private function __clone() {
            die();
        }

        public static function get($_name = false, $_create = true) {
            $name = \Core\Config::folders('path_databases') . DS . $_name . '.sqlite';
            if ($_create === false && realpath($name) === false) {
                throw new \ErrorException('UNFORCED MODE: Database does not exist - '.$_name);
            } else {
                if (!isset(self::$registered[$_name]) && $_name !== false) {
                    try {
                        self::$db = new \PDO('sqlite:' . $name);
                        self::$db->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_OBJ);
                        self::$db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_SILENT);
                        self::$registered[$_name] = self::$db;
                        debug('Setting up DB: ' . $_name, 1);
                        return self::$db;
                    } catch (\PDOException $e) {
                        echo $e->getMessage();
                    }
                } elseif ($_name !== false) {
                    self::$db = self::$registered[$_name];
                }
                debug('Getting database: ' . $_name, 3);
                return self::$db;
            }
        }

        public static function error() {
            return self::$db->errorInfo();
        }

        public static function createTable(\Core\Databases\Table $_table) {
            $sql = 'CREATE TABLE IF NOT EXISTS ' . self::strip($_table->getName()) . ' (';
            $key = array();
            $uni = array();
            $flds = array();
            foreach ($_table->getFields() as $field => $a) {
                self::strip($field);

                if ($a->type == 'INT' && $a->key) {
                    $flds[] = ($field . ' ' . 'INTEGER PRIMARY KEY AUTOINCREMENT');
                } else {
                    $flds[] = ($field . ' ' . self::strip($a->type)) .
                            (($a->notNull) ? ' NOT NULL' : '');

                    ($a->key) ? $key[] = $field : false;
                }
                ($a->unique) ? $uni[] = $field : false;
            }

            $sql .= implode(', ', $flds);
            if (!empty($key)) {
                $sql .= ', PRIMARY KEY (' . implode(', ', $key) . ')';
            }
            if (!empty($uni)) {
                $sql .= ', UNIQUE (' . implode(', ', $uni) . ')';
            }
            $sql .= ')';
            if (DEBUG) {
                $success = (self::$db->exec($sql) == 0);
                if ($success) {
                    debug('Table "' . $_table->getName() . '" exists or has been created');
                } else {
                    debug('Table "' . $_table->getName() . '" creation failed');
                }
                return $success;
            } else {
                return (self::$db->exec($sql) == 0);
            }
        }

        private static function strip($_var) {
            return strip_tags(stripslashes($_var));
        }

    }

}