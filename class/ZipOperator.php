<?php
    class ZipOperator {
        //指定されたパスへのzipファイルの作成・エントリ追加
        public static function saveZip(string $target_path, string $contents) {
            $zip = new ZipArchive();
            $zip->open($target_path, ZIPARCHIVE::CREATE);

            $temp_file = tempnam(sys_get_temp_dir(), 'Tmp');
            $handle = fopen($temp_file, "wb");
            fwrite($handle, $contents);
            fclose($handle);

            $zip->addFile($temp_file, time());
            $zip->close();
            unlink($temp_file);
        }

        //最新エントリの内容取得(取得失敗時：false)
        public static function getLatestEntryContent(string $target_zip_path) {
            $zip = new ZipArchive();
            if(!file_exists($target_zip_path)) return false;
            $zip->open($target_zip_path);
            $latest_index_num = ($zip->numFiles)-1;
            $content = $zip->getFromIndex($latest_index_num);
            $zip->close();
            return $content;
        }
    }
