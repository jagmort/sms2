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
<form id="ajax_form" method="post" action="">
<?php if(($identity = Yii::$app->user->identity) != NULL) echo '<input type="hidden" name="authkey" value="' . $identity->getAuthKey() . '" />' ?>

<div id="left">
<?php //<?= $datetime->format('d/m H:i') Недоступно оборудование ?>
<div>
<textarea id="text" name="text" maxlength="600"></textarea>
<span id="count"></span>
</div>
<div id="buttons">
<button type="submit" id="btn">Отправить</button> <span id="result"></span> <button type="button" id="clr">Очистить</button> 
</div>
<div>
<textarea id="phones" name="phones" readonly></textarea>
</div>
<div id="que">
<ul id="queue">
</ul>
</div>
</div>

<div id="tabs">
<?php
require('../../send/param.php');
if ($result = $db->query("SELECT tab.id AS id, tab.name AS name FROM `tab`, `group`, `group_tab`, `user` WHERE `group`.id = group_tab.group_id AND tab.id = tab_id AND user.group_id = `group`.id AND auth_key = '" . $identity->getAuthKey() . "' ORDER BY `order` DESC, tab.name")) {
?>
<ul class="tabs">
<?php
    $tabcont = '';
    $i = 0;
    while($row = $result->fetch_array(MYSQLI_ASSOC)) {
        $i++;
        $tabs[] = $row["id"];
?>
<li class="tab-link<?= $i != 1 ? '' : ' current' ?>" data-tab="tab-<?= $row["id"] ?>"><?= $row["name"] ?></li>
<?php

        $tabcont .= '<div id="tab-' . $row["id"] . '" class="tab-content' . ($i != 1 ? '' : ' current') . "\">\n";
        if ($result2 = $db->query("SELECT id, mobile, name, dept, position, work, email FROM contact WHERE tab_id = " . $row["id"] . " ORDER BY `order` DESC, name")) {
            $j = 0;
            $dept = "";
            while($row2 = $result2->fetch_array(MYSQLI_ASSOC)) {
                $j++;
                if(/*$dept != "" &&*/ $dept != $row2["dept"]) {
                    //$tabcont .= '<div class="newdept">';
                    $tabcont .= '<div class="depthead">' . (strlen($row2["dept"]) > 1 ? preg_replace('/[^,]*,(.*)/i', '${1}', $row2["dept"]) : '&nbsp;') . '</div>';
                    $tabcont .= '<div>';
                    $dept = $row2["dept"];
                }
                else {
                    if($dept == "") $dept = $row2["dept"];
                    $tabcont .= '<div>';
                }
           
                $tabcont .= '<input type="checkbox" id="phone' . $row2["id"] . '" value="' . $row2["id"] . '" />';
                $tabcont .= '<abbr title="'
                    . $row2["name"]
                    . "\n" . $row2["mobile"] 
                    . "\n" .  $row2["dept"] 
                    . "\n" .  $row2["position"] 
                    . "\n" .  $row2["work"] 
                    . "\n" .  $row2["email"] 
                    . '">' . preg_replace('/(.*)\'(.*)\'(.*)/i', '${1}<strong>${2}</strong>${3}', $row2["name"]) . '<br />';
                $tabcont .= '<span>' . $row2["position"] . '</span>';
                $tabcont .= "</abbr>";
                $tabcont .= '<div class="details">';
                if($row2["mobile"] != "") $tabcont .= 'Сотовый: <a href="sip:' .  $row2["mobile"] . '">' . preg_replace('/(\d{1})(\d{3})(\d{3})(\d{4})/i', '${1}-${2}-${3}-${4}', $row2["mobile"]) . '</a><br />';
                if($row2["work"] != "") {
                    if(strlen($row2["work"]) > 9) $tabcont .= 'Рабочий: <a href="sip:' . substr("8" . preg_replace('/[^\d]*/i', '', $row2["work"]), -11) . '">' .  $row2["work"] . '</a><br />';
                    else $tabcont .= 'Рабочий: ' . $row2["work"] . '<br />';
                }
                if($row2["email"] != "") $tabcont .= 'E-mail: <a href="mailto:' .  $row2["email"] . '">' .  $row2["email"] . '</a>';
                $tabcont .= '</div>';
                $tabcont .= "</div>\n";
            }
            $result2->free();
        }
        $tabcont .= "</div>\n\n";
    }
    $result->free();
?>
</ul>
<?= $tabcont ?>
<?php
}
?>
</div>


<?php
$k = 0;
foreach($tabs as $tab) {
    $identity->getAuthKey() . "' ORDER BY name";
    if ($result = $db->query("SELECT list.id AS id, list.name AS name FROM `list`, `group`, `group_list`, `user` WHERE tab_id = '" . $tab . "' AND list.id = list_id AND `group`.id = group_list.group_id AND user.group_id = `group`.id AND auth_key = '" . $identity->getAuthKey() . "' ORDER BY `order` DESC")) {
        $rlist = $result->num_rows;
        if($rlist > 38) $rlist = 38;
?>
<div class="list<?= ($k++ < 1) ? ' current' : '' ?>" id="list-tab-<?= $tab ?>">
<select id="list" size="<?= $rlist ?>">
<?php
        $i = 0;
        while($row = $result->fetch_array(MYSQLI_ASSOC)) {
            $i++;
            if ($result2 = $db->query("SELECT contact.id AS id, email_only FROM `contact`, `contact_list` WHERE contact.id = contact_id AND list_id = " . $row["id"])) {
                $first = true;
                $contacts = "";
                while($row2 = $result2->fetch_array(MYSQLI_ASSOC)) {
                    if($row2["email_only"] <> "0") $email_only = "-";
                    else $email_only = "";
                    if($first) {
                        $contacts = $row2["id"] . $email_only;
                        $first = false;
                    }
                    else $contacts .= "," . $row2["id"] . $email_only;
                }
                $result2->free();
            }
            echo '<option value="' . $contacts . '" title="' . $row["name"] . '">' . $row["name"] . '</option>';
        }
        $result->free();
    }
?>
</select>
</div>
<?php
}

$db->close();
?>
</form>
</div>
</div>
<!-- /view -->
