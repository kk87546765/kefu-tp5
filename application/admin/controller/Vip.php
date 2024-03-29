<?php
/**
 * vip
 */
namespace app\admin\controller;

use common\server\Vip\SellWorkOrderServer;
use common\server\Vip\VipServer AS thisServer;
use common\server\Vip\WelfareServer;

class Vip extends Oauth
{
    protected $no_oauth = [
        'userInputInfoConfig',
        'washUserListConfig',
        'vipUserInfoListConfig',
        'propRebateListConfig',
        'propRebateDetailConfig',
        'userOtherInfoListConfig',
        'userWelfareListConfig',
        'sellWorkOrderListConfig',
        'kefuPayOrderList',
        'getVipUserServerInfoByGameId',
        'userInputInfoCheckUser',
    ];

    #注册用户-列表
    public function userRechargeList(){

        $params = $this->getPost([
            ['page', 'int', 1],
            ['limit', 'int', 20],
            ['platform_id', 'int', 0],
            ['recharge_static', 'int', 0],
            ['login_static', 'int', 0],
            ['server_id_min', 'int', 0],
            ['server_id_max', 'int', 0],
            ['single_hign_pay_min', 'int', 0],
            ['single_hign_pay_max', 'int', 0],
            ['total_pay_min', 'int', 0],
            ['total_pay_max', 'int', 0],
            ['thirty_day_pay_min', 'int', 0],
            ['thirty_day_pay_max', 'int', 0],
            ['day_hign_pay_min', 'int', 0],
            ['day_hign_pay_max', 'int', 0],
            ['today_pay_min', 'int', 0],
            ['today_pay_max', 'int', 0],
            ['vip_grade', 'int', 0],
            ['role_id', 'int', 0],
            ['uid', 'int', 0],
            ['user_name', 'trim', ''],
            ['server_prefix', 'trim', ''],
            ['server_suffix', 'trim', ''],
            ['sort_field', 'trim', ''],
            ['sort', 'trim', ''],
            ['p_p', 'trim', ''],
            ['p_g', 'trim', ''],
        ]);

        $res = thisServer::userRechargeList($params);

        $this->rs = array_merge($this->rs,$res);

        return return_json($this->rs);
    }
    #注册用户-列表导出
    public function userRechargeExcel(){
        ini_set('memory_limit', '2048M');
        $params = $this->getPost([
            ['page', 'int', 1],
            ['limit', 'int', 20],
            ['platform_id', 'int', 0],
            ['recharge_static', 'int', 0],
            ['login_static', 'int', 0],
            ['server_id_min', 'int', 0],
            ['server_id_max', 'int', 0],
            ['single_hign_pay_min', 'int', 0],
            ['single_hign_pay_max', 'int', 0],
            ['total_pay_min', 'int', 0],
            ['total_pay_max', 'int', 0],
            ['thirty_day_pay_min', 'int', 0],
            ['thirty_day_pay_max', 'int', 0],
            ['day_hign_pay_min', 'int', 0],
            ['day_hign_pay_max', 'int', 0],
            ['today_pay_min', 'int', 0],
            ['today_pay_max', 'int', 0],
            ['vip_grade', 'int', 0],
            ['role_id', 'int', 0],
            ['uid', 'int', 0],
            ['user_name', 'trim', ''],
            ['server_prefix', 'trim', ''],
            ['server_suffix', 'trim', ''],
            ['sort_field', 'trim', ''],
            ['sort', 'trim', ''],
            ['p_p', 'trim', ''],
            ['p_g', 'trim', ''],
        ]);

        $params['is_excel'] = 1;

        $res = thisServer::userRechargeList($params);

        $this->rs = array_merge($this->rs,$res);

        return return_json($this->rs);
    }

