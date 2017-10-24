<?php
require('param.php');

function AddHistory3(&$db, $contacts, $text, $user_id, $uid) {
    $res = false;
    $mtext = $db->real_escape_string($text);
    if ($result = $db->query("SELECT group_id, group.name AS gname FROM `user`, `group` WHERE user.id = '$user_id'")) {
        $row = $result->fetch_array(MYSQLI_ASSOC);
        $group_id = $row["group_id"];
        $group = $row["gname"];
    }
    $txt = mb_substr($mtext, 0, MAX_SMS_LENGTH - strlen($group) - 3) . " ($group)";
    if($result = $db->query("INSERT INTO sms (text, put, user_id, gid, uid) VALUES ('$txt', NOW(), '$user_id', '$group_id', '$uid')")) {
        $sms_id = $db->insert_id;
        foreach($contacts as $contact_id) {
            if(strpos($contact_id, "-") > 0) $email_only = 1;
            else $email_only = 0;
            $contact_id = intval($contact_id);
            if ($result = $db->query("INSERT INTO recipient (sms_id, contact_id, email_only, status) VALUES ($sms_id, $contact_id, $email_only, 0)")) {
                $res = true;    
            }
        }
    }
    return $res;
}


function getName(&$db, $AuthKey) {
    $res = false;
    if ($result = $db->query("SELECT id FROM `user` WHERE auth_key = '$AuthKey'")) {
        $row = $result->fetch_array(MYSQLI_ASSOC);
        $res = $row["id"];
    }
    return $res;
}

// Main

if (isset($_POST["authkey"]) && isset($_POST["text"]) && isset($_POST["phones"])) {
    $text = trim($_POST["text"]);
    $order = array("\n", "\r", "\t", "\0", "\x0B");
    $text = str_replace($order, ' ', $text);
    $text = preg_replace('/\s+/', ' ', $text);
    $AuthKey = $_POST["authkey"];
    if($user_id = getName($db, $AuthKey)) {
        $uid = $datetime->format('Ymd-His-') . substr("000$user_id", -4);
        if(strlen($text) > 5) {
            //$text = htmlspecialchars($text);
            $phones = explode("; ", trim($_POST["phones"]));
            //if(AddHistory2($db, $phones, $text, $user_id, $uid)) {
            if(AddHistory3($db, $phones, $text, $user_id, $uid)) {
                echo "В очереди на отправку";
            }
            else echo "Ошибка отправки";
        }
        else echo "Пустое сообщение";
    }
    else echo "Ошибка авторизации";
}

$db->close();
