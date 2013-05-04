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
    /*
     * <form>	 Defines an HTML form for user input
      <input>	Defines an input control
      <textarea>	Defines a multiline input control (text area)
      <label>	Defines a label for an <input> element
      <fieldset>	Groups related elements in a form
      <legend>	Defines a caption for a <fieldset> element
      <select>	Defines a drop-down list
      <optgroup>	Defines a group of related options in a drop-down list
      <option>	Defines an option in a drop-down list
      <button>	Defines a clickable button
      <datalist>New	Specifies a list of pre-defined options for input controls
      <keygen>New	Defines a key-pair generator field (for forms)
      <output>New	Defines the result of a calculation
     */

    abstract class Form {

        private static $form = array();

        public static function open($_location, Array $_attr = null) {
            $attr = '';
            if (!is_null($_attr)) {
                $attr .= self::arrayToString($_attr);
            }

            $open = '<form method="post" accept-charset="utf-8"';
            $open .= ' action="' . $_location . '" ' . $attr . ' >';
            self::$form[] = $open;
            return $open;
        }

        public static function createInput($_attr, $_value = null) {
            return self::add(self::createInputWithType('text', $_attr, $_value));
        }

        public static function createPassword($_attr, $_value = null) {
            return self::add(self::createInputWithType('password', $_attr, $_value));
        }

        public static function createRadioButton($_attr, $_value) {
            return self::add(self::createInputWithType('radio', $_attr, null).$_value);
        }

        public static function createButton($_attr, $_value) {
            return self::add(self::createInputWithType('submit', $_attr, $_value));
        }

        private static function createInputWithType($_type, $_attr, $_value) {
            $input = '<input type="' . $_type . '" ';
            if (is_array($_attr)) {
                $input .= self::arrayToString($_attr) . ' />';
            } else {
                $input .= (is_null($_attr)) ? '' : 'name="' . $_attr . '" ';
                $input .= (is_null($_value)) ? '' : 'value="' . $_value . '" ';
                $input .= '/>';
            }

            return $input;
        }

        public static function createLabel($_for, $_value) {
            return self::add('<label for="' . $_for . '">' . $_value . '</label>');
        }

        private static function arrayToString(Array $_array) {
            $return = '';
            foreach ($_array as $k => $v) {
                $return .= $k . '="' . $v . '" ';
            }
            return $return;
        }
        
        private static function add($_element) {
            self::$form[] = $_element;
            return $_element;
        }
        
        public static function addBreak() {
            self::$form[] = '<br>';
            return '<br>';
        }

        public static function build() {
            self::$form[] = '</form>';
            return implode("\n", self::$form);
        }
    }

}
