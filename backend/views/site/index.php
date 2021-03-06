<?php
$this->title = 'SMS 2+';
$this->registerJsFile('js/jquery-3.2.1.js', ['position' => yii\web\View::POS_HEAD]);
$this->registerJsFile('js/send.js', ['position' => yii\web\View::POS_HEAD]);
$this->registerCssFile('css/main.css');
$this->registerCssFile('css/font-awesome.css');

$tz = new DateTimeZone('Europe/Moscow');
$datetime = new DateTime(null, $tz);
$tabs = array();
?>

<!-- view -->
<?php
if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
    $userip = $_SERVER['HTTP_CLIENT_IP'];
} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    $userip = $_SERVER['HTTP_X_FORWARDED_FOR'];
} else {
    $userip = $_SERVER['REMOTE_ADDR'];
}
if(($identity = Yii::$app->user->identity) != NULL):
    require('../../send/param.php');

    if (!empty($_GET['uid'])) 
    $uid = $_GET['uid'];
    if($stmt = $db->prepare("SELECT `uid`, `text`, `list_id`, `tab_id`, `contact_id`, `email_only`, `group`.`name` AS gname FROM `sms`, `list`, `recipient`, `group` WHERE `uid`=? AND `list`.id = `list_id` AND `sms`.id = `sms_id` AND `gid` = `group`.id")) {
        $stmt->bind_param("s", $uid);
        $stmt->execute();
        $result = $stmt->get_result();
        while($row = $result->fetch_array(MYSQLI_ASSOC)) {
            if(!isset($phones)) {
                $list_id = $row['list_id'];
                $text = preg_replace('/(.*) \(' . $row['gname'] . '\)$/', "$1", $row['text']);
                if($list_id > 0)
                    $tab_id = $row['tab_id'];
            }
            $phones .= $row['contact_id'] . ($row['email_only'] < 1 ? '' : '-') . '; ';
        }
    }
?>
<dialog id="edit"></dialog>
<dialog id="add"></dialog>
<dialog id="alert"><div class="msg"></div><div><button type="button" id="cancel" onclick="$('#alert')[0].close()">Закрыть</button></div></dialog>
<div id="main">
<div id="content">
<form id="ajax_form" method="post" action="" enctype="multipart/form-data">
<input id="identity" type="hidden" name="authkey" value="<?= $identity->getAuthKey() ?>" />
<input id="userip" type="hidden" name="userip" value="<?= $userip ?>" />
<input type="hidden" name="priority" value="0" />
<div id="left">
<div id="priority" title=""></div>
<div id="search">
<input type="text" name="search" value="" title="Поиск контакта" /><button id="search_btn" title="Поиск контакта"><i class="fa fa-search" aria-hidden="true"></i></button>
</div>
<div>
<select name="subject" id="subject" title="Тема e-mail">
<?php
    if ($stmt = $db->prepare("SELECT * FROM subject ORDER BY id")) {
        $stmt->execute();
        $result = $stmt->get_result();
        while($row = $result->fetch_array(MYSQLI_ASSOC)) {
?>
    <option value="<?= $row["id"] ?>" text="<?= $row["text"] ?>" class="opt<?= $row["priority"] ?>"><?= $row["text"] . $subject ?></option>
<?php
        }
        $result->free();
    }
?>
</select>
</div>
<div>
<textarea id="text" name="text" maxlength="<?= MAX_SMS_LENGTH ?>"><?= isset($text) ? $text : '' ?></textarea>
</div>
<span id="count"></span>
<div id="attach"><input type="file" name="file" id="file" /></div>
<div id="buttons">
<button type="submit" id="btn" disabled>Отправить</button> <input name="phone" id="single" size="11" title="Отправка на произвольный номер
(11 цифр, например, 89012345678)" disabled /> <span id="result"></span> <button type="button" id="clr" disabled>Очистить</button> 
</div>
<div>
<textarea id="phones" name="phones" readonly><?= isset($phones) ? ($list_id > 0 ? $list_id . ": " : '') . $phones : '' ?></textarea>
</div>
<div id="que">
<ul id="queue">
</ul>
</div>
</div>

<div id="tabs">
<?php
    if(isset($_GET["tab"]))
        $tab_id = $_GET["tab"];

    if($datetime->format('N') < 6) // Today is a workday
        $workday = true;
    else
        $workday = false;
    if ($stmt = $db->prepare("SELECT `workday` FROM `calendar` WHERE dt = ?")) {
        $stmt->bind_param("s", $datetime->format('Y-m-d'));
        $stmt->execute();
        $result = $stmt->get_result();
        if(($wd = $result->fetch_array(MYSQLI_NUM)) !== NULL) {
            if($wd[0] < 1)
                $workday = false;
            else
                $workday = true;
        }
        $result->free();
    }

    if ($stmt = $db->prepare("SELECT tab.id AS id, tab.name AS name, admin, `group`.name AS gname FROM `tab`, `group`, `group_tab`, `user` WHERE `group`.id = group_tab.group_id AND tab.id = tab_id AND user.group_id = `group`.id AND auth_key = ? ORDER BY `order` DESC, tab.name")):
        $stmt->bind_param("s", $identity->getAuthKey());
        $stmt->execute();
        $result = $stmt->get_result();
