<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\assets;

use yii\web\AssetBundle;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class WeiXinAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        "weixin/css/framework.css",
        "weixin/css/colorbox.css",
        "weixin/css/elements.css",
        "weixin/css/style.css",
        "weixin/css/responsive.css" ,
        "weixin/css/hidpi.css" ,
        "weixin/css/skin.css",
        "weixin/css/custom.css",
        'css/site.css',
    ];
    public $js = [
        "weixin/js/jquery.min.js",
        "weixin/js/effects.jquery-ui.min.js",
        "weixin/js/jquery.nivo-slider.min.js",
        "weixin/js/jquery.colorbox.min.js",
        "weixin/js/contact.js",
        "weixin/js/custom.js",
    ];
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
        'yii\web\JqueryAsset',
    ];
}
