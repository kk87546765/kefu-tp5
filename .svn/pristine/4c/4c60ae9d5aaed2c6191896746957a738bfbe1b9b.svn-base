<?php
namespace app\scripts\controller;

class Demo extends Base
{
    protected $func_arr = [
        ['func'=>'test1','param'=>'','delay_time'=>0,'runtime'=>10,'limit'=>0],
        ['func'=>'test2','param'=>'','delay_time'=>0,'runtime'=>0,'limit'=>0],
        ['func'=>'test3','param'=>'','delay_time'=>0,'runtime'=>5,'limit'=>0,'is_single'=>1],
    ];
    #脚本调用
    public function run()
    {
        $this->apiRun();//循环调用$func_arr里面配置方法
    }
    #检查脚本锁情况、执行次数
    public function check()
    {
        dd($this->apiCheckFuncList());
    }
    #清除脚本锁缓存
    public function clean()
    {
        $func = $this->req->get('func/s','all');
        dd($this->apiClean($func));
    }
    public function test(){
        dd($_SERVER);
    }

    protected function test1(){
        echo 1;
        sleep(2);
        //1.需要return不能中断
        return true;//当前方法全部执行完成返回true、自动执行下一个方法
        return false;//当前方法未执行完成返回false、下次执行还会调用当前方法（分页自行处理）
    }

    protected function test2(){
        echo 2;
        return true;
    }
    protected function test3(){
        echo 3;
        return true;
    }


}
