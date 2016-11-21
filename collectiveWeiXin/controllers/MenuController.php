<?php

namespace app\collectiveWeiXin\controllers;
use yii\helpers\Url;
class MenuController extends \yii\web\Controller
{
    public function actionIndex()
    {


    	$url = 'https://api.weixin.qq.com/cgi-bin/menu/create?access_token=%s';
    	$url = sprintf($url, \yii::$app->params['collectiveWeixinConfig']['access_token']);
    	$json = '{
            "button": [
                {
                    "name": "功能", 
                    "sub_button": [
                        {
                            "type": "view", 
                            "name": "添加小黄车", 
                            "url": "'.Url::to('collectiveWeiXin/ofo-bicycle/index',true).'", 
                            "sub_button": [ ]
                        }
                    ]
                }
            ]
        }';
    	echo $get = \app\helpers\Url::postUrl($url,$json);
    	// var_dump($get);
        // return $this->render('index');
    }

}
