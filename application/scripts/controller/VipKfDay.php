<?php
namespace app\scripts\controller;

use common\model\db_customer\QcConfig;
use common\model\db_statistic\EveryDayOrderCount;
use common\model\db_statistic\PlatformGameInfo;
use common\model\db_statistic\VipKfDayStatistic as thisModel;
use common\model\db_statistic\VipUserInfo;
use common\server\SysServer;

class VipKfDay extends Base
{
    const CACHE_PRE = 'script_vip_kf_day_';

    const FUNC_ARR = [
        'ascription',#当天：新增分配、新增分配活跃
        'washed_away',#当天-历史用户：新增充值流失
        'login_lost',#当天-历史用户：新增登录流失
        'month',#月-整合：统计
        'history',#历史：累计
    ];

    const ACTIVE_LIMIT = 1000;

    const TIME_WASHED_AWAY = 30;
    const TIME_LOGIN_LOST = 7;

    public $today = 0;
    public $is_get = 0;

    /*
    * run 缩减脚本命令
    * 函数约定
    * 1.未执行/执行失败 return
    * 2.执行成功 die
    */
    public function run(){
        //参数获取
        $add_day = getArrVal($_GET,'add_day',0);
        if(isset($_SERVER)){
            $get = getArrVal($_SERVER,'REQUEST_METHOD','');
            $this->is_get = $get=='GET'?1:0;
        }

        $allow_func = getArrVal($_GET,'allow_func','');

        if($allow_func){
            $allow_func = explode(',',$allow_func);
        }
        if(!$allow_func){
            $allow_func = self::FUNC_ARR;
        }


        $today_cache_name = 'script_vip_kf_day_today';//脚本执行时间

        if($add_day == 1){
            $this->today = cache($today_cache_name);
        }else{
            $this->today = strtotime('yesterday');
        }

        $func_list = arrMID(self::FUNC_ARR,$allow_func);

        foreach ($func_list['ai_com'] as $v){
            $this->base($v);
        }

        if($add_day
            && $this->today< strtotime('yesterday')
        ){
            $this->cleanCache('all');
            $this->today+=3600*24;
            cache($today_cache_name,$this->today,3600*24*3);
            $this->s_json('ok:'.date('Y-m-d',$this->today));
        }

        $this->end();
    }

    /**
     * 设置脚本时间-没有默认当天
     * @param day Y-m-d
     */
    public function set_time(){
        $time = $_GET['day'];


        cache('script_vip_kf_day_today',strtotime($time));

        $this->s_json();

    }

    /**
     * 清空所有缓存控制
     * @param func all,,,
     */
    public function clean(){

        $name = $this->request->get('func/s','all');
        if(isset($_SERVER)){
            $get = getArrVal($_SERVER,'REQUEST_METHOD','');
            $this->is_get = $get=='GET'?1:0;
        }

        $this->cleanCache($name);
        $this->s_json($name);
    }

    protected function base($func){

        $cache_name = self::CACHE_PRE.$this->is_get.$func;

        $common_lock_cache_name = self::CACHE_PRE.$this->is_get.'Lock';
        $lock_cache_name = $cache_name.'Lock';
        $page_cache_name = $cache_name.'page';

        $lock_flag = cache($lock_cache_name);
        $common_lock_flag = cache($common_lock_cache_name);

        if($lock_flag || $common_lock_flag){
            if(in_array($func,[
//                'ascription',
//                'washed_away',
//                'month',
//                'history',
//                'login_lost',
            ])){

            }else{
                return false;
            }
            // dd('lock_flag');
        }
        /*判断 end*/

        /*业务前logic*/
        cache($lock_cache_name,1,300);//业务开始默认锁定5分钟不执行
        cache($common_lock_cache_name,1,300);//业务开始默认锁定5分钟不执行

        $page = cache($page_cache_name);
        $page = $page?$page:1;

        /*业务 begin*/
        $func_end = $this->$func($page);
        /*业务 end*/

        /*业务后logic*/
        if($func_end){
            cache($lock_cache_name,2,3600*2);//全部执行停止2小时
            cache($page_cache_name,null);//清除分页缓存
        }else{
            cache($lock_cache_name,null);//如果执行完解除锁定
            $page++;
            cache($page_cache_name,$page,600);//执行成功保存分页
        }
        cache($common_lock_cache_name,null);//释放公共锁

        $this->s_json('success '.$func);
    }

