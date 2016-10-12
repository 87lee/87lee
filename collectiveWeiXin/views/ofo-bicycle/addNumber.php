<?php

/* @var $this \yii\web\View */
/* @var $content string */

use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;
use app\assets\AppAsset;
AppAsset::register($this);
$this->title = $title;
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
<div style="margin:0px auto;width:215px;">
	<?php echo Html::beginForm(['ofo-bicycle/add'/*, 'id' => $id*/], 'post', ['enctype' => 'multipart/form-data']) ?>
	号码：
	<?php echo Html::input('text', 'number'/*, $user->name, ['class' => $username]*/) ?>
	<br />
	密码：
	<?php echo Html::input('text', 'pwd'/*, $user->name, ['class' => $username]*/) ?>
	<br />
	<?= Html::submitButton('提交', ['class' => 'submit']) ?>

	<?php echo Html::endForm() ?>
</div>
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
