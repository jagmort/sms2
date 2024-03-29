<?php
require('param.php');

function getName(&$db, $AuthKey) {
    $res = false;
    if ($stmt = $db->prepare("SELECT username FROM `user` WHERE auth_key = ?")) {
        $stmt->bind_param("s", $AuthKey);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_array(MYSQLI_ASSOC);
        $res = $row["username"];
    }
    return $res;
}

$authkey = $db->real_escape_string($_POST["authkey"]);
try {
    $datetime1 = new DateTime($_POST["from_date"], new DateTimeZone('Europe/Moscow'));
} catch (Exception $e) {
    $datetime1 = new DateTime(null, new DateTimeZone('Europe/Moscow'));
}
try {
    $datetime2 = new DateTime($_POST["to_date"], new DateTimeZone('Europe/Moscow'));
} catch (Exception $e) {
    $datetime2 = new DateTime(null, new DateTimeZone('Europe/Moscow'));
}
$from_date = $datetime1->format('Y-m-d');
$to_date = $datetime2->format('Y-m-d');

if(!empty($_POST['argus'])) {
    $query = 'argus = ' . intval($_POST['argus']) . ' AND ';
    $datetime1->sub(new DateInterval('P1Y'));
}
else
    $query = '';

$from_date = $datetime1->format('Y-m-d');
$to_date = $datetime2->format('Y-m-d');
    
if($stmt2 = $db->prepare("SELECT admin, group_id FROM `user` WHERE auth_key = ?")) {
    $stmt2->bind_param("s", $authkey);
    $stmt2->execute();
    $result2 = $stmt2->get_result();
    $row2 = $result2->fetch_array(MYSQLI_ASSOC);
    if($row2["admin"] > USER_SUPERVISOR)
        $query .= "0 <";
    else
        $query .= "sms.gid =";
}