    #分配用户、分配活跃用户
    protected function ascription($page){

        $thisModel = new thisModel();
        $VipUserInfo = new VipUserInfo();
        $PlatformGameInfo = new PlatformGameInfo();
        $group_list = SysServer::getAdminGroupListShow(QcConfig::USER_GROUP_VIP);//分组列表
        $group_id_arr = [];

        foreach ($group_list as $v){
            $group_id_arr[] = $v['id'];
        }

        $admin_list = SysServer::getAdminListCache();
        foreach ($admin_list as $k=>$v){
            $this_info = arrMID(explode(',',$v['group_id']),$group_id_arr);

            if($this_info['ai_com']){
                $admin_list[$k]['group_id'] = array_shift($this_info['ai_com']);
            }
        }

        /*业务前logic*/

        /*业务 begin*/
        $time_arr = timeCondition('day',$this->today);//yesterday

        $day = $time_arr['starttime'];

        $where = [];
        $where['first_distribute_time'] = [['>=',$day],['<=',$time_arr['endtime']]];
        $where['ascription_vip'] = ['>',0];

        $field = '
            platform_id
            ,uid
            ,first_distribute_time
            ,ascription_vip as admin_id
            ,last_pay_game_id as game_id
        ';
        $user_list = $VipUserInfo->field($field)->where($where)->select()->toArray();

        if($user_list){
            $new_user_list = [];
            foreach ($user_list as $v){

                $this_p_a_g = $v['platform_id'].'_'.$v['admin_id'].'_'.$v['game_id'];
                if(!isset($new_user_list[$this_p_a_g])){

                    $where = getDataByField($v,['platform_id','game_id']);

                    $this_info = $PlatformGameInfo->where($where)->find();

                    if(!$this_info){
                        continue;
                    }

                    $new_user_list[$this_p_a_g] = getDataByField($v,['platform_id','admin_id','game_id']);

                    $new_user_list[$this_p_a_g]['product_id'] = $this_info->product_id;
                    $this_info = getArrVal($admin_list,$v['admin_id'],[]);
                    $new_user_list[$this_p_a_g]['group_id'] = $this_info?$this_info['group_id']:0;
                }
                $new_user_list[$this_p_a_g]['uid'][] = $v['uid'];
            }
            $add_list = [];

            foreach ($new_user_list as $v){
                $where = getDataByField($v,['platform_id','game_id','admin_id']);
                $where['day'] = $day;

                $this_info = $thisModel->where($where)->find();

                if($this_info){
                    $update_data = [];
                    $update_data['add_user_count'] = count($v['uid']);
                    $update_data['add_user_str'] = implode(',',$v['uid']);
                    $update_data['update_time'] = time();

                    $this_info->save($update_data);
                }else{

                    $add_data = getDataByField($v,['platform_id','game_id','admin_id','group_id','product_id']);
                    $add_data['day'] = $day;
                    $add_data['p_p'] = $add_data['platform_id'].'_'.$add_data['product_id'];
                    $add_data['p_g'] = $add_data['platform_id'].'_'.$add_data['game_id'];
                    $add_data['add_user_count'] = count($v['uid']);
                    $add_data['add_user_str'] = implode(',',$v['uid']);

                    $add_list[] = $add_data;
                }
            }

            if($add_list){
                $thisModel->saveAll($add_list);
            }
            $func_end = 1;
        }else{
            $func_end = 1;
        }

        return $func_end;
    }

