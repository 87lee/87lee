<?php

namespace app\helpers;

/**
 * ContactForm is the model behind the contact form.
 */
class Url 
{
	protected static $config = [
		'CURLOPT_HTTP_VERSION'=>CURL_HTTP_VERSION_1_0,
		'CURLOPT_CONNECTTIMEOUT'=>0,
		'CURLOPT_TIMEOUT'=>0,
		'CURLOPT_RETURNTRANSFER'=>TRUE,
		'CURLOPT_ENCODING'=>'',
		'CURLOPT_SSL_VERIFYPEER'=>FALSE,
		'CURLOPT_SSL_VERIFYHOST'=>2,
	]; 
    public function deleteUrl($url,$config = [])
    {
    	$config = array_merge(self::$config,$config);
        //CURL初始化
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");

        $ch = self::setCommonCurlSetopt($ch,$url,$config);

        $content = curl_exec($ch);
        $status = curl_getinfo($ch);
        curl_close($ch);
        if(intval($status["http_code"]) == 200){
            return $content;

        }else{

            return false;
        }
    }
    public static function getUrl($url,$config = [])
    {
        //CURL初始化
        $ch = curl_init();

        $ch = self::setCommonCurlSetopt($ch,$url,$config);

        $content = curl_exec($ch);
        $status = curl_getinfo($ch);
        curl_close($ch);
        if(intval($status["http_code"]) == 200)
        {
            return $content;
        }else
        {
            return false;
        }
    }

    protected static function setCommonCurlSetopt($ch,$url,$config=[])
    {
    	$config = array_merge(self::$config,$config);

    	curl_setopt($ch, CURLOPT_HTTP_VERSION, $config['CURLOPT_HTTP_VERSION']);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $config['CURLOPT_HTTP_VERSION']);
        curl_setopt($ch, CURLOPT_TIMEOUT,$config['CURLOPT_TIMEOUT']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, $config['CURLOPT_RETURNTRANSFER']);
        curl_setopt($ch, CURLOPT_ENCODING, $config['CURLOPT_ENCODING']);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $config['CURLOPT_SSL_VERIFYPEER']);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, $config['CURLOPT_SSL_VERIFYHOST']);
        curl_setopt($ch, CURLOPT_URL, $url);
    	return $ch;
    }

    public static function postUrl($url,$file,$config = [])
    {
        $header = array(
         'Content-Type: application/json; charset=utf-8',
          'Content-Length: ' . strlen($file)
        );
        //CURL初始化
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $file);
        //设置公共参数
        $ch = self::setCommonCurlSetopt($ch,$url,$config);
        $content = curl_exec($ch);
        $status = curl_getinfo($ch);
        curl_close($ch);
        if(intval($status["http_code"]) == 200)
        {
            return $content;

        }else
        {
            return false;
        }
    }
    /**
     * post Content-Type: application/x-www-form-urlencoded; charset=utf-8 类型数据
     * $data = 'rand=93w9&gmsfhm=440804198908110558&sfzxm=440800';
     * @param  [type] $url  [post地址]
     * @param  [type] $file [post 数据]
     * @return [type]       [description]
     */
    public function postFormUrl($url,$file)
    {
        
        $header = array(
         'Content-Type: application/x-www-form-urlencoded; charset=utf-8',
          'Content-Length: ' . strlen($file)
        );
        //CURL初始化
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $file);

        //设置公共参数
        $ch = self::setCommonCurlSetopt($ch,$url,$config);

        $content = curl_exec($ch);
        $status = curl_getinfo($ch);
        curl_close($ch);
        if(intval($status["http_code"]) == 200)
        {
            return $content;

        }else
        {

            return false;
        }


    }
 	public function cbc_decontent($content)
    {
		$content = str_replace("\r", "", $content);
		$content = str_replace("\n", "", $content);
		$content = str_replace(" ", "", $content);

		$key = 'YZV3141592653589';
		$vi = 'PLANCKH413566743';

		$model =MCRYPT_MODE_CBC;
		$cipher  = MCRYPT_RIJNDAEL_128;



		$content = mcrypt_decrypt($cipher,$key,base64_decode($content),$model,$vi);

		if ($content[strlen($content) - 1] !== '}') {
			$content = rtrim($content,$content[strlen($content) - 1]);
		}
		return  $content;
    }

    function cbc_encontent($content)
    {
		    		//加密参数
		$encryptKey = 'YZV3141592653589';
		$encryptVi = 'PLANCKH413566743';
		$encryptModel = MCRYPT_MODE_CBC;
		$encryptCipher  = MCRYPT_RIJNDAEL_128;

		$block = mcrypt_get_block_size($encryptCipher,$encryptModel);
		$num = $block - strlen($content)%$block;
		$content .= str_repeat(chr($num),$num);

		$content = mcrypt_encrypt($encryptCipher,$encryptKey,$content,$encryptModel,$encryptVi);
		$content = base64_encode($content);
		return $content;
    }
}
