<?php
$this->title = 'СЗО/ЦЭ — SMS 2+';
$this->registerJsFile('js/jquery-3.2.1.js', ['position' => yii\web\View::POS_HEAD]);
$this->registerJsFile('js/szo.js', ['position' => yii\web\View::POS_HEAD]);
$this->registerCssFile('css/main.css');

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
<div id="refresh2">Филиал: 
<select name="branch" id="branch">
<option>%%</option>
<option>bsh</option>
<option>chv</option>
<option>kir</option>
<option>mel</option>
<option>mrd</option>
<option>nzg</option>
<option>orb</option>
<option>pnz</option>
<option>smr</option>
<option>srt</option>
<option>tts</option>
<option>udm</option>
<option>uln</option>
</select>
</div>
</form>
</div>
<table id="problem">
<tr><th>Факт. дата (MSK)</th><th>Населенный пункт</th><th>Номер</th><th>Последний комментарий</th><th>Участок</th></tr>
<tbody id="result">
</tbody>
</table>
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
