<?php

namespace Home\Common\Lib;

use Org\Util\Curl;

class WeiChat
{

    private $token;

    private $appId;

    private $appSecret;

    private $encodingAesKey;

    private $signature;

    private $msgSignature;

    private $timestamp;

    private $nonce;

    public $baseText = '真开心被你关注，小哇在此恭候多时了 /:coffee

领福利请点击菜单：【签到赚钱】

小哇全心为你服务，是你煲剧路上的小伙伴哦~
更多福利，请到菜单栏查看，有惊喜哦！';
    //public $baseText="点击下方菜单栏<a href='http://mp.vsoontech.com/PHP/Wavideo/Home/Autumn/index'>【中秋活动】</a>即可参与活动。活动截止时间为2016年09.15晚24点，结果公布时间为2016.09.16日中午12点。";

    public $apiList = array(
        //获取accessToken
        'getToken' => 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=%s&secret=%s', 
        //创建自定义菜单
        'create' => 'https://api.weixin.qq.com/cgi-bin/menu/create?access_token=%s', 
        //获取菜单列表
        'get' => 'https://api.weixin.qq.com/cgi-bin/menu/get?access_token=%s');

    public function getToken()
    {
        return $this->token;
    }

    public function getAppId()
    {
        return $this->appId;
    }

    public function getAppSecret()
    {
        return $this->appSecret;
    }

    public function __construct($config)
    {
        isset($config['TOKEN']) && $this->token = $config['TOKEN'];
        isset($config['APP_ID']) && $this->appId = $config['APP_ID'];
        isset($config['APP_SECRET']) && $this->appSecret = $config['APP_SECRET'];
        isset($config['ENCODINGAESKEY']) && $this->encodingAesKey = $config['ENCODINGAESKEY'];
    }

