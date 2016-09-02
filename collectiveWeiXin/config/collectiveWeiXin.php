<?php 


return [
	'components' => [
        'response' => [
            'class' => 'yii\web\Response',
            'on beforeSend' => function ($event) {
                $response = $event->sender;
                if ($response->data !== null) {
                    $response->data = [
                        'success' => $response->isSuccessful,
                        'data' => $response->data,
                    ];
                    $response->statusCode = 200;
                }
            },
        ],
    ],
	'params'=>[
		'collectiveWeixinConfig' => [
	    	'appId'=>'wx21a3ada21689a706',
	    	'appsecret'=>'d4624c36b6795d1d99dcf0547af5443d',
	    	'token'=>'pgk123'
		]
	]	
];