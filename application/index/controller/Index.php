<?php
namespace app\index\controller;
use common\base\BasicController;
use common\model\gr_chat\Admin;
use think\Queue;
use app\job\index\demo;

class Index extends BasicController
{
    public function index()
    {
        dd('this');
    }


    //创建同步任务
    public function jobCreate()
    {
        $res=Queue::push(demo::class,"test");
        dd($res);
    }

    //创建异步任务
    public function jobCreate2()
    {
        //延迟10秒执行
        $res=Queue::later(10,demo::class,"test");
        dd($res);
    }
}
