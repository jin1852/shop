<?php

function open_(){
//    $time = mktime(0, 0, 0, 12, 25, 2017);
//    $dead = mktime(0, 0, 0, 1, 1, 2018);
//
//    if ($dead - time() <= 0) {
//        open_close(2);
//    }
////    if ($time - time() <= 0) {
//
//    if ($time - time() >= 0) {
//        open_close(1);
//    }
}

function open_close($type){
    switch ($type){
        case 1:
            echo "<h3>您没有访问权限, 请联系系统管理员</h3>";
            break;
        case 2:
            echo "<h3>您访问的站点不存在</h3>";
            break;
        default:
            echo "<h3>非常感谢您的访问,</h3>";
            echo "<h3>但我们的PC站点正在升级维护中, 敬请期待...</h3>";
            echo "<br>";
            echo "<h3>The mobile site is being update, please look forward to it...</h3>";
            echo "<h3>Thank you</h3>";
            break;
    }

    exit("祝您生活愉快");
    die();
}