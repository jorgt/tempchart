<?php

namespace Core\Output {

    abstract class Response {

        protected $payload;
        protected $raw;
        protected $request;
        protected $code;

        final public function __construct($_req) {
            debug('Instantiating ' . get_called_class(), 3);
            $this->headerResponseCode(404);
            $this->header('X-Content-Type-Options', 'nosniff');
            $this->request = $_req;
            $this->setDefaultHeader();
        }

        public function getCode() {
            return $this->code;
        }

        public function header($_option, $_value) {
            if (!headers_sent()) {
                header($_option . ': ' . $_value);
            }
        }

        public function headerXFrameOptions($_value) {
            $this->header('X-Frame-Options', $_value);
        }

        public function headerContentType($_value) {
            $this->header('Content-type', $_value);
        }

        public function headerResponseCode($_code) {
            $this->code = $_code;
            debug('HTTP Status set to: ', $_code, 3);
            http_response_code($_code);
        }

        public function redirect($_url) {
            if (DEBUG) {
                debug('Redirecting to: <a href="' . _URL . $_url . '">' . $_url . '</a>');
            } else {
                header('Location: ' . _URL . $_url);
            }
        }

        public static function get($_req) {
            if ($_req->isAJAX()) {
                return new \Core\Output\ResponseJson($_req);
            } else {
                $priority = self::parseAcceptRequest($_req->http_accept);
                foreach ($priority as $p => $v) {
                    foreach ($v as $type) {
                        switch ($type) {
                            case 'application/json':
                            case 'text/javascript':
                                return new \Core\Output\ResponseJson($_req);
                            case 'application/xml':
                                return new \Core\Output\response_xml($_req);
                            case 'text/html':
                                return new \Core\Output\ResponseHtml($_req);
                        }
                    }
                }
                return new \Core\Output\ResponseHtml($_req);
            }
        }

        private static function parseAcceptRequest($_accept) {
            foreach (explode(',', $_accept) as $v) {
                $map = explode(';', $v);
                if (sizeof($map) === 1) {
                    $r[1][] = $map[0];
                } else {
                    $r[str_replace(' q=', '', $map[1])][] = $map[0];
                }
            }
            krsort($r);
            return $r;
        }

        protected abstract function setDefaultHeader();

        public function transform($_output) {
            $this->payload = $_output;
        }

        /**
         * Send something to the browser without rendering a template. 
         * @param type $_result - The output
         * @param type $_type - Content type, default 'text/plain'
         */
        public function send($_output = null) {
            $this->headerResponseCode(200);
            if (!is_null($_output)) {
                $this->transform($_output);
            }
            die($this->payload);
        }

    }

}