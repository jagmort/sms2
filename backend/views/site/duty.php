<?php
$this->title = 'Смена ГМСПД — SMS 2+';
$this->registerJsFile('js/jquery-3.2.1.js', ['position' => yii\web\View::POS_HEAD]);
$this->registerJsFile('js/duty.js', ['position' => yii\web\View::POS_HEAD]);
$this->registerCssFile('css/main.css');
$this->registerCssFile('css/duty.css');
$this->registerCssFile('css/font-awesome.css');

if(($identity = Yii::$app->user->identity) != NULL) {
    require('../../send/param.php');

    if ($stmt = $db->prepare("SELECT `group`.id AS gid, `user`.id AS uid FROM `group`, `user` WHERE user.group_id = `group`.id AND auth_key = ?")) {
        $stmt->bind_param("s", $identity->getAuthKey());
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_array(MYSQLI_ASSOC);
        if(true || $row['uid'] == 1) { // jm only
?>
<!-- view -->
<div id="main">
<div id="content">
<div id="calendar">
<form id="ajax_form" method="post" action="">
<?php
            if(($identity = Yii::$app->user->identity) != NULL) echo '<input type="hidden" name="authkey" value="' . $identity->getAuthKey() . '" />'
?>
<input type="hidden" id="branch" name="branch" value="0">
</form>
</div>
<table id="duty">
<tr><th></th><th>Филиал</th><th>Сотрудник</th><th>Телефон</th><th>Сотовый</th><th>E-mail</th></tr>
<tbody id="result">
</tbody>
</table>
</div>
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