    #当天、新增充值流失
    protected function washed_away($page){

        $thisModel = new thisModel();
        $VipUserInfo = new VipUserInfo();
        $PlatformGameInfo = new PlatformGameInfo();
        $group_list = SysServer::getAdminGroupListShow(QcConfig::USER_GROUP_VIP);//分组列表
        $group_id_arr = [];

        foreach ($group_list as $v){
            $group_id_arr[] = $v['id'];
        }

        $admin_list = SysServer::getAdminListCache();
        foreach ($admin_list as $k=>$v){
            $this_info = arrMID(explode(',',$v['group_id']),$group_id_arr);

            if($this_info['ai_com']){
                $admin_list[$k]['group_id'] = array_shift($this_info['ai_com']);
            }
        }

        /*业务前logic*/

        /*业务 begin*/
        $time_arr = timeCondition('day',$this->today);//yesterday

        $day = $time_arr['starttime'];

        $where = [];
        $where['last_pay_time'] = [['>=',$day-3600*24*self::TIME_WASHED_AWAY],['<=',$time_arr['endtime']-3600*24*self::TIME_WASHED_AWAY]];
        $where['ascription_vip'] = ['>',0];

        $field = '
            platform_id
            ,uid
            ,first_distribute_time
            ,ascription_vip as admin_id
            ,game_id as game_id
        ';
        $user_list = $VipUserInfo->field($field)->where($where)->select()->toArray();

        if($user_list){
            $new_user_list = [];
            foreach ($user_list as $v){

                $this_p_a_g = $v['platform_id'].'_'.$v['admin_id'].'_'.$v['game_id'];
                if(!isset($new_user_list[$this_p_a_g])){

                    $where = getDataByField($v,['platform_id','game_id']);

                    $this_info = $PlatformGameInfo->where($where)->find();

                    if(!$this_info){
                        continue;
                    }

                    $new_user_list[$this_p_a_g] = getDataByField($v,['platform_id','admin_id','game_id']);

                    $new_user_list[$this_p_a_g]['product_id'] = $this_info->product_id;
                    $this_info = getArrVal($admin_list,$v['admin_id'],[]);
                    $new_user_list[$this_p_a_g]['group_id'] = $this_info?$this_info['group_id']:0;
                }
                $new_user_list[$this_p_a_g]['uid'][] = $v['uid'];
            }
            $add_list = [];

            foreach ($new_user_list as $v){
                $where = getDataByField($v,['platform_id','game_id','admin_id']);
                $where['day'] = $day;

                $this_info = $thisModel->where($where)->find();

                if($this_info){

                    $update_data = [];
                    $update_data['add_washed_away'] = count($v['uid']);
                    $update_data['add_washed_away_str'] = implode(',',$v['uid']);
                    $update_data['update_time'] = time();

                    $this_info->save($update_data);

                }else{

                    $add_data = getDataByField($v,['platform_id','game_id','admin_id','group_id','product_id']);
                    $add_data['day'] = $day;
                    $add_data['p_p'] = $add_data['platform_id'].'_'.$add_data['product_id'];
                    $add_data['p_g'] = $add_data['platform_id'].'_'.$add_data['game_id'];
                    $add_data['add_washed_away'] = count($v['uid']);
                    $add_data['add_washed_away_str'] = implode(',',$v['uid']);
                    $add_list[] = $add_data;
                }
            }
            if($add_list){
                $thisModel->saveAll($add_list);
            }
            $func_end = 1;
        }else{
            $func_end = 1;
        }

        return $func_end;
    }

