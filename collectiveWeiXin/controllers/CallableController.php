<?php

namespace app\collectiveWeiXin\controllers;
use Yii;
class CallableController extends \yii\web\Controller
{

	private $token;

    private $appId;

    private $appSecret;

    private $encodingAesKey;

    private $signature;

    private $msgSignature;

    private $timestamp;

    private $nonce;

	public function actionIndex()
	{


		$request = Yii::$app->request;
		$get = $request->get();
		// 第三方发送消息给公众平台
		$this->encodingAesKey = "abcdefghijklmnopqrstuvwxyz0123456789ABCDEFG";
		if (!empty($get['signature']) && !empty($get['timestamp']) && !empty($get['nonce']) &&!empty($get['echostr'])) {
			//验证回调域名
			$tmpArr = array(Yii::$app->params['collectiveWeixinConfig']['token'], $get['timestamp'], $get['nonce']);
			sort($tmpArr, SORT_STRING);
			$tmpStr = implode( $tmpArr );
			$tmpStr = sha1( $tmpStr );
			if( $tmpStr == $get['signature'] ){
				die($get['echostr']);
			}else{
				return false;
			}
		}else{
			$this->msgSignature = '';
            $this->signature = isset($get["signature"])?$get["signature"]:'';
            $this->nonce = $get["nonce"];
            $this->timestamp = $get["timestamp"];
            return $this->response();
		}
	}
    /**
     * 被动回复
     * @return string
     * @since  2016年6月28日
     */
    public function response()
    {
    	$postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
        $postStr = $this->decrypt($postStr);
        if (! empty($postStr)) {
            libxml_disable_entity_loader(true);
            $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
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
    	$filePath = '@app/'./*DIRECTORY_SEPARATOR .*/ 'vendor/'/*.DIRECTORY_SEPARATOR */.'weichat/'/*.DIRECTORY_SEPARATOR*/.'wxBizMsgCrypt.php';
		Yii::$classMap['WXBizMsgCrypt'] = $filePath;
        // include_once "Weichat/wxBizMsgCrypt.php";
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
        // include_once "Weichat/wxBizMsgCrypt.php";

        $filePath = '@app/'./*DIRECTORY_SEPARATOR .*/ 'vendor/'/*.DIRECTORY_SEPARATOR */.'weichat/'/*.DIRECTORY_SEPARATOR*/.'wxBizMsgCrypt.php';
		Yii::$classMap['WXBizMsgCrypt'] = $filePath;
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
}
