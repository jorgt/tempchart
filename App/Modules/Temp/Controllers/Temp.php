<?php

namespace App\Modules\Temp\Controllers {

    class Temp extends \Core\Controllers\NonAuthorized {

        public function __construct($_req, $_res, $_vars) {
            parent::__construct($_req, $_res, $_vars);

            \Core\Databases\Database::get('temp');
            $table = new \Core\Databases\Table('temperature');
            $table->field('date', 'INT', true);
            $table->field('temperature', 'INT', false, false);
            $table->field('period', 'INT', false, false);
            $table->field('spotting', 'INT', false, false);
            $table->field('opk_surge', 'INT', false, false);
            $table->field('comment', 'VARCHAR', false, false);
            \Core\Databases\Database::createTable($table);

            /*
            $t = new \Core\Models\DBModel('temperature');
            $t->create(array(
                'date' => 1367157600000,
                'temperature' => 35.7,
                'period' => true,
                'spotting' => false,
                'opk_surge' => true,
                'comment' => 'blah'));
             * 
             */
        }

        public function index() {
            $this->response->render();
        }

    }

}