    #当天、新增充值流失
    protected function login_lost($page){

        $thisModel = new thisModel();
        $VipUserInfo = new VipUserInfo();
        $PlatformGameInfo = new PlatformGameInfo();
        $group_list = SysServer::getAdminGroupListShow(QcConfig::USER_GROUP_VIP);//分组列表
        $group_id_arr = [];

        foreach ($group_list as $v){
            $group_id_arr[] = $v['id'];
        }

        $admin_list = SysServer::getAdminListCache();
        foreach ($admin_list as $k=>$v){
            $this_info = arrMID(explode(',',$v['group_id']),$group_id_arr);

            if($this_info['ai_com']){
                $admin_list[$k]['group_id'] = array_shift($this_info['ai_com']);
            }
        }

        /*业务前logic*/

        /*业务 begin*/
        $time_arr = timeCondition('day',$this->today);//yesterday

        $day = $time_arr['starttime'];

        $where = [];
        $where['last_login_time'] = [['>=',$day-3600*24*self::TIME_LOGIN_LOST],['<=',$time_arr['endtime']-3600*24*self::TIME_LOGIN_LOST]];
        $where['ascription_vip'] = ['>',0];

        $field = '
            platform_id
            ,uid
            ,ascription_vip as admin_id
            ,game_id as game_id
        ';
        $user_list = $VipUserInfo->field($field)->where($where)->select()->toArray();

        if($user_list){
            $new_user_list = [];
            foreach ($user_list as $v){

                $this_p_a_g = $v['platform_id'].'_'.$v['admin_id'].'_'.$v['game_id'];
                if(!isset($new_user_list[$this_p_a_g])){

                    $where = getDataByField($v,['platform_id','game_id']);

                    $this_info = $PlatformGameInfo->where($where)->find();

                    if(!$this_info){
                        continue;
                    }

                    $new_user_list[$this_p_a_g] = getDataByField($v,['platform_id','admin_id','game_id']);

                    $new_user_list[$this_p_a_g]['product_id'] = $this_info->product_id;
                    $this_info = getArrVal($admin_list,$v['admin_id'],[]);
                    $new_user_list[$this_p_a_g]['group_id'] = $this_info?$this_info['group_id']:0;
                }
                $new_user_list[$this_p_a_g]['uid'][] = $v['uid'];
            }
            $add_list = [];

            foreach ($new_user_list as $v){
                $where = getDataByField($v,['platform_id','game_id','admin_id']);
                $where['day'] = $day;

                $this_info = $thisModel->where($where)->find();

                if($this_info){

                    $update_data = [];
                    $update_data['add_login_lost_count'] = count($v['uid']);
                    $update_data['add_login_lost_str'] = implode(',',$v['uid']);
                    $update_data['update_time'] = time();

                    $res = $this_info->save($update_data);

                }else{

                    $add_data = getDataByField($v,['platform_id','game_id','admin_id','group_id','product_id']);
                    $add_data['day'] = $day;
                    $add_data['p_p'] = $add_data['platform_id'].'_'.$add_data['product_id'];
                    $add_data['p_g'] = $add_data['platform_id'].'_'.$add_data['game_id'];
                    $add_data['add_login_lost_count'] = count($v['uid']);
                    $add_data['add_login_lost_str'] = implode(',',$v['uid']);
                    $add_list[] = $add_data;
                }
            }
//dd($add_list);
            if($add_list){
                $thisModel->saveAll($add_list);
            }
            $func_end = 1;
        }else{
            $func_end = 1;
        }

        return $func_end;
    }

