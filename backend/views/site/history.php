<?php
use kartik\date\DatePicker;

$this->title = 'История оповещений — SMS 2+';
$this->registerJsFile('js/jquery-3.2.1.js', ['position' => yii\web\View::POS_HEAD]);
$this->registerJsFile('js/history.js', ['position' => yii\web\View::POS_HEAD]);
$this->registerCssFile('css/main.css');
$this->registerCssFile('css/font-awesome.css');

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
if(($identity = Yii::$app->user->identity) != NULL) echo '<input type="hidden" name="authkey" value="' . $identity->getAuthKey() . '" />';
if(!empty($_GET['argus'])) echo '<input type="hidden" name="argus" value="' . $_GET['argus'] . '" />';
?>
</form>
</div>
<div id="refresh">Обновление&nbsp;<input type="checkbox" /> <a id="export" href="/sms2/send/history-csv.php" target="_blank"><i class="fa fa-file-excel-o" aria-hidden="true"></i> Экспорт в CSV</a></div>
<div id="result"></div>
</div>
</div>
<div id="loading">
  <img id="loading-image" src="img/loading.gif" alt="Loading..." />
</div>
<!-- /view -->
