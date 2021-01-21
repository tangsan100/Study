<?php

require ("./Http.class.php");
$http = new Http("http://47.106.176.36/image/girl.jpg");
$http->setHeadInfo("Referer: http://img.baidu.org/");
$res = $http->get();

// 两个空行之前的字符串
$header = strchr($res,"\r\n\r\n",true);

//分割成数组
$header = explode("\r\n",$header);
$ext = "png";

// 寻找扩展名 stripos 寻找字符串第一次出现的位置，不区分大小写，没找到返回false
foreach ($header as $key=>$value){
    if (stripos($value,"Content-Type") !== false){
        $tmp1 = explode(":",$value);
        $tmp2= explode("/",$tmp1[1]);
        $ext = $tmp2[1];
    }
}


//返回两个换行符之后剩下的所有信息,效果和strchr 一样，两个函数基本等价
$res = strstr($res,"\r\n\r\n");

//返回从第4个位置开始剩下的内容
$res = substr($res,4);

//写入文件
file_put_contents("b.".$ext,$res);

echo "ok";