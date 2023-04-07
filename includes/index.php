<?php
#Include PHP files PHPMAILER
require __DIR__.'\PHPMailer.php';
require __DIR__.'\Exception.php';
require __DIR__.'\SMTP.php';

#Define name spaces
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

//Create an instance; passing `true` enables exceptions
$mail = new PHPMailer(true);
if(isset($_GET["path"])){
    $path = $_GET["path"];
}

try {
    //Server settings
    $mail->SMTPDebug = SMTP::DEBUG_SERVER;                 
    $mail->isSMTP();                                          
    $mail->Host       = 'smtp.gmail.com';                     
    $mail->SMTPAuth   = true;                              
    $mail->Username   = 'pingui.feedback@gmail.com';                  
    $mail->Password   = 'ctsispkfbsrfajlt';                           
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;           
    $mail->Port       = 465;                                    

    //Recipients
    $mail->setFrom('pingui.feedback@gmail.com', 'Feedback');
    $mail->addAddress('pingui.feedback@gmail.com', 'Pingui');     

    //Content
    $mail->isHTML(true);                                 
    $mail->Subject = 'Here is the subject';
    $mail->Body    = 'This is the HTML message body <b>in bold!</b>';
    $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

    //Attachments
    $mail->addAttachment($path, 'constraint.txt'); 

    $mail->send();
    echo 'Message has been sent';
} catch (Exception $e) {
    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
}
?>