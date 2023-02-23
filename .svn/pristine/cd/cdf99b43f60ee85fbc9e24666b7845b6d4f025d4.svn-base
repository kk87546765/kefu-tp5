<?php
namespace app\api\controller;
use common\model\db_statistic\VipUserInfo as thisModel;
use common\model\db_statistic\KefuUserRecharge;
use common\model\db_statistic\EveryDayOrderCount;
use common\server\SysServer;
use common\server\CustomerPlatform\CommonServer;
use common\server\Statistic\UserInfoServer;
use common\libraries\ApiUserInfoSecurity;


class Vip extends Base
{
    private $code = [
        1=>'参数错误',
        2=>'接口停止使用',
        3=>'签名校验错误',
        4=>'type错误',
        5=>'没有配置公司信息',
    ];

    public function index()
    {
        $model = new thisModel();

        dd($model->find()->toArray());
        dd('this');
    }

    /**
     * 更新所有用户累充和总充
     * @param platform_id 主体id 非必须 空全部 参数例子：1/1,2,3/array(1,2,3)
     * @param last_pay_time 最后支付时间-起始 非必须 空全部 参数例子：Y-m-d
     * @param page 页数 必须
     * @param limit 分页 非必须
     */
    public function updateAllTotalPayThirtyDayPay(){

        $page = $this->req->post('page/d',1);
        $limit = $this->req->post('limit/d',50);

        $platform_id = $this->req->post('platform_id/s','');
        $last_pay_time = $this->req->post('last_pay_time/s','');
        $last_pay_time_end = $this->req->post('last_pay_time_end/s','');

        $today = strtotime('today');//timestamp

        $KefuUserRecharge = new KefuUserRecharge();
        $model = new thisModel();

        $obj = $KefuUserRecharge->where('total_pay','>',0);

        if($last_pay_time){
            $obj = $obj->where('last_pay_time','>=',strtotime($last_pay_time));
        }

        if($last_pay_time_end){
            $obj = $obj->where('last_pay_time','<',strtotime($last_pay_time_end));
        }

        if($platform_id){
            if(is_array($platform_id)){
                $this_info = $platform_id;
            }else{
                $this_info = explode(',',$platform_id);
            }
            if(count($this_info) == 1){
                $obj = $obj->where('platform_id',$this_info[0]);
            }else{
                $obj = $obj->where('platform_id','in',$this_info);
            }
        }

        $list = $obj->page($page,$limit)->order('id asc')->select();//->buildSql()->fetchSql(true)

        if(!$list->toArray()){
            $this->rs['msg'] = 'end';
            $this->rs['code'] = 1;
            return return_json($this->rs);
        }

        $p_u_arr = [];
        foreach ($list as $v){
            $this_p_u = $v->platform_id.'_'.$v->uid;
            $p_u_arr[] = $this_p_u;
        }

        $EveryDayOrderCount = new EveryDayOrderCount();

        $before_30_day = strtotime(date('Y-m-d',strtotime('-31day')));

        $field = '
            p_u
            ,platform_id
            ,uid
            ,SUM(amount_count) AS total_pay
            ,SUM(CASE WHEN pay_time >='.$before_30_day.' THEN amount_count ELSE 0 END) AS thirty_day_pay
        ';
        $where = [];
        $where['p_u'] = ['in',$p_u_arr];
        $where['pay_time'] = ['<',$today];
        $every_day_count_list = $EveryDayOrderCount->field($field)->where($where)->group('p_u')->select()->toArray();

        if(!$every_day_count_list){
            $this->rs['msg'] = 'no every_day_count_list';
            $this->rs['code'] = 2;
            return return_json($this->rs);
        }

        $every_day_count_list_new = arrReSet($every_day_count_list,'p_u');

        $count_info = 0;//可能有问题
        $count_normal = 0;//正常不需修正
        $count_success = 0;//修正成功
        $count_error = 0;//修正失败
        $count_success_p_u_arr = [];

        foreach ($list as $v){
            $this_p_u = $v->platform_id.'_'.$v->uid;

            if(!isset($every_day_count_list_new[$this_p_u])){
                $count_info++;
                continue;
            }
            $this_every_day_count_info = $every_day_count_list_new[$this_p_u];
            $this_data = [];

            if( $v->total_pay!=$this_every_day_count_info['total_pay']){
                $this_data['total_pay']= intval($this_every_day_count_info['total_pay']);
            }

            if($v->thirty_day_pay!=$this_every_day_count_info['thirty_day_pay']){
                $this_data['thirty_day_pay']= intval($this_every_day_count_info['thirty_day_pay']);
            }

            if(!$this_data){
                $count_normal++;
                continue;
            }

            $res = $v->update($this_data);

            if(!$res){
                $count_error++;
                continue;
            }

            $where = [];
            $where['platform_id'] = $v->platform_id;
            $where['uid'] = $v->uid;
            $this_vip_info = $model->where($where)->find();

            if($this_vip_info){
                $this_vip_info->update($this_data);
            }

            $count_success++;
            $count_success_p_u_arr[] = $this_p_u;

        }
        $msg = "完成-修正成功:$count_success 正常:$count_normal = 警告:$count_info 修正失败:$count_error ";

        if($count_success > 0 ){
            $msg.=' p_u:'.implode(',',$count_success_p_u_arr);
        }

        $this->rs['msg'] = $msg;
        return return_json($this->rs);
    }

