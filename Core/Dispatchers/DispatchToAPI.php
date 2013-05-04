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
namespace Core\Dispatchers {

    class DispatchToApi extends \Core\Dispatcher {

        public function go() {
            $dispatch = $this->route->dispatch();
            $class = '\Core\api\Controllers\\'.array_shift($dispatch);
            if (class_exists($class)) {
                $this->request->addRouteParameters($this->route->variables());
                $controller = array(new $class($this->request, $this->response, $this->route->module()), array_shift($dispatch));
                if (is_callable($controller)) {
                    debug('Dispatching to: "' . $class . '::' . $controller[1] . '"');
                    call_user_func_array($controller, $this->route->variables());
                } else {
                    error('The controller\'s method is not found: ' . $controller[1], E_USER_ERROR);
                }
            } else {
                error('Class not found for dispatching: ' . $class);
            }
        }

    }

}