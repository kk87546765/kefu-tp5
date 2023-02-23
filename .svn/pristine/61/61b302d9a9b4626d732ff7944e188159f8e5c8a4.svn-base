<?php
/**
 * Created by PhpStorm.
 * User: crosstime
 * Date: 2017/2/28
 * Time: 下午2:16
 */
namespace traits\controller;

use common\server\SysServer;

trait lxxScript
{
    /**
     * 需调用函数配置
     * @var array [
     *  'func'=>'需调用函数', 必须
     *  'param'=>默认参数, 默认 ''
     *  'delay_time'=>当天多少秒后才执行, 默认 0
     *  'runtime'=>停止执行秒数, 默认 0
     *  'limit'=>一天执行次数 默认 0
     *  'is_single'=>是否单一执行方法 默认 0
     * ]
     * @example ['func'=>'需调用函数','param'=>[0=>''],'delay_time'=>0,'runtime'=>120,'limit'=>0] 每2分钟执行一次
     * ['func'=>'需调用函数','param'=>[0=>'aaa'],'delay_time'=>60*5,'runtime'=>3600,'limit'=>3] 需调用函数(aaa) 凌晨0分后开始、每小时执行一次、当天共执行3次
     */
    protected $func_arr = [];
    protected $cache_pre = [];//缓存前缀，一般是控制器名称

    /**
     * 1 初始化
     * @param string $name
     */
    public function initFuncConfig($name){
        $this->cache_pre = 'script_'.$name;
    }

    /**
     * 2 公共调用
     */
    protected function apiRun(){

        foreach ($this->func_arr as $v){
            $this->apiRunOne($v);
        }

        $res = $this->end();
    }
    public function apiRunOne($v){
        $this->initFuncConfigDefault($v);
        $res = $this->apiBase($v);
    }
    /**
     * 清除函数锁、分页缓存
     * @param $name 函数名
     */
    protected function apiClean($name){

        foreach ($this->func_arr as $v){
            $arr[] = $v['func'];
        }

        if(!in_array($name,$arr)){
            if($name != 'all'){
                dd('no');
            }
        }

        if($name == 'all'){
            foreach ($arr as $v){
                $this->apiClean($v);
            }
        }else{
            $cache_pre = $this->cache_pre;
            $cache_name = $cache_pre.$name;
            $lock_cache_name = $cache_name.'Lock';
            $common_lock_cache_name =  $cache_pre.'Lock';

            cache($lock_cache_name,null);//如果执行完解除锁定
            cache($common_lock_cache_name,null);//如果执行完解除锁定
        }
    }

    protected function apiCheckFuncList(){

        $func_list = $this->func_arr;

        $common_lock_cache_name = $this->cache_pre.'Lock';

        $common_lock = cache($common_lock_cache_name);

        $res = [];
        $lock_str = '<span style="color:#f00">锁定</span>';
        $no_lock_str = '<span style="color:#0f0">空闲</span>';
        $res[] = '公共锁：'.($common_lock?$lock_str:$no_lock_str);
        foreach ($func_list as $v){
            $func = $v['func'];
            $cache_name = $this->cache_pre.$func;
            $lock_cache_name = $cache_name.'Lock';
            $limit_cache_name = $cache_name.'Limit';

            $this_lock = cache($lock_cache_name);
            $this_limit = cache($limit_cache_name);

            $pre_num = $this->checkFuncConfig($v);

            $str = $func.'--锁：'.($this_lock?$lock_str:$no_lock_str).' 预期执行次数：<span style="color:#00f">'.$pre_num.'</span> 执行次数：';
            if($this_limit){
                if($this_limit<$pre_num){
                    $str .= '<span style="color:#f00">'.$this_limit.'</span>';
                }else{
                    $str .= $this_limit;
                }
            }else{
                $str .= $this_limit;
            }


            $res[] = $str;


        }
        return $res;
    }

    protected function checkFuncConfig($func_config){
        $delay_time = getArrVal($func_config,'delay_time',0);
        $runtime = getArrVal($func_config,'runtime',0);
        $limit = getArrVal($func_config,'limit',0);

        $res = 0;

        $now_num = time()-strtotime('today');
        if(!$now_num){
            return $res;
        }
        if(!$runtime){
            if($now_num >=60) $res = 1;
            return $res;
        }

        $fact_num = floor($now_num/$runtime);

        if($limit){
            if($fact_num < $limit){
                $res = $fact_num;
            }else{
                $res = $limit;
            }
        }else{
            $res = $fact_num;
        }

        return $res;
    }

    protected function initFuncConfigDefault(&$info){

        $arr = [
            'delay_time'=>0,
            'runtime'=>0,
            'limit'=>0,
            'is_single'=>0,
        ];

        foreach ($arr as $k => $v){
            $info[$k] = getArrVal($info,$k,$v);
        }
    }

    protected function apiBase($func_info){

        $today = strtotime('today');

        if(time()-$today < $func_info['delay_time']){
            return false;
        }

        $func = $func_info['func'];
        $runtime = $func_info['runtime'];
        $todayRemainingTime = getTodayRemainingTime();
        if($runtime){
            $runtime = $runtime>$todayRemainingTime?$todayRemainingTime:$runtime;
        }else{
            $runtime = getTodayRemainingTime();
        }
        $limit = $func_info['limit']?$func_info['limit']:0;


        $cache_pre = $this->cache_pre;

        $cache_name = $cache_pre.$func;

        $common_lock_cache_name = $cache_pre.'Lock';
        $lock_cache_name = $cache_name.'Lock';
        $limit_cache_name = $cache_name.'Limit';


        $lock_flag = cache($lock_cache_name);
        $common_lock_flag = cache($common_lock_cache_name);
        if($func_info['is_single']){
            $common_lock_flag = 0;
        }

        if($lock_flag || $common_lock_flag){
            if(in_array($func,[
//                'ascription',
//                'washed_away',
//                'month',
//                'history',
            ])){

            }else{
                return false;
            }
        }

        $this_func_limit = cache($limit_cache_name);
        $this_func_limit = $this_func_limit?$this_func_limit:0;

        if($limit && $this_func_limit >= $limit){
            return false;
        }
        $this_func_limit++;
        /*判断 end*/

        /*业务前logic*/
        if(!$func_info['is_single']){
            cache($common_lock_cache_name,1,getTodayRemainingTime());//公共锁
        }
        cache($lock_cache_name,1,$runtime);//方法锁
        cache($limit_cache_name,$this_func_limit,getTodayRemainingTime());//记录方法执行次数

        /*业务 begin*/
        $log_data = [];
        $log_data['controller'] = $this->cache_pre;
        $log_data['action'] = $func;
        $log_data['input_param'] = $func_info['param'];
        $log_id = SysServer::setRunLog($log_data);
        $func_end = $this->$func($func_info['param']);
        /**
         * func_end
         * [
         *      code
         *      data
         * ]
         */
        if($log_id){
            SysServer::setRunLog(['id'=>$log_id,'out_param'=>$func_end['data']]);
        }
//        dd($func_info,0);
//        dd($func_end,0);
        /*业务 end*/
        /*业务后logic*/
        if($func_end['code']){
            $res = cache($lock_cache_name,2,$runtime);//全部方法执行完锁定runtime
        }else{
            $res = cache($lock_cache_name,null);//如果方法未执行完解除锁定
        }

        if(!$func_info['is_single']){
            cache($common_lock_cache_name,null);//释放公共锁
        }


        $this->s_json('success '.$func);
    }

    protected function end(){
        $this->f_json('end');
    }

}
