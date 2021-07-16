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

if ($stmt2 = $db->prepare("SELECT branch AS `Филиал`, `Номер ГП`, minlevel AS `Уровень`, countcl AS `Клиентов`, actual AS `Начало`, estimated AS `Планируемое`, `Тип населенного пункта`, region AS `Регион`, exec_section AS `Очередь`, comment AS `Комментарий`, put AS `e-mail/telegram`, `last`, `nargus` FROM sms a RIGHT JOIN (SELECT branch, argus, number, `Номер ГП`, (SELECT name FROM level d WHERE d.id = MIN(level.id)) AS minlevel, COUNT(client.id) AS countcl, actual, estimated, closed, `Тип населенного пункта`, region, exec_section, comment, c.`update` AS `last`, c.`argus` AS `nargus` FROM client, level, argus c INNER JOIN (SELECT `update` FROM `argus` WHERE actual > closed ORDER BY `update` desc LIMIT 1) d ON c.`update` = d.`update` WHERE number = `Номер ГП` AND level.name = client.`Уровень обслуживания` AND (`Номер ГП` LIKE 'ГП СПД-%' OR `Номер ГП` LIKE 'ПРМОН-%') GROUP BY `Номер ГП`) b ON a.argus = b.argus WHERE minlevel = 'ПЛАТИНОВЫЙ' AND branch LIKE ? ORDER BY `Начало` ASC")) {
    $stmt2->bind_param("s", $branch);
    $stmt2->execute();
    $result2 = $stmt2->get_result();
    $i = 0;
    while($row2 = $result2->fetch_array(MYSQLI_ASSOC)) {
        $estimated = new DateTime($row2['Планируемое']);
        $actual = new DateTime($row2['Начало']);
        if($row2['e-mail/telegram'] !== NULL)
            $sms = new DateTime($row2['e-mail/telegram']);
        else
            $sms = '';
        $now = new DateTime(null, new DateTimeZone('Europe/Moscow'));
        echo '<tr><td><a href="http://omssis-sms.mts-nn.ru/post/copy.php?argus=' . $row2['Номер ГП']. '" target="_blank">' . $row2['Номер ГП']. '</a></td><td>' . mb_convert_case($row2['Уровень'], MB_CASE_TITLE) . '</td><td>' . $row2['Клиентов'] . '</td><td>' . $actual->format('d.m.Y H:i') . '</td><td' . ($now > $estimated ? ' class="far"' : '') . '>' . $estimated->format('d.m.Y H:i') . '</td><td>' . $row2['Тип населенного пункта'] . '</td><td>' . $row2['Регион'] . '</td><td>' . $row2['Очередь'] . '</td><td>' . str_replace(array("\n\n"), "<br />", $row2['Комментарий']) . '</td><td class="sms" data-argus="' . $row2['nargus']. '">' . ($sms !== '' ? $sms->format('d.m.Y H:i') : '–') . "</td></tr>\n";
        if($i < 1)
            $last = $row2['last'];
        $i++;
    }
    echo '<tr><td colspan="10"><br />Всего: ' . $i . '<br />Последнее обновление: ' . $last . '</td></tr>';
}
?>