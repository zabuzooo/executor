<?php
    require_once (__DIR__."/ZipOperator.php");

    class Mailer {
        private $title = '';
        private $from_addr = '';
        private $to_addr = '';
        private $content ='';

        //クラス内から呼び出すsetter
        private function setMailTitle($title) {
            $this->title = $title;
        }
        private function setFromAddr($from_addr) {
            $this->from_addr = $from_addr;
        }
        private function setToAddr($to_addr) {
            $this->to_addr = $to_addr;
        }

        //外部から呼び出すsetter
        public function setContent($content) {
            $this->content = $content;
        }

        public function sendNoticeMail($changed_instance){ //状態変更通知メール専用のメソッド
            $title = '[重要]ファイル状態変更のお知らせ';
            $from_addr = 'test@example.com';
            $to_addr = 'test+1@example.com';
            self::setMailTitle($title);
            self::setFromAddr($from_addr);
            self::setToAddr($to_addr);

            require(PHP_MAILER_AUTOLOAD_PATH);
            $mail = new PHPMailer();
            $mail->isSMTP(); //SMTPを使うようにメーラーを設定
            $mail->Host = MAIL_HOST; //SMTPサーバーを指定
            $mail->Port = 25; //TCPポート番号
            $mail->SMTPAuth = false; //SMTP認証
            $mail->Encoding = "7bit";
            $mail->CharSet = 'ISO-2022-JP';
            $mail->XMailer = " ";

            $mail->addAddress($this->to_addr);
            $mail->setFrom($this->from_addr, mb_encode_mimeheader("[重要]ファイル状態変更のお知らせ", "ISO-2022-JP"));
            $mail->Subject = mb_encode_mimeheader($this->title, "ISO-2022-JP");
            $mail->Body = mb_convert_encoding($this->content, "ISO-2022-JP");
            if(!$mail->send()) {
                $mail->Host = MAIL_HOST;
                if(!$mail->send()) {
                    $mail->isMail();
                    $mail->send();
                    echo 'Mailer Error: '. $mail->ErrorInfo;
                }
            }else {
                self::saveLog($changed_instance->getFilePath());
            }
        }

        private function saveLog(string $changed_file_path){
            $zip_operator = new ZipOperator();
            $log_content = $this->title. "\n". $this->to_addr. "\n". $this->content; //題名 宛先 変更内容(ファイル情報込み)
            $zip_operator->saveZip(MAIL_LOG_DIR.sha1($changed_file_path).'.zip', $log_content);
        }
    }