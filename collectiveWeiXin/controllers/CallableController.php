<?php

namespace app\collectiveWeiXin\controllers;
use Yii;
class CallableController extends \yii\web\Controller
{
	public function actionIndex()
	{

		$request = Yii::$app->request;
		$get = $request->get();

		//验证回调域名
		if (!empty($get['signature']) && !empty($get['timestamp']) && !empty($get['nonce']) && !empty($get['echostr']) ) {
			
			$tmpArr = array(Yii::$app->params['collectiveWeixinConfig']['token'], $get['timestamp'], $get['nonce']);
			sort($tmpArr, SORT_STRING);
			$tmpStr = implode( $tmpArr );
			$tmpStr = sha1( $tmpStr );
			if( $tmpStr == $get['signature'] ){
				echo $get['echostr'];
				die;
			}else{
				return false;
			}
		}

	}

}
