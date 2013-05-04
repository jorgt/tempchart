<?php

namespace Core\Output {

    class ResponseHtml extends \Core\Output\Response {

        private $template;

        protected function setDefaultHeader() {
            $this->template = new \Core\Output\Template();
            $this->headerContentType('text/html');
            $this->headerXFrameOptions('DENY');
            $this->headerResponseCode(404);
        }

        public function __call($_name, $_arguments) {
            if (method_exists($this->template, $_name)) {
                call_user_func_array(array($this->template, $_name), $_arguments);
            }
        }

        public function __set($_var, $_val) {
            $this->template->{$_var} = $_val;
        }

        public function send($_output = null) {
            $this->headerResponseCode(200);
            if (is_null($_output) === false) {
                die(parent::send($_output));
            } else {
                die($this->template->render());
            }
        }

    }

}
?>
