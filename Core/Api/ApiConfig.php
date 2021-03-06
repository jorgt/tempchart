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

namespace Core\Api {

    class ApiConfig extends \Core\Module {

        public function initialize() {
            //create           
            $this->post('api/:db/:model', 'api/create');
            $this->post('api/:db/:model/', 'api/create');

            //read 
            $this->get('api/:db/:model', 'api/read');
            $this->get('api/:db/:model/', 'api/read');
            $this->get('api/:db/:model/:key1', 'api/read');
            $this->get('api/:db/:model/:key1/:key2', 'api/read');
            $this->get('api/:db/:model/:key1/:key2/:key3', 'api/read');

            //update
            $this->put('api/:db/:model', 'api/update');
            $this->put('api/:db/:model/', 'api/update');
            $this->put('api/:db/:model/:key1', 'api/update');
            $this->put('api/:db/:model/:key1/:key2', 'api/update');
            $this->put('api/:db/:model/:key1/:key2/:key3', 'api/update');

            //delete
            $this->delete('api/:db/:model', 'api/delete');
            $this->delete('api/:db/:model/', 'api/delete');
            $this->delete('api/:db/:model/:key1', 'api/delete');
            $this->delete('api/:db/:model/:key1/:key2', 'api/delete');
            $this->delete('api/:db/:model/:key1/:key2/:key3', 'api/delete');
        }

    }

}
?>