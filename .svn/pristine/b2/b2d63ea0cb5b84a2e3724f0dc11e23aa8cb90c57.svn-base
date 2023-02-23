<?php
namespace app\scripts\controller;

use common\model\db_statistic\KefuUserRecharge;
use common\model\db_statistic\VipUserInfo;
use common\server\CustomerPlatform\CommonServer;
use common\server\Vip\LossUserServer;
use common\server\Vip\VipUserInfoServer;

class UserPayCount extends Base
{
    protected $func_arr = [
        ['func'=>'insertIntoUser','param'=>[0=>''],'delay_time'=>60*10,'runtime'=>3600*8,'limit'=>1],#添加注册用户
        ['func'=>'updateUserRechargeLastLoginTime','param'=>[0=>''],'delay_time'=>60*20,'runtime'=>0,'limit'=>1],#更新用户的最后登录时间

        ['func'=>'updateEveryDayOrderCount','param'=>[0=>''],'delay_time'=>60*30,'runtime'=>3600*8,'limit'=>1],#更新用户每天充值相关信息
        ['func'=>'updateUserPayInfo','param'=>[0=>''],'delay_time'=>60*35,'runtime'=>3600*8,'limit'=>1],#更新每天用户总充、累充数据
        ['func'=>'checkUserRechargeOverTime','param'=>[0=>''],'delay_time'=>60*40,'runtime'=>3600,'limit'=>1],#更新30天没有充值用户累充数据
        ['func'=>'checkUserRechargeOverTimeMonthPay','param'=>[0=>''],'delay_time'=>60*50,'runtime'=>3600,'limit'=>1],#更新当月没有充值用户月累充数据
        ['func'=>'updateUserRemarkPay','param'=>[0=>''],'delay_time'=>60*40,'runtime'=>3600*4,'limit'=>2],#更新用户维护充值数 VipUserInfo

        ['func'=>'becomeVip','param'=>[0=>'is_today=1'],'delay_time'=>60*60,'runtime'=>0,'limit'=>1],#更新达成vip用户数据
        ['func'=>'createVipUser','param'=>[0=>''],'delay_time'=>60*61,'runtime'=>0,'limit'=>1],#创建vip用户

        ['func'=>'vipAscriptionDistributionNew','param'=>[0=>''],'delay_time'=>60*63,'runtime'=>3600,'limit'=>1],#分配vip用户
        ['func'=>'todayOrderCount','param'=>[0=>''],'delay_time'=>3600*3,'runtime'=>60*10,'limit'=>0],#更新用户每天充值相关信息

        ['func'=>'insertIntoUser','param'=>[0=>'start_time=today&end_time=tomorrow'],'delay_time'=>3600*9,'runtime'=>3600*8,'limit'=>2],#添加注册用户
        ['func'=>'updateEveryDayOrderCount','param'=>[0=>'start_time=today'],'delay_time'=>3600*9,'runtime'=>3600*8,'limit'=>2],#更新用户每天充值相关信息
        ['func'=>'updateUserPayInfo','param'=>[0=>'start_time=today'],'delay_time'=>3600*9,'runtime'=>3600*8,'limit'=>2],#更新每天用户总充、累充数据
        ['func'=>'checkUserBaseInfo','param'=>[0=>''],'delay_time'=>3600*9,'runtime'=>0,'limit'=>1],#自检用户信息
        ['func'=>'becomeVip2','param'=>[0=>'is_today=1&start_time=today'],'delay_time'=>3600*9,'runtime'=>3600*8,'limit'=>1],#更新达成vip用户数据
        ['func'=>'createVipUser2','param'=>[0=>''],'delay_time'=>3600*9,'runtime'=>3600*8,'limit'=>1],#创建vip用户
        ['func'=>'vipAscriptionDistributionNew','param'=>[0=>''],'delay_time'=>3600*9,'runtime'=>0,'limit'=>2],#分配vip用户

        ['func'=>'updateUserRechargeThirtyDayPay','param'=>[0=>''],'delay_time'=>60*25,'runtime'=>0,'limit'=>1,'is_single'=>1],#分配vip用户

        ['func'=>'updateLossUserInfo','param'=>[0=>''],'delay_time'=>60*65,'runtime'=>0,'limit'=>1],#流失用户数据更新

        ['func'=>'updateVipRoleLevel','param'=>[0=>'date=yesterday'],'delay_time'=>60*68,'runtime'=>3600,'limit'=>1],#更新vip角色信息

        ['func'=>'updateVipRoleLevel','param'=>[0=>'date=today'],'delay_time'=>3600*8,'runtime'=>3600,'limit'=>2],#更新vip角色信息
    ];

