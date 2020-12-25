<?php
$this->title = 'Списки оповещения — SMS 2+';
$this->registerJsFile('js/jquery-3.2.1.js', ['position' => yii\web\View::POS_HEAD]);
$this->registerJsFile('js/list.js', ['position' => yii\web\View::POS_HEAD]);
$this->registerCssFile('css/main.css');

if(($identity = Yii::$app->user->identity) != NULL) {
    require('../../send/param.php');

    if ($stmt = $db->prepare("SELECT `group`.id AS gid, `user`.id AS `uid`, `admin` FROM `group`, `user` WHERE `user`.group_id = `group`.id AND auth_key = ?")) {
        $stmt->bind_param("s", $identity->getAuthKey());
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_array(MYSQLI_ASSOC);
        $user_uid = $row['uid'];
        $user_admin = $row['admin'];
        if($user_admin >= 30) { // jm only
?>
<!-- view -->
<div id="main">
<div id="content">
<div id="calendar">
<form id="ajax_form" method="post" action="">
<?php
            if(($identity = Yii::$app->user->identity) != NULL) echo '<input type="hidden" name="authkey" value="' . $identity->getAuthKey() . '" />'
?>
<input type="hidden" id="submit" name="submit" value="0" />
<input type="hidden" id="delete" name="delete" value="0" />
<div id="refresh">
<select name="group" id="group">
</select>
<select name="tab" id="tab">
</select>
<select name="list" id="list">
</select>
</div>
<div id="email"><textarea name="text"></textarea></div>
<div id="button"><button id="add">Add</button> <button id="clear">Clear</button></div>
</form>
</div>
<div id="result"></div>
</div>
</div>
<div id="loading">
  <img id="loading-image" src="img/loading.gif" alt="Loading..." />
</div>
<!-- /view -->
<?php
        }
        else {
?>
<script type='text/javascript'>
window.location.href = '/sms2';
</script>
<?php            
        }
    }
}
?>
