<?php

use kartik\date\DatePicker;

$this->title = 'SMS 2+';
$this->registerJsFile('js/jquery-3.2.1.js', ['position' => yii\web\View::POS_HEAD]);
$this->registerJsFile('js/history.js', ['position' => yii\web\View::POS_HEAD]);
$this->registerCssFile('css/main.css');

?>
<!-- view -->
<div id="main">
<div id="content">
<div id="calendar">
<form id="ajax_form" method="post" action="">
<?php
echo DatePicker::widget([
    'name' => 'from_date',
    'value' => date('Y-m-d'),
    'type' => DatePicker::TYPE_RANGE,
    'name2' => 'to_date',
    'value2' => date('Y-m-d'),
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
<!-- /view -->