    const INSERT_NUMBER = 2000;

    public function run()
    {
        $this->apiRun();
    }

    public function test(){
        echo '<pre>';
        $params = $this->request->param();

        $func = getArrVal($params,'func','');
        $param = getArrVal($params,'param','');
        print_r(compact('params','func','param'));
        if(!$func){
            echo 'no func';die;
        }
        print_r($this->$func([0=>$param]));
    }

    public function check()
    {
        echo '<pre>';
        print_r($this->apiCheckFuncList());
    }

    public function clean()
    {
        echo '<pre>';
        print_r($this->apiClean('all'));
    }

    /**
     * 插入各个平台用户数据。每天的凌晨开始跑昨天的数据
     * 1、传平台标识跑单个平台数据，没有轮询所有的平台
     * 2、可单传开始时间也可开始时间和结束时间都不传，不传开始时间就是昨天的凌晨时间节点、结束时间就是今天的凌晨的时间节点(日期时间格式)
     * 3、每2000条记录分批插入（性能考虑）
     * @param array $params
     */
    public function insertIntoUser(array $params)
    {
        ini_set('memory_limit', '2048M');
        $result = ['code'=>true, 'data'=>[]];
        if(isset($params[0])){
            $p_r = [];
            parse_str($params[0],$p_r);
            extract($p_r);
        }

        $end_time = isset($end_time) ? strtotime(date('Y-m-d', strtotime($end_time))) : strtotime(date("Y-m-d"));

        $start_time= isset($start_time) ? strtotime(date('Y-m-d', strtotime($start_time))) : strtotime(date("Y-m-d",strtotime("-1 day")));

        $platformSuffix = [];
        $userData = [];
        if (!empty($platform) && isset($this->config['platform_suffix'][$platform])){
            $platformSuffix[$platform] = $this->config['platform_suffix'][$platform];
        }else{
            //没有指定具体的平台数据，轮询获取
            $platformSuffix = $this->config['platform_suffix'];
        }

        if(!$platformSuffix){
            $result['data']['msg'] = 'no platform';
            return $result;
        }

        foreach ($platformSuffix as $k => $v){
            if(!$v){
                continue;
            }
            $model = CommonServer::getPlatformModel('KefuCommonMember',$k);
            $where = [];
            if (!empty((int)$start_time)) $where[] = ["reg_date", ">=",$start_time];
            if (!empty((int)$end_time)) $where[] = ["reg_date", "<",$end_time];
            $res = $model->where(setWhereSql($where,''))->select()->toArray();
            if (!empty($res)){
                $userData[$v] = $res;
            }
        }

        if(!$userData){
            $result['data']['msg'] = 'no user';
            return $result;
        }
        $data =[];
        foreach ($userData as $key=>$value){
            foreach ($value as $v){
                $tmp = [];
                $tmp['platform_id'] = $key;
                $tmp['uid']  = $v['uid'];
                $tmp['user_name'] = !empty($v['user_name']) ? addslashes($v['user_name']) : '';
                $tmp['mobile'] = !empty($v['mobile']) ? addslashes($v['mobile']) : '';
                $tmp['game_id'] = $v['reg_gid'];
                $tmp['reg_ip'] = $v['reg_ip'];
                $tmp['reg_time'] = $v['reg_date'];
                $tmp['add_time'] = time();
                $data[] = $tmp;
            }
        }
        unset($userData);
        $keFuUserRechargeModel = new KefuUserRecharge();
        //每2000条数据插入一次
        for ($i=0;count($data) >$i*self::INSERT_NUMBER;$i++)
        {
            $start = empty($i*self::INSERT_NUMBER) ? 0 : $i*self::INSERT_NUMBER;
            $insertData = array_slice($data, $start, self::INSERT_NUMBER);
            $res = $keFuUserRechargeModel->insertIgnoreInfo($insertData);
        }
        return $result;
    }

