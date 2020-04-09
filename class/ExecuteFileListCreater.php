<?php
    require_once (__DIR__."/ExecuteFile.php");
    require_once (__DIR__."/DirAllFileNamesGetter.php");
    require_once (__DIR__."/ZipOperator.php");

    class ExecuteFileListCreater {
        private $path = null;
        private $notice_modified = false;
        private $notice_created = false;
        private $notice_deleted = false;
        private $execute_file_instance_array = array();

        public function __construct(array $file_data){
            $this->path = $file_data['path'];
            $this->notice_modified = $file_data['modified_flag'];
            $this->notice_created = $file_data['create_flag'];
            $this->notice_deleted = $file_data['delete_flag'];
            self::createInstanceSwitch();
        }

        private function createInstanceSwitch() {
            if(is_dir($this->path)) {
                self::createFileInstanceFromDirPath();
            }else {
                self::createFileInstance();
            }
        }

        private function createFileInstanceFromDirPath() {
            $dir_instance = new DirAllFileNamesGetter($this->path);
            $belong_file_path_array = $dir_instance->getBelongFilePathArray();
            foreach ($belong_file_path_array as $belong_file_path) {
                $this->execute_file_instance_array[] = new ExecuteFile($belong_file_path);
            }

            $hash_path = sha1($this->path);
            $old_dir_execute_list_zip_path = BEFORE_ZIP_DIR.$hash_path.'.zip';
            $execute_list_json_str = json_encode($belong_file_path_array);

            if(file_exists($old_dir_execute_list_zip_path)) {
                $old_execute_json_decode_str= ZipOperator::getLatestEntryContent($old_dir_execute_list_zip_path);
                $old_execute_array = json_decode($old_execute_json_decode_str, true);
                $old_only_exists_path_array = array_diff($old_execute_array, $belong_file_path_array);
                foreach ($old_only_exists_path_array as $path) {
                    $this->execute_file_instance_array[] = new ExecuteFile($path);
                }
            }

            //現在監視ディレクトリに存在するファイル名一覧を記録、削除ファイルの検知に活用
            ZipOperator::saveZip($old_dir_execute_list_zip_path, $execute_list_json_str);
        }

        private function createFileInstance() {
            $this->execute_file_instance_array[] = new ExecuteFile($this->path);
        }

        public function noticeModified() {
            return $this->notice_modified;
        }
        public function noticeCreated() {
            return $this->notice_created;
        }
        public function noticeDeleted() {
            return $this->notice_deleted;
        }
        public function getExecuteFileInstanceArray() {
            return $this->execute_file_instance_array;
        }
    }