    #自助登记-列表
    public function userInputInfoList(){

        $p_data = $this->getPost([
            ['page','int',0],
            ['limit','int',0],
            ['platform_id','trim',''],
            ['p_p','trim',''],
            ['group_id','int',0],
            ['ascription_vip','trim',''],
            ['handler_id','int',0],
            ['user_name','trim',''],
            ['uid','trim',''],
            ['role_id','trim',''],
            ['role_name','trim',''],
            ['status','int',-1],
            ['add_time_start','trim',''],
            ['add_time_end','trim',''],
            ['handler_time_start','trim',''],
            ['handler_time_end','trim',''],
        ]);

        list($list,$count) = thisServer::userInputInfoList($p_data);

        $this->rs['data'] = $list;
        $this->rs['count'] = $count;
        return return_json($this->rs);
    }
    #自助登记-配置
    public function userInputInfoConfig(){

        $this->rs['data'] = thisServer::userInputInfoConfig();

        return return_json($this->rs);
    }
    #自助登记-详情
    public function userInputInfoDetail(){

        $id = $this->req->post('id/d',0);

        $this->rs = array_merge($this->rs,thisServer::userInputInfoDetail($id));

        return return_json($this->rs);
    }
    #自助登记-详情
    public function userInputInfoCheckUser(){
        $p_data = $this->getPost([
            ['platform_id','int',0],
            ['uid','int',0],
        ]);

        $this->rs = array_merge($this->rs,thisServer::userInputInfoCheckUser($p_data));

        return return_json($this->rs);

    }
    #自助登记-添加
    public function userInputInfoAdd(){
        $p_data = $this->getPost([
            ['id','int',0],
            ['platform_id','int',0],
            ['uid','trim',''],
            ['game_id','int',0],
            ['birthday','trim',0],
            ['phone_num','int',0],
            ['qq','trim',''],
            ['qq_question_answer','trim',''],
            ['wechat','trim',''],
            ['role_name','trim',''],
            ['role_id','int',0],
            ['server_id','int',0],
            ['server_name','trim',''],
            ['remarks','trim',''],
            ['status','int',0],
        ]);

        $this->rs = array_merge($this->rs,thisServer::userInputInfoSave($p_data));

        return return_json($this->rs);
    }
    #自助登记-修改
    public function userInputInfoEdit(){
        $p_data = $this->getPost([
            ['id','int',0],
            ['platform_id','int',0],
            ['uid','trim',''],
            ['game_id','int',0],
            ['birthday','trim',0],
            ['phone_num','int',0],
            ['qq','trim',''],
            ['qq_question_answer','trim',''],
            ['wechat','trim',''],
            ['role_name','trim',''],
            ['role_id','int',0],
            ['server_id','int',0],
            ['server_name','trim',''],
            ['remarks','trim',''],
            ['status','int',0],
        ]);

        $this->rs = array_merge($this->rs,thisServer::userInputInfoSave($p_data));

        return return_json($this->rs);
    }
    #自助登记-删除
    public function userInputInfoDelete(){

        $id = $this->request->post('id', 'int', 0);

        $this->rs = array_merge($this->rs,thisServer::userInputInfoDelete($id));

        return return_json($this->rs);
    }