    /**
     * 更新用户的最后登录时间。每天的凌晨开始跑昨天的数据
     * 1、传平台标识跑单个平台数据，没有轮询所有的平台
     * 2、可单传开始时间（日期时间）也可开始时间和结束时间都不传，不传开始时间就是昨天的凌晨时间节点、结束时间就是今天的凌晨的时间节点
     * 3、每2000条记录分批插入（性能考虑）
     * @param array $params
     */
    public function updateUserRechargeLastLoginTime(array $params)
    {
        ini_set('memory_limit', '2048M');
        $result = ['code'=>true, 'data'=>[]];
        if(isset($params[0])){
            $p_r = [];
            parse_str($params[0],$p_r);
            extract($p_r);
        }

        $start_time= isset($start_time) ? strtotime(date('Y-m-d', strtotime($start_time))) : strtotime(date("Y-m-d",strtotime("-1 day")));

        $platformSuffix = [];
        if (!empty($platform) && isset($this->config['platform_suffix'][$platform])){
            $platformSuffix[$platform] = $this->config['platform_suffix'][$platform];
        }else{
            //没有指定具体的平台数据，轮询获取
            $platformSuffix = $this->config['platform_suffix'];
        }

        if(!$platformSuffix){
            $result['data']['msg'] = 'no platform';
            return $result;
        }

        foreach ($platformSuffix as $k => $v){
            if(!$v){
                continue;
            }
            $this->everyDayUserRechargeLastLoginTime($start_time, $k,$v);
        }

        return $result;
    }

    /**
     * @param $start_time
     * @param $platformSuffix
     * @param $platform_id
     */
    protected function everyDayUserRechargeLastLoginTime($start_time, $platformSuffix,$platform_id)
    {
        ini_set('memory_limit', '2048M');
        $updateByInsert = $existUserArr = [];
        $Models = CommonServer::getPlatformModel('KefuLoginLog',$platformSuffix);

        $start_time = strtotime(date('Y-m-d', $start_time));
        $sql = "select uid,MAX(login_date) as last_login_time,$platform_id as platform_id,concat($platform_id,'_',uid) as puid
                from kefu_login_log 
                where 1 and login_date >= $start_time 
                GROUP BY uid";
        $res = $Models->query($sql);

        $keFuUserRechargeModels = new KefuUserRecharge();
        $countUpdateInfo = array_column($res, 'puid');
        //每2000条数据插入一次
        for ($i=0;count($countUpdateInfo) >$i*self::INSERT_NUMBER;$i++)
        {
            $start = empty($i*self::INSERT_NUMBER) ? 0 : $i*self::INSERT_NUMBER;
            $tmpWhereArr = array_slice($countUpdateInfo, $start, self::INSERT_NUMBER);
            $tmp = $keFuUserRechargeModels->rechargeUserIfExist($tmpWhereArr);
            if (!empty($tmp)) $existUserArr = array_merge($existUserArr, $tmp);
        }
        unset($countUpdateInfo);
        $tmpRes = [];
        foreach ($res as $k=>$v) {
            $tmpRes[$v['platform_id'].'_'.$v['uid']] = $v;
        }
        unset($res);
        foreach ($existUserArr as $value) {
            $updateByInsert[] = $tmpRes[$value];
        }
        unset($tmpRes,$existUserArr);
        if (!empty($updateByInsert)) {
            for ($i=0;count($updateByInsert) >$i*self::INSERT_NUMBER;$i++)
            {
                $start = empty($i*self::INSERT_NUMBER) ? 0 : $i*self::INSERT_NUMBER;
                $tmpArr = array_slice($updateByInsert, $start, self::INSERT_NUMBER);
                $res = $keFuUserRechargeModels->updateUserRechargeLastLoginTime($tmpArr);
            }
        }

    }

