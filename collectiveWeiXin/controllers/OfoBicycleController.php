<?php

namespace app\collectiveWeiXin\controllers;

class OfoBicycleController extends \yii\web\Controller
{
	public $layout = false;
    public $enableCsrfValidation = false;//去除CSRF令牌验证
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
