<?php

require_once dirname(dirname(__FILE__)).'/assets_sys/PHPMailer-master/src/Exception.php';
require_once dirname(dirname(__FILE__)).'/assets_sys/PHPMailer-master/src/PHPMailer.php';
require_once dirname(dirname(__FILE__)).'/assets_sys/PHPMailer-master/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
class Mail {

    public function valueMail($recipient, $subject, $body2 = null){
        $css = "<head><style>h1{color:#ff0000;}</style></head>";

            $body = '<html>'.$css.'<body>'.$body2.'</body>'.'</html>';
            $html = true;

        $this->sendMail($recipient, $subject, $body, $html);
    }

    public function sendMail($recipient, $subject, $body, $html){
        $mail = new PHPMailer(true);
        $mail->SMTPDebug = 0;
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->CharSet = 'UTF-8';
        $mail->Username = 'exaple@exaple.com';
        $mail->setFrom('exaple@exaple.com', 'Calendar App');
        $mail->Password = 'yourPassword';
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;
        $mail->addAddress($recipient);
        $mail->Subject  = $subject;
        $mail->Body = $body;
        $mail->isHTML($html);
        $mail->send();

    }
}
