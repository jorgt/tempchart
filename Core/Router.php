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

    use \Core\Route as route,
        \Core\Config as c;

    /**
     * 
     */
    class Router {

        /**
         * Holds all GET routes
         * 
         * @var type Array
         */
        private $get = array();

        /**
         * Holds all POST routes
         * 
         * @var type Array
         */
        private $post = array();

        /**
         * Holds all PUT routes
         * 
         * @var type Array
         */
        private $put = array();

        /**
         * Holds all DELETE routes
         * 
         * @var type Array
         */
        private $delete = array();

        /**
         * Holds all HEAD routes
         * 
         * @var type Array
         */
        private $head = array();

        /**
         * Holds all modules currently registered with the router
         * 
         * @var type Array
         */
        private $registeredModules = array();

        /**
         * Instantiates a Router
         */
        public function __construct() {
            debug('Instantiating: ' . __CLASS__, 3);
        }

        /**
         * Go! This will start matching all available routes against the 
         * current, actual request method and URL. 
         * 
         * @param \Core\Input\Request $_req
         * @return \Core\Route
         */
        public function go(\Core\Input\Request $_req) {
            $url = $_req->parameters()['url']['url'];
            $rm = $_req->request_method;
            debug('Start parsing URL: "' . $url . '"');
            foreach ($this->{$rm} as $route) {
                if ($route->match($url)) {
                    debug('Found a match: "' . $route->route(), 1);
                    return $route;
                    exit();
                }
            }
            debug('No match found: "' . $url, 1);
            return false;
        }

        /**
         * Some abstraction around feeding routes into the proper arrays per
         * request method. 
         * 
         * @param type $_name is the name of a request method
         * @param type $_args request route|controller|(optional) module. 
         */
        public function __call($_name, $_args) {
            $m = \Core\Application::http_methods_allowed;
            if (in_array($_name, explode('|', $m))) {
                if (sizeof($_args) === 2) {
                    $this->addToRoute($_name, $_args[0], $_args[1]);
                } elseif (sizeof($_args) === 3) {
                    $this->addToRoute($_name, $_args[0], $_args[1], $_args[2]);
                }
            } else {
                call_user_func_array(array($this, $_name), $_args);
            }
        }

        /**
         * Register a route and controller
         * @param type $_method
         * @param type $_route
         * @param type $_controller
         * @param type $_module
         */
        public function multi($_method, $_route, $_controller, $_module = null) {
            $this->addToRoute($_method, $_route, $_controller, $_module);
        }

        private function addToRoute($_type, $_route, $_method, $_module = null) {
            foreach (explode('|', $_type) as $type) {
                if (!in_array($type, explode('|', \Core\Application::http_methods_allowed))) {
                    throw new \Core\RouterException('Request Method "' . $type . '" is not supported', E_USER_ERROR);
                }
                if (is_array($this->{$type})) {
                    $this->{$type}[] = new route($_route, $_method, $_module);
                }
            }
        }

        public function registerModule($_module) {
            // find class and run settings thing
            $class = c::namespaces('core') . '\\' . ucfirst($_module) . '\\' . ucfirst($_module) . 'Config';
            if (!class_exists($class)) {
                $class = c::namespaces('modules') . '\\' . ucfirst($_module) . '\\' . ucfirst($_module) . 'Config';
            }
            $this->module($class, ucfirst($_module));
        }

        private function module($_class, $_name) {
            if (!in_array($_name, $this->registeredModules)) {
                if (class_exists($_class)) {
                    new $_class($this, $_name);
                } else {
                    throw new \Core\RouterException('Module not found: '.$_name, E_USER_ERROR);
                }
            } else {
                throw new \Core\RouterException('Module already registered: '.$_name, E_USER_ERROR);
            }
        }

    }

}