    /**
     * 生成vip用户数据
     * @param array $params
     */
    public function updateEveryDayOrderCount($params)
    {
        ini_set('memory_limit', '2048M');

        $result = ['code'=>true, 'data'=>[]];
        if(isset($params[0])){
            $p_r = [];
            parse_str($params[0],$p_r);
            extract($p_r);
        }

        $end_time = isset($end_time) ? strtotime(date('Y-m-d', strtotime($end_time))) : strtotime(date("Y-m-d", time()));

        $start_time= isset($start_time) ? strtotime(date('Y-m-d', strtotime($start_time))) : strtotime(date("Y-m-d",strtotime("-1 day")));

        $platformSuffix = [];
        if (!empty($platform) && isset($this->config['platform_suffix'][$platform])){
            $platformSuffix[$platform] = $this->config['platform_suffix'][$platform];
        }else{
            //没有指定具体的平台数据，轮询获取
            $platformSuffix = $this->config['platform_suffix'];
        }

        if(!$platformSuffix){
            $result['data']['msg'] = 'no platform';
            return $result;
        }

        foreach ($platformSuffix as $k => $v){
            if(!$v){
                continue;
            }
            $tmp_time = $start_time;
            while ($tmp_time <= $end_time) {
                VipUserInfoServer::everyDayOrderCountNew($tmp_time, $k,$v);
                $tmp_time += 86400;
            }
        }

        return $result;
    }

    /**
     * 更新每天用户总充累充数据
     * @param array $params
     * @return int
     */
    public function updateUserPayInfo(array $params = []){
        ini_set('memory_limit', '2048M');

        $result = ['code'=>true, 'data'=>[]];

        if(isset($params[0])){
            $p_r = [];
            parse_str($params[0],$p_r);
            extract($p_r);
        }

        $end_time = isset($end_time) ? strtotime(date('Y-m-d', strtotime($end_time))) : strtotime(date("Y-m-d", time()));

        $start_time= isset($start_time) ? strtotime(date('Y-m-d', strtotime($start_time))) : strtotime(date("Y-m-d",strtotime("-1 day")));//,strtotime("-1 day")

        $platformSuffix = [];
        if (!empty($platform) && isset($this->config['platform_suffix'][$platform])){
            $platformSuffix[$platform] = $this->config['platform_suffix'][$platform];
        }else{
            //没有指定具体的平台数据，轮询获取
            $platformSuffix = $this->config['platform_suffix'];
        }

        if(!$platformSuffix){
            $result['data']['msg'] = 'no platform';
            return $result;
        }

        $res_data = [];

        foreach ($platformSuffix as $k => $v){
            if(!$v){
                continue;
            }
            $tmp_time = $start_time;
            while ($tmp_time < $end_time) {
                $res_data[$tmp_time]= VipUserInfoServer::updateUserPayInfo($tmp_time, ['platform_id'=>$v,'suffix'=>$k]);
                $tmp_time += 86400;
            }
        }
        $result['data'] = $res_data;

        return $result;
    }

