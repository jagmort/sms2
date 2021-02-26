<?php
require('param.php');

$authkey = $db->real_escape_string($_POST["authkey"]);
$branch = $_POST["branch"];


if ($stmt = $db->prepare("SELECT `user`.id AS `uid`, `group`.id AS gid FROM `user`, `group` WHERE `user`.group_id = `group`.id AND auth_key = ?")) {
    $stmt->bind_param("s", $authkey);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_array(MYSQLI_ASSOC);
    $gid = $row["gid"];
    $user_id = $row["uid"];
}

if($branch > 0 && $gid == 1) {
    if ($stmt = $db->prepare("UPDATE `user_branch` SET `user_id`=?, taking = NOW() WHERE `branch_id`=?")) {
        $stmt->bind_param("ii", $user_id, $branch);
        $stmt->execute();
        $result = $stmt->get_result();
    }
}

if ($stmt2 = $db->prepare("SELECT `branch`.id AS bid, `human`, `name`, `work`, `mobile`, `home`, `contact`.email AS email, taking, `contact`.telegram AS telegram FROM `branch`, `user`, `contact`, `user_branch` WHERE `contact`.id = `user`.`contact_id` AND branch_id = `branch`.id AND `user_id` = `user`.id ORDER BY `branch`.id")) {
    //$stmt2->bind_param("s", $identity->getAuthKey());
    $stmt2->execute();
    $result2 = $stmt2->get_result();
    while($row2 = $result2->fetch_array(MYSQLI_ASSOC)) {
    echo '<tr><td>' . ($gid == 1 ? '<a class="take" bid="' . $row2["bid"] . '" title="Принять смену ' . $row2["human"] . '"><i class="fa fa-handshake-o" aria-hidden="true"></i></a>' : '') . '</td><td>' . $row2["human"] . "</td><td>" . $row2["name"] . "</td><td>" . $row2["work"] . "</td><td>" . $row2["mobile"] . "</td><td>" . $row2["home"] . "</td><td>" . ($row2["telegram"] > 0 ? '<i class="fa fa-telegram" aria-hidden="true"></i> ' : '') . "</td><td>" . $row2["email"] . "</td><td>" . ($gid == 1 ? $row2["taking"] : '') . "</td></tr>\n";
    }
}
?>