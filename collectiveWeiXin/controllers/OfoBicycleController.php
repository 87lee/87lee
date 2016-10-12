<?php

namespace app\collectiveWeiXin\controllers;

class OfoBicycleController extends \yii\web\Controller
{
	public $layout = false;
    public function actionIndex()
    {
        return $this->render('index');
    }
    public function actionAddNumber()
    {
    	// \yii\base\Module::layoutPath = false;
        // return $this->renderFile('@app/collectiveWeiXin/views/ofo-bicycle/addNumber.php');
        return $this->render('addNumber',['title'=>'小黄车']);
    }
}
