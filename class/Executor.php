<?php
    require_once (__DIR__."/Mailer.php");
    require_once (__DIR__."/ZipOperator.php");
    require_once (__DIR__."/BeforeFile.php");

    //状態変更前・状態変更後の配列(想定データ構造有)
    class Executor {
        public static function modifiedExecute($execute_instance, array $diff_array) {
            $mail_content_body = $execute_instance->getFilePath()." に対して、下記の様に[項目の変更]がありました<br>";
            foreach ($diff_array as $value) {
                $mail_content_body .= $value."<br>";
            }
            $mail_content_body .= 'プログラムを停止させます';

            $mailer = new MailSender();
            $mailer->setContent($mail_content_body);
            $mailer->sendNoticeMail($execute_instance);

            //変更された内容を該当zipファイルへ追加保存
            $before_instance = new BeforeFile($execute_instance->getFilePath());
            ZipOperator::saveZip($before_instance->getFilePath(), $execute_instance->getContent());
        }

        public static function createdExecute($execute_instance) {
            $mail_content_body = $execute_instance->getFilePath().'が 作成されました';

            $mailer = new MailSender();
            $mailer->setContent($mail_content_body);
            $mailer->sendNoticeMail($execute_instance);

            $before_instance = new BeforeFile($execute_instance->getFilePath());
            ZipOperator::saveZip($before_instance->getFilePath(), $execute_instance->getContent());
        }

        public static function deleteExecute($execute_instance) {
            $mail_content_body = $execute_instance->getFilePath().'が 削除されました<br>';
            $mail_content_body .= 'プログラムを停止させます';

            $mailer = new MailSender();
            $mailer->setContent($mail_content_body);
            $mailer->sendNoticeMail($execute_instance);

            $before_instance = new BeforeFile($execute_instance->getFilePath());
            ZipOperator::saveZip($before_instance->getFilePath(), 'deleted');
        }
    }