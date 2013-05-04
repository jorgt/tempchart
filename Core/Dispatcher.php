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

    use \Core\Config as c;

    abstract class Dispatcher {

        protected $request;
        protected $response;
        protected $route;

        final protected function __construct(\Core\Input\Request $_req, \Core\Output\Response $_res, \Core\Route $_route) {
            debug('Instantiating: ' . get_called_class(), 3);

            $this->request = $_req;
            $this->response = $_res;
            $this->route = $_route;

            $this->setConstants();
        }

        public static function get(\Core\Input\Request $_req, \Core\Output\Response $_res, \Core\Route $_route) {
            $executor = $_route->dispatch();
            switch (gettype($executor)) {
                case 'array':
                    if ($_route->module() === 'api') {
                        return new \Core\Dispatchers\DispatchToApi($_req, $_res, $_route);
                    } elseif ($_route->module() === 'authorization') {
                        return new \Core\Dispatchers\DispatchToAuthorization($_req, $_res, $_route);
                    }
                    return new \Core\Dispatchers\DispatchToController($_req, $_res, $_route);
                case 'object':
                    if (get_class($executor) === 'Closure') {
                        return new \Core\Dispatchers\DispatchToClosure($_req, $_res, $_route);
                    } else {
                        return new \Core\Dispatchers\DispatchToObject($_req, $_res, $_route);
                    }
                default:
                    new \Core\DispatchException('The executor returned from route is of the wrong type', E_USER_ERROR);
                    break;
            }
        }

        abstract public function go();

        private function setConstants() {
            c::current('module', $this->route->module());
            c::current('path_module', c::folders('path_modules') . DS . $this->route->module());
            if (is_array($this->route->dispatch())) {
                c::current('controller', $this->route->dispatch()[0]);
                if (sizeof($this->route->dispatch()) === 2) {
                    c::current('method', $this->route->dispatch()[1]);
                }
            }
        }

    }

}