    #当月分配
    protected function month($page){

        $thisModel = new thisModel();
        $EveryDayOrderCount = new EveryDayOrderCount();

        /*判断 end*/

        /*业务前logic*/

        $func_end = 0;
        $limit = 100;

        /*业务 begin*/
        $time_arr = timeCondition('day',$this->today);//yesterday
        $day = $time_arr['starttime'];
        $time_arr2 = timeCondition('month',$day);

        $where = [];
        $where['day'] = $day;

        $list = $thisModel->where($where)->page($page,$limit)->select();

        if($list->toArray()){
            foreach ($list as $v){
                /*add_user_count_month
                 *add_washed_away_month
                 *add_login_lost_month
                 *add_user_active_month
                 *add_user_active_amount_month
                */
                $where = getDataByField($v->toArray(),['platform_id','admin_id','game_id']);
                $where['day'] = [['>=',$time_arr2['starttime']],['<',$day+3600*24]];

                $this_month_list = $thisModel->where($where)->select()->toArray();

                if(!$this_month_list){
                    continue;
                }
                $this_data = [
                    'add_user_count_month'=>0,//当月累计新增分配
                    'add_user_str_month'=>[],//当月累计新增分配
                    'add_washed_away_month'=>0,//当月新增流失
                    'add_login_lost_month'=>0,//当月新增流失
                    'add_user_active_month'=>0,//当月、已分配、活跃用户
                    'add_user_active_str_month'=>[],//当月、已分配、活跃用户
                    'add_user_active_amount_month'=>0,//当月、已分配、活跃用户当月累计充值
                ];
                $this_month_add_user_arr = [];//
                $this_month_add_washed_away_arr = [];
                $this_month_add_login_lost_arr = [];

                foreach ($this_month_list as $v1){
                    if($v1['add_user_str']){
                        $this_data['add_user_str_month'] = array_merge($this_data['add_user_str_month'],explode(',',$v1['add_user_str']));
                        $this_str = str_replace(',',",$v1[platform_id]_",$v1['add_user_str']);
                        $this_str = "$v1[platform_id]_".$this_str;
                        $this_month_add_user_arr = array_merge($this_month_add_user_arr,explode(',',$this_str));
                    }
                    if($v1['add_washed_away_str']){
                        $this_str = str_replace(',',",$v1[platform_id]_",$v1['add_washed_away_str']);
                        $this_str = "$v1[platform_id]_".$this_str;
                        $this_month_add_washed_away_arr = array_merge($this_month_add_washed_away_arr,explode(',',$this_str));
                    }
                    if($v1['add_login_lost_str']){
                        $this_str = str_replace(',',",$v1[platform_id]_",$v1['add_login_lost_str']);
                        $this_str = "$v1[platform_id]_".$this_str;
                        $this_month_add_login_lost_arr = array_merge($this_month_add_login_lost_arr,explode(',',$this_str));
                    }
                }

                if($this_month_add_user_arr){
                    $this_data['add_user_count_month'] = count($this_month_add_user_arr);
                    $where = [];
                    $where['pay_time'] = [['>=',$day-3600*24*self::TIME_WASHED_AWAY],['<',$day+3600*24]];
                    $where['p_u'] = ['in',$this_month_add_user_arr];
                    $this_info = $EveryDayOrderCount
                        ->field('platform_id,uid,sum(amount_count) as amount_count')
                        ->where($where)
                        ->group('p_u')
                        ->select()->toArray();

                    if($this_info){

                        foreach ($this_info as $ti_v){

                            if($ti_v['amount_count']>=self::ACTIVE_LIMIT){
                                $this_data['add_user_active_month']++;
                                $this_data['add_user_active_str_month'][] = $ti_v['uid'];
                            }
                            $this_data['add_user_active_amount_month']+=$ti_v['amount_count'];
                        }
                    }
                }
                if($this_month_add_washed_away_arr){
                    $this_data['add_washed_away_month'] = count($this_month_add_washed_away_arr);
                }
                if($this_month_add_login_lost_arr){
                    $this_data['add_login_lost_month'] = count($this_month_add_login_lost_arr);
                }
                $this_data['add_user_active_str_month'] = implode(',',$this_data['add_user_active_str_month']);
                $this_data['add_user_str_month'] = implode(',',$this_data['add_user_str_month']);

                $v->save($this_data);

            }
        }else{
            $func_end = 1;
        }



        return $func_end;
    }

