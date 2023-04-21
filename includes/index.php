<?php
#Include PHP files PHPMAILER
require __DIR__.'/PHPMailer.php';
require __DIR__.'/Exception.php';
require __DIR__.'/SMTP.php';
include 'connection.php';

#Define name spaces
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
if($_SERVER['REQUEST_METHOD']=='POST'){
    $log_base64 = $_POST['content'];
    $subject = $_POST['subject'];
    $body = $_POST['body'];
    $file_type = $_POST['file_type'];
    $time_code = $_POST['time_code'];
}else{
    die("The method is ".$_SERVER['REQUEST_METHOD']." There was a problem with the POST method. Check GameMaker code, error logs and access logs for more information.");
}

//Create an instance; passing `true` enables exceptions
$mail = new PHPMailer(true);

try {
    //Choose tipo_feed depending on what was received on POST request.
    $tipo_feed=0;
    if($file_type=="errors.log"){
        $tipo_feed=2;
    }elseif($file_type=="feed.log"){
        $tipo_feed=1;
    }
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
    foreach($conn->query("SELECT U.NOMBRE, U.CORREO, TF.ID FROM USUARIOS U, USUARIOS_has_CARGOS UC, TIPO_FEED_has_CARGOS TFC, CARGOS C, TIPO_FEED TF WHERE U.ID=UC.USUARIOS_ID AND C.ID=UC.CARGOS_ID AND TF.ID=TFC.TIPO_FEED_ID AND TFC.CARGOS_ID=UC.CARGOS_ID") as $row){
        if($row['ID']==$tipo_feed){
            $mail->addAddress($row['CORREO'], $row['NOMBRE']);
        }
    }

    //Content
    $mail->isHTML(true);                                 
    $mail->Subject = $subject;
    $mail->Body    = $body;
    $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

// Decode the log data from Base64 format
    $log_contents = base64_decode($log_base64);

// Append the log data to the log file
    $log_file = fopen("/var/www/html/PINGUI-SERVER/log_files/".$time_code.$file_type, "w") or die("There is a problem");
    fwrite($log_file, $log_contents);
    fclose($log_file);

//SQL Query
    $location = "/var/www/html/PINGUI-SERVER/log_files/".$time_code.$file_type;
    $sql = "INSERT INTO FEED (ID, UBICACION, CREACION, TIPO_FEED_ID) VALUES (?, ?, NULL, ?)";
    $stmt = $conn->prepare($sql);
    $stmt ->execute([$time_code, $location, $tipo_feed]);

    //Attachments
    $mail->addAttachment("/var/www/html/PINGUI-SERVER/log_files/".$time_code.$file_type, $file_type); 

    $mail->send();
    echo 'Message has been sent';
} catch (Exception $e) {
    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
}
?>
