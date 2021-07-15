<?php
require('param.php');

$authkey = $db->real_escape_string($_POST["authkey"]);
$offset = array();
$stmt = $db->prepare("SELECT initi, offset FROM `branch`");
$stmt->execute();
$result = $stmt->get_result();
while($row = $result->fetch_array(MYSQLI_ASSOC)) {
    $offset[$row["initi"]] = $row["offset"];
}
$stmt->free_result();

if ($stmt2 = $db->prepare("SELECT branch AS `Филиал`, `Номер ГП`, minlevel AS `Уровень`, countcl AS `Клиентов`, actual AS `Начало`, estimated AS `Планируемое`, `Тип населенного пункта`, region AS `Регион`, exec_section AS `Очередь`, comment AS `Комментарий`, put AS `e-mail/telegram`, `last` FROM sms a RIGHT JOIN (SELECT branch, argus, number, `Номер ГП`, (SELECT name FROM level d WHERE d.id = MIN(level.id)) AS minlevel, COUNT(client.id) AS countcl, actual, estimated, closed, `Тип населенного пункта`, region, exec_section, comment, c.`update` AS `last` FROM client, level, argus c INNER JOIN (SELECT `update` FROM `argus` WHERE actual > closed ORDER BY `update` desc LIMIT 1) d ON c.`update` = d.`update` WHERE number = `Номер ГП` AND level.name = client.`Уровень обслуживания` AND (`Номер ГП` LIKE 'ГП СПД-%' OR `Номер ГП` LIKE 'ПРМОН-%') GROUP BY `Номер ГП`) b ON a.argus = b.argus WHERE minlevel = 'ПЛАТИНОВЫЙ' ORDER BY `Начало` ASC")) {
    $stmt2->execute();
    $result2 = $stmt2->get_result();
    while($row2 = $result2->fetch_array(MYSQLI_ASSOC)) {
        $estimated = new DateTime($row2['Планируемое']);
        $estimated->sub(new DateInterval('PT' . $offset[$row2['Филиал']] . 'H'));
        $actual = new DateTime($row2['Начало']);
        if($row2['e-mail/telegram'] !== NULL)
            $sms = new DateTime($row2['e-mail/telegram']);
        else
            $sms = '';
        $now = new DateTime(null, new DateTimeZone('Europe/Moscow'));
        echo '<tr><td><a href="http://omssis-sms.mts-nn.ru/post/copy.php?argus=' . $row2['Номер ГП']. '" target="_blank">' . $row2['Номер ГП']. '</a></td><td>' . $row2['Уровень'] . '</td><td>' . $row2['Клиентов'] . '</td><td>' . $actual->format('d.m.Y H:i') . '</td><td' . ($now > $estimated ? ' class="far"' : '') . '>' . $estimated->format('d.m.Y H:i') . '</td><td>' . $row2['Тип населенного пункта'] . '</td><td>' . $row2['Регион'] . '</td><td>' . $row2['Очередь'] . '</td><td>' . str_replace(array("\n\n"), "<br />", $row2['Комментарий']) . '</td><td>' . ($sms !== '' ? $sms->format('d.m.Y H:i') : '–') . "</td></tr>\n";
        $last = $row2['last'];
    }
    echo '<tr><td colspan="10">Последнее обновление: ' . $last . '</td></tr>';
}
?>