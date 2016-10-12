<?php

/* @var $this \yii\web\View */
/* @var $content string */

use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;
use app\assets\AppAsset;
AppAsset::register($this);
?>
<?php $this->beginPage()?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= Html::csrfMetaTags() ?>
    <title><?php echo  Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>
<body>
<?php $this->beginBody() ?>
<?php echo Html::beginForm(['order/update'/*, 'id' => $id*/], 'post', ['enctype' => 'multipart/form-data']) ?>
<?php echo Html::input('text', 'username'/*, $user->name, ['class' => $username]*/) ?>
<?php echo Html::endForm() ?>
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
