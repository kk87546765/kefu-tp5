<?php

namespace app\job\index;

use think\Log;
use think\queue\Job;

class demo
{
    public function fire(Job $job)
    {
        /*逻辑*/

        Log::write("开始任务");

        if ($job->attempts() > 1) {
            Log::write("这个任务已经重试了".$job->attempts()."次");
        }
        //如果任务执行成功后 记得删除任务，不然这个任务会重复执行，直到达到最大重试次数后失败后，执行failed方法
        $job->delete();
        Log::write("删除该任务");
    }
    public function failed()
    {
        Log::write("fail++");
        // ...任务达到最大重试次数后，失败了
    }
}