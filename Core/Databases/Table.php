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

    class Table {

        private $_name;
        private $fields = array();

        public function __construct($_name) {
            $this->name = $_name;
        }

        public function field($_name, $_type, $_key = false, $_notNull = true, $_unique = false) {
            $_type = strtoupper($_type);
            $return = new \stdClass();
            if (!preg_match('/(NULL|INTEGER|INT|REAL|TEXT|BLOB|VARCHAR)/', $_type) || !is_bool($_key) || !is_bool($_unique) || !is_bool($_notNull)) {

                throw new \InvalidArgumentException('DATABASE:: Check your entry');
            }

            $return->type = $_type;
            $return->key = $_key;
            $return->unique = $_unique;
            $return->notNull = $_notNull;

            $this->fields[$_name] = $return;
        }

        public function getFields() {
            return $this->fields;
        }

        public function getName() {
            return $this->name;
        }

    }

}