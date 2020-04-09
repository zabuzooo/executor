<?php
    //ファイルの抽象クラス
    abstract class AbstractFile {
        protected $path = null;
        protected $file_content = null; //取得失敗：false

        public function __construct(string $path){
            $this->path = $path;
            $this->file_content = self::returnContentToSelfPath();
        }
        private function returnContentToSelfPath() {
            if(!file_exists($this->path)) return false;
            return file_get_contents($this->path);
        }

        public function getFilePath() {
            return $this->path;
        }
        public function getContent() {
            return $this->file_content;
        }
    }