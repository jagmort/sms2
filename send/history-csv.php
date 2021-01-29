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

$authkey = $db->real_escape_string($_GET["authkey"]);
try {
    $datetime1 = new DateTime($_GET["from_date"], new DateTimeZone('Europe/Moscow'));
} catch (Exception $e) {
    $datetime1 = new DateTime(null, new DateTimeZone('Europe/Moscow'));
}
try {
    $datetime2 = new DateTime($_GET["to_date"], new DateTimeZone('Europe/Moscow'));
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

header('Content-Disposition: attachment; filename="' . $from_date . '-' . $to_date . '.csv";');
echo '"DateTime";"Argus";"User"' . "\n";
$csv = array();
if($stmt = $db->prepare("SELECT `put`, `username`, `argus` FROM `sms`, `user`WHERE `recovery` IN (0, 3) AND `user`.id = `sms`.`user_id` AND put >= ? AND put <= (? + INTERVAL 1 DAY) AND group_id = gid AND " . $query . " ? ORDER BY `sms`.`put` DESC")) {
    $stmt->bind_param("ssi", $from_date, $to_date, $row2["group_id"]);
    $stmt->execute();
    $result = $stmt->get_result();
    while($row = $result->fetch_array(MYSQLI_ASSOC)) {
        echo '"' . $row["put"] . '";"' . $row["argus"] . '";"' . $row["username"] . '"' . "\n";
    }
}
