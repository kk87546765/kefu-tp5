<?php
/**
 * vip配置
 */
namespace app\admin\controller;

use common\server\Vip\ConfigServer AS thisServer;
class VipConfig extends Oauth
{
    protected $no_oauth = [
        'becomeVipStandardListConfig',
        'becomeVipStandardDetailConfig',
        'serverManageListConfig',
        'serverManageDetailConfig',
        'sellerKpiConfig',
        'getGameListByUid',
        'getUsersKpi',
        'sellerCommissionConfigListConfig',
    ];

    #返利道具配置-列表
    public function rebateConfigList()
    {
        $params = $this->getPost([
            ['page','int',1],
            ['limit','int',20],
            ['product_name','trim',''],
            ['title','trim',''],
            ['content','trim',''],
            ['prop_id','trim',0],
            ['status','int',1]
        ]);

        list($data,$count) = thisServer::getRebateConfigList($params);

        $this->rs['data'] = $data;
        $this->rs['count'] = $count;

        return return_json($this->rs);
    }
    #返利道具配置-导入
    public function rebateConfigImport()
    {
        $this_file = $this->request->file('file');

        $this->rs = array_merge($this->rs,thisServer::rebateConfigImport($this_file));

        return return_json($this->rs);
    }
    #返利道具配置-批量删除
    public function batchDeleteRebate()
    {
        $ids = $this->request->post('ids/a',[]);

        $res = thisServer::editRebateStatus($ids);

        if($res){
            $this->rs['msg'] = '操作成功：总条数->'.count($ids).'成功删除条数->'.$res;
        }else{
            $this->rs['code'] = 1;
            $this->rs['msg'] = '操作失败';
        }
        return return_json($this->rs);
    }

    #vip达成配置-列表
    public function becomeVipStandardList(){

        $p_data = $this->getPost([
            ['page','int',1],
            ['limit','int',20],
            ['vip_game_product_id','int',0],
            ['static','int',0],
            ['platform_id','trim',''],
            ['p_p','trim',''],
            ['p_g','trim',''],
        ]);

        list($list,$count) = thisServer::becomeVipStandardList($p_data);

        $this->rs['data'] = $list;
        $this->rs['count'] = $count;

        return return_json($this->rs);
    }
    #vip达成配置-列表配置
    public function becomeVipStandardListConfig(){

        $this->rs['data'] =thisServer::becomeVipStandardListConfig();

        return return_json($this->rs);
    }
    #vip达成配置-详情
    public function becomeVipStandardDetail(){

        $param = $this->getPost([
            ['id','int',0],
            ['vip_game_product_id','int',0],
            ['game_id','int',0],
        ]);

        $this->rs = array_merge($this->rs,thisServer::becomeVipStandardDetail($param));

        return return_json($this->rs);
    }
    #vip达成配置-详情配置
    public function becomeVipStandardDetailConfig(){

        $this->rs['data'] =thisServer::becomeVipStandardDetailConfig();

        return return_json($this->rs);
    }
    #vip达成配置-添加
    public function becomeVipStandardAdd(){

        $p_data = $this->getPost([
            ['id','int',0],
            ['vip_game_product_id','int',0],
            ['game_id','int',0],
            ['day_pay','int',0],
            ['thirty_day_pay','int',0],
            ['total_pay','int',0],
            ['static','int',0],
        ]);


        $res = thisServer::becomeVipStandardSave($p_data);

        if($res){
            $this->rs['msg'] = '操作成功';
        }else{
            $this->rs['msg'] = '操作成功';
            $this->rs['code'] = 1;
        }

        return return_json($this->rs);
    }
    #vip达成配置-修改
    public function becomeVipStandardEdit(){


        return $this->becomeVipStandardAdd();
    }

