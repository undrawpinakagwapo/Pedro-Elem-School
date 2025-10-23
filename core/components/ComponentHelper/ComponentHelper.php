<?php 

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class ComponentHelper {

    private $db;

    public function __construct($db) {
        $this->db = $db;
    }


    public function sentToEmail($recipient, $subject, $body) {

        try {


            $mail = new PHPMailer(true);
            //Server settings
            $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      //Enable verbose debug output
            $mail->isSMTP();                                            //Send using SMTP
            $mail->Host       = "smtp.gmail.com";                     //Set the SMTP server to send through
            $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
            $mail->Username   = $_ENV['EMAIL_EMAIL'];                     //SMTP username
            $mail->Password   = $_ENV['EMAIL_APP_PASSWORD'];                           //SMTP password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
            $mail->Port       = 465;
            
            // Email content
            //Recipients
            $mail->isHTML(true);    
            $mail->setFrom($_ENV['EMAIL_EMAIL']);
            $mail->addAddress($recipient);     //Add a recipient
            $mail->Subject = $subject;
            $mail->Body = $body;
            // Send email
            if(!$mail->send()) {
                return false;
            } else {
                return true; // success
            }
        } catch (Exception $e) {
           return false;
        }


    }

    
    public function sentSMS($number, $message){
        $send_data = [];

        //START - Parameters to Change
        //Put the SID here
        $send_data['sender_id'] = "PhilSMS";
        //Put the number or numbers here separated by comma w/ the country code +63
        $send_data['recipient'] = $number;
        //Put message content here
        $send_data['message'] = $message;
        //Put your API TOKEN here
        // $token = "991|KUk2Y8TdT3mgtJvDT1B7E5nwo8bnqcAMBkKXJ3Hq";
        $token = "1169|2rHPKCPe2tS6UWGb9qutIRAblvICOJnNt1teSdnv";
        //END - Parameters to Change
         
        //No more parameters to change below.
        $parameters = json_encode($send_data);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://app.philsms.com/api/v3/sms/send");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $parameters);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $headers = [];
        $headers = array(
            "Content-Type: application/json",
            "Authorization: Bearer $token"
        );
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        
        $get_sms_status = curl_exec($ch);
        
        return json_encode($get_sms_status);
        // var_dump($get_sms_status);
        // return true;
    }


    public function generateRandomString($length = 10) {
        $randomNumber = mt_rand(100000, 999999);
        return $randomNumber;
    }


}