    #更新30天没有充值用户累充数据
    public function checkUserRechargeOverTime($params){

        $result = ['code'=>true, 'data'=>[]];

        if(isset($params[0])){
            $p_r = [];
            parse_str($params[0],$p_r);
            extract($p_r);
        }

        $platformSuffix = [];
        if (!empty($platform) && isset($this->config['platform_suffix'][$platform])){
            $platformSuffix[$platform] = $this->config['platform_suffix'][$platform];
        }else{
            //没有指定具体的平台数据，轮询获取
            $platformSuffix = $this->config['platform_suffix'];
        }

        if(!$platformSuffix){
            $result['data']['msg'] = 'no platform';
            return $result;
        }

        foreach ($platformSuffix as $k=>$v){
            $res = VipUserInfoServer::updateUserRechargeOverTime(['platform'=>$k]);
            if($res['code'] == 0){
                $result['code'] = false;
                $result['data'][] = $res['msg'];
            }
        }

        return $result;
    }

    /**
     * 生成vip用户。每天的凌晨开始跑昨天的数据
     * 1、is_today是否是只跑一天的数据,默认：true 是，false 全部都跑
     * 2、可单传开始时间也可开始时间和结束时间都不传，不传开始时间就是昨天的凌晨时间节点、结束时间就是今天的凌晨的时间节点（时间为日期格式）
     * 3、每2000条记录分批插入（性能考虑）
     * @param array $params
     */
    public function becomeVip(array $params)
    {
        $tmpTime = time();
        $result = ['code'=>true, 'data'=>[]];

        if(isset($params[0])){
            $p_r = [];
            parse_str($params[0],$p_r);
            extract($p_r);
        }

        $is_today =  isset($is_today) ? $is_today : false;
        $start_time= isset($start_time) ? strtotime(date('Y-m-d', strtotime($start_time))) : 0;
        $end_time= isset($end_time) ? strtotime(date('Y-m-d', strtotime($end_time))) : 0;
        //找出今天活跃充值达标的用户在更新

        $model = new KefuUserRecharge();
        //处理没有配置的
        $model->updateNoGameConfigIsVipField($start_time,$end_time,$is_today);

        $result['data']['timeConsume'] = time()-$tmpTime;
        return $result;
    }

    public function becomeVip2(array $params){
        return $this->becomeVip($params);
    }

    /**
     * 生成vip用户数据
     * @param array $params
     */
    public function createVipUser(array $params)
    {
        ini_set('memory_limit', '2048M');
        $tmpTime = time();
        $result = ['code'=>true, 'data'=>[]];
        if(isset($params[0])){
            $p_r = [];
            parse_str($params[0],$p_r);
            extract($p_r);
        }
        $end_time = isset($end_time) ? strtotime(date('Y-m-d', strtotime($end_time))) : time();

        $start_time= isset($start_time) ? strtotime(date('Y-m-d', strtotime($start_time))) : strtotime(date("Y-m-d",strtotime("-1 day")));

        //找出vip用户数据
        $model = new KefuUserRecharge();

        $field = '
            platform_id
            ,uid
            ,user_name
            ,mobile
            ,game_id
            ,server_id
            ,server_name
            ,last_pay_game_id
            ,role_id
            ,role_name
            ,reg_ip
            ,reg_time
            ,day_hign_pay
            ,single_hign_pay
            ,total_pay
            ,last_day_pay
            ,thirty_day_pay
            ,last_pay_time
            ,total_time
            ,update_time
            ,vip_time
            ,last_login_time
            ,month_pay
        ';
        $where = [];
        $where['is_vip'] = 1;
        $where[] = ['total_time','>=',$start_time];
        $where[] = ['total_time','<',$end_time];
        $vipUserInfo = $model->field($field)->where(setWhereSql($where,''))->select()->toArray();

        //更新或生成vip用户数据
        if (!empty($vipUserInfo)) {
            $vipUserInfoModel = new VipUserInfo();
            for ($i=0;count($vipUserInfo) >$i*self::INSERT_NUMBER;$i++)
            {
                $start = empty($i*self::INSERT_NUMBER) ? 0 : $i*self::INSERT_NUMBER;
                $tmpArr = array_slice($vipUserInfo, $start, self::INSERT_NUMBER);
                $res = $vipUserInfoModel->insertOrUpdate($tmpArr);
            }
        }

        $result['data']['timeConsume'] = time()-$tmpTime;

        return $result;
    }