    #区服分配管理-列表
    public function serverManageList(){
        $p_data = $this->getPost([
            ['page', 'int', 1],
            ['limit', 'int', 20],
            ['game_product_id', 'trim', ''],
            ['admin_user_id', 'int', 0],
        ]);

        $this->rs = array_merge($this->rs,thisServer::serverManageList($p_data));

        return return_json($this->rs);
    }
    #区服分配管理-列表配置
    public function serverManageListConfig(){

        $this->rs = array_merge($this->rs,thisServer::serverManageListConfig());

        return return_json($this->rs);
    }
    #区服分配管理-详情
    public function serverManageDetail(){

        $id = $this->req->post('id/d',0);

        $this->rs['data'] = thisServer::serverManageDetail($id);

        return return_json($this->rs);
    }
    #区服分配管理-添加
    public function serverManageAdd(){
        $code = [
            1=>'请选择游戏产品！！',2=>'请选择vip专员',3=>'请正确输入区服区间id',4=>'操作失败'
        ];

        $p_data = $this->getPost([
            ['vip_game_product_id','int',0],
            ['admin_user_id','int',0],
            ['server_prefix','trim',''],
            ['server_suffix','trim',''],
            ['start_server_id','int',0],
            ['end_server_id','int',0],
            ['special_server_id','trim',''],
            ['is_server_remainder','int',0],
            ['remove_server_id','trim',0],
            ['id','int',0]
        ]);

        if (empty($p_data['vip_game_product_id'])) {
            $this->rs['code'] = 1;
            $this->rs['msg'] = $code[1];
            return return_json($this->rs);
        }
        if (empty($p_data['admin_user_id'])) {
            $this->rs['code'] = 2;
            $this->rs['msg'] = $code[2];
            return return_json($this->rs);
        }
        if (
            (empty($p_data['special_server_id'])
                && (empty($p_data['start_server_id']) || empty($p_data['end_server_id']))
            )
            || ((int)$p_data['end_server_id'] < (int)$p_data['start_server_id'])
        ) {
            $this->rs['code'] = 3;
            $this->rs['msg'] = $code[3];
            return return_json($this->rs);
        }

        $serverIdsAll = thisServer::getVASGRServerId($p_data);

        $count = ceil(count($serverIdsAll)/thisServer::$AUGSLimit);

        $res = thisServer::serverManageSave($p_data);

        if($res){
            $this->rs['data'] = ['id'=>$res,'count'=>$count];
            return return_json($this->rs);
        }else{
            $this->rs['code'] = 4;
            $this->rs['msg'] = $code[4];
            return return_json($this->rs);
        }
    }
    #区服分配管理-修改
    public function serverManageEdit(){
        return $this->serverManageAdd();
    }
    #区服分配管理-删除
    public function serverManageDelete(){

        $id  = $this->request->post('id/d', 0);

        if( empty($id) ){
            $this->rs['code'] = 1;
            $this->rs['msg'] = '参数错误!';
            return return_json($this->rs);
        }

        $res = thisServer::serverManageDelete($id);

        if(!empty($res)){
            $this->rs['msg'] = '删除成功!';
        }else {
            $this->rs['code'] = 2;
            $this->rs['msg'] = '操作失败，请联系技术!';
        }

        return return_json($this->rs);
    }
    #区服分配管理-批量转移
    public function serverManageChangeAdmin(){
        $p_data = $this->getPost([
            ['idStr','array',[]],
            ['ascription_vip','int',0],
            ['ascription_vip_old','int',0],
            ['is_save_admin','int',0],
        ]);

        $this->rs = array_merge($this->rs,thisServer::serverManageChangeAdmin($p_data));

        return return_json($this->rs);
    }
    #区服分配管理-关联分配区服数据列表
    public function adminGameServerList(){
        $p_data = $this->getPost([
            ['page','int',1],
            ['limit','int',10],
            ['ascription_game_server_id','int',0],
            ['admin_user_id','int',0],
            ['vip_game_product_id','int',0],
        ]);

        $this->rs = array_merge($this->rs,thisServer::adminGameServerList($p_data));

        return return_json($this->rs);
    }

    #营销kpi配置-配置
    public function sellerKpiConfig(){

        $this->rs['data'] = thisServer::sellerKpiConfig();

        return return_json($this->rs);
    }

    public function getKpiConfigList(){
        $p_data = $this->getPost([
            ['page','int',1],
            ['limit','int',20],
            ['list_group_id','int',0],
            ['list_admin_id','int',0],
            ['list_game_product_id','trim',0],
            ['list_month','trim',''],
        ]);

        $p_data_n = [];

        foreach ($p_data as $k => $v) {
            $this_key = str_replace('list_', '', $k);
            $p_data_n[$this_key] = $v;
        }

        $page = $this->request->get('page','int',1);

        $limit = $this->request->get('limit','int',20);

        if($p_data_n['month']){
            $p_data_n['kpi_date'] = strtotime($p_data_n['month']);
        }else{
            $p_data_n['kpi_date'] = strtotime(date('Y-m'));
        }

        unset($p_data_n['month']);

        if($p_data_n['game_product_id']){
            $game_product_id_param = explode('_',$p_data_n['game_product_id']);
            if(count($game_product_id_param) == 2){
                $p_data_n['product_id'] = $game_product_id_param[1];
                $p_data_n['platform_id'] = $game_product_id_param[0];
            }
        }
        unset($p_data_n['game_product_id']);

        list($list,$count)= thisServer::getKpiConfigListGroup($p_data_n,$page,$limit);

        if($list){
            $this->rs['data'] = $list;
            $this->rs['count'] = $count;
            return return_json($this->rs);
        }else{
            $this->rs['msg'] = '没有数据';
            $this->rs['code'] = 1;
            return return_json($this->rs);
        }
    }

