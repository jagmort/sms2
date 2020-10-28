<?php
use kartik\date\DatePicker;

$this->title = 'SMS 2+';
$this->registerJsFile('js/jquery-3.2.1.js', ['position' => yii\web\View::POS_HEAD]);
$this->registerJsFile('js/initi.js', ['position' => yii\web\View::POS_HEAD]);
$this->registerCssFile('css/main.css');

if(($identity = Yii::$app->user->identity) != NULL) {
    require('../../send/param.php');

    $today = $datetime->format('Y-m-d');
    $first = $datetime->format('Y-m-d');

    if ($stmt = $db->prepare("SELECT `group`.id AS gid FROM `group`, `user` WHERE user.group_id = `group`.id AND auth_key = ?")) {
        $stmt->bind_param("s", $identity->getAuthKey());
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_array(MYSQLI_ASSOC);
        if($row['gid'] == 1) { // gmspd only
?>
<!-- view -->
<div id="main">
<div id="content">
<div id="calendar">
<form id="ajax_form" method="post" action="">
<?php
            $datetime = new DateTime(null, new DateTimeZone('Europe/Moscow'));
            echo DatePicker::widget([
                'name' => 'from_date',
                'value' => $first,
                'type' => DatePicker::TYPE_RANGE,
                'name2' => 'to_date',
                'value2' => $today,
                'pluginOptions' => [
                    'autoclose'=>true,
                    'format' => 'yyyy-mm-dd'
                ]
            ]);
            if(($identity = Yii::$app->user->identity) != NULL) echo '<input type="hidden" name="authkey" value="' . $identity->getAuthKey() . '" />'
?>
<div id="refresh">
<select name="brunch" id="brunch">
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
<select name="node" id="node">
</select>
</div>
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