    #流失用户-列表
    public function washUserList(){

        $p_data = $this->getPost([
            ['page','int',1],
            ['limit','int',20],
            ['uid','int',0],
            ['total_pay_start','int',0],
            ['total_pay_end','int',0],
            ['ascription_vip','int',0],
            ['is_record','int',0],
            ['last_login_time_num_start','int',0],
            ['last_login_time_num_end','int',0],
            ['last_pay_time_num_start','int',0],
            ['last_pay_time_num_end','int',0],
            ['platform_id','trim',''],
            ['p_g','trim',''],
            ['p_p','trim',''],
            ['user_name','trim',''],
            ['role_id','trim',''],
            ['last_login_time','trim',''],
            ['last_pay_time','trim',''],
            ['contact_type','trim',''],
        ]);

        list($list,$count)= thisServer::washUserList($p_data);

        if($list){
            $this->rs['count'] = $count;
            $this->rs['data'] = $list;
            return return_json($this->rs);
        }else{
            $this->rs['msg'] = '没有数据';
            $this->rs['code'] = 1;
            return return_json($this->rs);
        }
    }
    #流失用户-列表配置
    public function washUserListConfig(){

        $this->rs['data'] = thisServer::washUserListConfig();

        return return_json($this->rs);
    }
    #流失用户-导出
    public function washUserListExcel(){
        ini_set('memory_limit', '2048M');
        $p_data = $this->getPost([
            ['page','int',1],
            ['limit','int',20],
            ['uid','int',0],
            ['total_pay_start','int',0],
            ['total_pay_end','int',0],
            ['ascription_vip','int',0],
            ['is_record','int',0],
            ['last_login_time_num_start','int',0],
            ['last_login_time_num_end','int',0],
            ['last_pay_time_num_start','int',0],
            ['last_pay_time_num_end','int',0],
            ['platform_id','trim',''],
            ['p_g','trim',''],
            ['p_p','trim',''],
            ['user_name','trim',''],
            ['role_id','trim',''],
            ['last_login_time','trim',''],
            ['last_pay_time','trim',''],
            ['contact_type','trim',''],
        ]);

        $p_data['is_excel'] = 1;

        list($list,$count)= thisServer::washUserList($p_data);

        if($list){
            $this->rs['name'] = '流失预警'.date('YmdHis');
            $this->rs['count'] = $count;
            $this->rs['data'] = $list;
            return return_json($this->rs);
        }else{
            $this->rs['msg'] = '没有数据';
            $this->rs['code'] = 1;
            return return_json($this->rs);
        }
    }

    #手机号解密
    public function decryptMobile(){

        $p_data = $this->getPost([
            ['platform_id','int',0],
            ['uid','int',0],
        ]);

        $this->rs = array_merge($this->rs,thisServer::decryptMobile($p_data));

        return return_json($this->rs);
    }

