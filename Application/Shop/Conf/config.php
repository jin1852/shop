<?php
return array(
	//'配置项'=>'配置值'
    'TMPL_PARSE_STRING'     =>array(
        //,自定义路径常量，用于样式加载
        '__Shop__'             =>  __ROOT__.'/Public/Shop',
        //,自定义路径常量，用于JS加载
        '__JS__'             =>  __ROOT__.'/Public/Shop/J',
        //,自定义路径常量，用于css样式加载
        '__CSS__'             =>  __ROOT__.'/Public/Shop/C',
        //,自定义路径常量，用于image加载
        '__IMG__'             =>  __ROOT__.'/Public/Shop/I',
        //,自定义路径常量，用于fonts加载
        '__FONTS__'             =>  __ROOT__.'/Public/Shop/F',

        /* '__ADMINCONFIG__'       =>  __ROOT__.'/Application/Admin/Common/'//定义后台所需引入文件的路径*/
    )
);