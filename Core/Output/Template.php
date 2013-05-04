<?php

namespace Core\Output {

    use \Core\Config as c;

    class template extends \Core\ViewAbstract {

        /**
         * Constructor. There will be only one response class, because 
         * there should be only one entity dealing with the browser. This object
         * will hold the entire rendered HTML, the result of all rendered views
         * and templates. 
         */
        public function __construct() {
            debug('Instantiating: ' . __CLASS__, 3);
            $this->setDefaultTemplate();
            $this->vars['javascript'] = '';
            $this->vars['css'] = '';
        }

        public function render() {
            die(parent::render());
        }

        public function addCSS($_css) {
            $this->vars['css'] = $this->vars['css'] . '<link rel="stylesheet" href="' . $_css . '">';
        }

        public function addJS($_js) {
            $this->vars['javascript'] = $this->vars['javascript'] . '<script src="' . $_js . '"></script>';
        }

        /**
         * Sets the template the class will use to render output. If the file 
         * is not already set, it'll first take module\index.html as default, 
         * if that's not found it'll take 
         * @param type $_file
         */
        private function setDefaultTemplate() {
            $_file = false;
            //find a template in the current module or controller if available
            if (!is_null(c::current('path'))) {
                $path = c::current('path') . DS . 'templates' . DS;

                if (!is_null(c::current('module')) && !is_null(c::current('method'))) {
                    if (!$_file && is_null($this->file))
                        $_file = realpath($path . c::current('module') . '_' . c::current('method') . '.phtml');
                }

                if (!is_null(c::current('controller'))) {
                    if (!$_file && is_null($this->file))
                        $_file = realpath($path . c::current('controller') . '.phtml');
                }

                if (!$_file && is_null($this->file))
                    $_file = realpath($path . DS . 'index.phtml');

                if (!is_null(c::current('module'))) {
                    if (!$_file && is_null($this->file))
                        $_file = realpath(c::folders('path_templates') . DS . c::current('module') . '.phtml');
                }
            }

            if ($_file === false) {
                $_file = realpath(c::folders('path_templates') . DS . 'index.phtml');
            }

            if ($_file === false) {
                debug('No default templates found');
            } else {
                $this->file = $_file;
                debug('Setting template to: ' . $this->file);
            }
        }

        public function setTemplate($_file) {
            if (file_exists(realpath(_BASE . DS . $_file))) {
                $this->file = realpath(_BASE . DS . $_file);
                debug('Setting template to: ' . $this->file);
            } else {
                debug('Template not found: ' . $_file);
            }
        }

        public function resolveFile($_file) {
            return file_get_contents($_file);
        }

        public function url($_url) {
            return (_URL . '/' . $_url);
        }

        public function resolveURL($_url, Array $_post = array()) {
            $url = \Core\Input\Request::get()->server_name .
                    ':' . \Core\Input\Request::get()->server_port .
                    '/' . _URL . '/' . $_url;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, '3');

            //Set request method to POST
            curl_setopt($ch, CURLOPT_POST, 1);

            // Set query data here with CURLOPT_POSTFIELDS
            curl_setopt($ch, CURLOPT_POSTFIELDS, $_post);

            $content = trim(curl_exec($ch));

            echo curl_error($ch);

            curl_close($ch);

            return $content;
        }

    }

}