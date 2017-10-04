<?php
require('param.php');

if ($result = $db->query("SELECT uid, contact_id, text, put, sent, done, status, user_id, phone, email_only FROM history ORDER BY put DESC")) {

    $i = 0;
    $uid = '';
    while($row = $result->fetch_array(MYSQLI_ASSOC)) {
        if($uid !== $row["uid"]) {
            if($uid !== "") {
                if($result2 = $db->query("INSERT INTO sms (text, put, user_id, uid) VALUES ('".$row["text"]."', '".$row["put"]."', '".$row["user_id"]."', '".$row["uid"]."')")) {
                    $sms_id = $db->insert_id;
                }
            }
            $i++;
            $uid = $row["uid"];
        }
        else {
            $result3 = $db->query("INSERT INTO recipient (sms_id, contact_id, sent, done, status, email_only, phone) VALUES ($sms_id, '".$row["contact_id"]."', '".$row["sent"]."', '".$row["done"]."', '".$row["status"]."', '".$row["email_only"]."', '".$row["phone"]."')");
        }
    }
}
