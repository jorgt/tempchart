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
     * Module
     * 
     * Module is the base class for every module used by V8. It needs to be 
     * extended and implemented by actual modules. On instantiation, it will 
     * check the validity of the Router and Name. 
     * 
     * @abstract
     */
    abstract class Module {

        protected $name;
        protected $router;

        const reserved = 'Authorization|Core';

        final public function __construct(\Core\Router $_router, $_name) {
            debug('instantiating: ' . __CLASS__ . ' via ' . get_called_class());
            $this->router = $_router;
            $this->name = $_name;

            // now initialize the class that was initially called. 
            $class = get_called_class();
            $class::initialize();
        }

        /**
         * Pass calls on to the router. 
         * 
         * @param type $_name String
         * @param array $_arguments Array
         */
        public function __call($_name, $_arguments) {
            $_arguments[] = $this->name;
            call_user_func_array(array($this->router, $_name), $_arguments);
        }

        abstract public function initialize();
    }

}