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
    require 'Functions.php';
    require 'ClassLoader.php';
    require 'Exceptions.php';
    require 'Debug.php';
    require 'Config.php';


    use \Core\Config as c;

    /**
     * Bootstrap the App. 
     * 
     * Application bootstraps the web app. It will instantiate all necessary bits
     * and pieces like the request, the response, the routes and the dispatchers. 
     */
    class Application {

        /**
         *
         * @var type \Core\Input\Request
         */
        protected $request;
        
        /**
         *
         * @var type \Core\Output\Response
         */
        protected $response;
        
        /**
         *
         * @var type \Core\Router
         */
        protected $router;
        
        /**
         *
         * @var type \Core\Dispatcher
         */
        protected $dispatcher;
        
        /**
         *
         * @var type \Core\ClassLoader
         */
        protected $classLoader;
        
        const http_methods_allowed = 'put|get|post|delete|head';

        /**
         * Create an application. Bootstraps everything. 
         */
        public function __construct() {
            self::def('DS', DIRECTORY_SEPARATOR);
            self::def('_BASE', realpath(__DIR__ . DS . '..' . DS));
            $this->setClassLoader();
              
            $this->request = new \Core\Input\Request();
            if ($this->request->isAJAX()) {
                self::def('AJAX', true);
            } else {
                self::def('AJAX', false);
            }

            c::load();
            if (AJAX) {
                if(c::debugging('debug')) {
                    c::debugging('to_file', true);
                    c::debugging('to_screen', false);
                }
            }
            \Core\Debug::initialize(
                    c::debugging('debug'), 
                    c::debugging('log_detail_level'), 
                    c::debugging('collect'), 
                    c::debugging('to_file'), 
                    c::debugging('to_screen'), 
                    c::folders('path_logs'));

            self::def('_URL', c::installation('url'));

            debug('Instantiating: ' . get_called_class(), 3);

            $this->setErrorAndDebugging();


            $this->setExceptionHandler();
            $this->response = \Core\Output\Response::get($this->request);
            $this->router = new \Core\Router();
            \Core\Databases\Database::get(c::installation('default_database'));

            if (c::modules('authenticate')) {
                $this->router->registerModule('Authenticate');
            }

            if (c::modules('api')) {
                $this->router->registerModule('Api');
            }
        }

        /**
         * 
         * @return type \Core\Router
         */
        public function router() {
            return $this->router;
        }

        /**
         * 
         * @return type \Core\Input\Request
         */
        public function request() {
            return $this->request;
        }

        /**
         * 
         * @return type \Core\Output\Response
         */
        public function response() {
            return $this->response;
        }

        /**
         * Start the web application
         * 
         * GO is the method that gets call when all routes are set up and modules
         * registered. This method will match the current URL against all routes
         * and execute the configured methods. 
         */
        public function go() {
            debug('Starting the engine', 1);
            $route = $this->router->go($this->request);
            if ($route !== false) {
                $this->dispatcher = \Core\Dispatcher::get($this->request, $this->response, $route);
                $this->dispatcher->go($route);
                die();
            } else {
                $this->response->headerResponseCode(404);
            }

            // either redirect to the error page, or die with a json message. 
            if ($this->request->isAJAX()) {
                die(json_encode('The requested URL could not be loaded. Check URL and Request method.'));
            } else {
                $this->response->redirect('/error.html');
            }
        }

        /**
         * Sets the error levels and debugging variables
         */
        protected function setErrorAndDebugging() {
            error_reporting(c::debugging('error_level'));
            ini_set('display_errors', c::debugging('display_errors'));
        }

        /**
         * Sets the exception handler
         */
        protected function setExceptionHandler() {
            \Core\Exceptions::initiate(c::debugging('log_detail_level'));
        }

        /**
         * Sets the class loader
         */
        protected function setClassLoader() {
            $this->classLoader = new \Core\ClassLoader(_BASE);
            $this->classLoader->register();
        }

        /**
         * 
         * @static
         * @param type $_constant the name of the constant
         * @param type $_value the value of the constant
         */
        public static function def($_constant, $_value) {
            if (!defined($_constant)) {
                define(strtoupper($_constant), $_value, true);
            }
        }

        /**
         * On destruction of the instance, the debugger dumps lines to the
         * screen if configured that way. 
         */
        public function __destruct() {
            echo \Core\Debug::dump();
        }

    }

}