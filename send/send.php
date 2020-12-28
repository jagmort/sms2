<?php
require('param.php');

function AddHistory3(&$db, $contacts, $subject, $list, $text, $user_id, $userip, $uid, $put, $name, $priority, $recovery) {
    $res = false;
    if ($stmt = $db->prepare("SELECT group_id, group.name AS gname, recovery FROM `user`, `group` WHERE group_id = `group`.id AND user.id = ?")) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_array(MYSQLI_ASSOC);
        $group_id = $row["group_id"];
        $group = $row["gname"];
    }
    $txt = mb_substr($text, 0, MAX_SMS_LENGTH - strlen($group) - 3) . " ($group)";
    $argus = 0;
    if($row["recovery"] > 0) {
        if(preg_match_all('/(ПРМОН|ГП\ СПД|АВР_SQM)\-(\d+)/', $txt, $reg, PREG_SET_ORDER))
            if(count($reg) < 3)
                $argus = $reg[0][2];
    }
    if($stmt = $db->prepare("INSERT INTO sms (subject_id, list_id, text, argus, recovery, user_id, ip, gid, uid, filename, priority) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)")) {
        $stmt->bind_param("iisiiisissi", $subject, $list, $txt, $argus, $recovery, $user_id, $userip, $group_id, $uid, $name, $priority);
        $stmt->execute();
        $sms_id = $db->insert_id;
        foreach($contacts as $contact_id) {
            if(strpos($contact_id, "-") > 0) $email_only = 1;
            else $email_only = 0;
            $contact_id = intval($contact_id);
            if ($stmt = $db->prepare("INSERT INTO recipient (sms_id, contact_id, email_only, status) VALUES (?, ?, ?, 0)")) {
                $stmt->bind_param("iii", $sms_id, $contact_id, $email_only);
                $stmt->execute();
                $res = true;
            }
        }
        if($stmt = $db->prepare("UPDATE `sms` SET `put`=? WHERE `id`=?")) {
            $stmt->bind_param("si", $put, $sms_id);
            $stmt->execute();
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

function mkdir_force($dir) {
    $parts = explode('/', $dir);
    $dir = 'files';
    foreach($parts as $part)
        if(!is_dir($dir .= "/$part")) mkdir($dir);
    //file_put_contents("$dir/$file", $contents);
}

// Main
$year = $datetime->format('Y');
$mon = $datetime->format('m');
$day = $datetime->format('d');
$dir = $year . "/" . $mon . "/" . $day;
$name = '';

if (isset($_POST["authkey"]) && isset($_POST["text"]) && isset($_POST["phones"])) {
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
        $str_phones = trim($_POST['phones']);
        if(strlen($text) > 5 && (mb_strpos($str_phones, ';') !== FALSE)) {
            $exp = explode(": ", $str_phones);
            $count = count($exp);
            $list = $count > 1 ? $exp[0] : 0;
            $phones = explode("; ", $exp[$count - 1]);
            $priority = trim($_POST["priority"]);
            if($_FILES["file"]["error"] === UPLOAD_ERR_OK) {
                mkdir_force($dir);
                $name = $datetime->format('His-') . $_FILES['file']['name'];
                move_uploaded_file($_FILES['file']['tmp_name'], "files/$dir/$name");
            }
            $recovery = isset($_POST["recovery"]) ? $_POST["recovery"] : 0;
            if(AddHistory3($db, $phones, $subject, $list, $text, $user_id, $userip, $uid, $put, $name, $priority, $recovery)) {
                echo "Отправлено";
            }
            else echo "Ошибка отправки";
        }
        else echo "Пустое сообщение";
    }
    else echo "Ошибка авторизации";
}

$db->close();
