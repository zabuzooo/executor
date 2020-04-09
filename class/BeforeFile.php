<?php
    require_once (__DIR__."/AbstractFile.php");
    require_once (__DIR__."/ZipOperator.php");

    //状態変更前のファイルクラス
    class BeforeFile extends AbstractFile {
        public function __construct(string $path) {
            $hash_path = sha1($path);
            $this->path = BEFORE_ZIP_DIR.$hash_path.'.zip';
            $this->file_content = self::returnContentToSelfPath();
        }

        private function returnContentToSelfPath() {
            return ZipOperator::getLatestEntryContent($this->path);
        }
    }