    public function createVipUser2(array $params){
        return $this->createVipUser($params);
    }

    /**
     * 分配vip用户的负责专员。每天的凌晨开始跑昨天的数据
     * 1、可单传开始时间也可开始时间和结束时间都不传，不传开始时间就是昨天的凌晨时间节点、结束时间就是今天的凌晨的时间节点（时间为日期格式）
     * @param array $params
     */
    public function vipAscriptionDistributionNew(array $params = [])
    {
        ini_set('memory_limit', '2048M');
        $tmpTime = time();
        $result = ['code'=>true, 'data'=>[]];
        VipUserInfoServer::vipAscriptionDistribution();
        $result['data']['timeConsume'] = time()-$tmpTime;
        return $result;
    }

    public function checkUserBaseInfo($params){
        ini_set('memory_limit', '2048M');

        $result = ['code'=>true, 'data'=>[]];

        if(isset($params[0])){
            $p_r = [];
            parse_str($params[0],$p_r);
            extract($p_r);
        }
        $limit = 100;
        $platformSuffix = [];
        if (!empty($platform) && isset($this->config['platform_suffix'][$platform])){
            $platformSuffix[$platform] = $this->config['platform_suffix'][$platform];
        }else{
            //没有指定具体的平台数据，轮询获取
            $platformSuffix = $this->config['platform_suffix'];
        }

        if(!$platformSuffix){
            $result['data']['msg'] = 'no platform';
            return $result;
        }

        foreach ($platformSuffix as $key=>$value){
            $res = VipUserInfoServer::updateUserBaseInfo(['limit'=>$limit,'platform'=>$key]);
            if($res['code'] == 0){
                $result['code'] = false;
                $result['data'][] = $res['msg'];
            }
        }

        return $result;
    }

    /**
     * 每十分钟统计当天的充值数据汇总
     * @param array $params
     */
    public function todayOrderCount(array $params)
    {
        ini_set('memory_limit', '2048M');

        $result = ['code'=>true, 'data'=>[]];

        if(isset($params[0])){
            $p_r = [];
            parse_str($params[0],$p_r);
            extract($p_r);
        }

        $tmpTime = time();

        $platformSuffix = [];
        if (!empty($platform) && isset($this->config['platform_suffix'][$platform])){
            $platformSuffix[$platform] = $this->config['platform_suffix'][$platform];
        }else{
            //没有指定具体的平台数据，轮询获取
            $platformSuffix = $this->config['platform_suffix'];
        }

        if(!$platformSuffix){
            $result['data']['msg'] = 'no platform';
            return $result;
        }

        $end_time= $tmpTime;

        $start_time = $end_time - 60*15; //往回十分钟的数据

        $userData = [];
        //没有指定具体的平台数据，轮询获取
        foreach ($platformSuffix as $k => $v){
            $res = VipUserInfoServer::todayOrderCount($start_time,$end_time,$k,$v);
            if (!empty($res)){
                $userData[$v] = $res;
            }
        }

        if (empty($userData)) {
            $result['data']['msg'] = 'fail';
        }else {
            $result['data']['data'] = $userData;
        }
        $result['data']['runTime'] = time() - $tmpTime;

        return $result;
    }
    /**
     * 减去统计时间的30天的充值金额。每天的凌晨开始跑昨天的数据
     * 1、开始时间（日期时间格式）可传可不传，不传开始时间就是昨天的凌晨时间节点，结束时间可传可不传，不传以今天凌晨的时间节点。已时间节点减去30天的时间
     * 2、每2000条记录分批插入（性能考虑）
     * @param array $params
     */
    public function updateUserRechargeThirtyDayPay(array $params)
    {
        ini_set('memory_limit', '2048M');
        $tmpTime = time();
        $result = ['code'=>1, 'data'=>[]];
        if(isset($params[0])){
            $p_r = [];
            parse_str($params[0],$p_r);
            extract($p_r);
        }
        $end_time = isset($end_time) ? strtotime($end_time) : strtotime(date("Y-m-d", time()));
        $start_time = isset($start_time) ? strtotime($start_time) :  strtotime(date('Y-m-d', time())." -1 day");
        if ($start_time >= $end_time) {
            $result = ['code'=>0, 'msg'=>'error time'];
            return $result;
        }

        for ($start_time; $start_time< $end_time; $start_time += 86400) {
            VipUserInfoServer::__updateUserRechargeThirtyDayPay($start_time);
        }

        $result['runTime'] = time() - $tmpTime;

        return $result;
    }

