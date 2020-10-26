<?php

/* @var $this \yii\web\View */
/* @var $content string */

use backend\assets\AppAsset;
use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;
use common\widgets\Alert;

AppAsset::register($this);
?>
<?php $this->beginPage() ?>

<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
    <link rel="apple-touch-icon" sizes="180x180" href="/sms2/backend/web/img/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/sms2/backend/web/img/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/sms2/backend/web/img/favicon-16x16.png">
    <link rel="manifest" href="/sms2/backend/web/img/manifest.json">
    <link rel="mask-icon" href="/sms2/backend/web/img/safari-pinned-tab.svg" color="#5bbad5">
    <meta name="theme-color" content="#ffffff">
    <link rel="shortcut icon" href="/sms2/backend/web/img/favicon.ico">
</head>
<body>
<?php $this->beginBody() ?>

<div class="wrap">
    <?php
    NavBar::begin([
        'brandLabel' => 'SMS 2+',
        'brandUrl' => Yii::$app->homeUrl,
        'options' => [
            'class' => 'navbar-inverse navbar-fixed-top',
        ],
    ]);
    $menuItems = [
        ['label' => 'Главная', 'url' => ['/site/index']],
        ['label' => 'История', 'url' => ['/site/history']],
        ['label' => 'Статистика', 'url' => ['/site/stats']],
        ['label' => 'Инити', 'url' => ['/site/initi']],
        ['label' => 'Сбросить пароль', 'url' => '/sms2/frontend/web/index.php?r=site%2Frequest-password-reset'],
    ];
    if (Yii::$app->user->isGuest) {
        $menuItems[] = ['label' => 'Войти', 'url' => ['/site/login']];
    } else {
        $menuItems[] = '<li>'
            . Html::beginForm(['/site/logout'], 'post')
            . Html::submitButton(
                'Выйти (' . Yii::$app->user->identity->username . ')',
                ['class' => 'btn btn-link logout']
            )
            . Html::endForm()
            . '</li>';
    }
    echo Nav::widget([
        'options' => ['class' => 'navbar-nav navbar-right'],
        'items' => $menuItems,
    ]);
    NavBar::end();
    ?>

    <div class="container-fluid">
        <?= Breadcrumbs::widget([
            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
        ]) ?>
        <?= Alert::widget() ?>
        <?= $content ?>
    </div>
</div>

<footer class="footer">
    <div class="container">
        <p class="pull-left">&copy; RT <?= date('Y') ?></p>

        <p class="pull-right"></p>
    </div>
</footer>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
