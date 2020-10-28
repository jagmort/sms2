<?php
use kartik\date\DatePicker;

$this->title = 'SMS 2+';
$this->registerJsFile('js/jquery-3.2.1.js', ['position' => yii\web\View::POS_HEAD]);
$this->registerJsFile('js/stats.js', ['position' => yii\web\View::POS_HEAD]);
$this->registerCssFile('css/main.css');

if(($identity = Yii::$app->user->identity) != NULL):
    require('../../send/param.php');

$today = $datetime->format('Y-m-d');
$first = $datetime->format('Y-m-01');
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
</form>
</div>
<div id="result"></div>
</div>
</div>
<div id="loading">
  <img id="loading-image" src="img/loading.gif" alt="Loading..." />
</div>
<!-- /view -->
<?php endif ?>
