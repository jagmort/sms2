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

if($stmt2 = $db->prepare("SELECT admin, group_id FROM `user` WHERE auth_key = ?")) {
    $stmt2->bind_param("s", $authkey);
    $stmt2->execute();
    $result2 = $stmt2->get_result();
    $row2 = $result2->fetch_array(MYSQLI_ASSOC);
    if($row2["admin"] > USER_SUPERVISOR)
        $query = "0 <";
    else
        $query = "sms.gid =";
}

if($stmt = $db->prepare("SELECT DATE(`put`) AS dt, COUNT(`sms`.`id`) AS sum, `username` FROM `sms`, `user` WHERE `group_id` = ? AND `priority` != 5 AND `user`.`id` = `sms`.`user_id` AND `put` >= ? AND `put` <= (? + INTERVAL 1 DAY) GROUP BY `user_id`, DATE(`put`) ORDER BY `user_id` ASC, DATE(`put`) DESC")) {

    $stmt->bind_param("iss", $row2["group_id"], $from_date, $to_date);
    $stmt->execute();
    $result = $stmt->get_result();

    $data = array();
    while($row = $result->fetch_array(MYSQLI_ASSOC)) {
        $data[$row['username']][$row['dt']] = $row['sum'];
    }
?>
<table class="history">
<tr><th>User</th>
<?php
    $date = $from_date;
    while (strtotime($date) <= strtotime($to_date)) {
                echo "<th>" . date('j/m', strtotime($date)) . "</th>";
                $date = date ("Y-m-d", strtotime("+1 day", strtotime($date)));
	}
?>
</tr>
<?php
    $i = 1;
    foreach($data as $key => $val) {
        if($key == getName($db, $authkey)) {
            echo '<tr' . (($i & 1) ? ' class="myodd"' : ' class="my"') . "><td>$key</td>";
        }
        else echo '<tr' . (($i & 1) ? ' class="odd"' : '') . "><td>$key</td>";
        $date = $from_date;
        while (strtotime($date) <= strtotime($to_date)) {
                    echo "<td>$val[$date]</td>";
                    $date = date ("Y-m-d", strtotime("+1 day", strtotime($date)));
        }
        echo "</tr>\n";
        $i++;
    }
?>
</table>
<?php
}

$db->close();