    #更新用户user_name空的用户基础数据
    public function updateUserBaseInfo(){

        $param['limit'] = $this->req->post('limit/d',50);

        $param['platform'] = $this->req->post('platform/s','youyu');

        $res = UserInfoServer::updateUserBaseInfo($param);
        $this->rs['code'] = $res['code'];
        $this->rs['msg'] = $res['msg'];
        return return_json($this->rs);
    }


    public function delErrorDistributeVip(){

        $start_time = $this->req->post('start_time/s',date('Y-m-d'));

        $limit = $this->req->post('limit/d',50);
        $page = $this->req->post('page/d',1);

        $end_time = $this->req->post('end_time/s','');

        $platform = $this->req->post('platform/s','youyu');

        $platform_list = SysServer::getPlatformList();

        $platform_info = [];
        foreach ($platform_list as $item){
            if($item['suffix'] == $platform){
                $platform_info = $item;
                break;
            }
        }

        $model = new thisModel();

        $obj = $model->where('last_record_time',0)
            ->where('ascription_vip','>',0)
            ->where('last_distribute_time','>=',strtotime($start_time));

        if($end_time){
            $obj = $obj->where('last_distribute_time','<',strtotime($start_time));
        }

        if(!$platform_info){
            $obj = $obj->where('platform_id',$platform_info['platform_id']);
        }
//        dd($where);
        $user_list = $obj->page($page,$limit)->order('id asc')->select();

        if(!$user_list->toArray()){
            $this->rs['msg'] = 'no user';
            $this->rs['code'] = '1';
            return return_json($this->rs);
        }

        $count_all =0;

        $count_update = 0;

        foreach ($user_list as $item){

            $count_all++;
            $this_info = $item->toArray();

            $res = UserInfoServer::checkVipUserCanDistribute($this_info);

            if($res){
                continue;
            }

            $up_data = [
                'first_distribute_time'=>0,
                'last_distribute_time'=>0,
                'ascription_vip'=>0,
            ];
            $where = getDataByField($this_info,['uid','platform_id']) ;

            $res = $model->where($where)->update($up_data);

            if($res){
                $count_update++;
            }
        }
        $msg = "all $count_all count_update:$count_update";

        if($count_update){
            $this->rs['msg'] = $msg;
            $this->rs['code'] = 2;
            return return_json($this->rs);
        }

        $this->rs['msg'] = $msg;
        return return_json($this->rs);
    }

