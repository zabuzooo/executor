<?php
    $exit_flag = false;

    require_once (__DIR__."\class\Watcher.php");
    require_once (__DIR__.'\spyc\Spyc.php');

    $config_array = spyc_load_file('config.yaml');
    foreach ($config_array as $name => $value) {
        define($name, $value);
    }
    $execute_file_list = spyc_load_file('execute_files.yaml');

    //監視対象の状態変更を確認しに行き、変更されたファイルリストを取得する
    foreach ($execute_file_list as $file_data) {
        $watcher = new Watcher($file_data);

        $watcher->trigerModified();
        $watcher->trigerCreate();
        $watcher->trigerDelete();
        if($watcher->getExitFlag()) $exit_flag = true;
    }

    if($exit_flag) exit;