<?php
defined('THINK_PATH') or exit();
return  array(
    'TMPL_PARSE_STRING'     =>array(
        //,自定义路径常量，用于后台样式加载
        '__ADMIN__'             =>  __ROOT__.'/Application/Admin/Common/Template/',
        //,自定义路径常量，用于后台JS样式加载
        '__ADMINJS__'             =>  __ROOT__.'/Application/Admin/Common/Js/',
        //,自定义路径常量，用于后台css样式加载
        '__ADMINCSS__'             =>  __ROOT__.'/Application/Admin/Common/Css/',
        //,自定义路径常量，用于后台image加载
        '__ADMINIMG__'             =>  __ROOT__.'/Application/Admin/Common/Images/',
        
       /* '__ADMINCONFIG__'       =>  __ROOT__.'/Application/Admin/Common/'//定义后台所需引入文件的路径*/
    )
);