    #vip用户管理-列表
    public function vipUserInfoList(){

        $params = $this->getPost([
            ['page', 'int', 1],
            ['limit', 'int', 20],
            ['platform_id', 'int', 0],
            ['recharge_static', 'int', 0],
            ['login_static', 'int', 0],
            ['server_id_min', 'int', 0],
            ['server_id_max', 'int', 0],
            ['single_hign_pay_min', 'int', 0],
            ['single_hign_pay_max', 'int', 0],
            ['total_pay_min', 'int', 0],
            ['total_pay_max', 'int', 0],
            ['thirty_day_pay_min', 'int', 0],
            ['thirty_day_pay_max', 'int', 0],
            ['day_hign_pay_min', 'int', 0],
            ['day_hign_pay_max', 'int', 0],
            ['today_pay_min', 'int', 0],
            ['today_pay_max', 'int', 0],
            ['vip_grade', 'int', 0],
            ['role_id', 'int', 0],
            ['uid', 'int', 0],
            ['distribute_static', 'int', 0],
            ['is_display_mobile', 'int', 0],
            ['is_record', 'int', 0],
            ['is_remark','int',0],
            ['is_record_data', 'int', 0],
            ['ascription_vip', 'int', 0],
            ['user_name', 'trim', ''],
            ['server_prefix', 'trim', ''],
            ['server_suffix', 'trim', ''],
            ['sort_field', 'trim', ''],
            ['sort', 'trim', ''],
            ['p_p', 'trim', ''],
            ['p_g', 'trim', ''],
            ['first_distribute_time', 'trim', ''],
            ['last_distribute_time', 'trim', ''],
            ['last_login_time', 'trim', ''],
            ['last_pay_time', 'trim', ''],
            ['last_record_time', 'trim', ''],
        ]);

        $res = thisServer::vipUserInfoList($params);

        $this->rs = array_merge($this->rs,$res);

        return return_json($this->rs);
    }
    #vip用户管理-配置
    public function vipUserInfoListConfig(){

        $this->rs['data'] = thisServer::vipUserInfoListConfig();

        return return_json($this->rs);
    }
    #vip用户管理-导出
    public function vipUserInfoListExcel(){
        ini_set('memory_limit', '2048M');
        $params = $this->getPost([
            ['page', 'int', 1],
            ['limit', 'int', 20],
            ['platform_id', 'int', 0],
            ['recharge_static', 'int', 0],
            ['login_static', 'int', 0],
            ['server_id_min', 'int', 0],
            ['server_id_max', 'int', 0],
            ['single_hign_pay_min', 'int', 0],
            ['single_hign_pay_max', 'int', 0],
            ['total_pay_min', 'int', 0],
            ['total_pay_max', 'int', 0],
            ['thirty_day_pay_min', 'int', 0],
            ['thirty_day_pay_max', 'int', 0],
            ['day_hign_pay_min', 'int', 0],
            ['day_hign_pay_max', 'int', 0],
            ['today_pay_min', 'int', 0],
            ['today_pay_max', 'int', 0],
            ['vip_grade', 'int', 0],
            ['role_id', 'int', 0],
            ['uid', 'int', 0],
            ['distribute_static', 'int', 0],
            ['is_display_mobile', 'int', 0],
            ['is_record', 'int', 0],
            ['is_remark', 'int', 0],
            ['is_record_data', 'int', 0],
            ['ascription_vip', 'int', 0],
            ['user_name', 'trim', ''],
            ['server_prefix', 'trim', ''],
            ['server_suffix', 'trim', ''],
            ['sort_field', 'trim', ''],
            ['sort', 'trim', ''],
            ['p_p', 'trim', ''],
            ['p_g', 'trim', ''],
            ['first_distribute_time', 'trim', ''],
            ['last_distribute_time', 'trim', ''],
            ['last_login_time', 'trim', ''],
            ['last_pay_time', 'trim', ''],
            ['last_record_time','trim',''],
        ]);

        $params['is_excel'] = 1;

        $res = thisServer::vipUserInfoList($params);

        $this->rs = array_merge($this->rs,$res);

        $this->rs['name'] = 'vip用户管理'.date('YmdHis');

        return return_json($this->rs);
    }
    #vip用户管理-批量分配
    public function vipDistribution()
    {
        $params = $this->getPost([
            ['idStr','array',[]],
            ['admin_id','int',0],
        ]);

        if(thisServer::vipDistribution($params)){
            $this->rs['msg'] = '成功';
        }else{
            $this->rs['code'] = 1;
            $this->rs['msg'] = '失败';
        }

        return return_json($this->rs);
    }
    #vip用户管理-剔除批量分配
    public function vipDistributionDelete()
    {
        $id = $this->request->post('id/a',  []);

        $this->rs = array_merge($this->rs,thisServer::vipDistributionDelete($id));

        return return_json($this->rs);
    }

    #vip用户其他信息-详情
    public function vipUserOtherInfoDetail(){

        $platform_id = $this->req->post('platform_id/d',0);
        $uid = $this->req->post('uid/d',0);

        $res = thisServer::vipUserOtherInfoDetail($platform_id,$uid);

        if(!$res){
            $this->rs['code'] = 1;
            $this->rs['msg'] = '用户信息不存在';
        }else{
            $this->rs['data'] = $res;
        }

        return return_json($this->rs);
    }
    #vip用户其他信息-添加
    public function vipUserOtherInfoAdd(){

        $p_data = $this->getPost([
            ['id', 'int', 0],
            ['platform_id', 'int', 0],
            ['birth_month', 'int', 0],
            ['birth_day', 'int', 0],
            ['uid', 'int', 0],
            ['product_id', 'int', 0],
            ['user_name', 'trim', ''],
            ['mobile', 'trim', ''],
            ['qq_number', 'trim', ''],
            ['wechat', 'trim', ''],
            ['pursue_game', 'trim', ''],
            ['remarks', 'trim', ''],
        ]);

        $this->rs = array_merge($this->rs,thisServer::vipUserOtherInfoSave($p_data));

        return return_json($this->rs);
    }
    #vip用户其他信息-修改
    public function vipUserOtherInfoEdit(){
        return $this->vipUserOtherInfoAdd();
    }

