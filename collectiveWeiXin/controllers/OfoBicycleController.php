<?php

namespace app\collectiveWeiXin\controllers;

class OfoBicycleController extends \yii\web\Controller
{
	public $layout = false;
    public function actionIndex()
    {
        /*$a = \Yii::getAlias('@web');
        var_dump($a);
        die;*/
        return $this->render('index',['title'=>'小黄车']);
    }
    public function actionAddNumber()
    {
    	// \yii\base\Module::layoutPath = false;
        // return $this->renderFile('@app/collectiveWeiXin/views/ofo-bicycle/addNumber.php');
        return $this->render('addNumber',['title'=>'小黄车']);
    }
    public function actionAdd()
    {
        $request = \Yii::$app->request;
        $post = $request->post();
        
        $connection = new \app\collectiveWeiXin\models\OfoBicycle();
        $a = $connection->addOptions($post);
        // $connection->createCommand()->insert('user', $post)->execute();
        // var_dump($a);
        return $this->redirect(\yii\helpers\Url::to('collectiveWeiXin/ofo-bicycle/add-number',true));
    }
}
