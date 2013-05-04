<?php

namespace Core\Output {

    class ResponseJson extends \Core\Output\Response {

        public function setHeader($_code = null, $_type = null) {
            if (!is_null($_type)) {
                header('Content-type: ' . $_type);
            } else {
                header('Content-type: ' . self::defaultJSONHeader);
            }

            if (!is_null($_code)) {
                http_response_code($_code);
            } else {
                http_response_code(self::defaultHTTPCode);
            }
        }

        protected function setDefaultHeader() {
            $this->headerContentType('application/json');
            $this->headerXFrameOptions('SAMEORIGIN');
            http_response_code(404);
        }

        public function transform($_output) {
            $this->payload = json_encode($_output);
        }
    }

}
?>
