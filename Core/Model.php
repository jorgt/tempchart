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
     * Model
     * 
     * Abstract class that is the base of every model used by V8. Provides a common
     * constructor and a few usefull methods. 
     * 
     * @abstract
     */
    abstract class Model {

        /**
         * The name of the model
         * 
         * @var type string
         */
        protected $name;

        /**
         * Array containing key fields
         * 
         * @var type Array
         */
        protected $keys;

        /**
         * Array containing all fields
         * 
         * @var type Array
         */
        protected $fields;

        /**
         * Does the model allow ranges to be pulled from the database?
         * 
         * @var type Boolean
         */
        protected $allowRanges;

        const protected_tables = 'random|table';

        public function __construct($_name, Array $_keys, Array $_fields, $_allowRanges = false) {
            $this->name = $_name;
            $this->keys = $_keys;
            $this->fields = $_fields;
            $this->allowRanges = $_allowRanges;
            \Core\Databases\Database::get();
        }

        /**
         * @todo this is checking custom models before instsntiating a dbmodel
         * @param type $_model
         */
        static public function get($_model, $_range) {
            $class = \Core\Config::namespaces('models') . '\\' . ucfirst($_model);
            if (class_exists($class)) {
                return new $class($_range);
            } else {
                return new \Core\Models\DbModel(strtolower($_model), $_range);
            }
        }

        /**
         * @abstract
         */
        abstract public function create(Array $_data);

        /**
         * @abstract
         */
        abstract public function read(Array $_key);

        /**
         * @abstract
         */
        abstract function update(Array $_where, Array $_data);

        /**
         * @abstract
         */
        abstract public function delete(Array $_key);

        /**
         * Checks if all not-null fields are present in the entered array
         * 
         * @param type $_array
         * @return boolean
         */
        protected function areAllRequiredFieldsPopulated($_array) {
            foreach ($this->fields as $field) {
                if ($field->notnull === '1') {
                    if (!array_key_exists($field->name, $_array)) {
                        return false;
                    }
                }
            }
            return true;
        }

        /**
         * Checks if all not-null fields are present in the entered array
         * 
         * @param type $_array
         * @return boolean
         */
        protected function areKeyFieldsPresent($_array) {
            foreach ($this->keys as $key) {
                if (!array_key_exists($key, $_array)) {
                    return false;
                }
            }
            return true;
        }

        /**
         * Throws an exception when any of the values in the array is an 
         * array itself. 
         * 
         * @param type $_array
         * @throws \Core\ModelException
         */
        protected function noMultiDimensionalArraysAllowed($_array) {
            if (count($_array) !== count($_array, COUNT_RECURSIVE)) {
                throw new \Core\ModelException('Multi dimensional arrays are not allowed', E_USER_ERROR);
            }
        }

    }

}