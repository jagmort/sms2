<?php
require('param.php');
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
if ($result = $db->query("SELECT uid, contact.name AS name, dept, text, sent, done, recipient.status AS status, username, `group`.name AS gname, phone FROM recipient, sms, contact, user, `group` WHERE put >= '$from_date' AND put <= ('$to_date' + INTERVAL 1 DAY) AND user.id = user_id AND contact_id = contact.id AND sms_id = sms.id AND `group`.id = gid ORDER BY put DESC, contact.`order` DESC, name ASC")) {
?>
<table class="history">
<tr>
<th>ID</th>
<th>User</th>
<th>Text</th>
<th>To</th>
<th>Created</th>
<th>SMS Sent</th>
<th>Status</th>
</tr>
<?php
    $i = 0;
    $uid = '';
    while($row = $result->fetch_array(MYSQLI_ASSOC)) {
        if($uid !== $row["uid"]) {
            if($uid !== "") {
?>
<tr<?= ($i & 1) ? ' class="odd"' : '' ?>>
<td><?= $uid ?></td>
<td><?= $username ?></td>
<td><?= $text ?></td>
<td><?= $name ?></td>
<td><?= $sent ?></td>
<td><?= $done ?></td>
<td><?= $status ?></td>
</tr>
<?php
            }
            $i++;
            $uid = $row["uid"];
            $username = $row["username"];
            $group = $row["gname"];
            $name = $row["name"] . " (" . $row["dept"] . ")";
            $text = $row["text"];
            if($row["sent"] != DATE0) $sent = DateTime::createFromFormat('Y-m-d H:i:s', $row["sent"])->format('d/m H:i');
            else $sent = '—';
            if($row["done"] != DATE0) $done = DateTime::createFromFormat('Y-m-d H:i:s', $row["done"])->format('d/m H:i');
            else $done = '—';
            $status = $row["status"]; 
            if(($row["status"] & 4) > 0) {
                $status .= "; Sent " . $row["phone"];
            }
            else {
                if(($row["status"] & 2) > 0) {
                    $status .= "; Queue " . $row["phone"];
                }
                if(($row["status"] & 8) > 0) {
                    $status .= "; Error";
                }
            }
            if(($row["status"] & 64) > 0) {
                $status .= "; No e-mail";
            }
            else {
                if(($row["status"] & 16) > 0) {
                    $status .= "; E-mail";
                }
            }
        }
        else {
            $name .= "<br />\n" . $row["name"] . " (" . $row["dept"] . ")";
            if($row["done"] != DATE0) $done .= "<br />\n" . DateTime::createFromFormat('Y-m-d H:i:s', $row["done"])->format('d/m H:i');
            else $done .= "<br />\n—";

            $status .= "<br />\n" . $row["status"]; 
            if(($row["status"] & 4) > 0) {
                $status .= "; Sent " . $row["phone"];
            }
            else {
                if(($row["status"] & 2) > 0) {
                    $status .= "; Queue " . $row["phone"];
                }
                if(($row["status"] & 8) > 0) {
                    $status .= "; Error";
                }
            }
            if(($row["status"] & 64) > 0) {
                $status .= "; No e-mail";
            }
            else {
                if(($row["status"] & 16) > 0) {
                    $status .= "; E-mail";
                }
            }
        }
    }
    if($uid !== "") {
?>
<tr<?= ($i & 1) ? ' class="odd"' : '' ?>>
<td><?= $uid ?></td>
<td><?= $username ?></td>
<td><?= $text ?></td>
<td><?= $name ?></td>
<td><?= $sent ?></td>
<td><?= $done ?></td>
<td><?= $status ?></td>
</tr>
<?php
    }

?>
</table>

<?php
}

$db->close();
