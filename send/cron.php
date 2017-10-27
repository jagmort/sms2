<?php
namespace PHPMailer\PHPMailer;
$webdir = "/var/www/html/sms2/send";

function SendSMS($uid, $name, $dept, $phone, $email, $text) {
    mb_internal_encoding("UTF-8");

    $phone .= "F";
    if($phone[0] == "8") $phone[0] = "7";
    $pdu_number = "";

    for ($i = 0; $i <= strlen($phone); $i++) {
        if($i % 2 > 0) $pdu_number .= $phone[$i] . $phone[$i - 1];
    }

    $txt = mb_substr($text, 0, MAX_SMS_LENGTH);

    $arText = array();
    $i = 0;
    $notEnd = true;
    while($notEnd) {
        $arText[$i] = mb_substr($txt, $i * 67, 67);
        if(mb_strlen($arText[$i]) < 67) $notEnd = false;
        $i++;
    }

    $rand = strtoupper(dechex(rand(17, 255))); // two hex digits

    $count = substr("0".strtoupper(dechex(count($arText))), -2);
    $num = 0;
    $at = "komy $name wt	$dept	$phone	$email$txt\r\n\r\n";
    foreach ($arText as $text) {

        $size = substr("0" . strtoupper(dechex(mb_strlen($text) * 2 + 6)), -2); // длина сообщения в HEX для вставки в хедер SMS

        $usc2 = mb_convert_encoding($text, "UCS-2BE", "UTF-8"); 
        $hex = strtoupper(bin2hex($usc2));

        $snum = substr("0".strtoupper(dechex(++$num)), -2);
        $pdu_data = "0051000B91" . $pdu_number . "000808" . $size . "050003" . $rand . $count . $snum . $hex; // Начало формирования, заголовок + номер + текст
        $length = (strlen($pdu_data) - 2) / 2; // длина для AT+CMGS=

        $at .= "ATZ^\r\n";
        $at .= "1 pause\r\n";
        $at .= "ATE0^\r\n";
        $at .= "1 pause\r\n";
        $at .= "AT+CSMS=0^\r\n";
        $at .= "1 pause\r\n";
        $at .= "AT+CMGS=$length^\r\n";
        $at .= "1 pause\r\n";
        $at .= "$pdu_data|\r\n";
        $at .= "8 pause\r\n";

    } //foreach


    $fname = "00-" . $uid . "-" . rand(10, 99) . "-" . substr($name, 0, strpos($name, " ")) . "-" . $dept . ".txt";

    $fout = fopen(dirname(__FILE__) . "/out/$fname", "w");
    if(!$fout) {
        $err = error_get_last();
        return $err["message"];
    }
    fwrite($fout, mb_convert_encoding($at, "CP1251", "UTF-8"));
    fclose($fout);

    return true;
}

// Main

require 'PHPMailer.php';
require 'SMTP.php';
require 'Exception.php';
require 'param.php';

