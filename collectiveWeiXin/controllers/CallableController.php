<?php

namespace app\collectiveWeiXin\controllers;
use Yii;
class CallableController extends \yii\web\Controller
{
    	public function actionIndex()
    	{
    		$request = Yii::$app->request;

		$get = $request->get();

	    	$signature = $request->get("signature",1);

	        	$timestamp = $request->get("timestamp",1) ;

	        	$nonce = $request->get("nonce",1) ;

		$token = 'pgk123';

		$tmpArr = array($token, $timestamp, $nonce);

		sort($tmpArr, SORT_STRING);

		$tmpStr = implode( $tmpArr );

		$tmpStr = sha1( $tmpStr );


		if( $tmpStr == $signature ){
			Yii::trace('验证成功');
			echo $request->get("echostr",1);
			die;
			// return true;

		}else{
			Yii::trace('验证失败');
			return false;

		}
        		// return $this->render('index');
    	}

}
