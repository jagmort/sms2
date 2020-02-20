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

if($stmt = $db->prepare("SELECT uid, contact.name AS name, position, mobile, dept, subject_id, text, argus, sms.recovery AS recovery, sent, done, recipient.status AS status, username, `group`.name AS gname, phone, contact.email AS email, filename, put FROM recipient, sms, contact, user, `group` WHERE put >= ? AND put <= (? + INTERVAL 1 DAY) AND user.id = sms.user_id AND recipient.contact_id = contact.id AND recipient.sms_id = sms.id AND `group`.id = gid AND sms.gid IN (SELECT group_id FROM user, `group` WHERE `group`.id = group_id AND auth_key = ?) ORDER BY uid DESC, name ASC")) {

    $stmt->bind_param("sss", $from_date, $to_date, $authkey);
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
<tr><th>ID</th><th>User</th><th>Subj</th><th>Text</th><th>Argus</th><th>To</th><th>Created</th><th>SMS Sent</th><th>Status</th></tr>
<?php
    $i = 0;
    $uid = '';
    while($row = $result->fetch_array(MYSQLI_ASSOC)) {
        $arr = preg_split("/[\s,_]+/", $row["name"]);
        $contact = '<span title="' . htmlentities($row["name"]) . "\n" . htmlentities($row["position"]) . "\n" . $row["mobile"] . "\n" . htmlentities($row["email"]) . '">' . $arr[0] . ' ' . mb_substr($arr[1], 0, 1) . ' ' . mb_substr($arr[2], 0, 1) . "</span> (" . $row["dept"] . ")";
        if($uid !== $row["uid"]) {
            if($uid !== "") {
                if($username == getName($db, $authkey)) {
                    echo '<tr' . (($i & 1) ? ' class="myodd"' : ' class="my"') . '>';
                }
                else echo '<tr' . (($i & 1) ? ' class="odd"' : '') . '>';
                echo "<td>$uid</td><td>$username</td><td>$subject</td><td>$text $filename</td><td>$argus</td><td>$name</td><td>$sent</td><td>$done</td><td>$status</td>";
                echo "</tr>\n";
            }
            $i++;
            $uid = $row["uid"];
            $username = $row["username"];
            $group = $row["gname"];
            $name = $contact;
            $subject = $row["subject_id"];
            $text = $row["text"];
            $text = str_replace("\n", '<br />', $text);
            $argus = ($row["argus"] > 0 ? $row["argus"] : "") . ($row["recovery"] > 0 ? "<br />+" : "");
            if(strlen($row["filename"]) > 0) {
                $dtput = new DateTime($row["put"]);
                $filename = '<a href="/sms2/send/files/' . $dtput->format('Y/m/d/') . $row["filename"] . '">&#128193;</a>';
            }
            else $filename = '';
            if($row["sent"] != DATE0)
                $sent = DateTime::createFromFormat('Y-m-d H:i:s', $row["sent"])->format('d/m H:i');
            else
                $sent = '—';
            if($row["done"] != DATE0)
                $done = DateTime::createFromFormat('Y-m-d H:i:s', $row["done"])->format('d/m H:i');
            else
                $done = '—';
            $status = $row["status"]; 
        }
        else {
            $name .= "<br />\n" . $contact;
            if($row["done"] != DATE0)
                $done .= "<br />\n" . DateTime::createFromFormat('Y-m-d H:i:s', $row["done"])->format('d/m H:i');
            else
                $done .= "<br />\n—";

            $status .= "<br />\n" . $row["status"]; 
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
            $status .= "; <i>No e-mail</i>";
        else {
            if((($row["status"] & STATUS_EMAIL_SENT) > 0) && (($row["status"] & (STATUS_SMS_SENT | STATUS_INIT)) <= 0))
                $status .= "; <i>E-mail only</i>";
        }
        // ---------------

    }
    if($uid !== "") {
        if($username == getName($db, $authkey)) {
            echo '<tr' . (($i & 1) ? ' class="myodd"' : ' class="my"') . '>';
        }
        else echo '<tr' . (($i & 1) ? ' class="odd"' : '') . '>';
        echo "<td>$uid</td><td>$username</td><td>$subject</td><td>$text $filename</td><td>$argus</td><td>$name</td><td>$sent</td><td>$done</td><td>$status</td>";
        echo "</tr>\n";
    }
?>
</table>

<?php
}

$db->close();
