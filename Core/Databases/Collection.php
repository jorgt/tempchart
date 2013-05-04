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

    use \Core\Databases\Database as database;

    class Collection {

        private static $collections = array();
        private $table;
        private $name;

        private function __construct($_name, $_table) {
            $this->table = $_table;
            $this->name = $_name;
        }

        private function isField($_field) {
            return array_key_exists($_field, (array) $this->table->byName);
        }

        public function getKeys() {
            return $this->table['keys'];
        }

        public function getFields() {
            return ($this->table['byName']);
        }

        public function numberOfEntries(Array $_where = array(), $_limit = null, \Closure $_callback = null) {
            $sql = "SELECT Count(*) FROM $this->name ";
            foreach ($_where as $k => $v) {
                $where[] = $k . '= ? ';
                $val[] = $v;
            }

            if (isset($where)) {
                $sql .= ' WHERE ' . implode(' AND ', $where);
            } else {
                $sql .= ' WHERE 1';
            }

            $sql .= (!is_null($_limit)) ? ' LIMIT ' . $_limit : '';

            $stmt = database::get()->prepare($sql);
            (isset($val)) ? $stmt->execute($val) : $stmt->execute();
            $result = $stmt->fetch();
            return $result->{"Count(*)"};
        }

        private function select(Array $_where, $_limit, \Closure $_callback = null) {
            $sql = "SELECT * FROM $this->name ";
            foreach ($_where as $k => $v) {
                $where[] = $k . '= ? ';
                $val[] = $v;
            }

            if (isset($where)) {
                $sql .= ' WHERE ' . implode(' AND ', $where);
            } else {
                $sql .= ' WHERE 1';
            }

            $sql .= (!is_null($_limit)) ? ' LIMIT ' . $_limit : '';

            $stmt = database::get()->prepare($sql);
            (isset($val)) ? $stmt->execute($val) : $stmt->execute();
            return ($_callback == null) ? $stmt : $_callback($stmt);
        }

        public function getAsEntries(Array $_where = array(), $_limit = null, \Closure $_callback = null) {
            $db = $this;
            $result = $this->select($_where, $_limit, function($_stmt) use ($db) {
                        $return = array();
                        while ($row = $_stmt->fetch()) {
                            $return[] = $db->existingEntry($row);
                        }
                        return $return;
                    });

            return is_null($_callback) ? $result : $_callback($result);
        }

        public function getAll(Array $_where = array(), $_limit = null, \Closure $_callback = null) {
            $return = false;
            foreach ($this->getAsEntries($_where, $_limit) as $row) {
                $return[] = ((array) $row->getAll(true));
            }
            return is_null($_callback) ? $return : $_callback($return);
        }

        public function getSingle(Array $_where = array(), \Closure $_callback = null) {
            $stmt = $this->select($_where, '1', null);
            if (!is_null($_callback))
                return $_callback(self::existingEntry($stmt->fetch()));
            else
                return self::existingEntry($stmt->fetch());
        }

        public function update(\Core\Databases\Entry $_entry) {
            try {
                if (!$_entry->isKeyChanged()) {
                    $sql = 'UPDATE ' . $this->name . ' SET';
                    foreach ($_entry->getAll() as $k => $v) {
                        $set[] = ' ' . $k . '=? ';
                        $val[] = $v;
                    }
                    $sql .= implode(', ', $set) . ' WHERE ';
                    foreach ($_entry->getKeys() as $k) {
                        $where[] = $k . '= ? ';
                        $val[] = $_entry->{$k};
                    }

                    $sql .= implode(' AND ', $where);
                    $stmt = database::get()->prepare($sql);

                    return ($stmt->execute($val) !== 1);
                } else {
                    return false;
                }
            } catch (PDOException $e) {
                if (DEBUG) {
                    var_dump($e);
                } else {
                    return false;
                }
            }
        }

        public function modify(\Core\Databases\Entry $_entry) {
            if ($_entry->isKeyChanged()) {
                return $this->insert($_entry);
            } else {
                return $this->update($_entry);
            }
        }

        public function insert(\Core\Databases\Entry $_entry) {
            try {
                if ($_entry->isKeyChanged()) {
                    $sql = "INSERT INTO " . $this->name;
                    $sql .= ' (' . implode(', ', array_keys($_entry->getAll())) . ')';
                    $sql .= ' VALUES ( ' . implode(', ', $this->qmArray(sizeof($_entry->getAll()))) . ')';

                    $stmt = database::get()->prepare($sql);

                    return $stmt->execute(array_values($_entry->getAll()));
                    //return $stmt->rowCount() > 0;
                } else {
                    return false;
                }
            } catch (PDOException $e) {
                if (DEBUG) {
                    var_dump($e);
                } else {
                    return false;
                }
            }
        }

        public function delete(\Core\Databases\Entry $_entry) {
            try {
                $sql = 'DELETE FROM ' . $this->name . ' WHERE ';
                foreach ($_entry->getKeys() as $k) {
                    $where[] = $k . '= ? ';
                    $val[] = $_entry->{$k};
                }

                $sql .= implode(', ', $where);
                $stmt = database::get()->prepare($sql);

                return $stmt->execute($val);
            } catch (PDOException $e) {
                if (DEBUG) {
                    var_dump($e);
                } else {
                    return false;
                }
            }
        }

        public function existingEntry($results) {
            if ($results === false) {
                return false;
            } else {
                return new Entry($this->name, $this->table, $results);
            }
        }

        public function newEntry() {
            return new Entry($this->name, $this->table);
        }

        /**
         * 
         * @param type $_name
         * @param \Core\Databases\Table $_table
         * @return boolean|\self
         * @throws \Core\Databases\DatabaseException
         */
        public static function get($_name, \Core\Databases\Table $_table = null) {
            try {
                self::strip($_name);

                if (isset(self::$collections[$_name])) {
                    return self::$collections[$_name];
                } else {

                    $created = database::get()->query(
                            "SELECT count(*) FROM sqlite_master WHERE type='table' AND name='" . $_name . "'");

                    if ($created->fetch()->{"count(*)"} == "0" && !is_null($_table)) {
                        database::createTable($_table);
                    }

                    $query = database::get()->query("PRAGMA table_info(" . $_name . ")");
                    $table = array();
                    $table['byRow'] = array();
                    $table['byName'] = array();
                    $table['keys'] = array();

                    foreach ($query as $row) {
                        $table['byRow'][$row->cid] = $row;
                        $table['byName'][$row->name] = $row;
                        if ($row->pk == 1) {
                            $table['keys'][] = $row->name;
                        }
                    }

                    if (sizeof($table['byRow']) === 0) {
                        throw new \Core\Databases\DatabaseException('Table not found: ' . $_name);
                    }

                    $models[$_name] = new self($_name, $table);
                    return $models[$_name];
                }
            } catch (PDOException $e) {
                if (DEBUG) {
                    var_dump($e);
                } else {
                    return false;
                }
            }
        }

        private static function strip(&$_var) {
            return strip_tags(stripslashes($_var));
        }

        public function rowsAffected() {
            
        }

        private function qmArray($_int) {
            $qm = array();
            $x = 0;
            while ($x++ < $_int) {
                $qm[] = '?';
            }
            return $qm;
        }

    }

} 