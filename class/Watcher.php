<?php
    require_once (__DIR__."/ExecuteFileListCreater.php");
    require_once (__DIR__."/BeforeFile.php");
    require_once (__DIR__."/JsonKeyArrayGetter.php");
    require_once (__DIR__."/Executor.php");

    class Watcher {
        private $execute_file_list_instance = null;
        private $exit_flag = false;

        public function __construct($file_data){
            $this->execute_file_list_instance = new ExecuteFileListCreater($file_data);
        }

        //modified判定
        public function trigerModified() {
            if(!$this->execute_file_list_instance->noticeModified()) return;
            foreach ($this->execute_file_list_instance->getExecuteFileInstanceArray() as $execute_instance) {
                $before_instance = new BeforeFile($execute_instance->getFilePath());
                $ex_path = explode('.', $execute_instance->getFilePath());
                $extension = end($ex_path);

                //JSON変更検知箇所
                if($extension === 'json') {
                    $changed_json_key_list_instance = new JsonKeyArrayGetter($execute_instance->getContent());
                    $changed_key_array = $changed_json_key_list_instance->getKeyIndex();
                    $before_json_key_list_instance = new JsonKeyArrayGetter($before_instance->getContent());
                    $before_key_array = $before_json_key_list_instance->getKeyIndex();

                    if(!$changed_key_array) continue; //json形式であることを確認 エラー処理に飛ばしても良い

                    $key_diff_array = array();
                    $changed_add_key_array = array_diff($changed_key_array, $before_key_array);
                    foreach ($changed_add_key_array as $value) {
                        $key_diff_array[] = '+ '.$value;
                    }
                    $changed_delete_key_array = array_diff($before_key_array, $changed_key_array);
                    foreach ($changed_delete_key_array as $value) {
                        $key_diff_array[] = '- '.$value;
                    }

                    //差分がある && zipファイルが存在しており直前のzipエントリ内容が削除判定ではない && 現在も内容がある
                    if($key_diff_array && ($before_instance->getContent() || $before_instance->getContent()==='') && $before_instance->getContent()!=='deleted' && ($execute_instance->getContent() || $before_instance->getContent()==='')) {
                        Executor::modifiedExecute($execute_instance, $key_diff_array);
                        $this->exit_flag = true;
                    }
                }
            }
        }

        //created判定
        public function trigerCreate() {
            if(!$this->execute_file_list_instance->noticeCreated()) return;
            foreach ($this->execute_file_list_instance->getExecuteFileInstanceArray() as $execute_instance) {
                $before_instance = new BeforeFile($execute_instance->getFilePath());
                //(そもそもzipファイルが存在していない || zipファイルがあるが直前のエントリ内容が削除判定) && 現在は対象ファイルがある
                if(($before_instance->getContent()===false || $before_instance->getContent()==='deleted') && file_exists($execute_instance->getFilePath())) {
                    Executor::createdExecute($execute_instance);
                }
            }
        }

        //deleted判定
        public function trigerDelete() {
            if(!$this->execute_file_list_instance->noticeDeleted()) return;
            foreach ($this->execute_file_list_instance->getExecuteFileInstanceArray() as $execute_instance) {
                $before_instance = new BeforeFile($execute_instance->getFilePath());

                //現在は対象ファイルがない && zipファイルが存在しており直前のエントリ内容が削除判定ではない
                if(!file_exists($execute_instance->getFilePath()) && file_exists($before_instance->getFilePath()) && $before_instance->getContent()!=='deleted'){
                    Executor::deleteExecute($execute_instance);
                    $this->exit_flag = true;
                }
            }
        }

        public function getExitFlag() {
            return $this->exit_flag;
        }
    }
