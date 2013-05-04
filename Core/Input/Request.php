<?php

namespace Core\Input {

    class Request {

        private static $self = null;
        protected $parameters;

        public function __construct() {
            $parameters['input'] = array();
            $parameters['url'] = array();
            debug('Instantiating: ' . __CLASS__, 3);
            foreach ($_SERVER as $key => $value) {
                if (!is_array($value)) {
                    $this->{strtolower($key)} = strtolower(strip($value));
                }
            }
            switch ($this->request_method) {
                case 'get':
                    array_merge($parameters['input'], array_map("strip", $_GET));
                case 'post':
                    array_merge($parameters['input'], array_map("strip", $_POST));
                case 'put':
                case 'delete':
                    parse_str(file_get_contents("php://input"), $post_vars);
                    foreach ($post_vars as $k => $v) {
                        if ($this->isJson($k)) {
                            $array = json_decode($k);
                            foreach ($array as $ak => $av) {
                                $parameters['input'][$ak] = strip($av);
                            }
                        } else {
                            if (!is_null($v)) {
                                $parameters['input'][$k] = strip($v);
                            } else {
                                $parameters['input'][] = strip($k);
                            }
                        }
                    }
                    break;
                default:
                    array_merge($parameters['input'], array_map("strip", $_GET));
                    break;
            }
            $this->parameters = $parameters;
            if (!isset($this->parameters['url']['url'])) {
                $this->parameters['url']['url'] = str_replace('url=', '', $_SERVER['REDIRECT_QUERY_STRING']);
            }
        }

        public static function get() {
            if (self::$self == null) {
                self::$self = new self();
            }
            return self::$self;
        }

        public function isJson($string) {
            json_decode($string);
            return (json_last_error() == JSON_ERROR_NONE);
        }

        public function parameters() {
            return $this->parameters;
        }

        public function addRouteParameters(Array $_array) {
            $this->parameters['url'] = array_merge($this->parameters['url'], $_array);
        }

        public function isAJAX() {
            return isset($this->{'http_x_requested_with'}) &&
                    !strpos($this->{'http_x_requested_with'}, 'xmlhttprequest');
        }

    }

}