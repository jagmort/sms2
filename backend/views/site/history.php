<?php
use kartik\date\DatePicker;

$this->title = 'SMS 2+';
$this->registerJsFile('js/jquery-3.2.1.js', ['position' => yii\web\View::POS_HEAD]);
$this->registerJsFile('js/history.js', ['position' => yii\web\View::POS_HEAD]);
$this->registerJsFile('js/xlsx.core.min.js', ['position' => yii\web\View::POS_HEAD]);
$this->registerJsFile('js/FileSaver.min.js', ['position' => yii\web\View::POS_HEAD]);
$this->registerJsFile('js/tableexport.min.js', ['position' => yii\web\View::POS_HEAD]);
$this->registerCssFile('css/main.css');

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
    'value' => $datetime->format('Y-m-d'),
    'type' => DatePicker::TYPE_RANGE,
    'name2' => 'to_date',
    'value2' => $datetime->format('Y-m-d'),
    'pluginOptions' => [
        'autoclose'=>true,
        'format' => 'yyyy-mm-dd'
    ]
]);
if(($identity = Yii::$app->user->identity) != NULL) echo '<input type="hidden" name="authkey" value="' . $identity->getAuthKey() . '" />'
?>
</form>
</div>
<div id="refresh">Обновление&nbsp;<input type="checkbox" /></div>
<div id="export">
</div>
<div id="result"></div>
</div>
</div>
<!-- /view -->
