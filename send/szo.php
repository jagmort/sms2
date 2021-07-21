<?php
require('param.php');

$authkey = $db->real_escape_string($_POST["authkey"]);
if(isset($_POST["branch"]))
    $branch = $_POST["branch"];
else
    $branch = '%%';
$offset = array();
$stmt = $db->prepare("SELECT initi, offset FROM `branch`");
$stmt->execute();
$result = $stmt->get_result();
while($row = $result->fetch_array(MYSQLI_ASSOC)) {
    $offset[$row["initi"]] = $row["offset"];
}
$stmt->free_result();

if ($stmt2 = $db->prepare("SELECT actual, closed, estimated, region, number, comment, exec_section, branch, flag, a.`update` AS `last` FROM argus a INNER JOIN (SELECT `update` FROM `argus` WHERE `actual` > `closed` ORDER BY `update` desc LIMIT 1) b ON a.update = b.update WHERE `flag` & 4 > 0 AND branch LIKE ? ORDER BY actual")) {
    $stmt2->bind_param("s", $branch);
    $stmt2->execute();
    $result2 = $stmt2->get_result();
    $i = 0;
    while($row2 = $result2->fetch_array(MYSQLI_ASSOC)) {
        $estimated = new DateTime($row2['estimated']);
        $actual = new DateTime($row2['actual']);
        $now = new DateTime(null, new DateTimeZone('Europe/Moscow'));
        echo '<tr><td' . ($now > $estimated ? ' class="far"' : '') . '>' . $actual->format('d.m.Y H:i') . '</td><td>' . $row2['region'] . '</td><td><a href="' . COPY_URL . '?argus=' . $row2['number']. '" target="_blank">' . $row2['number']. '</a></td><td>' . str_replace(array("\n\n"), "<br />", $row2['comment']) . '</td><td>' . $row2['exec_section'] . "</td></tr>\n";
        if($i < 1)
            $last = $row2['last'];
        $i++;
    }
    echo '<tr><td colspan="5"><br />Всего: ' . $i . '<br />Последнее обновление: ' . $last . '</td></tr>';
}
?>