    #加密vip
    public function encryptMobile(){

        $time_data = [
            'first'=>microtime(),
        ];

        $limit = $this->req->post('limit/d',50);
        $change = $this->req->post('change/d',0);
        $change = 0;


        $model = new KefuUserRecharge();
        $vip_user_info = new thisModel();

        $obj = $model;

        if($change){
            $obj=$obj->where('mobile','!=','');
            $obj=$obj->where('mobile','<',0);
        }else{
            $obj=$obj->where('mobile','>',10);
        }

        $user_list = $obj->limit($limit)->order('uid asc')->select();
        $time_data['select'] = microtime();
        if(!$user_list->toArray()){
            $this->rs['code'] = 2;
            $this->rs['msg'] = 'no user';
            return return_json($this->rs);
        }

        $msg_arr = [
            'count_all'=>'总',
            'count_update'=>'总更新',
            'count_vip'=>'vip总',
            'count_update_vip'=>'vip总更新',
        ];

        $count_all =0;

        $count_update = 0;

        $count_vip = 0;

        $count_update_vip = 0;

        $log_text_arr = [];

        $log_text = '';

        $first_name = '';

        foreach ($user_list as $item){

            $count_all++;
            $this_info = $item->toArray();

            if(!$change &&!isphone($this_info['mobile'])){
                continue;
            }
            $platform_id = $this_info['platform_id'];
            if(!isset($log_text_arr[$platform_id])){
                $log_text_arr[$platform_id] = [
                    'count'=>0,
                    'text'=>'',
                    'first_name'=>$platform_id.'_'.$this_info['uid'],
                ];
            }

            $log_text_arr[$platform_id]['text'].=$this_info['platform_id'].'_'.$this_info['uid'].','.$this_info['mobile'].'[lxx_data]';

            $up_data = [];
            if($change){
                $up_data['mobile'] = ApiUserInfoSecurity::decrypt($this_info['mobile']);
            }else{
                $up_data['mobile'] = ApiUserInfoSecurity::encrypt($this_info['mobile']);
            }
            $where = getDataByField($this_info,['uid','platform_id']);

            $res = $model->where($where)->update($up_data);

            if(!$res){
                continue;
            }

            $count_update++;
            $log_text_arr[$platform_id]['count']++;

            if($this_info['is_vip'] == 1){
                $count_vip++;

                $res = $vip_user_info->where($where)->update($up_data);
                if($res){
                    $count_update_vip++;
                }
            }
        }
        $time_data['update'] = microtime();
        $msg = '';
        foreach ($msg_arr as $k => $v){
            $msg.= "$v:".$$k.' ';
        }

        if(!$count_update){
            $this->rs['code'] = 1;
            $this->rs['msg'] = $msg;
            return return_json($this->rs);
        }

        foreach ($log_text_arr as $k => $v){
            $res = $this->log($v['text'],$v['count'],$v['first_name'],$k);
        }
        $time_data['log'] = microtime();
        sleep(1);

        $this->rs['msg'] = $msg;
        $this->rs['data'] = $time_data;
        return return_json($this->rs);
    }

    public function encryptMobileByPlatform(){
        $time_data = [
            'first'=>microtime(),
        ];

        $limit = $this->req->post('limit/d',50);
        $change = $this->req->post('change/d',0);
        $platform_id = $this->req->post('platform_id/d',0);
        $change = 0;

        if(!$platform_id){
            $this->rs['code'] = 1;
            $this->rs['msg'] = 'no platform_id';
            return return_json($this->rs);
        }

        $platform_list = SysServer::getPlatformList();

        $platform_info = getArrVal($platform_list,$platform_id,[]);

        if(!$platform_info){
            $this->rs['code'] = 2;
            $this->rs['msg'] = 'no platform_info';
            return return_json($this->rs);
        }

        $model = CommonServer::getPlatformModel('KefuCommonMember',$platform_info['suffix']);

        $obj = $model;

        if($change){
            $obj=$obj->where('mobile','!=','');
            $obj=$obj->where('mobile','<',0);
        }else{
            $obj=$obj->where('mobile','>',10);
        }

        $user_list = $obj->limit($limit)->order('uid asc')->select();

        $time_data['select'] = microtime();
        if(!$user_list->toArray()){
            $this->rs['code'] = 3;
            $this->rs['msg'] = 'no user';
            return return_json($this->rs);
        }

        $msg_arr = [
            'count_all'=>'总',
            'count_update'=>'总更新',
        ];

        $count_all =0;

        $count_update = 0;

        $log_text = '';

        $first_name = '';

        foreach ($user_list as $item){

            $count_all++;
            $this_info = $item->toArray();

            if(!$change &&!isphone($this_info['mobile'])){
                continue;
            }

            if($first_name == ''){
                $first_name=$platform_id.'_'.$this_info['uid'];
            }

            $log_text.=$platform_id.'_'.$this_info['uid'].','.$this_info['mobile'].'[lxx_data]';

            $up_data = [];

            if($change){
                $up_data['mobile'] = ApiUserInfoSecurity::decrypt($this_info['mobile']);
            }else{
                $up_data['mobile'] = ApiUserInfoSecurity::encrypt($this_info['mobile']);
            }
            $where = getDataByField($this_info,['uid']);

            $res = $model->where($where)->update($up_data);

            if(!$res){
                continue;
            }

            $count_update++;
        }
        $time_data['update'] = microtime();
        $msg = '';
        foreach ($msg_arr as $k => $v){
            $msg.= "$v:".$$k.' ';
        }

        if(!$count_update){
            $this->rs['code'] = 4;
            $this->rs['msg'] = $msg;
            return return_json($this->rs);
        }

        $res = $this->log($log_text,$count_update,$first_name,$platform_id);

        $time_data['log'] = microtime();

        $new_time_data = [];

        foreach ($time_data as $key => $v) {
            $this_info = splitToArr($v,'');
            $time_data[$key] = $this_info[0]+$this_info[1];
        }

        $new_time_data['select'] = $time_data['select'] - $time_data['first'];
        $new_time_data['update'] = $time_data['update'] - $time_data['select'];
        $new_time_data['log'] = $time_data['log'] - $time_data['update'];

        $this->rs['data'] = $new_time_data;
        $this->rs['msg'] = $msg;
        return return_json($this->rs);
    }

