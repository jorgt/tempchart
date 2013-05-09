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
/**
 * @todo: fix some sort of key exchange in there, matching domain to key. 
 */

namespace Core\Api\Controllers {

    /**
     * @todo abstract db crud. assume random stuff. leave todo specific stuff out
     */
    class Api extends \Core\Controllers\NonAuthorized {

        private $params;
        private $model;
        private $allowRange;

        public function __construct($_req, $_res, $_vars) {
            if (!AJAX) {
                error('The API is only accessible through XMLHTTPRequest', E_USER_ERROR);
            }

            $this->allowRange = true;

            parent::__construct($_req, $_res, $_vars);

            debug('API taking over', 1);
            $this->params = $this->request->parameters();
            try {
                \Core\Databases\Database::get($this->params['url']['db'], false);
            } catch (\ErrorException $e) {
                $this->response->headerResponseCode(200);
                throw $e;
            }
            $this->model = \Core\Model::get($this->params['url']['model'], $this->allowRange);
        }

        protected function getKeysFromURI() {
            $keys = $this->model->getKeys();
            $where = array();
            for ($i = 1; $i <= sizeof($keys); $i++) {
                $p = 'key' . $i;
                if (isset($this->params['url'][$p])) {
                    $where[$keys[$i - 1]] = $this->params['url'][$p];
                }
            }
            return (sizeof($where) > 0) ? $where : false;
        }

        protected function getWhereFromParameters() {
            $fields = $this->model->getFields();
            $params = $this->request->parameters();
            $where = array();
            foreach ($fields as $field) {
                if (array_key_exists($field->name, (array) $params['url'])) {
                    $where[$field->name] = $params['url'][$field->name];
                }elseif (array_key_exists($field->name, (array) $params['input'])) {
                    $where[$field->name] = $params['input'][$field->name];
                }
            }
            return $where;
        }

        public function create() {
            $params = (array)$this->request->parameters();
            $new = $params['input'];
            $db = \Core\Databases\Database::get();
            $this->model->create($new);
            $this->response->send(array('id'=>$db->lastInsertId()));
        }

        public function read() {
            $where = $this->getKeysFromURI();

            if ($where === false) {
                $where = $this->getWhereFromParameters();
            }

            $results = array($this->params['url']['model'] => $this->model->read($where));
            $this->response->send($results);
        }

        public function update() {
            $params = (array)$this->request->parameters();
            $data = $params['input'];
            $keys = $this->model->getKeys();
            foreach($keys as $k) {
                $where[$k] = $data[$k];
                unset($data[$k]);
            }
            $this->response->send($this->model->update($where,$data));
        }

        public function delete() {
           $params = $this->request->parameters()['input'];
            
            $this->response->send($this->model->delete($params));
            
        }

    }

}
?>