// Create SMS file
if ($result = $db->query("SELECT recipient.id AS id, email_only, contact.name AS name, dept, mobile, contact.email AS tomail, group.email AS frommail, group.name AS fname, text, sign, uid FROM `sms`, `recipient`, `contact`, `user`, `group` WHERE user_id = user.id AND group_id = group.id AND contact_id = contact.id AND sms_id = sms.id AND recipient.status = 0")) {
    $mail = new PHPMailer(true);
    //Server settings
    $mail->SMTPDebug = 0;                                 // Enable verbose debug output
    //$mail->SMTPKeepAlive = true;
    $mail->isSMTP();                                      // Set mailer to use SMTP
    $mail->Host = $host;                                // Specify main and backup SMTP servers
    $mail->SMTPAuth = false;                               // Enable SMTP authentication
    $mail->Username = '';                                // SMTP username
    $mail->Password = '';                               // SMTP password
    $mail->SMTPSecure = $SMTPSecure;                            // Enable TLS encryption, `ssl` also accepted
    $mail->Port = $port;                                    // TCP port to connect to
    $mail->CharSet = 'utf-8'; 
    $mail->isHTML(false);                                  // Set email format to HTML

    $uid = "";
    $sendmail = false;
    while($row = $result->fetch_array(MYSQLI_ASSOC)) {
        if($uid == "") {
            $uid = $row["uid"];
            $cc = $row["frommail"];
            $fname = $row["fname"];
            $body = $row["text"] . $row["sign"] . "\n\nID: " . $row["uid"];
            $mail->addAddress($row["frommail"], $row["fname"]);
        }
        $status = 0;
        if($row["mobile"] < MIN_PHONE_NUM) $row["email_only"] = 1; // wrong mobile number
        if($row["email_only"] < 1) { // Skip SMS if e-mail only
            if(SendSMS($row["uid"], $row["name"], $row["dept"], $row["mobile"], $row["tomail"], $row["text"])) {
                $status = $status | 1;
                $result2 = $db->query("UPDATE recipient SET sent = NOW(), status = " . $status . " WHERE id = " . $row["id"]);
            }
            else {
                $status = $status | 8;
                $result2 = $db->query("UPDATE recipient SET sent = NOW(), status = " . $status . " WHERE id = " . $row["id"]);
            }
        }

        if($uid == $row["uid"]) {
            if($row["tomail"] != "") {
                $mail->addAddress($row["tomail"], $row["name"]);     // Add address to multirecipient email
                $sendmail = true;
                if($row["email_only"] > 0) {
                    $status = $status | 16;
                    $result2 = $db->query("UPDATE recipient SET sent = NOW(), done = NOW(), status = " . $status . " WHERE id = " . $row["id"]);
                }
                else {
                    $status = $status | 16;
                    $result2 = $db->query("UPDATE recipient SET status = " . $status . " WHERE id = " . $row["id"]);
                }
            }
            else { // blank email
                if($row["email_only"] > 0) {
                    $status = $status | 64;
                    $result2 = $db->query("UPDATE recipient SET sent = NOW(), done = NOW(), status = " . $status . " WHERE id = " . $row["id"]);
                }
                else {
                    $status = $status | 64;
                    $result2 = $db->query("UPDATE recipient SET status = " . $status . " WHERE id = " . $row["id"]);
                }
            }
        }
        else { // Sent email and start next message
            if($sendmail) {
                $mail->Subject = $subject;
                $mail->setFrom($cc, $fname);
                //$mail->addCC($cc);
                $mail->Body = $body;
                try {
                    $mail->send();
                }
                catch (Exception $e) {

                }
            }
            $mail->clearAddresses();

            $uid = $row["uid"];
            $cc = $row["frommail"];
            $fname = $row["fname"];
            $body = $row["text"] . $row["sign"] . "\n\nID: " . $row["uid"];
        }

    }

    if($uid != "") { // Sent last email if exist
        if($sendmail) {
            $mail->Subject = $subject;
            $mail->setFrom($cc, $fname);
            $mail->Body = $body;
            try {
                $mail->send();
            }
            catch (Exception $e) {

            }
        }
    }
    $result->free();
}


// Check SMS file
if ($result = $db->query("SELECT recipient.id AS id, email_only, contact.name AS name, dept, mobile, contact.email AS tomail, group.email AS frommail, group.name AS fname, text, sign, uid, recipient.status AS status FROM `sms`, `recipient`, `contact`, `user`, `group` WHERE user_id = user.id AND group_id = group.id AND contact_id = contact.id AND sms_id = sms.id AND recipient.status > 0")) {
    while($row = $result->fetch_array(MYSQLI_ASSOC)) {
        $present = false;
        for ($i = 1; $i < 6; $i++) {
            $response = file("$webdir/in/smsVB$i.txt");
            foreach($response as $line) {
                if((strpos($line, $row["uid"]) !== false) AND ((strpos($line, substr($row["name"], 0, strpos($row["name"], " "))) !== false))) {
                    $present = true;
                    $status = $row["status"] | 2;
                    $result2 = $db->query("UPDATE recipient SET phone = $i, status = " . $status . " WHERE id = " . $row["id"]);
                }
            }
        }
        if(!$present) {
            if(($row["status"] & 2) > 0 && ($row["status"] & 4) == 0) {
                $status = $row["status"] | 4;
                $status = $status ^ 2;
                $result2 = $db->query("UPDATE recipient SET done = NOW(), status = " . $status . " WHERE id = " . $row["id"]);
            }
        }
    }
}
