<?php

$this->title = 'SMS 2';
$this->registerJsFile('js/jquery-3.2.1.js', ['position' => yii\web\View::POS_HEAD]);
$this->registerJsFile('js/send.js', ['position' => yii\web\View::POS_HEAD]);
$this->registerCssFile('css/jm.css');

$datetime = new DateTime(null, new DateTimeZone('Europe/Moscow'));
$tabs = array();
?>

<!-- view -->
<div id="main">
<form id="ajax_form" method="post" action="">
<?php if(($identity = Yii::$app->user->identity) != NULL) echo '<input type="hidden" name="authkey" value="' . $identity->getAuthKey() . '" />' ?>

<article class="text">
<section>
<textarea id="text" name="text"><?= $datetime->format('d/m H:i') ?> Недоступно оборудование </textarea>
</section>
</article>
<article class="phones">
<section>
<button type="submit" id="btn">Отправить</button> <span id="result"></span> 
<textarea id="phones" name="phones" readonly></textarea>
<button type="button" id="clr">Очистить</button> 
</section>
</article>
<article class="queue">
<section>
<ul id="queue">
</ul>
</section>
</article>
<article class="tabs" id="tabs">
<?php
$db = new mysqli('localhost','sms','Fs0TR2bMCG4x3gLc','sms');
if($db->connect_errno){
    echo $db->connect_error;
}
$db->set_charset("utf8");
if ($result = $db->query("SELECT tab.id AS id, tab.name AS name FROM `tab`, `group`, `group_tab`, `user` WHERE `group`.id = group_tab.group_id AND tab.id = tab_id AND user.group_id = `group`.id AND auth_key = '" . $identity->getAuthKey() . "' ORDER BY `order` DESC")) {
    $i = 0;
    while($row = $result->fetch_array(MYSQLI_ASSOC)) {
        $i++;
        $tabs[] = $row["id"];
?>
<section id="tab<?= $row["id"] ?>">
<h2><a href="#tab<?= $row["id"] ?>"><?= $row["name"] ?></a></h2>
<div class="col">
<?php
        if ($result2 = $db->query("SELECT id, mobile, name, dept, position, work, email FROM contact WHERE tab_id = " . $row["id"] . " ORDER BY `order` DESC, name")) {
            $j = 0;
            while($row2 = $result2->fetch_array(MYSQLI_ASSOC)) {
                $j++;
?>
<div><input type="checkbox" id="phone<?= $row2["id"] ?>" value="<?= $row2["mobile"] ?>" /><abbr title="<?= $row2["name"] . "\n" . $row2["mobile"] . "\n" .  $row2["dept"] . "\n" .  $row2["position"] . "\n" .  $row2["work"] . "\n" .  $row2["email"] ?>"><?= $row2["name"] ?><br /><span><?= $row2["position"] ?></span></abbr></div>
<?php
            }
            $result2->free();
        }
?>
</div>
</section>
<?php
    }
    $result->free();
}

?>
</article>

<article class="list">
<section>
<?php
if ($result = $db->query("SELECT list.id AS id, list.name AS name FROM `list`, `group`, `group_list`, `user` WHERE list.id = list_id AND `group`.id = group_list.group_id AND user.group_id = `group`.id AND auth_key = '" . $identity->getAuthKey() . "' ORDER BY name")) {
    $rlist = $result->num_rows;
    if($rlist > 38) $rlist = 38;
?>
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
?>
<option value="<?= $contacts ?>"><?= $row["name"] ?></option>
<?php
    }
    $result->free();
}
?>
</select>
</section>
</article>
<?php
$db->close();
?>
</form>
</div>
<!-- /view -->
