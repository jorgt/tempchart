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

    class Route {

        /**
         * The route that is being stored
         * 
         * @var type String
         */
        private $route;

        /**
         * The thing being executed if this route is the one matched to the 
         * current URL. It could be an object/method combo, a V8 controller, 
         * a closure... 
         * 
         * @var type 
         */
        private $controller;

        /**
         * Regex to match the route
         * 
         * @var type String
         */
        private $regex;

        /**
         * Parsed version of the controller
         * 
         * @var type 
         */
        private $dispatch;

        /**
         * Holds a module name in case this route is called via a module. 
         * 
         * @var type String
         */
        private $module = null;

        /**
         * After parsing, this holds the variables extracted from the Route/URL
         * 
         * @var type Array
         */
        private $variables = array();

        /**
         * 
         * @param type $_route the route
         * @param type $_controller the controller
         * @param type $_module optional name of the module
         */
        public function __construct($_route, $_controller, $_module = null) {
            debug('Instantiating: ' . __CLASS__, 3);
            $this->route = $_route;
            $this->controller = $_controller;
            $this->module = $_module;
            $this->regex = '/^' . implode(array_map(array($this, 'getRegex'), explode('/', $_route)), '\/') . '$/';
        }

        /**
         * Matches a URL against the route stored inside and sets up the dispatcher array
         * 
         * @param type $_url
         * @return boolean
         */
        public function match($_url) {
            if (preg_match($this->regex, $_url, $m)) {
                array_shift($m);
                $this->variables = array_combine($this->variables, $m);
                if (gettype($this->controller) === 'string') {
                    $controller = explode('/', $this->controller);
                    $controllerUpper = array_map('ucfirst', $controller);
                    $this->dispatch = array_map(array($this, 'replaceVariable'), $controllerUpper);
                } elseif (gettype($this->controller) === 'object') {
                    $this->dispatch = $this->controller;
                }
                return true;
            } else {
                return false;
            }
        }

        /**
         * 
         * @return type Array
         */
        public function variables() {
            return $this->variables;
        }

        /**
         * 
         * @return type String
         */
        public function regex() {
            return $this->regex;
        }

        /**
         * 
         * @return type String
         */
        public function Route() {
            return $this->route;
        }

        /**
         * 
         * @return type String
         */
        public function controller() {
            return $this->controller;
        }

        /**
         * 
         * @return type Array
         */
        public function dispatch() {
            return $this->dispatch;
        }

        /**
         * 
         * @return type String
         */
        public function module() {
            return $this->module;
        }

        /**
         * Turns a chunk of route into regex. 
         * 
         * @param type $_var
         * @return string
         */
        private function getRegex($_var) {
            if ($this->isVariable($_var)) {
                $this->variables[] = substr($_var, 1);
                return '([A-Za-z0-9_-]*)';
            } else {
                return $_var;
            }
        }

        /**
         * Substitutes a variable if it exists
         * 
         * @param type $_var
         * @return type String
         */
        private function replaceVariable($_var) {
            if ($this->isVariable($_var)) {
                return $this->variables[substr($_var, 1)];
            } else {
                return $_var;
            }
        }

        /**
         * Confirms or denies if a chunk of route is a variable
         * @param type $_var
         * @return type
         */
        private function isVariable($_var) {
            return (strpos($_var, ':') === 0);
        }

    }

}