    #历史分配用户统计
    protected function history($page){

        $thisModel = new thisModel();
        $VipUserInfo = new VipUserInfo();
        $PlatformGameInfo = new PlatformGameInfo();
        $group_list = SysServer::getAdminGroupListShow(QcConfig::USER_GROUP_VIP);//分组列表
        $group_id_arr = [];

        foreach ($group_list as $v){
            $group_id_arr[] = $v['id'];
        }

        $admin_list = SysServer::getAdminListCache();
        foreach ($admin_list as $k=>$v){
            $this_info = arrMID(explode(',',$v['group_id']),$group_id_arr);

            if($this_info['ai_com']){
                $admin_list[$k]['group_id'] = array_shift($this_info['ai_com']);
            }
        }


        /*业务前logic*/
        $func_end = 0;
        $limit = 20;


        /*业务 begin*/
        $time_arr = timeCondition('day',$this->today);//yesterday

        $day = $time_arr['starttime'];

        $where = [];
        //最后分配时间
        $where['first_distribute_time']=['<=',$day+3600*24];

        //已分配
        $where['ascription_vip']=['>',0];


        $field = "platform_id,last_pay_game_id as game_id,ascription_vip as admin_id";
        $field .=",count(id) as add_user_count_all";
        $field .=",SUM(CASE WHEN thirty_day_pay>=".self::ACTIVE_LIMIT." THEN 1 ELSE 0 END) as active_count_all";
        $field .=",SUM(CASE WHEN last_pay_time<".($day-3600*24*self::TIME_WASHED_AWAY)." THEN 1 ELSE 0 END) as washed_away_all";
        $field .=",SUM(CASE WHEN last_login_time<".($day-3600*24*self::TIME_LOGIN_LOST)." THEN 1 ELSE 0 END) as login_lost_all";
        $field .=",SUM(CASE WHEN last_record_time > 0 THEN 1 ELSE 0 END) as maintain_user_all";

        $user_list = $VipUserInfo
            ->field($field)//,count(id) as c
            ->where($where)
            ->page($page,$limit)
            ->group('platform_id,last_pay_game_id,ascription_vip')
            ->select()->toArray();

        /*active_count_all
        add_user_count_all
        washed_away_all
        login_lost_all
        */

        $new_user_list = [];
        if($user_list){
            foreach ($user_list as $v){
                $this_p_a_g = $v['platform_id'].'_'.$v['admin_id'].'_'.$v['game_id'];
                if(!isset($new_user_list[$this_p_a_g])){

                    $where = getDataByField($v,['platform_id','game_id']);

                    $this_info = $PlatformGameInfo->where($where)->find();

                    if(!$this_info){
                        continue;
                    }

                    $new_user_list[$this_p_a_g] = getDataByField($v,['platform_id','admin_id','game_id','active_count_all','add_user_count_all','washed_away_all','login_lost_all','maintain_user_all']);
                    $new_user_list[$this_p_a_g]['product_id'] = $this_info->product_id;
                    $this_info = getArrVal($admin_list,$v['admin_id'],[]);
                    $new_user_list[$this_p_a_g]['group_id'] = $this_info?$this_info['group_id']:0;
                    $new_user_list[$this_p_a_g]['active_user_str_all'] = '';
                    if($v['active_count_all']>0){
                        $where = [];
                        $where['ascription_vip'] = $v['admin_id'];
                        $where['last_pay_game_id'] = $v['game_id'];
                        $where['platform_id'] = $v['platform_id'];
                        $where['thirty_day_pay'] = ['>=',self::ACTIVE_LIMIT];
                        $this_info = $VipUserInfo->field('platform_id,uid')->where($where)->select()->toArray();
                        if($this_info){
                            $this_info_new = [];
                            foreach ($this_info as $ti_v) {
                                $this_info_new[] = $ti_v['platform_id'].'_'.$ti_v['uid'];
                            }
                            $new_user_list[$this_p_a_g]['active_user_str_all'] = implode(',',$this_info_new);
                        }

                    }
                }
            }

            $add_list = [];
            foreach ($new_user_list as $k => $v){

                $where = getDataByField($v,['platform_id','admin_id','game_id']);
                $where['day'] = $day;
                $this_info = $thisModel->where($where)->find();

                if($this_info){

                    $update_data = getDataByField($v,['active_count_all','add_user_count_all','washed_away_all','active_user_str_all','login_lost_all','maintain_user_all']);
                    $update_data['update_time'] = time();
                    $this_info->save($update_data);
                }else{
                    $add_data = getDataByField($v,['platform_id','game_id','admin_id','group_id','product_id','active_count_all','add_user_count_all','washed_away_all','active_user_str_all','login_lost_all','maintain_user_all']);
                    $add_data['p_p'] = $add_data['platform_id'].'_'.$add_data['product_id'];
                    $add_data['p_g'] = $add_data['platform_id'].'_'.$add_data['game_id'];
                    $add_data['day'] = $day;
                    $add_list[] = $add_data;
                }
            }

            if($add_list){
                $thisModel->saveAll($add_list);
            }

        }else{
            $func_end = 1;
        }
        /*业务 end*/

        return $func_end;
    }

    protected function cleanCache($name){

        $arr = self::FUNC_ARR;

        if(!in_array($name,$arr)){
            if($name != 'all'){
                dd('no');
            }

        }

        if($name == 'all'){
            foreach ($arr as $v){
                $this->cleanCache($v);
            }
        }else{
            $cache_name = self::CACHE_PRE.$this->is_get.$name;


            $lock_cache_name = $cache_name.'Lock';
            $page_cache_name = $cache_name.'page';
            $common_lock_cache_name =  self::CACHE_PRE.$this->is_get.'Lock';

            cache($lock_cache_name,null);//如果执行完解除锁定
            cache($page_cache_name,null);//如果执行完解除锁定
            cache($common_lock_cache_name,null);//如果执行完解除锁定
        }


    }

    protected function end(){
        $this->f_json('end',[],1001);
    }



}
