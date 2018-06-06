<?php
function ismobile() {
// 如果有HTTP_X_WAP_PROFILE则一定是移动设备
if (isset ($_SERVER['HTTP_X_WAP_PROFILE']))
return true;

//此条摘自TPM智能切换模板引擎，适合TPM开发
if(isset ($_SERVER['HTTP_CLIENT']) &&'PhoneClient'==$_SERVER['HTTP_CLIENT'])
return true;
//如果via信息含有wap则一定是移动设备,部分服务商会屏蔽该信息
if (isset ($_SERVER['HTTP_VIA']))
//找不到为flase,否则为true
return stristr($_SERVER['HTTP_VIA'], 'wap') ? true : false;
//判断手机发送的客户端标志,兼容性有待提高
if (isset ($_SERVER['HTTP_USER_AGENT'])) {
$clientkeywords = array(
    //原有
    'nokia','sony','ericsson','mot','samsung','htc','sgh','lg','sharp','sie-','philips','panasonic',
    'alcatel','lenovo','iphone','ipod','blackberry','meizu','android','netfront','symbian','ucweb',
    'windowsce','palm','operamini','operamobi','openwave','nexusone','cldc','midp','wap','mobile',
);
//从HTTP_USER_AGENT中查找手机浏览器的关键字
if (preg_match('/(' . implode('|', $clientkeywords) . ')/i', strtolower($_SERVER['HTTP_USER_AGENT']))) {
return true;
}
}
//协议法，因为有可能不准确，放到最后判断
if (isset ($_SERVER['HTTP_ACCEPT'])) {
// 如果只支持wml并且不支持html那一定是移动设备
// 如果支持wml和html但是wml在html之前则是移动设备
if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html')))) {
return true;
}
}
return false;
}
function subtextt($text, $length)
{
    $text=htmldecode($text);
    if(mb_strlen($text, 'utf8') > $length)
        return mb_substr($text, 0, $length, 'utf8').'...';
    return $text;
}
function htmldecode($str) {
    if (empty ( $str ) || "" == $str) {
        return "";
    }

    $str = strip_tags ( $str );
    $str = htmlspecialchars ( $str );
    $str = nl2br ( $str );
    $str = str_replace ( "?", "", $str );
    $str = str_replace ( "*", "", $str );
    $str = str_replace ( "!", "", $str );
    $str = str_replace ( "~", "", $str );
    $str = str_replace ( "$", "", $str );
    $str = str_replace ( "%", "", $str );
    $str = str_replace ( "^", "", $str );
    $str = str_replace ( "^", "", $str );
    $str = str_replace ( "select", "", $str );
    $str = str_replace ( "join", "", $str );
    $str = str_replace ( "union", "", $str );
    $str = str_replace ( "where", "", $str );
    $str = str_replace ( "insert", "", $str );
    $str = str_replace ( "delete", "", $str );
    $str = str_replace ( "update", "", $str );
    $str = str_replace ( "like", "", $str );
    $str = str_replace ( "drop", "", $str );
    $str = str_replace ( "create", "", $str );
    $str = str_replace ( "modify", "", $str );
    $str = str_replace ( "rename", "", $str );
    $str = str_replace ( "alter", "", $str );
    $str = str_replace ( "cast", "", $str );

    $farr = array ("//s+/", //过滤多余的空白
        "/<(//?)(img|script|i?frame|style|html|body|title|link|meta|/?|/%)([^>]*?)>/isU", //过滤 <script 防止引入恶意内容或恶意代码,如果不需要插入flash等,还可以加入<object的过滤
        "/(<[^>]*)on[a-zA-Z]+/s*=([^>]*>)/isU" )//过滤javascript的on事件
    ;
    $tarr = array (" ", "", //如果要直接清除不安全的标签，这里可以留空
        "" );
    return $str;
}
?>