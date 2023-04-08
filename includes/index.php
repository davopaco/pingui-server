<?php
#Include PHP files PHPMAILER
require __DIR__.'/PHPMailer.php';
require __DIR__.'/Exception.php';
require __DIR__.'/SMTP.php';

#Define name spaces
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
if($_SERVER['REQUEST_METHOD']=='POST'){
$log_base64 = $_POST['gamedata'];
}else{
die("The method is ".$_SERVER['REQUEST_METHOD']);
}

//Create an instance; passing `true` enables exceptions
$mail = new PHPMailer(true);

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

// Decode the log data from Base64 format
    $log_contents = base64_decode($log_base64);

// Append the log data to the log file
    $log_file = fopen("/var/www/html/PINGUI-SERVER/gamedata.json", "w") or die("There is a problem");
    fwrite($log_file, $log_contents);
    fclose($log_file);
    //Attachments
    $mail->addAttachment("/var/www/html/PINGUI-SERVER/gamedata.json", 'feed.json'); 

    $mail->send();
    echo 'Message has been sent';
} catch (Exception $e) {
    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
}
?>
