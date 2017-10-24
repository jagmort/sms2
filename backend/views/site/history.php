<?php

$this->title = 'SMS 2+';
$this->registerJsFile('js/jquery-3.2.1.js', ['position' => yii\web\View::POS_HEAD]);
$this->registerJsFile('js/send.js', ['position' => yii\web\View::POS_HEAD]);
$this->registerCssFile('css/main.css');

$datetime = new DateTime(null, new DateTimeZone('Europe/Moscow'));
$tabs = array();
?>

<!-- view -->
<div id="main">
<div id="content">
<?php
require('../../send/param.php');
if ($result = $db->query("SELECT uid, contact.name AS name, dept, text, sent, done, recipient.status AS status, username, `group`.name AS gname, phone FROM recipient, sms, contact, user, `group` WHERE put >= (NOW() - INTERVAL 1 DAY) AND user.id = user_id AND contact_id = contact.id AND sms_id = sms.id AND `group`.id = gid ORDER BY put DESC, contact.`order` DESC, name ASC")) {
?>
<table class="history">
<tr>
<th>ID</th>
<th>User</th>
<th>Group</th>
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
<td><?= $group ?></td>
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
            if($row["sent"] != '0000-00-00 00:00:00') $sent = DateTime::createFromFormat('Y-m-d H:i:s', $row["sent"])->format('d/m H:i');
            else $sent = '—';
            if($row["done"] != '0000-00-00 00:00:00') $done = DateTime::createFromFormat('Y-m-d H:i:s', $row["done"])->format('d/m H:i');
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
            if($row["done"] != '0000-00-00 00:00:00') $done .= "<br />\n" . DateTime::createFromFormat('Y-m-d H:i:s', $row["done"])->format('d/m H:i');
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
<td><?= $group ?></td>
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
?>
</div>
</div>
<!-- /view -->
