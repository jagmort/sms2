<?php
require('param.php');
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

if($stmt = $db->prepare("SELECT uid, contact.name AS name, position, mobile, dept, text, sent, done, recipient.status AS status, username, `group`.name AS gname, phone, contact.email AS email FROM recipient, sms, contact, user, `group` WHERE put >= ? AND put <= (? + INTERVAL 1 DAY) AND user.id = sms.user_id AND recipient.contact_id = contact.id AND recipient.sms_id = sms.id AND `group`.id = gid AND sms.gid IN (SELECT group_id FROM user, `group` WHERE `group`.id = group_id AND auth_key = ?) ORDER BY put DESC, name ASC")):

$stmt->bind_param("sss", $from_date, $to_date, $authkey);
$stmt->execute();
$result = $stmt->get_result();

// очереди на телефонах
$webdir = "/var/www/html/sms2/send";
$files = array();
$sum = 0;
$out = '';
for ($i = 1; $i < 6; $i++) {
    unset($response);
    $response = file("$webdir/in/smsVB$i.txt");
    $count = sizeof($response);
    if($i > 1)
        $out .= " + ";
    if($count > 20) 
        $out .= "<span class=\"max\">$count</span>";
    else 
        $out .= $count;
    $sum += $count;
}
$out .= " = $sum";
if ($sum > 0):
    echo '<p';
    if($sum > 100)
        echo ' class="max"';
    echo '>Queue: ' . $out . '</p>';
endif;
// ---------------------

?>
<table class="history">
<tr><th>ID</th><th>User</th><th>Text</th><th>To</th><th>Created</th><th>SMS Sent</th><th>Status</th></tr>
<?php
    $i = 0;
    $uid = '';
    while($row = $result->fetch_array(MYSQLI_ASSOC)):
        $arr = preg_split("/[\s,_]+/", $row["name"]);
        $contact = '<span title="' . htmlentities($row["name"]) . "\n" . htmlentities($row["position"]) . "\n" . $row["mobile"] . "\n" . htmlentities($row["email"]) . '">' . $arr[0] . ' ' . mb_substr($arr[1], 0, 1) . ' ' . mb_substr($arr[2], 0, 1) . "</span> (" . $row["dept"] . ")";
        if($uid !== $row["uid"]):
            if($uid !== ""):
                echo '<tr' . (($i & 1) ? ' class="odd"' : '') . '>';
                echo "<td>$uid</td><td>$username</td><td>$text</td><td>$name</td><td>$sent</td><td>$done</td><td>$status</td>";
                echo "</tr>\n";
            endif;
            $i++;
            $uid = $row["uid"];
            $username = $row["username"];
            $group = $row["gname"];
            $name = $contact;
            $text = $row["text"];
            if($row["sent"] != DATE0)
                $sent = DateTime::createFromFormat('Y-m-d H:i:s', $row["sent"])->format('d/m H:i');
            else
                $sent = '—';
            if($row["done"] != DATE0)
                $done = DateTime::createFromFormat('Y-m-d H:i:s', $row["done"])->format('d/m H:i');
            else
                $done = '—';
            $status = $row["status"]; 

        else:
            $name .= "<br />\n" . $contact;
            if($row["done"] != DATE0)
                $done .= "<br />\n" . DateTime::createFromFormat('Y-m-d H:i:s', $row["done"])->format('d/m H:i');
            else
                $done .= "<br />\n—";

            $status .= "<br />\n" . $row["status"]; 
        endif;

        // статус отправки
        if(($row["status"] & 4) > 0):
            $status .= "; Sent " . $row["phone"];
        else:
            if(($row["status"] & 2) > 0) $status .= "; Queue " . $row["phone"];
            if(($row["status"] & 8) > 0) $status .= "; <b>Error</b>";
            if(($row["status"] & 15) == 1) $status .= "; <b>SMS not sent</b>";
        endif;
        if(($row["status"] & 64) > 0):
            $status .= "; <i>No e-mail</i>";
        elseif(($row["status"] & 16) > 0):
            if(($row["status"] & 15) <= 0)
                $status .= "; <i>E-mail only</i>";
        endif;
        // ---------------

    endwhile;
    if($uid !== ""):
        echo '<tr' . (($i & 1) ? ' class="odd"' : '') . '>';
        echo "<td>$uid</td><td>$username</td><td>$text</td><td>$name</td><td>$sent</td><td>$done</td><td>$status</td>";
        echo "</tr>\n";
    endif;
?>
</table>

<?php
endif;

$db->close();
