<?php
namespace PHPMailer\PHPMailer;

function SendSMS($uid, $contact_id, $dept, $phone, $email, $text, $priority) {
    mb_internal_encoding("UTF-8");

    $rid = "R" . substr("0000$contact_id", -5);

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
    $order = array("|"); // проблемный символ, зависает отправка
    $at = "komy $contact_id wt	$dept	$phone	$email" . str_replace($order, "/", $txt) . "\r\n\r\n";
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

    $fname = $priority . $priority . "-$uid-" . rand(10, 99) . "-$rid.txt";

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
use \DateTime;

// Create SMS file
if ($stmt = $db->prepare("SELECT `recipient`.id AS id, `recipient`.contact_id AS contact_id, email_only, `contact`.name AS name, dept, mobile, `contact`.email AS tomail, `group`.email AS frommail, `group`.name AS fname, text, sign, uid, filename, priority, put FROM `sms`, `recipient`, `contact`, `user`, `group` WHERE `sms`.user_id = `user`.id AND `user`.group_id = `group`.id AND `recipient`.contact_id = `contact`.id AND `recipient`.sms_id = `sms`.id AND `recipient`.status = 0 AND `sms`.put > '0000-00-00 00:00:00'")) {
    $stmt->execute();
    $result = $stmt->get_result();
    $mail = new PHPMailer(true);
    //Server settings
    $mail->SMTPDebug = 0;                                 // Enable verbose debug output
    //$mail->SMTPKeepAlive = true;
    //$mail->isSMTP();                                      // Set mailer to use SMTP
    $mail->isMail();                                      // Set mailer to use mail()
    //$mail->Host = $host;                                // Specify main and backup SMTP servers
    //$mail->SMTPAuth = true;                               // Enable SMTP authentication
    //$mail->Username = $muser;                                // SMTP username
    //$mail->Password = $mpass;                               // SMTP password
    //$mail->SMTPSecure = $SMTPSecure;                            // Enable TLS encryption, `ssl` also accepted
    //$mail->Port = $port;                                    // TCP port to connect to
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
            if(strlen($row["filename"]) > 0) {
                $dtput = new DateTime($row["put"]);
                $filename = $dtput->format('Y/m/d/') . $row["filename"];
            }
            else {
                $filename = '';
            }
        }
        $status = STATUS_NONE;
        if($row["mobile"] < MIN_PHONE_NUM) $row["email_only"] = 1; // wrong mobile number
        if($row["email_only"] < 1) { // Skip SMS if e-mail only
            if(SendSMS($row["uid"], $row["contact_id"], $row["dept"], $row["mobile"], $row["tomail"], $row["text"], $row["priority"])) {
                $status = $status | STATUS_INIT;
                $stmt = $db->prepare("UPDATE recipient SET sent = NOW(), status = ? WHERE id = ?");
                $stmt->bind_param("ii", $status, $row["id"]);
                $stmt->execute();
            }
            else {
                $status = $status | STATUS_EMAIL_ONLY;
                $stmt = $db->prepare("UPDATE recipient SET sent = NOW(), status = ? WHERE id = ?");
                $stmt->bind_param("ii", $status, $row["id"]);
                $stmt->execute();
            }
        }

        if($uid == $row["uid"]) {
            if($row["tomail"] != "") {
                $mail->addAddress($row["tomail"], $row["name"]);     // Add address to multirecipient email
                $sendmail = true;
                if($row["email_only"] > 0) {
                    $status = $status | STATUS_EMAIL_SENT;
                    $stmt = $db->prepare("UPDATE recipient SET sent = NOW(), done = NOW(), status = ? WHERE id = ?");
                    $stmt->bind_param("ii", $status, $row["id"]);
                    $stmt->execute();
                }
                else {
                    $status = $status | STATUS_EMAIL_SENT;
                    $stmt = $db->prepare("UPDATE recipient SET status = ? WHERE id = ?");
                    $stmt->bind_param("ii", $status, $row["id"]);
                    $stmt->execute();
                }
            }
            else { // blank email
                if($row["email_only"] > 0) {
                    $status = $status | STATUS_NO_EMAIL;
                    $stmt = $db->prepare("UPDATE recipient SET sent = NOW(), done = NOW(), status = ? WHERE id = ?");
                    $stmt->bind_param("ii", $status, $row["id"]);
                    $stmt->execute();
                }
                else {
                    $status = $status | STATUS_NO_EMAIL;
                    $stmt = $db->prepare("UPDATE recipient SET status = ? WHERE id = ?");
                    $stmt->bind_param("ii", $status, $row["id"]);
                    $stmt->execute();
                }
            }
        }
        else { // Sent email and start next message
            if($sendmail) {
                $mail->Subject = $subject;
                $mail->setFrom($cc, $fname);
                //$mail->addCC($cc);
                $mail->Body = $body;
                if(strlen($filename) > 0) $mail->addAttachment("/var/www/html/sms2/send/files/$filename");
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
            if(strlen($filename) > 0) $mail->addAttachment("/var/www/html/sms2/send/files/$filename");
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
if ($stmt = $db->prepare("SELECT recipient.id AS id, contact_id, uid, recipient.status AS status FROM `sms`, `recipient`, `contact`, `user`, `group` WHERE user_id = user.id AND group_id = group.id AND contact_id = contact.id AND sms_id = sms.id AND (recipient.status & 1) > 0 AND put >= (NOW() - INTERVAL 1 DAY)")) {
    //$stmt->bind_param("i", 0);
    $stmt->execute();
    $result = $stmt->get_result();
    while($row = $result->fetch_array(MYSQLI_ASSOC)) {
        $present = false;
        for ($i = 1; $i <= PHONES_QTY; $i++) {
            $response = file(dirname(__FILE__) . "/in/smsVB$i.txt");
            foreach($response as $line) {
                $rid = "R" . substr("0000" . $row["contact_id"], -5);
                if((strpos($line, $row["uid"]) !== false) AND ((strpos($line, $rid) !== false))) {
                    $present = true;
                    $status = $row["status"] | STATUS_QUE;
                    $stmt = $db->prepare("UPDATE recipient SET phone = $i, status = ? WHERE id = ?");
                    $stmt->bind_param("ii", $status, $row["id"]);
                    $stmt->execute();
                }
            }
        }
        if(!$present) {
            if(($row["status"] & STATUS_QUE) > 0 && ($row["status"] & STATUS_SMS_SENT) <= 0) {
                $status = $row["status"] | STATUS_SMS_SENT;
                $status = $status ^ STATUS_QUE;
                $status = $status ^ STATUS_INIT;
                $stmt = $db->prepare("UPDATE recipient SET done = NOW(), status = ? WHERE id = ?");
                $stmt->bind_param("ii", $status, $row["id"]);
                $stmt->execute();
            }
        }
    }
}