if($stmt = $db->prepare("SELECT uid, contact.id AS cid, contact.name AS name, position, mobile, dept, list_id, `subject`.text AS subject, `list`.name AS list, `sms`.text AS text, argus, sms.recovery AS recovery, put, sent, done, recipient.status AS status, username, `group`.name AS gname, phone, contact.email AS email, filename, recipient.single AS single, message_id FROM `recipient`, `sms`, `contact`, `user`, `group`, `list`, `subject` WHERE subject_id = `subject`.id AND list_id = `list`.id AND put >= ? AND put <= (? + INTERVAL 1 DAY) AND user.id = sms.user_id AND recipient.contact_id = contact.id AND recipient.sms_id = sms.id AND `group`.id = gid AND " . $query . " ? ORDER BY uid DESC, name ASC")) {

    $stmt->bind_param("ssi", $from_date, $to_date, $row2["group_id"]);
    $stmt->execute();
    $result = $stmt->get_result();

    // очереди на телефонах
    $webdir = "/var/www/html/sms2/send";
    $files = array();
    $sum = 0;
    $out = '';
    for ($i = 1; $i <= PHONES_QTY; $i++) {
        unset($response);
        $response = file("$webdir/in/smsVB$i.txt");
        $count = sizeof($response);
        if($i > 1)
            $out .= " + ";
        if($count > PHONES_QUE) 
            $out .= "<span class=\"max\">$count</span>";
        else 
            $out .= $count;
        $sum += $count;
    }
    $out .= " = $sum";
    if ($sum > 0) {
        echo '<p';
        if($sum > PHONES_ALL)
            echo ' class="max"';
        echo '>Lines: ' . $out . '</p>';
    }
    // ---------------------

?>
<table class="history">
<tr><th>ID</th><th>User</th><th>Text</th><th>Argus</th><th>To</th><th>Created</th><th>Sent</th><th>Status</th></tr>
<?php
    $i = 0;
    $uid = '';
    $answer = '';
    while($row = $result->fetch_array(MYSQLI_ASSOC)) {
        // get replies
        if($stmt2 = $db->prepare("SELECT email, text FROM `contact`, `telegram` WHERE chat_id = `contact`.telegram AND `telegram`.reply_to_message_id > 0 AND `telegram`.reply_to_message_id = ?;")) {

            $stmt2->bind_param("i", $row["message_id"]);
            $stmt2->execute();
            $result2 = $stmt2->get_result();
            while($row2 = $result2->fetch_array(MYSQLI_ASSOC)) {
                $answer .= '<br />' . $row2["email"] . ': ' . $row2["text"];
            }
        }
        $arr = mb_split("[\s,_]+", $row["name"]);
        if($row["cid"] != 0)
            $contact = '<span title="' . htmlentities($row["name"]) . "\n" . htmlentities($row["position"]) . "\n" . $row["mobile"] . "\n" . htmlentities($row["email"]) . '">' . str_replace("'", "", $arr[0]) . ' ' . mb_substr($arr[1], 0, 1) . ' ' . mb_substr($arr[2], 0, 1) . "</span>" . (strlen($row["dept"]) > 0 ? " (" . mb_substr($row["dept"], 0, 60) . ")" : '');
        else
            $contact = '<span>' . $row["single"] . '</span>';
        if($uid !== $row["uid"]) {
            if($uid !== "") {
                if($username == getName($db, $authkey)) {
                    echo '<tr' . (($i & 1) ? ' class="myodd"' : ' class="my"') . '>';
                }
                else echo '<tr' . (($i & 1) ? ' class="odd"' : '') . '>';
                echo '<td title="Копировать" class="id">' . $uid . '</td><td>' . $username . '</td><td class="text">' . ($list != 'Blank' ? "<span>$list ($list_id)</span>" : '') . "$text $filename" . ($answer != '' ? "<span class=\"answer\">$answer</span>" : '') . "</td><td><a href=\"" . COPY_URL . "?argus=$argus\" target=\"_blank\">$argus</a>$recovery</td><td>$name</td><td>$sent</td><td>$done</td><td>$status</td>";
                echo "</tr>\n";
            }
            $answer = '';
            $i++;
            $uid = $row["uid"];
            $username = $row["username"];
            $group = $row["gname"];
            $name = $contact;
            $subject = $row["subject"];
            $list_id = $row["list_id"];
            $list = $row["list"];
            $text = $row["text"];
            $text = str_replace("\n", '<br />', $text);
            $argus = ($row["argus"] > 0 ? $row["argus"] : "");
            $recovery = $row["recovery"] > 0 ? "<br />+" : ""; 
            if(strlen($row["filename"]) > 0) {
                $dtput = new DateTime($row["put"]);
                $filename = '<a href="/sms2/send/files/' . $dtput->format('Y/m/d/') . $row["filename"] . '">&#128193;</a>';
            }
            else $filename = '';
            if($row["put"] != DATE0)
                $sent = DateTime::createFromFormat('Y-m-d H:i:s', $row["put"])->format('d/m H:i');
            else
                $sent = '—';
            if($row["done"] != DATE0)
                $done = DateTime::createFromFormat('Y-m-d H:i:s', $row["done"])->format('d/m H:i');
            else
                $done = '—';
            $status = dechex($row["status"]); 
        }
        else {
            $name .= "<br />\n" . $contact;
            if($row["done"] != DATE0)
                $done .= "<br />\n" . DateTime::createFromFormat('Y-m-d H:i:s', $row["done"])->format('d/m H:i');
            else
                $done .= "<br />\n—";

            $status .= "<br />\n" . dechex($row["status"]); 
        }

        // статус отправки
        if(($row["status"] & STATUS_SMS_SENT) > 0)
            $status .= "; Sent " . $row["phone"];
        else {
            if(($row["status"] & STATUS_QUE) > 0) $status .= "; Queue " . $row["phone"];
            if(($row["status"] & STATUS_EMAIL_ONLY) > 0) $status .= "; <b>Error</b>";
            if(($row["status"] & STATUS_INIT) == 1) $status .= "; <b>SMS not sent</b>";
        }
        if(($row["status"] & STATUS_NO_EMAIL) > 0)
            $status .= "; <i>no e-mail</i>";
        else {
            if((($row["status"] & STATUS_EMAIL_SENT) > 0) && ((($row["status"] & STATUS_TELEGRAM_SENT) <= 0) && ($row["status"] & (STATUS_SMS_SENT | STATUS_INIT)) <= 0))
                $status .= "; <i>e-mail</i>";
        }
        if(($row["status"] & STATUS_TELEGRAM_SENT) > 0)
            $status .= "; <i>telegram</i>";
        // ---------------

    }
    if($uid !== "") {
        if($username == getName($db, $authkey)) {
            echo '<tr' . (($i & 1) ? ' class="myodd"' : ' class="my"') . '>';
        }
        else echo '<tr' . (($i & 1) ? ' class="odd"' : '') . '>';
        echo '<td title="Копировать" class="id">' . $uid . '</td><td>' . $username . '</td><td class="text">' . ($list != 'Blank' ? "<span>$list ($list_id)</span>" : '') . "$text $filename" . ($answer != '' ? "<span class=\"answer\">$answer</span>" : '') . "</td><td><a href=\"". COPY_URL . "?argus=$argus\" target=\"_blank\">$argus</a>$recovery</td><td>$name</td><td>$sent</td><td>$done</td><td>$status</td>";
        echo "</tr>\n";
    }
?>
</table>

<?php
}

$db->close();
