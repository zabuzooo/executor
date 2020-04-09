<?php
    //JSON文字列 → 使用されているkeyの配列
    class JsonKeyArrayGetter {
        private $json_array = array();
        private $json_key_index = array();

        public function __construct(string $json_str) {
            $this->json_array = json_decode($json_str, true);
            self::setKeyIndexStarter();
        }

        private function setKeyIndexStarter() {
            if(is_array($this->json_array)) self::setKeyIndex($this->json_array);
        }

        private function setKeyIndex(array $array=[], string $path='') {
            foreach ($array as $key => $value) {
                if(is_array($value)) {
                    $this->json_key_index[] = $path.$key;
                    self::setKeyIndex($value, $path.$key.SEPARATOR);
                }else {
                    $this->json_key_index[] = $path.$key;
                }
            }
        }

        public function getKeyIndex() {
            return $this->json_key_index;
        }
    }