<?php

namespace Core\Models {

    use \Core\Databases\Collection as collection;

    class DbModel extends \Core\Model {

        public $collection;
        protected $allowRanges = false;

        public function __construct($_name, $_allowRanges = false) {
            debug('Base class, mapping to table: ' . $_name);
            if (in_array($_name, explode('|', self::protected_tables))) {
                throw new \Core\Models\ModelException('This table is protected: ' . $_name, E_USER_ERROR);
            }
            try {
            $col = collection::get($_name);
            parent::__construct($_name, $col->getKeys(), $col->getFields(), $_allowRanges);
            $this->collection = $col;
            } catch (\Core\Databases\DatabaseException $e) {
                throw new \Core\Models\ModelException($e);
            }
        }

        public function getKeys() {
            return $this->keys;
        }

        public function getFields() {
            return $this->fields;
        }

        public function create(Array $_data) {
            $this->noMultiDimensionalArraysAllowed($_data);
            if (!$this->areAllRequiredFieldsPopulated($_data)) {
                throw new \Core\Models\ModelException('Mandatory fields missing for table: ' . $this->name, E_USER_ERROR);
            }
            $entry = $this->collection->newEntry();
            $entry->load($_data);
            if ($this->collection->insert($entry)) {
                return true;
            } else {
                return false;
            }
        }

        public function read(Array $_where) {
            $this->noMultiDimensionalArraysAllowed($_where);
            if ($this->allowRanges) {
                $result = $this->collection->getAll($_where);
            } elseif ($this->areKeyFieldsPresent($_where)) {
                $result = $this->collection->getSingle($_where, function($_entry) {
                            if (is_object($_entry)) {
                                return $_entry->getAll();
                            } else {
                                return false;
                            }
                        });
            } else {
                throw new \Core\Models\ModelException('Your selection should contain the full key', E_USER_ERROR);
            }
            return $result;
        }

        public function update(Array $_where, Array $_data) {

            $this->noMultiDimensionalArraysAllowed($_data);
            $this->noMultiDimensionalArraysAllowed($_where);
            $return['success'] = 0;
            $return['failed'] = 0;
            if ($this->areKeyFieldsPresent($_data)) {
                throw new \Core\Models\ModelException('Key fields found in data, so not an update.', E_USER_ERROR);
            }
            $entries = $this->collection->getAsEntries($_where);
            if (sizeof($entries) === 0) {
                return false;
            }

            if (!$this->areKeyFieldsPresent($_where) && !$this->allowRanges) {
                throw new \Core\Models\ModelException('Your selection should contain the full key', E_USER_ERROR);
            }

            foreach ($entries as $entry) {
                foreach ($_data as $k => $v) {
                    $entry->{$k} = $v;
                }
                if ($this->collection->update($entry)) {
                    $return['success']++;
                } else {
                    $return['failed']++;
                }
            }
            if ($this->areKeyFieldsPresent($_where)) {
                return $return['success'] === 1;
            } else {
                return $return;
            }
        }

        public function delete(Array $_where) {
            $this->noMultiDimensionalArraysAllowed($_where);
            $return['success'] = 0;
            $return['failed'] = 0;
            $entries = $this->collection->getAsEntries($_where);
            if (sizeof($entries) === 0) {
                return false;
            }

            if (!$this->areKeyFieldsPresent($_where) && $this->allowRanges === false) {
                throw new \Core\Models\ModelException('Your selection should contain the full key', E_USER_ERROR);
            }
            foreach ($entries as $entry) {
                if ($this->collection->delete($entry)) {
                    $return['success']++;
                } else {
                    $return['failed']++;
                }
            }
            if ($this->areKeyFieldsPresent($_where)) {
                return $return['success'] === 1;
            } else {
                return $return;
            }
        }

    }

}