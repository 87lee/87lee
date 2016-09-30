<?php

namespace app\collectiveWeiXin;

/**
 * collectiveWeiXin module definition class
 */
class weixin extends \yii\base\Module
{
    /**
     * @inheritdoc
     */
    public $controllerNamespace = 'app\collectiveWeiXin\controllers';
    /**
     * 
     * @inheritdoc
     * style.com/collectiveWeiXin/callable
     */
    public function init()
    {
        //继承父级初始化
        parent::init();
        //加入模块配置
        \Yii::configure($this,require( __DIR__ . DIRECTORY_SEPARATOR . 'config'. DIRECTORY_SEPARATOR . 'collectiveWeiXin.php'));
        //操作前触发
        \Yii::$app->on(\yii\base\Application::EVENT_BEFORE_ACTION, function ($event) {
            $params = $this->params;
            $weixinAccessTokenFile = __DIR__ . DIRECTORY_SEPARATOR .'weixinAccessToken.conf';
            if (file_exists($weixinAccessTokenFile)) {
                $accessTokenStr = file_get_contents($weixinAccessTokenFile);
                $accessTokenArr = \yii\helpers\Json::decode($accessTokenStr);
            }
            if (!empty($accessTokenArr)) {
                if ( $accessTokenArr['time'] + $accessTokenArr['expires_in'] - time() - 900 <= 0) {
                    $res = $this->getAccessToken();
                    $res['time'] = time();
                }else{
                    $res = $accessTokenArr;
                }
            }else{
                $res = $this->getAccessToken();
                $res['time'] = time();
            }
            file_put_contents($weixinAccessTokenFile, \yii\helpers\Json::encode($res));
            \Yii::$app->params['collectiveWeixinConfig']['access_token'] = $res['access_token'];
        });
    }
    
    protected function getAccessToken()
    {
        $params = $this->params;
        $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=' . $params['collectiveWeixinConfig']['appId'] . '&secret='.$params['collectiveWeixinConfig']['appsecret'];
        $str = \app\helpers\Url::getUrl($url);
        if ($str === false ) {
            die('远程获取access_token失败');
        }else{
            $res = \yii\helpers\Json::decode($str);
            if (isset($res['access_token'])) {
                return $res;
            }else{
                die('微信端请求:'.$res['errcode'] .' 信息：'.$res['errmsg']);
            }
        }
    }
}