    public function checkUserRechargeOverTime(){

        $limit = $this->req->post('limit/d',50);

        $platform_id = $this->req->post('platform_id/d',0);

        if(!$platform_id){
            $this->rs['code'] = 1;
            $this->rs['msg'] = 'no platform_id';
            return return_json($this->rs);
        }

        $platform_list = SysServer::getPlatformList();

        $platform_info = getArrVal($platform_list,$platform_id,[]);

        if(!$platform_info){
            $this->rs['code'] = 2;
            $this->rs['msg'] = 'no platform_info';
            return return_json($this->rs);
        }

        $param = [];
        $param['limit'] = $limit;

        $param['platform_id'] = $platform_id;

        $res = UserInfoServer::updateUserThirtyDayPay($param);

        if($res['code']){
            $this->rs['code'] = $res['code'];
            $this->rs['msg'] = $res['msg'];
        }else{
            $this->rs['msg'] = 'ok';
        }
        return return_json($this->rs);

    }

    public function updateRemarkTime(){
        $param = [];
        $param['limit'] = $this->request->getPost('limit','int',50);

        $res = UserInfoServer::updateRemarkTime($param);

        if($res['code']){
            $this->error($res['msg']);
        }else{
            $this->success($res['msg']);
        }
    }

    #更新用户user_name空的用户基础数据
    public function delErrorUser(){

        $param['limit'] = $this->req->post('limit/d',50);

        $param['platform_id'] = $this->req->post('platform_id/d',6);

        $res = UserInfoServer::delErrorUser($param);
        $this->rs['code'] = $res['code'];
        $this->rs['msg'] = $res['msg'];
        return return_json($this->rs);
    }

    private function log($text,$text_count,$first_name,$platform_id){

        $file_max_limit = 50000;


        $pre = 'api_'.$this->req->controller().'_'.$this->req->action().$platform_id;
        $cache_name_fn = $pre.'_fn';

        $path = RUNTIME_PATH.'api/'.$this->req->controller().'/'.$this->req->action();

        $file_name = cache($cache_name_fn);

        if(!$file_name){
            $file_name = $first_name.'_'.createNonceStr(5);
            cache($cache_name_fn,$file_name,3600*3);
        }

        $cache_name_limit = $file_name.'_limit';

        $file_count = cache($cache_name_limit);

        if(!$file_count){
            $file_count = 0;
        }

        $file = $path.'/'.$file_name.'.txt';

        if(!file_exists($path)){
            mkdir($path,0777,true);
        }

        $my_file = fopen($file, "a");

        if(!$my_file){
            return '文件操作失败';
        }

        fwrite($my_file, $text);

        $file_count+= $text_count;
//        dd($file_name,0);dd($file_count,0);dd($text_count,0);

        if($file_count >= $file_max_limit){
            cache($cache_name_fn,null);
        }else{
            cache($cache_name_limit,$file_count,3600*3);
        }

        return  'ok';

    }
}
