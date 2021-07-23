<?php
$this->title = 'Клиенты — SMS 2+';
$this->registerJsFile('js/jquery-3.2.1.js', ['position' => yii\web\View::POS_HEAD]);
$this->registerJsFile('js/client.js', ['position' => yii\web\View::POS_HEAD]);
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
Уровень: 
<select name="level" id="level">
<option>%%</option>
<?php
    if ($stmt2 = $db->prepare("SELECT `name` FROM `level` ORDER BY id ASC")) {
      $stmt2->execute();
      $result2 = $stmt2->get_result();
      while($row2 = $result2->fetch_array(MYSQLI_ASSOC)) {
        echo "<option>" . $row2['name'] . "</option>\n";        
      }
    }
?>
</select></div>
</form>
</div>
<table id="client">
<tr><th>Номер ГП</th><th>Уровень</th><th>Клиентов</th><th>Факт. дата (MSK)</th><th>Планируемое (MSK)</th><th>Тип населенного пункта</th><th>Населенный пункт</th><th>Участок</th><th>Последний комментарий</th><th>e-mail / telegram</th></tr>
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
