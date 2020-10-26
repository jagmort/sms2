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
$brunch = $db->real_escape_string($_POST["brunch"]);
if($brunch === '') $brunch = '%%';
$node = $db->real_escape_string($_POST["node"]);
if($node === '') $node = '%%';

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
    $group_id = $row2["group_id"];
}

if($stmt = $db->prepare("SELECT `Домен`, `Заголовок`, `Имя устройства`, INET_NTOA(`IP-адрес`) AS `IP-адрес`, `Критичность`, `Время апдейта`, `Время начала`, `Последнее изменение`, `Время очистки`, `Длительность`, `Принято в работу`, `Тикет`, `Затронуто устройств`, `Затронуто физических лиц`, `Затронуто юридических лиц`, `Количество затронутых сервисов`, `Причина`, `Детализация`, `Количество`, `Адрес в системе`, `Тип модели`, `Роль в Гермес`, `Идентификатор в Гермес`, `Роль в Аргус`, `Имя в Аргус`, `Тип модели в Аргус`, `Идентификатор в Аргус`, `Адрес в Аргус`, `Район в Аргус`, `Регион в Аргус`, `Узел в Аргус`, `Тип`, `Принявший`, `Комментарий`, `Устаревшая`, `Функция в Аргус` FROM `initi` WHERE `Домен` LIKE ? AND `Время начала` >= ? AND `Время начала` <= (? + INTERVAL 1 DAY) ORDER BY `Время начала` DESC")) {

    $stmt->bind_param("sss", $brunch, $from_date, $to_date);
    $stmt->execute();
    $result = $stmt->get_result();

?>
<table class="history">
<tr><th><input id="all" type="checkbox"></th><th>Домен</th><th>Имя устройства</th><th>IP-адрес</th><th>Время начала</th><th>Тикет</th><th>Устройств</th><th>Физ.</th><th>Юр.</th><th>Роль</th><th>Адрес</th><th>Узел</th>
</tr>
<?php
    $nodes = [];
    $i = 1;
    while($row = $result->fetch_array(MYSQLI_ASSOC)) {
        $argus = json_decode($row["Тикет"], TRUE);
        if(!in_array($row["Узел в Аргус"], $nodes))
            $nodes[] = $row["Узел в Аргус"];
        if($node === '%%' || $node === $row["Узел в Аргус"]) {
            echo '<tr' . (($i & 1) ? ' class="odd"' : '') . '><td><input id="row' . $i . '" type="checkbox"></td><td>' . $row["Домен"] . "</td><td>" . $row["Имя устройства"] . "</td><td>" . $row["IP-адрес"] . "</td><td>" . $row["Время начала"] . '</td><td>' . (empty($argus) ? '' : '<a target="_blank" href="' . $argus["link"]["data"] . '">' . $argus["text"]["data"] . "</a>") . "</td><td>" . $row["Затронуто устройств"] . "</td><td>" . $row["Затронуто физических лиц"] . "</td><td>" . $row["Затронуто юридических лиц"] . "</td><td>" . $row["Роль в Аргус"] . "</td><td>" . $row["Адрес в Аргус"] . "</td><td>" . $row["Узел в Аргус"] . "</td>";
            echo "</tr>\n";
            $i++;
        }
    }
?>
</table>
<div id="sms"><textarea id="text">
</textarea></div>
<script type='text/javascript'>
const nodes = ["<?= implode('","', $nodes) ?>"];
addnodes.apply(null, nodes);
$('select option[value="<?= $node ?>"]').attr("selected",true);
</script>

<?php
}

$db->close();
?>
