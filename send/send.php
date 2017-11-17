<?php
require('param.php');

function AddHistory3(&$db, $contacts, $text, $user_id, $uid) {
    $res = false;
    $mtext = $db->real_escape_string($text);
    if ($stmt = $db->prepare("SELECT group_id, group.name AS gname FROM `user`, `group` WHERE group_id = `group`.id AND user.id = ?")) {
        $stmt->bind_param("i", $user_id);
        if (!$stmt->execute())
        {
            // handle error
        }
        $result = $stmt->get_result();
        $row = $result->fetch_array(MYSQLI_ASSOC);
        $group_id = $row["group_id"];
        $group = $row["gname"];
    }
    $txt = mb_substr($mtext, 0, MAX_SMS_LENGTH - strlen($group) - 3) . " ($group)";
    if($stmt = $db->prepare("INSERT INTO sms (text, put, user_id, gid, uid) VALUES (?, NOW(), ?, ?, ?)")) {
        $stmt->bind_param("siis", $txt, $user_id, $group_id, $uid);
        if (!$stmt->execute())
        {
            // handle error
        }
        $sms_id = $db->insert_id;
        foreach($contacts as $contact_id) {
            if(strpos($contact_id, "-") > 0) $email_only = 1;
            else $email_only = 0;
            $contact_id = intval($contact_id);
            if ($stmt = $db->prepare("INSERT INTO recipient (sms_id, contact_id, email_only, status) VALUES (?, ?, ?, 0)")) {
                $stmt->bind_param("iii", $sms_id, $contact_id, $email_only);
                if (!$stmt->execute())
                {
                    // handle error
                }
                $res = true;    
            }
        }
    }
    return $res;
}


function getName(&$db, $AuthKey) {
    $res = false;
    if ($stmt = $db->prepare("SELECT id FROM `user` WHERE auth_key = ?")) {
        $stmt->bind_param("s", $AuthKey);
        if (!$stmt->execute())
        {
            // handle error
        }
        $result = $stmt->get_result();
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
    $AuthKey = $db->real_escape_string($_POST["authkey"]);
    if($user_id = getName($db, $AuthKey)) {
        $uid = $datetime->format('Ymd-His-') . substr("000$user_id", -4);
        if(strlen($text) > 5) {
            $phones = explode("; ", trim($_POST["phones"]));
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
