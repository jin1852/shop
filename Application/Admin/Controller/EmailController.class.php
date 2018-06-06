<?php
namespace Admin\Controller;
use Think\Controller;

class EmailController extends Controller{
    //定义
    public $mail_smtp=true;
   // public $mail_host='c1.icoremail.net'; //国内
    public $mail_host='h1.icoremail.net'; //国外
    public $mail_port=465;
    public $mail_smtopauth=true;
    public $mail_username='order@galison.co'; //SMTP服务器登陆用户名
    public $mail_password='Izr0_cXh6QC4Rbj7';
    public $mail_secure='ssl';
    public $mail_charset='utf-8';
    public $mail_ishtml=false;
    public $mail_from='系统管理员';
    //发送类
    public function send_email($to,$cc,$subject,$content){
        if($to && $subject && $content) {
            date_default_timezone_set('Asia/Shanghai');//设定时区东八区
            require_once('PHPMailer-master/PHPMailerAutoload.php');
            include('PHPMailer-master/class.smtp.php');
            $mail = new \PHPMailer();
            //配置邮件服务器
            if ($this->mail_smtp) {
                $mail->isSMTP();
            }
            $mail->Host = $this->mail_host;
            $mail->Port = $this->mail_port;
            $mail->SMTPAuth = $this->mail_smtopauth;
            $mail->Username = $this->mail_username;
            $mail->Password = $this->mail_password;
            $mail->SMTPSecure = $this->mail_secure;
            $mail->CharSet = $this->mail_charset;
            $mail->SMTPDebug = 0; // will echo errors, server responses and client messages
            $mail->WordWrap = 50; //设置每行字符长度
            $mail->Priority = 3;   // 设置邮件优先级 1：高, 3：正常（默认）, 5：低
            //配置邮件头部
            $mail->SetFrom($this->mail_username);
            if(is_array($to)) {
                foreach ($to as &$one) {
                    $mail->addAddress($one['address']);
                }
            }else{
                $mail->addAddress($to);
            }

            if ($cc) {
                if(is_array($cc)) {
                    foreach ($cc as &$one) {
                        $mail->addCC($one['address']);
                    }
                }else{
                    $mail->addCC($cc);
                }
            }
            $mail->FromName = $this->mail_from;
            $mail->IsHTML($this->mail_ishtml);// 是否HTML格式邮件
            //配置邮件正文
            $mail->Subject = $subject;//邮件主题
            $mail->Body = $content;//邮件内容
            $mail->AltBody = $content; //邮件正文不支持HTML的备用显示
            //发送邮件
            if (!$mail->Send()) {
//                echo '发送失败！';
               // echo $mail->ErrorInfo;
               // echo "邮件发送失败. <p>";
                // echo "错误原因: " . $mail->ErrorInfo;
                return -1;
            } else {
                    //echo '发送成功';
                return 1;
            }
        }else{
           // echo '参数不全';
            return 0;
        }
        //die();
    }

}