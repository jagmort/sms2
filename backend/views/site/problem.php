<?php
$this->title = 'ГП — SMS 2+';
$this->registerJsFile('js/jquery-3.2.1.js', ['position' => yii\web\View::POS_HEAD]);
$this->registerJsFile('js/problem.js', ['position' => yii\web\View::POS_HEAD]);
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
</form>
<div id="branch">
<a id="mrf">МРФ</a>
<a id="nzg">НФ</a>
<a id="bsh">БИС</a>
<a id="kir">Киров</a>
<a id="mel">Марий Эл</a>
<a id="mrd">Мордовия</a>
<a id="orb">Оренбург</a>
<a id="pnz">Пенза</a>
<a id="smr">Самара</a>
<a id="srt">Саратов</a>
<a id="tts">Татарстан</a>
<a id="udm">Удмуртия</a>
<a id="uln">Ульяновск</a>
<a id="chv">Чувашия</a>
</div>
</div>
<table id="problem">
<tr><th>Создан</th><th>Факт. дата</th><th>(С)КВ</th><th>Адрес</th><th>Номер</th><th>Название</th><th>Последний комментарий</th><th>Участок</th></tr>
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
