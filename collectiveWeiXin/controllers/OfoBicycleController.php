<?php

namespace app\collectiveWeiXin\controllers;

class OfoBicycleController extends \yii\web\Controller
{
	public $layout = false;
    public function actionIndex()
    {
        return $this->render('index',['title'=>'小黄车']);
    }
    
    public function actionAdd()
    {
        $request = \Yii::$app->request;
        $post = $request->post();
        $connection = new \app\collectiveWeiXin\models\OfoBicycle();
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        return $connection->addOptions($post);
    }
}