    public function checkUserRechargeOverTimeMonthPay($params){
        ini_set('memory_limit', '2048M');

        if(isset($params[0])){
            $p_r = [];
            parse_str($params[0],$p_r);
            extract($p_r);
        }

        $result = ['code'=>1, 'data'=>[]];

        $platformSuffix = [];
        if (!empty($platform) && isset($this->config['platform_suffix'][$platform])){
            $platformSuffix[$platform] = $this->config['platform_suffix'][$platform];
        }else{
            //没有指定具体的平台数据，轮询获取
            $platformSuffix = $this->config['platform_suffix'];
        }

        if(!$platformSuffix){
            $result['data']['msg'] = 'no platform';
            return $result;
        }

        foreach ($platformSuffix as $key=>$value){
            $res = VipUserInfoServer::updateUserRechargeOverTimeMonthPay(['platform_id'=>$value]);
        }

        return $result;
    }

    /**
     * 更新每天用户总充累充数据
     * @param array $params
     * @return int
     */
    public function updateUserRemarkPay(array $params = []){
        ini_set('memory_limit', '2048M');

        if(isset($params[0])){
            $p_r = [];
            parse_str($params[0],$p_r);
            extract($p_r);
        }

        $result = ['code'=>1, 'data'=>[]];

        $platformSuffix = [];
        if (!empty($platform) && isset($this->config['platform_suffix'][$platform])){
            $platformSuffix[$platform] = $this->config['platform_suffix'][$platform];
        }else{
            //没有指定具体的平台数据，轮询获取
            $platformSuffix = $this->config['platform_suffix'];
        }

        if(!$platformSuffix){
            $result['data']['msg'] = 'no platform';
            return $result;
        }

        foreach ($platformSuffix as $key=>$value){
            VipUserInfoServer::updateUserRemarkPay(['platform_info'=>['platform_id'=>$value,'suffix'=>$key]]);
        }

        return $result;
    }

    public function updateLossUserInfo(array $params = []){

        ini_set('memory_limit', '2048M');

        if(isset($params[0])){
            $p_r = [];
            parse_str($params[0],$p_r);
            extract($p_r);
        }

        $result = ['code'=>1, 'data'=>[]];

        $date = !empty($date)?$date:strtotime('yesterday');

        LossUserServer::updateUserInfo(compact('date'));

        return $result;
    }

    public function updateVipRoleLevel(array $params = []){
        ini_set('memory_limit', '2048M');

        if(isset($params[0])){
            $p_r = [];
            parse_str($params[0],$p_r);
            extract($p_r);
        }

        $result = ['code'=>1, 'data'=>[]];

        $platformSuffix = [];
        if (!empty($platform) && isset($this->config['platform_suffix'][$platform])){
            $platformSuffix[$platform] = $this->config['platform_suffix'][$platform];
        }else{
            //没有指定具体的平台数据，轮询获取
            $platformSuffix = $this->config['platform_suffix'];
        }

        if(!$platformSuffix){
            $result['data']['msg'] = 'no platform';
            return $result;
        }

        $date = !empty($date)?strtotime($date):strtotime('yesterday');

        foreach ($platformSuffix as $key=>$value){
            $res = VipUserInfoServer::updateVipRoleInfo(['platform_info'=>['platform_id'=>$value,'suffix'=>$key],'date'=>$date]);
        }

        return $result;
    }
}