    #道具返利-列表
    public function propRebateList()
    {
        $data = $this->getPost([
            ['platform_id', 'int', 0],
            ['uid', 'int', 0],
            ['date', 'trim', ''],
            ['game_id', 'int', 0]
        ]);

        $this->rs['data'] = WelfareServer::getUserRebatePropList($data);

        return return_json($this->rs);
    }
    #道具返利-列表配置
    public function propRebateListConfig(){

        $this->rs['data'] = WelfareServer::propRebateConfig();

        return return_json($this->rs);
    }
    #道具返利-审核
    public function propRebateExamine()
    {
        $p_data = $this->getPost([
            ['ids', 'array', []],
            ['examine_status', 'int', 0],
        ]);

        $this->rs = array_merge($this->rs,WelfareServer::propRebateExamine($p_data));

        return return_json($this->rs);
    }
    #道具返利-申请
    public function propRebateApply()
    {
        $p_data = $this->getPost([
            ['ids', 'array', []],
            ['val', 'int', 0],
        ]);

        $this->rs = array_merge($this->rs,WelfareServer::propRebateApply($p_data));

        return return_json($this->rs);
    }
    #道具返利-详情
    public function propRebateDetail(){

    }
    #道具返利-详情配置
    public function propRebateDetailConfig(){

        $this->rs['data'] = WelfareServer::propRebateDetailConfig();

        return return_json($this->rs);
    }
    #道具返利-添加
    public function propRebateAdd(){
        $p_data = $this->getPost([
            ['apply_status', 'int', 0],
            ['rebate_prop_config_id', 'int', 0],
            ['date', 'trim', ''],
            ['platform_id', 'int', 0],
            ['uid', 'int', 0],
            ['game_id', 'int', 0]
        ]);
        $res = WelfareServer::propRebateSave($p_data);
        if($res){
            $this->rs['msg'] = '操作成功';
        }else{
            $this->rs['code'] = 1;
            $this->rs['msg'] = '操作失败';
        }

        return return_json($this->rs);
    }
    #道具返利-修改
    public function propRebateEdit(){

    }

    #用户生日管理-列表
    public function userOtherInfoList(){

        $this_month_arr = timeCondition('month');
        $this_month_arr['starttime_str'] = date('Y-m-d',$this_month_arr['starttime']);
        $this_month_arr['endtime_str'] = date('Y-m-d',$this_month_arr['endtime']);
        $p_data = $this->getPost([
            ['page','int',1],//vip
            ['limit','int',20],//vip
            ['birthday_start','trim',$this_month_arr['starttime_str']],//other
            ['birthday_end','trim',$this_month_arr['endtime_str']],//other
            ['group_id','int',0],//all
            ['ascription_vip','trim',''],//vip
            ['p_p','trim',''],//other
            ['platform_id','trim',''],
            ['thirty_day_pay_start','int',0],//vip
            ['thirty_day_pay_end','int',0],//vip
            ['total_pay_start','int',0],//vip
            ['total_pay_end','int',0],//vip
        ]);

        list($list,$count) = thisServer::userOtherInfoList($p_data);

        $this->rs['data'] = $list;
        $this->rs['count'] = $count;
        return return_json($this->rs);
    }
    #用户生日管理-列表配置
    public function userOtherInfoListConfig(){

        $this->rs['data'] = thisServer::userOtherInfoListConfig();

        return return_json($this->rs);
    }

