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

    class Entry {
        /*
         * 
         */

        private $fields = null;
        private $name = null;
        private $initialValues;
        private $keyChanged = false;
        private $keys = null;
        private $asArray = true;

        public function __construct($_name, $_fields, $_entries = array()) {
            $this->fields = $_fields;
            $this->name = $_name;
            foreach ($_entries as $k => $v) {
                $this->initialValues[$k] = $v;
            }
            $this->keys = $_fields['keys'];
        }

        public function load(Array $_entries) {
            $this->keyChanged = true;
            foreach ($_entries as $k => $v) {
                $this->initialValues[$k] = $v;
            }
        }

        public function setAsArray(Boolean $_asArray) {
            $this->asArray = $_asArray;
        }

        public function __set($_var, $_val) {

            if (!array_key_exists($_var, (array) $this->fields['byName'])) {
                throw new \InvalidArgumentException('There is no variable named: ' . $_var);
            } else {
                $this->setVariable($_var, $_val);
            }
        }

        public function __get($_var) {
            return $this->getVariable($_var);
        }

        public function getAll($_asArray = null) {
            return $this->returnIt($this->initialValues, $_asArray);
        }

        public function getKeys($_asArray = null) {
            return $this->returnIt($this->keys, $_asArray);
        }

        private function returnIt($_toReturn, $_asArray) {
            if (is_null($_asArray))
                $_asArray = $this->asArray;

            return ($_asArray) ? $_toReturn : (object) $_toReturn;
        }

        private function getVariable($_var) {
            return $this->initialValues[$_var];
        }

        private function setVariable($_var, $_arguments) {
            // this takes all the current values, and re-sorts them in the order
            // the database expects them. this way, it doesn't matter in what
            // order you populate the entry values. 
            $oldArray = $this->initialValues;
            $newArray = array();

            // this sets the keyChanged flag if the var being set is in keys
            // flag will be used to determine if an insert or update needs to 
            // take place.
            if (in_array($_var, $this->keys)) {
                $this->keyChanged = true;
            }

            $oldArray[$_var] = $_arguments;

            foreach (array_keys($this->fields['byName']) as $v) {
                if (array_key_exists($v, $oldArray)) {
                    $newArray[$v] = $oldArray[$v];
                }
            }
            $this->initialValues = $newArray;
        }

        public function isKeyChanged() {
            return $this->keyChanged;
        }

    }

}