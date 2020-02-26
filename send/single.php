<?php
require('param.php');

function SendSingleSMS($uid, $phone, $text, $priority) {
    mb_internal_encoding("UTF-8");

    $rid = "R00000";

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
    $at = "komy User wt	RT	$phone	user@rt.ru" . str_replace($order, "/", $txt) . "\r\n\r\n";
    $at .= "ATZ^\r\n";
    $at .= "1 pause\r\n";
    $at .= "ATE0^\r\n";
    $at .= "1 pause\r\n";

    foreach ($arText as $text) {

        $size = substr("0" . strtoupper(dechex(mb_strlen($text) * 2 + 6)), -2); // длина сообщения в HEX для вставки в хедер SMS

        $usc2 = mb_convert_encoding($text, "UCS-2BE", "UTF-8"); 
        $hex = strtoupper(bin2hex($usc2));

        $snum = substr("0".strtoupper(dechex(++$num)), -2);
        $pdu_data = "0051000B91" . $pdu_number . "000808" . $size . "050003" . $rand . $count . $snum . $hex; // Начало формирования, заголовок + номер + текст
        $length = (strlen($pdu_data) - 2) / 2; // длина для AT+CMGS=

        $at .= "AT+CMGS=$length^\r\n";
        $at .= "1 pause\r\n";
        $at .= "$pdu_data|\r\n";
        $at .= "8 pause\r\n";

    } //foreach

    $fname = $priority . $priority . "-$uid-" . rand(10, 99) . "-$rid.txt";

    $fout = fopen(__DIR__ . "/out/$fname", "w");
    if(!$fout) {
        $err = error_get_last();
        return $err["message"];
    }
    fwrite($fout, mb_convert_encoding($at, "CP1251", "UTF-8"));
    fclose($fout);

    return true;
}

// Add to table
function AddHistory(&$db, $phone, $subject, $text, $user_id, $userip, $uid, $put, $priority) {
    $res = false;
    if ($stmt = $db->prepare("SELECT group_id, group.name AS gname FROM `user`, `group` WHERE group_id = `group`.id AND user.id = ?")) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_array(MYSQLI_ASSOC);
        $group_id = $row["group_id"];
        $group = $row["gname"];
    }
    $txt = mb_substr($text, 0, MAX_SMS_LENGTH - strlen($group) - 3) . " ($group)";

    if($stmt = $db->prepare("INSERT INTO sms (subject_id, text, user_id, ip, gid, uid, priority, put) VALUES (?, ?, ?, ?, ?, ?, ?, ?)")) {
        $stmt->bind_param("isisisis", $subject, $txt, $user_id, $userip, $group_id, $uid, $priority, $put);
        $stmt->execute();
        $sms_id = $db->insert_id;
        if ($stmt = $db->prepare("INSERT INTO recipient (sms_id, contact_id, email_only, status, single) VALUES (?, 0, 0, 0, ?)")) {
            $stmt->bind_param("is", $sms_id, $phone);
            $stmt->execute();
            $res = $db->insert_id;
        }
    }
    return $res;
}


function getName(&$db, $AuthKey) {
    $res = false;
    if ($stmt = $db->prepare("SELECT id FROM `user` WHERE auth_key = ?")) {
        $stmt->bind_param("s", $AuthKey);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_array(MYSQLI_ASSOC);
        $res = $row["id"];
    }
    return $res;
}

// Main
$year = $datetime->format('Y');
$mon = $datetime->format('m');
$day = $datetime->format('d');
$dir = $year . "/" . $mon . "/" . $day;

if (true || isset($_POST["authkey"]) && isset($_POST["text"]) && isset($_POST["phone"])) {
    $userip = trim($_POST["userip"]);
    if(isset($_POST["subject"]))
        $subject = trim($_POST["subject"]);
    else
        $subject = 1;
    $text = trim($_POST["text"]);
    $order = array("\r", "\t", "\0", "\x0B", "^");
    $text = str_replace($order, ' ', $text);
    $text = mb_ereg_replace('/\h+/', ' ', $text);
    $AuthKey = $_POST["authkey"];

    if($user_id = getName($db, $AuthKey)) {
        $uid = $datetime->format('Ymd-His-') . substr("000$user_id", -4);
        $put = $datetime->format('Y-m-d H:i:s');
        if(strlen($text) > 5) {
            $phone = trim($_POST["phone"]);
            $priority = trim($_POST["priority"]);
            $rid = AddHistory($db, $phone, $subject, $text, $user_id, $userip, $uid, $put, $priority);
            if($rid !== false) {
                $status = STATUS_NONE;
                if($phone >= MIN_PHONE_NUM) {
                    if(SendSingleSMS($uid, $phone, $text, $priority)) {
                        $status = $status | STATUS_INIT;
                        $stmt = $db->prepare("UPDATE recipient SET sent = NOW(), status = ? WHERE id = ?");
                        $stmt->bind_param("ii", $status, $rid);
                        $stmt->execute();
                        echo "Отправлено";
                    }
                    else echo "Ошибка отправки";
                }
                else echo "Неправильный номер";
            }
            else echo "Ошибка БД";
        }
        else echo "Пустое сообщение";
    }
    else echo "Ошибка авторизации";
}

$db->close();