    public function getKpiConfigByMonth(){
        $month = $this->request->post('month');

        $this->rs['data'] = thisServer::getKpiConfigByMonth($month);//游戏列表

        return return_json($this->rs);
    }

    /**
     *营销kpi配置-更新用户kpi
     */
    public function setUsersKpi(){

        $p_data = $this->getPost([
            ['id','int',0],
            ['group_id','int',0],
            ['admin_id','int',0],
            ['month','trim',''],
            ['kpi_value',null,0],
            ['game_product_id','trim',''],
        ]);

        $p_data['kpi_date'] = strtotime($p_data['month']);
        unset($p_data['month']);

        $game_product_id_param = explode('_',$p_data['game_product_id']);
        $p_data['product_id'] = $game_product_id_param[1];
        $p_data['platform_id'] = $game_product_id_param[0];
        unset($p_data['game_product_id']);

        $res = thisServer::setUsersKpi($p_data);

        if($res){
            $this->rs['msg'] = '更新成功';
        }else{
            $this->rs['msg'] = '更新失败';
            $this->rs['code'] = 1;
        }

        return return_json($this->rs);
    }

    /**
     *营销kpi配置-获取用户kpi
     */
    public function getUsersKpi(){

        $p_data = $this->getPost([
            ['group_id','int',0],
            ['admin_id','int',0],
            ['month','trim',''],
            ['game_product_id','trim',''],
        ]);

        $p_data['kpi_date'] = strtotime($p_data['month']);
        unset($p_data['month']);

        $res = thisServer::getUsersKpi($p_data);

        if($res){
            $this->rs['msg'] = '获取成功';
            $this->rs['data'] = $res;
        }else{
            $this->rs['msg'] = '获取失败';
            $this->rs['code'] = 1;
        }

        return return_json($this->rs);
    }
    /**
     *营销kpi配置-获取用户已分配游戏列表
     */
    public function getGameListByUid(){

        $info = thisServer::getGameListByUid($this->request->post('uid/d',0));

        if($info){
            $this->rs['data']=$info;
        }else{
            $this->rs['msg']='error';
            $this->rs['code']=1;
        }

        return return_json($this->rs);
    }

    public function sellerCommissionConfigList(){

        $p_data = $this->getPost([
            ['page','int',1],
            ['limit','int',20],
            ['money_min','float',0],
            ['money_max','float',0],
            ['start_time','trim',''],
            ['end_time','trim',''],
            ['game_product_id','int',-1],
            ['status','int',0],
        ]);

        if($p_data['start_time']){
            $p_data['start_time'] = strtotime($p_data['start_time']);
        }

        if($p_data['end_time']){
            $p_data['end_time'] = strtotime($p_data['end_time']);
        }

        list($list,$count) = thisServer::getSCCList($p_data);

        $this->rs['data'] = $list;
        $this->rs['count'] = $count;

        return return_json($this->rs);
    }

    public function sellerCommissionConfigListConfig(){

        $this->rs['data'] = thisServer::sellerCommissionConfigListConfig();

        return return_json($this->rs);
    }

    public function sellerCommissionConfigDetail(){

        $id = $this->request->post('id/d',0);


        $this->rs['data'] = thisServer::sellerCommissionConfigDetail($id);

        return return_json($this->rs);
    }

    public function sellerCommissionConfigAdd(){
        $p_data = $this->getPost([
            ['id','int',0],
            ['game_product_id','int',0],
            ['money_min','float',0],
            ['money_max','float',0],
            ['commission_rate','trim',0],
            ['commission_num','trim',0],
            ['start_time','trim',''],
            ['end_time','trim',''],
            ['status','int',0],
            ['desc','trim',''],
        ]);

        if($p_data['start_time']){
            $p_data['start_time'] = strtotime($p_data['start_time']);
        }

        if($p_data['end_time']){
            $p_data['end_time'] = strtotime($p_data['end_time']);
        }

        $this->rs = array_merge($this->rs,thisServer::sCCSave($p_data));

        return return_json($this->rs);
    }

    public function sellerCommissionConfigEdit(){
        return $this->sellerCommissionConfigAdd();
    }

    #vip提成配置-删除
    public function sellerCommissionConfigDel(){

        $id = $this->request->post('id/d',0);

        $res = thisServer::sCCDel($id);

        if($res){
            $this->rs['msg'] = '操作成功';
        }else{
            $this->rs['code'] = 1;
            $this->rs['msg'] = '操作失败';
        }

        return return_json($this->rs);
    }
}
