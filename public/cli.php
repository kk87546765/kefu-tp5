<?php

//header('Access-Control-Allow-Headers:token');
header('Access-Control-Allow-Headers:*');
header('Access-Control-Max-Age:86400');
//header('Access-Control-Allow-Origin:'.$_SERVER['HTTP_ORIGIN']);
header('Access-Control-Allow-Origin:*');


$depr = '/';
$path = isset($_SERVER['argv'][1])?$_SERVER['argv'][1]:'';


if(!empty($path)) {
    $params = explode($depr,trim($path,$depr));
}
 
!empty($params)?$_GET['g']=array_shift($params):"";
!empty($params)?$_GET['m']=array_shift($params):"";
!empty($params)?$_GET['a']=array_shift($params):"";

set_time_limit(0);
 
// 解析剩余参数 并采用GET方式获取
@parse_str($_SERVER['argv'][2],$_GET['p']);

// 定义应用目录
define('APP_PATH', __DIR__ . '/../application/');
// 加载框架引导文件
require __DIR__ . '/../thinkphp/start.php';
