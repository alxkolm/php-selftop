<?php
use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;
use app\assets\AppAsset;

/* @var $this \yii\web\View */
/* @var $content string */

AppAsset::register($this);
$this->title .= ' | ' . Yii::$app->name;
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>
<body>
<?php $this->beginBody() ?>
<div class="ui container main-menu" id="main-menu">
    <div class="ui labeled button mini">
        <div class="ui button mini black">
            Menu
        </div>
        <a class="ui basic left pointing black label">Dashboard</a>
    </div>
</div>
<div id="app"></div>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
