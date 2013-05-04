<?php

namespace App\Modules\Temp {

    class TempConfig extends \Core\Module {
        
        public function initialize() {
            $this->get('index', 'temp/index');
        }
    }

}