<?php
    class DirAllFileNamesGetter {
        private $dir_path = null;
        private $belong_file_path_array = array();

        public function __construct(string $path){
            $this->dir_path = $path; //最終バクスラ込みのpath
            self::starter();
        }

        private function starter() {
            $file_names_array = scandir($this->dir_path);
            if(is_array($file_names_array)) self::setFileNamesArray($file_names_array, $this->dir_path);
        }

        private function setFileNamesArray(array $file_names_array, string $path) {
            foreach($file_names_array as $file_name) {
                if(!preg_match( '/^(\.|\.\.)$/', $file_name) && !is_link($path.$file_name)) {
                    if(is_dir($path.$file_name)) {
                        self::setFileNamesArray(scandir($path.$file_name), $path.$file_name.SEPARATOR);
                    }else {
                        $this->belong_file_path_array[] = $path.$file_name;
                    }
                }
            }
        }

        public function getDirPath() {
            return $this->dir_path;
        }
        public function getBelongFilePathArray() {
            return $this->belong_file_path_array;
        }
    }