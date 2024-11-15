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
    $headers = apache_request_headers();
    $passphrase="ArCaycHarlixCxfeatpiNgui99$";
}else{
    die("The method is ".$_SERVER['REQUEST_METHOD']." There was a problem with the POST method. Check GameMaker code, error logs and access logs for more information.");
}

//Create an instance; passing `true` enables exceptions
$mail = new PHPMailer(true);
$passphrase_decoded="";

try {
    //Choose tipo_feed depending on what was received on POST request.
    $tipo_feed=0;
    if($file_type=="errors.log"){
        $tipo_feed=2;
    }elseif($file_type=="feed.log"){
        $tipo_feed=1;
    }

    //Verify the authentication header through base64 decode
    $auth_header=$headers['Authorization'];
    if(!isset($auth_header)){
        http_response_code(401);
        die("No está autorizadx para hacer este request!");
    }
    if(strpos($auth_header, 'basic ') !== 0){
        http_response_code(401);
        die("No está autorizadx para hacer este request!");
    }
    $passphrase_decoded=base64_decode(substr($auth_header, 6));
    if($passphrase!==$passphrase_decoded){
        http_response_code(401);
        die("No está autorizadx para hacer este request!");
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
    foreach($conn->query("SELECT U.NOMBRE, U.CORREO, TF.ID FROM USUARIOS U, USUARIOS_has_CARGOS UC, TIPO_FEED_has_CARGOS TFC, CARGOS C, TIPO_FEED TF WHERE U.ID=UC.USUARIOS_ID AND C.ID=UC.CARGOS_ID AND TF.ID=TFC.TIPO_FEED_ID AND TFC.CARGOS_ID=UC.CARGOS_ID AND TF.ID=".$tipo_feed) as $row){
        $mail->addAddress($row['CORREO'], $row['NOMBRE']);
    }

    //Content
    $mail->isHTML(true);                                 
    $mail->Subject = $subject;
    $mail->Body    = $body;
    $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

// Decode the log data from Base64 format
    $log_contents = base64_decode($log_base64);

// Append the log data to the log file
    $i=1;
    $filename ="/var/www/html/PINGUI-SERVER/log_files/".$time_code.$file_type;
    while(file_exists($filename)){
        $time_code.=strval($i);
        $filename ="/var/www/html/PINGUI-SERVER/log_files/".$time_code.$file_type;
        $i+=1;
    }
    $log_file = fopen($filename, "w") or die("There is a problem");
    fwrite($log_file, $log_contents);
    fclose($log_file);

//SQL Query
    $sql = "INSERT INTO FEED (ID, UBICACION, CREACION, TIPO_FEED_ID) VALUES (?, ?, NULL, ?)";
    $stmt = $conn->prepare($sql);
    $stmt ->execute([$time_code, $filename, $tipo_feed]);

    //Attachments
    $mail->addAttachment($filename, $file_type); 

    $mail->send();
    echo 'El mensaje ha sido enviado!';
} catch (Exception $e) {
    echo "El mensaje no se pudo enviar. Mailer Error: {$mail->ErrorInfo}";
}
?>