?>
<ul class="tabs">
<?php
        $tabcont = '';
        $i = 0;
        while($row = $result->fetch_array(MYSQLI_ASSOC)) {
            $signlen = mb_strlen($row["gname"]) + 3;
            $admin = $row["admin"];
            $i++;
            $tabs[] = $row["id"];
            if(isset($tab_id)) {
                echo '<li class="tab-link';
                $tabcont .= '<div id="tab-' . $row["id"] . '" class="tab-content';
                if($tab_id == $row["id"]) {
                    echo ' current';
                    $tabcont .= ' current';
                }
                echo '" data-tab="tab-' . $row["id"] . '">' . $row["name"] . "</li>\n";
                $tabcont .= "\">\n";
            }
            else {
?>
<li class="tab-link<?= $i != 1 ? '' : ' current' ?>" data-tab="tab-<?= $row["id"] ?>"><?= $row["name"] ?></li>
<?php
                $tabcont .= '<div id="tab-' . $row["id"] . '" class="tab-content' . ($i != 1 ? '' : ' current') . "\">\n";
            }

            if ($stmt = $db->prepare("SELECT `contact`.id AS id, mobile, name, dept, block, position, work, home, email, comment, keyword, `contact_tab`.`order`AS `order`, telegram, pin, tab_id, work_from, work_to, vac_from, vac_to FROM `contact`, `contact_tab` WHERE `contact`.hide < 1 AND `contact`.id = contact_id AND tab_id = ? ORDER BY block, `order` DESC, name")):
                $stmt->bind_param("i", $row["id"]);
                $stmt->execute();
                $result2 = $stmt->get_result();
                $j = 0;
                $block = "";
                while($row2 = $result2->fetch_array(MYSQLI_ASSOC)):
                    $j++;
                    if($block != $row2["block"]):
                        if($block != "") $tabcont .= "</div>\n";
                        $tabcont .= "\n<div class=\"dept\">\n";
                        $tabcont .= '<div class="depthead">' . (strlen($row2["block"]) > 1 ? preg_replace('/(.*)\'(.*)\'(.*)/i', '${1}<strong>${2}</strong>${3}', preg_replace('/[^,]*,(.*)/i', '${1}', htmlentities($row2["block"]))) : '&nbsp;') . '</div>';
                        $tabcont .= '<div>';
                        $block = $row2["block"];
                    else:
                        if($block == "")
                            $block = $row2["block"];
                        $tabcont .= '<div';
                        if(isset($_GET['id']) && ($_GET['id'] == $row2["id"])) $tabcont .= ' class="detailed"';
                        $tabcont .= '>';
                    endif;
                    if($row2['vac_from'] != '0000-00-00' && $row2['vac_to'] != '0000-00-00') {
                        $vac_from = DateTime::createFromFormat('Y-m-d', $row2['vac_from'], $tz);
                        $vac_to = DateTime::createFromFormat('Y-m-d', $row2['vac_to'], $tz);
                    }
                    else {
                        $vac_from = new DateTime(null, $tz);
                        $vac_from->sub(new DateInterval('P1D'));
                        $vac_to = new DateTime(null, $tz);
                        $vac_to->sub(new DateInterval('P1D'));
                    }
                    if($row2['work_from'] != '00:00:00' && $row2['work_to'] != '00:00:00') {
                        $work_from = DateTime::createFromFormat('Y-m-d H:i:s', $datetime->format('Y-m-d ') . $row2['work_from'], $tz);
                        $work_to = DateTime::createFromFormat('Y-m-d H:i:s', $datetime->format('Y-m-d ') . $row2['work_to'], $tz);
                    }
                    else {
                        $work_from = DateTime::createFromFormat('Y-m-d H:i:s', $datetime->format('Y-m-d 00:00:00'), $tz);
                        $work_to = DateTime::createFromFormat('Y-m-d H:i:s', $datetime->format('Y-m-d 00:00:00'), $tz);
                    }
                    $tabcont .= "<input title=\"&#128241; Telegram и e-mail\n&#9993;  только e-mail\n&times; недоступен\" type=\"checkbox\" id=\"phone" . $row2["id"] . '" value="' . $row2["id"] . '" data-keyword="' . htmlentities($row2["keyword"]) .'"';
                    if(($vac_from <= $datetime && $vac_to >= $datetime) || (($work_from != $work_to) && ($work_from > $datetime || $work_to < $datetime || !$workday))) // Vacation or not a worktime
                        $tabcont .= ' disabled';
                    $tabcont .= ' wd="' . $workday . '" />';
                    $tabcont .= '<abbr order="' . $row2["order"] . '">';
                    $tabcont .= preg_replace('/(.*)\'(.*)\'(.*)/i', '${1}<strong>${2}</strong>${3}', preg_replace('/_/i', ' ', htmlentities($row2["name"]))) . ($row2["telegram"] != 0 && $row2["pin"] == 0 ? ' <span><i class="fa fa-telegram" aria-hidden="true"></i></span>' : '') . '<br />';
                    $tabcont .= '<span>'. $row2["position"] . ', ' . $row2["dept"] . '</span>';
                    $tabcont .= "</abbr>";
                    $tabcont .= '<div class="details"';
                    if(isset($_GET['id']) && ($_GET['id'] == $row2["id"])) $tabcont .= ' style="display: block;"';
                    $tabcont .= '>';
                    if($row2["mobile"] != "")
                        $tabcont .= 'Сотовый: <a href="sip:' .  $row2["mobile"] . '">' . preg_replace('/(\d{1})(\d{3})(\d{3})(\d{4})/i', '${1}-${2}-${3}-${4}', $row2["mobile"]) . '</a><br />';
                    if($row2["work"] != ""):
                        if(strlen(preg_replace('/[^\d]*/i', '', $row2["work"])) > 9):
                            $tabcont .= 'Рабочий: <a href="sip:' . substr("8" . preg_replace('/[^\d]*/i', '', $row2["work"]), -11) . '">' .  htmlentities($row2["work"]) . '</a><br />';
                        else:
                            $tabcont .= 'Рабочий: ' . htmlentities($row2["work"]) . '<br />';
                        endif;
                    endif;
                    if($row2["home"] != ""):
                        if(strlen(preg_replace('/[^\d]*/i', '', $row2["home"])) > 9):
                            $tabcont .= 'Домашний: <a href="sip:' . substr("8" . preg_replace('/[^\d]*/i', '', $row2["home"]), -11) . '">' .  htmlentities($row2["home"]) . '</a><br />';
                        else:
                            $tabcont .= 'Домашний: ' . htmlentities($row2["home"]) . '<br />';
                        endif;
                    endif;
                    if($row2["email"] != "")
                        $tabcont .= 'E-mail: <a href="mailto:' .  htmlentities($row2["email"]) . '">' . htmlentities($row2["email"]) . '</a>';
                    if($row2["comment"] != "")
                        $tabcont .= '<br />' . htmlentities($row2["comment"]);
                    if($admin > USER_KEYWORD):
                        $tabcont .= '<br /><span data-tab="' . $row2["tab_id"] . '" data-id="' . $row2["id"] . '"><i class="fa fa-pencil-square-o" aria-hidden="true"></i> Редактировать<br />ID: ' . $row2["id"];
                        $tabcont .= '<br />Вкладка: ' . $row2["tab_id"];
                        $tabcont .= '<br />Блок: ' . htmlentities($row2["block"]);
                        $tabcont .= '<br />Порядок: ' . $row2["order"];
                        if($vac_to > $datetime)
                            $tabcont .= '<br />Отпуск: ' . $vac_from->format('d.m.Y') . ' — ' . $vac_to->format('d.m.Y');
                        if($work_from != $work_to)
                            $tabcont .= '<br />Время работы: ' . $work_from->format('H:i') . ' — ' . $work_to->format('H:i T');
                        $tabcont .= '<br />Keyword: ' . htmlentities($row2["keyword"]) . '</span>';
                    endif;
                    $tabcont .= '</div>';
                    $tabcont .= "</div>\n";

                    unset($vac_from);
                    unset($vac_to);
                    unset($work_from);
                    unset($work_to);
                endwhile;
                if($block != "") $tabcont .= "</div>\n";
                $result2->free();
            endif;
            if($admin > USER_ADMIN) {
                $tabcont .= '<a class="addcontact" data-tab="' . $row["id"] . '">Добавить контакт</a>';
            }
            $tabcont .= "</div>\n\n";
        }
        $result->free();
