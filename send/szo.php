<?php
require('param.php');

$authkey = $db->real_escape_string($_POST["authkey"]);

if ($stmt2 = $db->prepare("SELECT actual, region, number, comment, exec_section, flag FROM argus a INNER JOIN (SELECT `update` FROM `argus` ORDER BY `update` desc LIMIT 1) b ON a.update = b.update WHERE `flag` & 4 > 0 ORDER BY actual")) {
    $stmt2->execute();
    $result2 = $stmt2->get_result();
    while($row2 = $result2->fetch_array(MYSQLI_ASSOC)) {
        echo '<tr><td>' . $row2['actual'] . '</td><td>' . $row2['region'] . '</td><td><a href="http://omssis-sms.mts-nn.ru/post/copy.php?argus=' . $row2['number']. '" target="_blank">' . $row2['number']. '</a></td><td>' . str_replace(array("\n\n"), "<br />", $row2['comment']) . '</td><td>' . $row2['exec_section'] . "</td></tr>\n";
    }
}
?>