    #vip用户福利-列表
    public function userWelfareList(){

        $p_data = $this->getPost([
            ['page','int',1],
            ['limit','int',20],
            ['admin_id','int',0],
            ['platform_id','int',0],
            ['group_id','int',0],
            ['is_send','int',-1],
            ['p_u','trim',''],
            ['user_name','trim',''],
            ['add_time_start','trim',''],
            ['add_time_end','trim',''],
        ]);

        list($list,$count) = thisServer::userWelfareList($p_data);

        if($list){
            $this->rs['data'] = $list;
            $this->rs['count'] = $count;
        }else{
            $this->rs['msg'] = '没有数据';
        }

        return return_json($this->rs);
    }
    #vip用户福利-列表配置
    public function userWelfareListConfig(){

        $this->rs['data'] = thisServer::userWelfareListConfig();

        return return_json($this->rs);
    }
    #vip用户福利-详情
    public function userWelfareDetail(){

        $id = $this->request->post('id/d',0);

        $p_u = $this->request->post('p_u/s','');

        $info = [
            'id'=>0
        ];

        if($id){
            $info = thisServer::getVipUserWelfareDetail($id);
        }else{
            $user_info = [];
            if($p_u){
                $p_u_info = explode('_',$p_u);
                if(count($p_u_info) == 2){
                    $user_info = thisServer::getVipUserInfoByPidUid($p_u_info[0],$p_u_info[1]);
                }
            }
            if(!$user_info){
                $this->rs['code'] = 1;
                $this->rs['msg'] = '没有用户数据';
                return return_json($this->rs);
            }
            $info['user_name'] = $user_info['user_name'];
            $info['platform_id'] = $user_info['platform_id'];
            $info['uid'] = $user_info['uid'];
        }

        $config = thisServer::userWelfareDetailConfig();

        $this->rs['data'] = [
            'detail'=>$info,
            'config'=>$config,
        ];

        return return_json($this->rs);
    }
    #vip用户福利-添加
    public function userWelfareAdd(){

        $p_data = $this->getPost([
            ['id','int',0],
            ['platform_id','int',0],
            ['uid','int',0],
            ['qq','trim',0],
            ['goods','trim',''],
            ['address','trim',''],
            ['phone','trim',''],
            ['is_send','int',0],
            ['desc','trim',''],
            ['content','trim',''],
            ['imgs','trim',''],
        ]);

        $res = thisServer::VipUserWelfareSave($p_data);

        $this->rs = array_merge($this->rs,$res);

        return return_json($this->rs);
    }
    #vip用户福利-修改
    public function userWelfareEdit(){
        return $this->userWelfareAdd();
    }

    #营销用户-详情
    public function sellWorkOrderInfo(){

        $id = $this->request->post('id/d',0);//vip用户信息id

        //用户信息查询 & 历史游戏信息查询 & 配置信息
        $this->rs['data'] = thisServer::getUserRechargeInfoById($id);

        return return_json($this->rs);
    }

    /*营销用户工单-列表*/
    public function sellWorkOrderList(){

        $time_arr = timeCondition('month',time());
        $def_add_time_str = date('Y-m-d H:i:s',$time_arr['starttime']);

        $p_data = $this->getPost([
            ['page','int',1],
            ['limit','int',20],
            ['is_excel','int',0],
            ['kf_id','int',0],
            ['type','int',0],
            ['sell_type','int',0],
            ['server_id_start','int',0],
            ['server_id_end','int',0],
            ['username','trim',''],
            ['sell_amount_start','int',0],
            ['sell_amount_end','int',0],
            ['group_id','int',0],
            ['id','int',0],
            ['qc_first_admin_id','int',0],
            ['qc_second_admin_id','int',0],
            ['add_time_start','trim',$def_add_time_str],
            ['add_time_end','trim',''],
            ['qc_first_time_start','trim',''],
            ['qc_first_time_end','trim',''],
            ['qc_second_time_start','trim',''],
            ['qc_second_time_end','trim',''],
            ['pay_time_start','trim',''],
            ['pay_time_end','trim',''],
            ['sort_field','trim',''],
            ['sort','trim',''],
            ['is_all','int',0],
            ['uid','int',0],
            ['status','trim',''],
            ['p_p','trim',''],
            ['platform_id','trim',''],
        ]);

        $this->rs = array_merge($this->rs,SellWorkOrderServer::sellWorkOrderList($p_data));

        return return_json($this->rs);
    }