?>
</ul>
<?= $tabcont ?>
<?php
    endif;
?>
</div>


<?php
    $k = 0;
    foreach($tabs as $tab) {
        if ($stmt = $db->prepare("SELECT optgroup FROM `list`, `group`, `group_list`, `user` WHERE tab_id = ? AND list.id = list_id AND `group`.id = group_list.group_id AND user.group_id = `group`.id AND optgroup <> '' AND auth_key = ? GROUP BY optgroup")):
            $stmt->bind_param("is", $tab, $identity->getAuthKey());
            $stmt->execute();
            $result = $stmt->get_result();
            $rlist = $result->num_rows;
            if ($stmt = $db->prepare("SELECT list.id AS id, list.name AS name, alert, optgroup FROM `list`, `group`, `group_list`, `user` WHERE tab_id = ? AND list.id = list_id AND `group`.id = group_list.group_id AND user.group_id = `group`.id AND auth_key = ? ORDER BY `order` DESC, `id` ASC")):
                $stmt->bind_param("is", $tab, $identity->getAuthKey());
                $stmt->execute();
                $result = $stmt->get_result();
                $rlist = $rlist + $result->num_rows;
                if($rlist > 0):
                    if($rlist > 40) $rlist = 40;
                    if(isset($tab_id)) { // select list in current tab
?>
<div class="list<?= $tab_id == $tab ? ' current' : '' ?>" id="list-tab-<?= $tab ?>">
<select
<?php
                    }
                    else { // list in first tab
?>
<div class="list<?= ($k < 1) ? ' current' : '' ?>" id="list-tab-<?= $tab ?>">
<select
<?php
                    }
                    if($rlist != 1):
                        echo ' size="' . $rlist . '">';
                    else: 
                        echo ' class="single" size="2">';
                        echo '<option disabled></option>';
                    endif;
                    $optgroup = "";
                    $i = 0;
                    while($row = $result->fetch_array(MYSQLI_ASSOC)) {
                        $i++;
                        if($stmt = $db->prepare("SELECT contact.id AS id, email_only, escalate FROM `contact`, `contact_list` WHERE active > 0 AND contact.id = contact_id AND list_id = ?")) {
                            $stmt->bind_param("i", $row["id"]);
                            $stmt->execute();
                            $result2 = $stmt->get_result();
                            $first_reg = true;
                            $first_esc = true;
                            $contacts = "";
                            $escalate = "";
                            while($row2 = $result2->fetch_array(MYSQLI_ASSOC)) {
                                if($row2["email_only"] > 0) {
                                    $email_only = "-";
                                }
                                else {
                                    $email_only = "";
                                }
                                if($row2["escalate"] > 0) { // контакт для эскалации
                                    if($first_esc) {
                                        $escalate = $row2["id"] . $email_only;
                                        $first_esc = false;
                                    }
                                    else {
                                        $escalate .= "," . $row2["id"] . $email_only;
                                    }
                                }
                                else { // обычный контакт
                                    if($first_reg) {
                                        $contacts = $row2["id"] . $email_only;
                                        $first_reg = false;
                                    }
                                    else {
                                        $contacts .= "," . $row2["id"] . $email_only;
                                    }
                                }
                            }
                            $result2->free();
                        }
                        if($row["optgroup"] != $optgroup) {
                            $optgroup = $row["optgroup"];
                            echo '<optgroup title="' . $optgroup . '" label="' . $optgroup . '"></optgroup>';
                        }
                        echo '<option data-list="' . $row["id"] . '" data-alert="' . htmlentities($row["alert"]) . '" data-esc="' . $escalate . '" value="' . $contacts . '" title="' . htmlentities($row["name"] . " (" . $row["id"] . ")") . '"' . ($list_id == $row['id'] ? ' selected' : '') . '>' . htmlentities($row["name"]) . '</option>';
                    }
                    $result->free();
?>
</select>
</div>
<?php
                endif;
                $k++;
            endif;
        endif;
    } //foreach

    $db->close();
?>
</form>
</div>
</div>
<script>
    var signlen = <?= $signlen ?>;
<?php
    if(isset($phones)) {
?>
    list = <?= isset($list_id) ? $list_id : 0 ?>;
    var all_checkboxes = $('#tabs input:checkbox');
    all_checkboxes.prop('checked', false);
    all_checkboxes.prop('indeterminate', false);
    all_checkboxes.data('checked', 0);
    var str = '<?= $phones ?>';
    var arr = str.split('; ');
    arr.forEach(function(item, i, arr) {
        if(item.substr(-1, 1) != '-') {
            $("#phone" + item).prop('checked', true);
            $("#phone" + item).data('checked', 2);
        }
        else {
            $("#phone" + item.slice(0, -1)).prop('indeterminate', true);
            $("#phone" + item.slice(0, -1)).data('checked', 1);
        }
    });
    $("#clr").prop('disabled', false);
    $("#btn").prop('disabled', false);
<?php
    }
?>
</script>
<!-- /view -->
<?php endif ?>
