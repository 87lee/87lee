<?php

namespace app\collectiveWeiXin\controllers;

class CallableController extends \yii\web\Controller
{
    	public function actionIndex()
    	{
	    	$signature = $_GET["signature"];

	        	$timestamp = $_GET["timestamp"];

	        	$nonce = $_GET["nonce"];

		$token = 'pgk123';

		$tmpArr = array($token, $timestamp, $nonce);

		sort($tmpArr, SORT_STRING);

		$tmpStr = implode( $tmpArr );

		$tmpStr = sha1( $tmpStr );


		if( $tmpStr == $signature ){
			echo $_GET["echostr"];
			// return true;

		}else{

			return false;

		}
        		// return $this->render('index');
    	}

}
