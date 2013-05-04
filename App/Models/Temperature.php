<?php

namespace App\Models {

    class Temperature extends \Core\Models\DbModel {

        public function __construct($_allowRanges = false) {
            parent::__construct('temperature', $_allowRanges);
        }

    }

}