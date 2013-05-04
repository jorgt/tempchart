<?php

namespace Core\Output {

    class view extends \Core\ViewAbstract {

        public function __construct($_file) {
            $file = realpath(_CURRENT_PATH . DS . 'views' . DS . $_file . self::extension);
            if (!$file) {
                $file = realpath(_VIEWS . DS . $_file . self::extension);
            }
            $this->file = (file_exists($file)) ? $file : null;
            if (is_null($this->file)) {
                throw new \InvalidArgumentException;
            }
        }

    }

}
?>