    public function sellWorkOrderListExcel(){
        ini_set('memory_limit', '2048M');
        $this->rs['name'] = '工单列表'.date('YmdHis');
        return $this->sellWorkOrderList();
    }

    public function sellWorkOrderListConfig(){

        $this->rs['data'] = SellWorkOrderServer::sellWorkOrderListConfig();

        return return_json($this->rs);
    }

    /*营销用户工单-详情*/
    public function sellWorkOrderDetail(){

        $p_data = $this->getPost([
            ['type','int',1],//1 登记 2 销售 3 维护 4 召回
            ['id','int',0],//工单id
            ['user_recharge_info_id','int',1],//用户充值信息id
        ]);

        $this->rs = array_merge($this->rs,SellWorkOrderServer::sellWorkOrderDetail($p_data));

        return return_json($this->rs);
    }

    public function sellWorkOrderAdd(){

        $type = $this->request->post('type/d',0);
        //提交参数字段数据
        $p_arr = [
            ['kf_id','int',0],//客服专员id
            ['product_id','int',0],//游戏大类id
            ['contact_type','int',0],//联系方式
            ['id','int',0],//详情id
            ['type','int',0],//工单类型
            ['uid','int',0],//vip用户id
            ['platform_id','int',0],//平台id
            ['game_id','int',0],//平台id
            ['content','trim',''],//工单内容
        ];

        if($type == 2){//销售
            $p_arr[] = ['sell_type','int',0];//销售类型
            $p_arr[] = ['is_first','int',0];//是否首次提单
            $p_arr[] = ['order_ids','array',[]];//订单id
        }elseif($type == 4){
            $p_arr[] = ['recall_account','trim',''];//召回账号
            $p_arr[] = ['recall_time','trim',''];//召回时间
        }elseif($type == 5){
            $p_arr[] = ['chum_reason','trim',''];//流失原因
        }else{
            $p_arr[] = ['server_id','trim',''];//区服id
        }

        //获取提交数据
        $p_data = $this->getPost($p_arr);

        //保存
        $this->rs = array_merge($this->rs,SellWorkOrderServer::sWOSave($p_data));

        return return_json($this->rs);
    }

    public function sellWorkOrderEdit(){
        return $this->sellWorkOrderAdd();
    }

    public function sellWorkOrderQcFirst(){

        $p_data = $this->getPost([
            ['id','int',0],
            ['status','int',0],
            ['reason','trim', ''],
        ]);

        $this->rs = array_merge($this->rs,SellWorkOrderServer::sWOQc($p_data));

        return return_json($this->rs);
    }

    public function sellWorkOrderQcSecond(){
        return $this->sellWorkOrderQcFirst();
    }

    /*营销用户工单-用户订单查询*/
    public function kefuPayOrderList(){

        $p_data = $this->getPost([
            ['page','int',1],
            ['limit','int',20],
            ['platform_id','int',0],
            ['uid','int',0],
            ['gid','int',0],
            ['server_id','int',0],
            ['order_time_start','trim',''],
            ['order_time_end','trim',''],
            ['sell_work_order_id','int',0],
            ['product_id','int',0],
            ['sell_type','int',1],
        ]);

        $this->rs = array_merge($this->rs,SellWorkOrderServer::getKefuPayOrderList($p_data));

        return return_json($this->rs);
    }

    /*获取vip用户充值区分信息*/
    public function getVipUserServerInfoByGameId(){

        $p_data = $this->getPost([
            ['uid','int',0],
            ['product_id','int',0],
            ['platform_id','int',0],
        ]);

        $info = thisServer::getVUServerInfoByWhere($p_data);

        if(!$info){
            $this->rs['code'] = 1;
            $this->rs['msg'] = '没有数据';
        }else{
            $this->rs['data'] = $info;
        }

        return return_json($this->rs);
    }
}