    /**
     * 签名验证
     *
     *
     * @author 张涛<1353178739@qq.com>
     * @since  2016年6月28日
     */
    public function access()
    {
        $signature = $_GET["signature"];
        $msgSignature = $_GET["msg_signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];
        $token = $this->getToken();
        $echoStr = $_GET["echostr"];
        
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode($tmpArr);
        $tmpStr = sha1($tmpStr);
        
        if ($tmpStr == $signature && $echoStr) {
            return $echoStr;
        } else {
            $this->msgSignature = $msgSignature;
            $this->signature = $signature;
            $this->nonce = $nonce;
            $this->timestamp = $timestamp;
            return $this->response();
        }
    }

    /**
     * 获取access_token
     * 
     * 
     * @author 张涛<1353178739@qq.com>
     * @since  2016年6月28日
     */
    public function getAccessToken($force = false)
    {
        $weChatMod = M('WeixinToken');
        $appId = $this->appId;
        $secret = $this->appSecret;
        $old = $weChatMod->where(['appId' => $appId, 'secret' => $secret])->find();
        if (empty($old) || $old['expires'] < time()) {
            $curl = new Curl();
            $url = sprintf($this->apiList['getToken'], $appId, $secret);
            $res = $curl->get($url);
            $res = json_decode($res, true);
            $token = [
                'appId' => $appId, 
                'secret' => $secret, 
                'token' => $res['access_token'], 
                'expires' => time() + $res['expires_in'] - 200, 
                'time' => time()];
            if (empty($old)) {
                $weChatMod->add($token);
            } else {
                $weChatMod->where(['id' => $old['id']])->save($token);
            }
            return $res['access_token'];
        } else {
            return $old['token'];
        }
    }

    /**
     * 请求微信重新获取access_token
     */
    public function updateAccessToken()
    {
        $appId = $this->appId;
        $secret = $this->appSecret;
        $curl = new Curl();
        $url = sprintf($this->apiList['getToken'], $appId, $secret);
        $res = $curl->get($url);
        return json_decode($res, true);
    }

    /**
     * 获取api_ticket
     * @author 张涛<1353178739@qq.com>
     * @since  2016年7月18日
     */
    public function getJsApiTicket($force = false)
    {
        $accessToken = $this->getAccessToken();
        $ticketMod = M('JsApiTicket');
        $old = $ticketMod->find();
        $curl = new Curl();
        if (empty($old)) {
            $url = 'https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token=%s&type=jsapi';
            $res = $curl->get(sprintf($url, $accessToken));
            $res = json_decode($res, true);
            $ticket = ['api_ticket' => $res['ticket'], 'expire' => time() + $res['expires_in'] - 200];
            $ticketMod->add($ticket);
            return $res['ticket'];
        } else {
            if ($old['expire'] < time()) {
                $url = 'https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token=%s&type=jsapi';
                $res = $curl->get(sprintf($url, $accessToken));
                $res = json_decode($res, true);
                $ticket = ['api_ticket' => $res['ticket'], 'expire' => time() + $res['expires_in'] - 200];
                $ticketMod->where(['id' => $old['id']])->save($ticket);
                return $res['ticket'];
            } else {
                return $old['api_ticket'];
            }
        }
    }

    /**
     * 生成jssdk签名
     * 
     * 
     * @author 张涛<1353178739@qq.com>
     * @since  2016年7月18日
     */
    public function getSignPackage()
    {
        $ticket = $this->getJsApiTicket();
        $timestamp = time();
        $noncestr = rand_code(16);
        $protocol = (! empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $url = "$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        
        $string = "jsapi_ticket=$ticket&noncestr=$noncestr&timestamp=$timestamp&url=$url";
        $signature = sha1($string);
        
        $signPackage = array(
            "appId" => $this->appId, 
            "nonceStr" => $noncestr, 
            "timestamp" => $timestamp, 
            "url" => $url, 
            "signature" => $signature,
            /* "rawString" => $string */);
        return $signPackage;
    }

    /**
     * 创建自定义菜单
     * 
     * 
     * @author 张涛<1353178739@qq.com>
     * @since  2016年6月28日
     */
    public function createItem()
    {
        $accessToken = $this->getAccessToken();
        $curl = new Curl();
        $url = sprintf($this->apiList['create'], $accessToken);
        $menuJson = '{
                    "button": [
                        {
                            "name": "影视推荐", 
                            "sub_button": [
                                {
                                    "type": "click", 
                                    "name": "挖好视频", 
                                    "key": "item001", 
                                    "sub_button": [ ]
                                }, 
                                {
                                    "type": "view", 
                                    "name": "热剧：老九门", 
                                    "url": "http://m.iqiyi.com/a_19rrhbeaxt.html", 
                                    "sub_button": [ ]
                                }, 
                                {
                                    "type": "view", 
                                    "name": "独家：灭罪师", 
                                    "url": "http://m.iqiyi.com/a_19rrhao9z5.html", 
                                    "sub_button": [ ]
                                }, 
                                {
                                    "type": "view", 
                                    "name": "热综：上学啦", 
                                    "url": "http://m.iqiyi.com/v_19rrm09uvo.html#vfrm=2-3-0-1", 
                                    "sub_button": [ ]
                                }, 
                                {
                                    "type": "view", 
                                    "name": "历史消息", 
                                    "url": "http://mp.weixin.qq.com/mp/getmasssendmsg?__biz=MzI4MzMxMzQyNw==#wechat_webview_type=1&wechat_redirect", 
                                    "sub_button": [ ]
                                }
                            ]
                        }, 
                        {
                                    "type": "view", 
                                    "name": "签到赚钱", 
                                    "url": "http://mp.vsoontech.com/PHP/Wavideo/index.php/Home/Account/sign/s/1", 
                                    "sub_button": [ ]
                        },  
                        {
                            "name": "我的", 
                            "sub_button": [
                                {
                                    "type": "view", 
                                    "name": "爱奇艺会员", 
                                    "url": "http://mp.vsoontech.com/PHP/Wavideo/index.php/Home/Activity/iQiYi/s/1", 
                                    "sub_button": [ ]
                                }, 
                                {
                                    "type": "view", 
                                    "name": "订单查询", 
                                    "url": "http://mp.vsoontech.com/PHP/Wavideo/index.php/Home/Order/index/s/1", 
                                    "sub_button": [ ]
                                }, 
                                {
                                    "type": "view", 
                                    "name": "下载TV端", 
                                    "url": "http://mp.vsoontech.com/PHP/Wavideo/index.php/Home/Help/tvClientDownload/s/1", 
                                    "sub_button": [ ]
                                }
                            ]
                        }
                    ]
                }';
        $res = $curl->post($url, $menuJson);
        $res = json_decode($res, true);
        if ($res['errcode'] == 0) {
            return 'success';
        } else {
            return $res;
        }
    }

    /**
     * 获取自定义菜单
     * 
     * 
     * @author 张涛<1353178739@qq.com>
     * @since  2016年6月28日
     */
    public function getItem()
    {
        $accessToken = $this->getAccessToken();
        $curl = new Curl();
        $url = sprintf($this->apiList['get'], $accessToken);
        return $curl->get($url);
    }

    /**
     * 被动回复
     * 
     * 
     * @return string
     * @author 张涛<1353178739@qq.com>
     * @since  2016年6月28日
     */
    public function response()
    {
        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
        $postStr = $this->decrypt($postStr);
        if (! empty($postStr)) {
            libxml_disable_entity_loader(true);
            $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
            
             $errorFile = LOG_PATH . 'Home/respon' . date('Ymd', time()) . '.log';
             \Think\Log::write(var_export($postObj,true), '', '', $errorFile);
            
            $msgType = $postObj->MsgType;
            switch ($msgType) {
                case 'text':
                    $text = $postObj->Content;
                    $reply = auto_reply($text);
                    if (! empty($reply)) {
                        $msg = $this->responseText($postObj, $reply);
                    } else {
                        //视频检索,需要设定匹配规则
                        $video = D('Program', 'Vod')->alias('p')
                            ->join('vod_program_source ps ON p.id = ps.program_id')
                            ->where(['p.name' => trim($text)])
                            ->field('p.intro, p.id, p. name,p.down_pic')
                            ->find();
                        if (! empty($video)) {
                            $pic = array(
                                array(
                                    'title' => $video['name'], 
                                    'desc' => mb_substr($video['intro'], 0, 30, 'utf-8') . '...', 
                                    'picUrl' => $video['down_pic'], 
                                    'url' => U('Video/index', array('videoId' => $video['id']), '', $_SERVER['SERVER_NAME'])));
                            return $this->responseImage($postObj, $pic);
                        } else {
                            //推送给企业客服
                            $fromUsername = $postObj->FromUserName;
                            $userInfo = R('Weixin/getBase', ['openId' => $fromUsername]);
                            $kfList = R('Service/getKfList', ['type' => 'external']);
                            $kfInfo = D('KefuAccess', 'Logic')->access(trim($fromUsername), 
                                ['type' => 'text', 'content' => trim($text)]);
                            if ($kfInfo == false) {
                                //没有客服接入
                                $linkIn = U('Service/linkIn', ['openid' => $fromUsername], '', $_SERVER['SERVER_NAME']);
                                $typeContent = "【{$userInfo['nickname']}】咨询：" . $text . "   <a href='" . $linkIn . "'>点击接入</a>";
                            } else {
                                //有接入客服
                                $typeContent = "【{$userInfo['nickname']}】咨询【{$kfInfo['name']}】：" . $text;
                                R('Service/sendToKf', 
                                    ['from' => $fromUsername, 'to' => $kfInfo['userid'], 'typeContent' => $text, 'type' => 'text']);
                            }
                            R('Service/sendMsg', ['userid' => C('PUSH_USERID_LIST'), 'typeContent' => $typeContent, 'type' => 'text']);
                        }
                    }
                    break;
                case 'image':
                    $pic = $postObj->PicUrl;
                    $fromUsername = $postObj->FromUserName;
                    $userInfo = R('Weixin/getBase', ['openId' => $fromUsername]);
                    $this->mkdir(C('IMAGE_TEMP_PATH') . $fromUsername . '/');
                    $path = C('IMAGE_TEMP_PATH') . $fromUsername . '/' . time() . '.png';
                    \Org\Net\Http::curlDownload($pic, $path);
                    $res = R('Service/uploadMedia', ['path' => $path, 'type' => 'image']);
                    $mediaId = $res['media_id'];
                    //推送给企业客服
                    $kfList = R('Service/getKfList', ['type' => 'external']);
                    $kfInfo = D('KefuAccess', 'Logic')->access(trim($fromUsername), ['type' => 'image', 'content' => trim($mediaId)]);
                    if ($kfInfo == false) {
                        //没有客服接入
                        $linkIn = U('Service/linkIn', ['openid' => $fromUsername], '', $_SERVER['SERVER_NAME']);
                        $typeContent = "【{$userInfo['nickname']}】咨询：" . "<a href='" . $pic . "'>点击图片查看</a>" . "   <a href='" . $linkIn .
                             "'>点击接入</a>";
                    } else {
                        //有接入客服
                        $typeContent = "【{$userInfo['nickname']}】咨询【{$kfInfo['name']}】：" . "<a href='" . $pic . "'>点击图片查看</a>";
                        R('Service/sendToKf', 
                            ['from' => $fromUsername, 'to' => $kfInfo['userid'], 'typeContent' => $mediaId, 'type' => 'image']);
                    }
                    R('Service/sendMsg', ['userid' => C('PUSH_USERID_LIST'), 'typeContent' => $typeContent, 'type' => 'text']);
                    break;
                case 'location':
                case 'voice':
                case 'video':
                case 'shortvideo':
                case 'link':
                    //$msg = $this->responseText($postObj);
                    break;
                case 'event':
                    $msg = $this->responseEvent($postObj);
                    break;
                default:
                    break;
            }
            if(empty($msg)){
                $msg=$this->respCustom($postObj);
            }
            $encyMsg = $this->encrypt($msg);
            return $encyMsg;
        }
    }

    /**
     * 回复文本
     * 
     * 
     * @param unknown $postObj
     * @param string $msg
     * @return string
     * @author 张涛<1353178739@qq.com>
     * @since  2016年6月28日
     */
    public function responseText($postObj, $msg = '')
    {
        $fromUsername = $postObj->FromUserName;
        $toUsername = $postObj->ToUserName;
        $keyword = trim($postObj->Content);
        $time = time();
        $tpl = "<xml>
                  <ToUserName><![CDATA[%s]]></ToUserName>
                  <FromUserName><![CDATA[%s]]></FromUserName>
                  <CreateTime>%s</CreateTime>
                  <MsgType><![CDATA[%s]]></MsgType>
                  <Content><![CDATA[%s]]></Content>
                  <FuncFlag>0</FuncFlag>
                </xml>";
        $msgType = "text";
        $contentStr = ! empty($msg) ? $msg : $this->baseText;
        $resultStr = sprintf($tpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
        return $resultStr;
    }

    /**
     * 回复事件请求响应
     * @author 张涛<1353178739@qq.com>
     * @since  2016年6月28日
     */
    public function responseEvent($postObj)
    {
        $fromUsername = $postObj->FromUserName;
        $toUsername = $postObj->ToUserName;
        $event = $postObj->Event;
        $eventKey = $postObj->EventKey;
        $ticket = $postObj->Ticket;
        
        $openId = trim($fromUsername);
        if (! empty($ticket) && in_array($event, array('subscribe', 'SCAN'))) {
            $sceneId = str_replace('qrscene_', '', trim($eventKey));
            if ((int)$sceneId >= C('MIN_SCENE_ID_FOR_UUID')) {
                //TV端绑定扫码关注
                $this->log('response.txt', var_export(R('Weixin/getBase'), true));
                D('UuidOpenid', 'Logic')->addRecord($sceneId, $openId);
                D('WxUser', 'Logic')->bindTvClient($sceneId, $openId);
                if ($event == 'subscribe') {
                    D('QcodeAttentionRecord', 'Logic')->scanRecord($sceneId, $openId);
                    D('TvFirstSubscribe', 'Logic')->addRecord($sceneId, $openId);
                }
            } else {
                    if ($event == 'subscribe') {
                        $errorFile = LOG_PATH . 'Home/respon' . date('Ymd', time()) . '.log';
                        \Think\Log::write('关注事件'.$sceneId.'--'.$openId, '', '', $errorFile);
                        //抽荔枝TV会员卡分享扫码关注
                        D('QcodeAttentionRecord', 'Logic')->qcodeAttention($sceneId, $openId);
                    }else{
                        $errorFile = LOG_PATH . 'Home/respon' . date('Ymd', time()) . '.log';
                        \Think\Log::write('非关注事件', '', '', $errorFile);
                    }
            }
            
            //记录首次关注公众号,扫码关注
            if ($event == 'subscribe') {
                D('FirstSubscribe', 'Logic')->addRecord($openId, 'qcode',$sceneId);
            }
            
            return $this->responseText($postObj);
        } else {
            //记录首次关注公众号,普通关注
            if ($event == 'subscribe') {
                D('FirstSubscribe', 'Logic')->addRecord(trim($fromUsername), 'normal');
            }
            
            if (strtolower($event) == 'click') {
                $randVideo = D('VideoPublish', 'Video')->getOneByRand();
                $pic = array(
                    array(
                        'title' => $randVideo['name'], 
                        'desc' => $randVideo['short_comment'], 
                        'picUrl' => $randVideo['pic'], 
                        'url' => U('Page/index', array('vi' => $randVideo['video_id']), '', $_SERVER['SERVER_NAME'])));
                return $this->responseImage($postObj, $pic);
            } else {
                return $this->responseText($postObj);
            }
        }
    }

    /**
     * 回复图文消息
     * 
     * 
     * @author 张涛<1353178739@qq.com>
     * @since  2016年6月28日
     */
    public function responseImage($postObj, $pic)
    {
        $fromUsername = $postObj->FromUserName;
        $toUsername = $postObj->ToUserName;
        $time = time();
        $count = count($pic) > 10 ? 10 : count($pic);
        
        $contentStr = "<xml> 
                  <ToUserName><![CDATA[{$fromUsername}]]></ToUserName>  
                  <FromUserName><![CDATA[{$toUsername}]]></FromUserName>  
                  <CreateTime>{$time}</CreateTime>  
                  <MsgType><![CDATA[news]]></MsgType>  
                  <ArticleCount>{$count}</ArticleCount>
                  <Articles>";
        $i = 1;
        $itemTpl = "<item> 
                      <Title><![CDATA[%s]]></Title>  
                      <Description><![CDATA[%s]]></Description>  
                      <PicUrl><![CDATA[%s]]></PicUrl>  
                      <Url><![CDATA[%s]]></Url> 
                   </item> ";
        foreach ($pic as $k => $v) {
            if ($i > 10) {
                break;
            }
            $contentStr .= sprintf($itemTpl, $v['title'], $v['desc'], $v['picUrl'], $v['url']);
            $i ++;
        }
        
        $contentStr .= "</Articles> 
               </xml>";
        return $contentStr;
    }

    /**
     * 解密方法
     *
     *
     * @author 张涛<1353178739@qq.com>
     * @since  2016年6月29日
     */
    public function decrypt($xml)
    {
        include_once "Weichat/wxBizMsgCrypt.php";
        $encodingAesKey = $this->encodingAesKey;
        $token = $this->token;
        $timeStamp = $this->timestamp;
        $nonce = $this->nonce;
        $appId = $this->appId;
        $msgSignature = $this->msgSignature;

        $pc = new \WXBizMsgCrypt($token, $encodingAesKey, $appId);
        $msg = '';
        $errCode = $pc->decryptMsg($msgSignature, $timeStamp, $nonce, $xml, $msg);
        if ($errCode == 0) {
            return $msg;
        } else {
            return $errCode;
        }
    }

    /**
     * 加密方法
     *
     *
     * @author 张涛<1353178739@qq.com>
     * @since  2016年6月29日
     */
    public function encrypt($xml)
    {
        include_once "Weichat/wxBizMsgCrypt.php";
        $encodingAesKey = $this->encodingAesKey;
        $token = $this->token;
        $timeStamp = $this->timestamp;
        $nonce = $this->nonce;
        $appId = $this->appId;
        $pc = new \WXBizMsgCrypt($token, $encodingAesKey, $appId);
        $encryptMsg = '';
        $errCode = $pc->encryptMsg($xml, $timeStamp, $nonce, $encryptMsg);
        if ($errCode == 0) {
            return $encryptMsg;
        } else {
            return $errCode;
        }
    }

    /**
     * 消息转发多客服
     * 
     * 
     * @author 张涛<1353178739@qq.com>
     * @since  2016年8月22日
     */
    public function respCustom($postObj)
    {
        $fromUsername = $postObj->FromUserName;
        $toUsername = $postObj->ToUserName;
        $time = time();
        $tpl = "<xml> 
                  <ToUserName><![CDATA[%s]]></ToUserName>  
                  <FromUserName><![CDATA[%s]]></FromUserName>  
                  <CreateTime>%d</CreateTime>  
                  <MsgType><![CDATA[%s]]></MsgType> 
                </xml>";
        $msgType = "transfer_customer_service";
        $resultStr = sprintf($tpl, $fromUsername, $toUsername, $time, $msgType);
        return $resultStr;
    }

    /**
     * 上传临时素材
     * @param unknown $path
     * @param string $type
     * @return void|mixed
     * @author 张涛<1353178739@qq.com>
     * @since  2016年8月30日
     */
    public function uploadMedia($path, $type = 'image')
    {
        $accessToken = $this->getAccessToken();
        $url = "https://api.weixin.qq.com/cgi-bin/media/upload?access_token={$accessToken}&type={$type}";
        if (file_exists($path)) {
            if (class_exists('CURLFile')) {
                $filedata = array('media' => new \CURLFile($path));
            } else {
                $filedata = array('media' => '@' . $path);
            }
            $res = http_post($url, $filedata);
            $res = json_decode($res, true);
            return $res;
        } else {
            return;
        }
    }

}
