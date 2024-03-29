<?php
/**
 * 系统
 */
namespace common\server\Vip;

use common\base\BasicServer;
use common\model\db_customer\QcConfig;
use common\model\db_statistic\AdminUserGameServer;
use common\model\db_statistic\BecomeVipStandard;
use common\model\db_statistic\GameProduct;
use common\model\db_statistic\PlatformGameInfo;
use common\model\db_statistic\ProductPropConfig;
use common\model\db_statistic\SellerCommissionConfig;
use common\model\db_statistic\SellerKpiConfig;
use common\model\db_statistic\VipAscriptionGameServerRange;
use common\model\db_statistic\VipUserInfo;
use common\model\gr_chat\Admin;
use common\server\ListActionServer;
use common\server\Statistic\GameProductServer;
use common\server\SysServer;
use PhpOffice\PhpSpreadsheet\Reader\Csv;
use PhpOffice\PhpSpreadsheet\Reader\Xls;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;

class ConfigServer extends BasicServer
{
    public static $AUGSLimit = 300;

    /**
     * 列表数据
     * @param $params
     * @return array
     */
    public static function getRebateConfigList($params)
    {
        $page = getArrVal($params,'page',1);
        $limit = getArrVal($params,'limit',20);

        $where = getDataByField($params,['product_name','title','content','prop_id','status'],true);

        $model = new ProductPropConfig();

        $count = $model->where($where)->count();

        if(!$count){
            return [[],0];
        }

        $listObj = $model->where($where);

        if($page && $limit){
            $listObj = $listObj->page($page,$limit);
        }

        $list = $listObj->order('id desc')->select()->toArray();

        $list = self::getAdminName($list);

        return [$list,$count];
    }

    /**
     * 组装后台用户名称及状态
     * @param array $data
     * @return array|mixed
     */
    public static function getAdminName($data = array())
    {
        if (empty($data)) return [];
        $adminUserInfo = SysServer::getAdminListCache();
        foreach ($data as &$v) {
            $v['action'] = ListActionServer::checkRebateConfigListAction($v);
            $v['add_admin_name'] = $adminUserInfo[$v['add_admin_id']]['realname'] ?? '';
            $v['edit_admin_name'] = $adminUserInfo[$v['edit_admin_id']]['realname'] ?? '';
            $v['status'] = $v['status'] == 1 ? '开启' : '关闭';
        }
        return $data;
    }

    /**
     * 导入文件数据处理
     * @param $this_file
     * @return array
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    public static function rebateConfigImport($this_file)
    {
        $code = [0=>'success',1=>'没有上传文件',2=>'不符合类型',3=>'没有数据',4=>'添加失败'];

        if(!$this_file) return ['code'=>1,'msg'=>$code[1]];

        $file_mimes = array(
            'text/x-comma-separated-values',
            'text/comma-separated-values',
            'application/octet-stream',
            'application/vnd.ms-excel',
            'application/x-csv',
            'text/x-csv',
            'text/csv',
            'application/csv',
            'application/excel',
            'application/vnd.msexcel',
            'text/plain',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        );

        $file_info = $this_file->getInfo();

        if(!in_array($file_info['type'], $file_mimes)) {
            return ['code'=>2,'msg'=>$code[2]];
        }

        $file_path_info = pathinfo($file_info['name']);

        if('csv' == $file_path_info['extension']) {
            $reader = new Csv();
        } elseif ('xls' == $file_path_info['extension']) {
            $reader = new Xls();
        }else {
            $reader = new Xlsx();
        }

        $spreadsheet = $reader->load( $file_info['tmp_name'] );
        $sheetData = $spreadsheet->getActiveSheet()->ToArray();

        if (count($sheetData) <2) return ['code'=>3,'msg'=>$code[3]];

        $add_data = [];
        $model = new ProductPropConfig();

        foreach ($sheetData as $k => $v) {
            $tmp = [];
            if ($k ==0 || count($v) < 4 || empty($v[0]) || empty($v[1]) || empty($v[2]) || empty($v[3])) continue;
            $tmp['product_name'] = $v[0];
            $tmp['title']        = $v[1];
            $tmp['title_md5']    = md5($v[1]);
            $tmp['content']      = $v[2];
            $tmp['prop_id']      = $v[3];
            $tmp['add_admin_id'] = self::$user_data['id'];
            //$tmp['add_time']     = $time;

            $add_data[] = $tmp;
        }

        $res = $model->saveAll($add_data);

        if($res){
            return ['count'=>$res];
        }else{
            return ['code'=>4,'msg'=>$code[4]];
        }

        return $model->saveAll($add_data);
    }
    /**
     * @param array $ids
     * @return ProductPropConfig|int
     */
    public static function editRebateStatus($ids=array())
    {
        $result = 0;

        if (empty($ids) && is_array($ids)) return $result;

        $model = new ProductPropConfig();

        return $model->where('id','in',$ids)->update([
            'edit_admin_id'=>self::$user_data['id'],
            'status'=>2,
        ]);
    }

    public static function becomeVipStandardList($param){

        $page = getArrVal($param,'page',1);
        $limit = getArrVal($param,'limit',20);

        $model = new BecomeVipStandard();

        $where = getDataByField($param,['static'],true,'bvs.');

        if(!empty($param['platform_id'])){
            $where[] = getWhereDataArr($param['platform_id'],"gp.platform_id");
        }

        if(!empty($param['p_p'])){
            $where[] = getWhereDataArr($param['p_p'],"CONCAT(gp.platform_id,'_',gp.product_id)");
        }

        if(!empty($param['p_g'])){
            $where[] = getWhereDataArr($param['p_g'],"CONCAT(gp.platform_id,'_',bvs.game_id)");
        }

        $field = '
            bvs.*
            ,gp.platform_id
            ,gp.product_name
        ';

        $count = $model
            ->alias('bvs')
            ->join('game_product gp','gp.id = bvs.vip_game_product_id')
            ->where(setWhereSql($where,''))
            ->fetchSql()
            ->count();

        $listObj = $model->alias('bvs')->field($field)->join('game_product gp','gp.id = bvs.vip_game_product_id')->where(setWhereSql($where,''));

        if($page && $limit){
            $listObj = $listObj->page($page,$limit);
        }

        $list = $listObj->order('id desc')->select();

        if($list){

            $list = $list->toArray();

            $admin_list = SysServer::getAdminListCache();
            $platform_list = SysServer::getPlatformList();

            foreach ($list as $k => $v) {
                $list[$k]['action'] = ListActionServer::checkBecomeVipStandardListAction($v);

                $list[$k]['add_admin_id_name'] = isset($admin_list[$v['add_admin_id']])?$admin_list[$v['add_admin_id']]['name']:'';
                $list[$k]['edit_admin_id_name'] = isset($admin_list[$v['edit_admin_id']])?$admin_list[$v['edit_admin_id']]['name']:'';

                $list[$k]['game_id_str'] = '默认';

                if($v['game_id']){

                    $this_info = GameProductServer::getPlatformGameInfoByGameId($v['game_id'],$v['platform_id']);

                    if($this_info){
                        $list[$k]['game_id_str'] = $this_info->game_name;
                    }
                }

                $this_platform = getArrVal($platform_list,$v['platform_id'],[]);

                if($this_platform) $list[$k]['product_name'].="({$this_platform['name']})";

                $list[$k]['static_str'] = $v['static'] == 1?'开启':'关闭';
            }
        }

        return [$list,$count];
    }

    public static function becomeVipStandardListConfig(){

        $search_config = [
            'product'=>['status'=>1],
            'game'=>['status'=>1],
        ];

        return compact('search_config');
    }

    public static function becomeVipStandardDetail($param){

        $code = [1=>'参数不足'];

        $detail = [
            'static'=>1,
            'id'=>0,
        ];

        if( !empty($param['id']) && ( !empty($param['vip_game_product_id']) || !empty($param['game_id']) )){
            return ['code'=>1,'msg'=>$code[1]];
        }

        $where = getDataByField($param,['id','vip_game_product_id','game_id'],true);

        if($where){
           $model = new BecomeVipStandard();
           $info = $model->where($where)->find();

           if($info){
               $detail = $info->toArray();
           }
        }

        return ['data'=>$detail];
    }

    public static function becomeVipStandardDetailConfig(){

        $config = [];

        $config['product_list'] = SysServer::getGameProductByPower();


        return compact('config');
    }

    public static function becomeVipStandardSave($p_data){
        $id = isset($p_data['id'])?$p_data['id']:0;

        $model = new BecomeVipStandard();

        $common_field = [
            'vip_game_product_id'
            ,'day_pay'
            ,'game_id'
            ,'thirty_day_pay'
            ,'total_pay'
            ,'static'
        ];

        if($id){

            $info = $model->where('id',$id)->find();

            if(!$info){
                return false;
            }
            //筛选要更新数据
            $save_data = getDataByField($p_data,$common_field);
            $save_data['edit_admin_id'] = self::$user_data['id'];

            $res = $info->save($save_data);

            if(!$res) return false;
        }else{

            $add_data = getDataByField($p_data,$common_field);

            $add_data['edit_admin_id'] = $add_data['add_admin_id'] = self::$user_data['id'];

            $res = $model->create($add_data);

            if(!$res) return false;
        }

        return true;
    }

    public static function serverManageList($param){

        $page = getArrVal($param,'page',1);
        $limit = getArrVal($param,'limit',20);

        $res = [];

        $where = getDataByField($param,['admin_user_id','static'],true);

        if (!empty($param['game_product_id'])) {
            $where[] = getWhereDataArr($param['game_product_id'],'vip_game_product_id');
        }

        $model = new VipAscriptionGameServerRange();

        $count = $model->where(setWhereSql($where,''))->count();

        if(!$count){
            return $res;
        }

        $res['count'] = $count;

        $listObj = $model->where(setWhereSql($where,''));

        if($page && $limit){
            $listObj = $listObj->page($page,$limit);
        }

        $list = $listObj->order('id desc')->select()->toArray();


        if($list){
            $becomeVipStandard = (new BecomeVipStandard)->select()->toArray();
            $tmp = [];
            foreach ($becomeVipStandard as $v) {
                $tmp[$v['vip_game_product_id']] = $v;
            }
            $server_remainder_arr = [
                0 =>'--',
                1 =>'单数',
                2 =>'双数',
            ];
            $admin_list = SysServer::getAdminListCache();
            $gameProductList = SysServer::getGameProductCache();

            foreach ($list as &$v) {
                $v['action'] = ListActionServer::checkAdminUserGameServerListAction($v);

                $v['admin_user_name'] = $admin_list[$v['admin_user_id']]['realname'] ?? '';
                $v['add_admin_name'] = $admin_list[$v['add_admin_id']]['realname'] ?? '';
                $v['edit_admin_name'] = $admin_list[$v['edit_admin_id']]['realname'] ?? '';

                $v['game_product_name'] = $gameProductList[$v['vip_game_product_id']]['name'] ?? '';
                $v['blend_field'] = '';
                if(isset($tmp[$v['vip_game_product_id']])){
                    $this_info = $tmp[$v['vip_game_product_id']];
                    $v['blend_field'] .= '默认-单日累充：'.$tmp[$v['vip_game_product_id']]['day_pay'].';近30天充值金额：'.$tmp[$v['vip_game_product_id']]['thirty_day_pay'].
                        ';历史累充金额：'.$tmp[$v['vip_game_product_id']]['total_pay'].';';
                }
                $v['blend_field'] .= '区服前缀：'.$v['server_prefix'].';区服后缀'.$v['server_suffix'].
                    ';区服区间：'.$v['start_server_id'].'-'.$v['end_server_id'].';区服余数：'.$server_remainder_arr[$v['is_server_remainder']].
                    ';排除的区服：'.$v['remove_server_id'].';特殊区服：'.$v['special_server_id'];
            }
        }

        $res['data'] = $list;

        return $res;
    }

    public static function serverManageListConfig(){

        $config = [];
        $config['game_product_list'] = SysServer::getGameProductByPower();
        $config['admin_list'] = SysServer::getUserListByAdminInfo(self::$user_data,[
            'group_type'=>[QcConfig::USER_GROUP_VIP],
            'not_group_type'=>[QcConfig::USER_GROUP_QC],
            'is_active'=>1
        ]);


        $res['data'] = compact('config');

        return $res;
    }

    public static function serverManageDetail($id){

        $config = [];
        $config['admin_list'] = SysServer::getUserListByAdminInfo(self::$user_data,['group_type'=>[QcConfig::USER_GROUP_VIP,'is_active'=>1]]);
        $gameProductArr = SysServer::getGameProductByPower();

        $detail = [
            'id'=>0,
            'vip_game_product_id'=>0,
        ];

        if($id){
            $model = new VipAscriptionGameServerRange();
            $detailObj = $model->where('id',$id)->find();
            if($detailObj){
                $detail = $detailObj->toArray();
            }
        }
        $gameProductList = [];

        foreach ($gameProductArr as $v) {
            $this_v=[];
            $this_v['name'] = $v['name'];
            $this_v['value'] = $v['id'];
            if($v['id'] == $detail['vip_game_product_id']){
                $this_v['selected'] = true;
            }
            $gameProductList[] = $this_v;
        }

        $config['game_product_list'] = $gameProductList;

        return compact('config','detail');
    }

    public static function serverManageSave($p_data){
        $model = new VipAscriptionGameServerRange();

        $id = getArrVal($p_data,'id',0);

        $p_data['static'] = 0;

        if($id){

            $p_data['edit_admin_id'] = self::$user_data['id'];

            $res = $model->where('id',$id)->update($p_data);

            if(!$res){
                $id = 0;
            }
        }else{
            $p_data['add_admin_id'] = self::$user_data['id'];
            $p_data['add_time'] = time();
            $id = $model->insertGetId($p_data);
        }

        return $id;
    }

    public static function adminUserGameServerSave($ags_id){

        $code = [
            0=>'ok',1=>'end',2=>'配置不存在',3=>'任务已结束',4=>'没有需要执行区服'
        ];

        $VipAscriptionGameServerRange = new VipAscriptionGameServerRange();

        $info = $VipAscriptionGameServerRange->where(['id'=>$ags_id])->find();

        if(!$info){
            return ['code'=>2,'msg'=>$code[2]];
        }

        if($info->static == 2){
            return ['code'=>1,'msg'=>$code[1]];
        }

        $model = new AdminUserGameServer();

        $info_data = $info->toArray();

        $cache_name = 'getAUGSServerId_'.$info_data['id'];

        if($info_data['static'] == 0){

            $info->static = 1;
            $info->save();

            $serverIdsAll = self::getVASGRServerId($info_data);

            $model->execute(setDelSql('admin_user_game_server',['ascription_game_server_id'=>$info_data['id']],0));

        }else{
            $serverIdsAll = cache($cache_name);
        }

        if(!$serverIdsAll){
            return ['code'=>4,'msg'=>$code[4]];
        }
        $flag = 0;
        $add_data = [];
        foreach ($serverIdsAll as $k => $v){

            if($k >=self::$AUGSLimit){
                break;
            }

            unset($serverIdsAll[$k]);

            $where = [];
            $where['vip_game_product_id'] = $info_data['vip_game_product_id'];
            $where['server_id'] = $v;

            $this_info = $model->where($where)->count();

            if($this_info){
                continue;
            }

            $add_data[] =[
                'ascription_game_server_id'=>$info_data['id'],
                'admin_user_id'=>$info_data['admin_user_id'],
                'vip_game_product_id'=>$info_data['vip_game_product_id'],
                'server_id'=>$v,
            ];

            $flag++;
        }

        if($add_data){
            $res = $model->execute(
                setIntoSql(
                    'admin_user_game_server',
                    [
                        'ascription_game_server_id',
                        'admin_user_id',
                        'vip_game_product_id',
                        'server_id',
                    ],
                    $add_data
                )
            );
        }

        if(!$serverIdsAll){
            cache($cache_name,null);
            $info->static = 2;
            $info->save();

            return ['code'=>1,'msg'=>$code[1]];
        }

        $serverIdsAll = array_values($serverIdsAll);

        cache($cache_name,$serverIdsAll,3600*2);

        return ['code'=>0,'msg'=>$code[0]];
    }

    public static function getVASGRServerId($info_data){

        $serverIdsAll = [];

        if (!empty($info_data['special_server_id'])) {
            $tmpSpecialArr = explode(',',$info_data['special_server_id'] );
            $serverIdsAll = array_merge($serverIdsAll,$tmpSpecialArr);
        }
        $i = $info_data['start_server_id'];
        for ($i; $i <= $info_data['end_server_id']; $i++) {
            if (!empty($info_data['is_server_remainder'])) {
                if ($info_data['is_server_remainder'] == 1 && $i%2!=0) {
                    //单数
                    $tmpStr = '';
                    $tmpStr = trim($info_data['server_prefix'].$i.$info_data['server_suffix']);
                    array_push($serverIdsAll,$tmpStr);
                }elseif ($info_data['is_server_remainder'] == 2 && $i%2 ==0) {
                    //双数
                    $tmpStr = '';
                    $tmpStr = trim($info_data['server_prefix'].$i.$info_data['server_suffix']);
                    array_push($serverIdsAll,$tmpStr);
                }else {
                    continue;
                }
            }else {
                $tmpStr = trim($info_data['server_prefix'].$i.$info_data['server_suffix']);
                array_push($serverIdsAll,$tmpStr);
            }

        }

        if (!empty($info_data['remove_server_id'])) {
            $tmpArr = explode(',', $info_data['remove_server_id']);
            $serverIdsAll = array_diff($serverIdsAll, $tmpArr);
        }
        $serverIdsAll = array_unique($serverIdsAll);

        return $serverIdsAll;
    }

    public static function serverManageDelete($id){

        $res = $re = false;
        $AdminUserGameServerModel = new AdminUserGameServer();
        $re = $AdminUserGameServerModel->where('ascription_game_server_id',$id)->delete();
        if ($re) {
            $model = new VipAscriptionGameServerRange();
            $res = $model->where('id',$id)->delete();
        }

        return $res;
    }

    public static function serverManageChangeAdmin($param){
        $code = [
            0=>'success',1=>"操作失败",2=>'参数错误'
        ];

        $gameServerIds = getArrVal($param,'idStr',[]);
        $ascription_vip = getArrVal($param,'ascription_vip',0);
        $ascription_vip_old = getArrVal($param,'ascription_vip_old',0);
        $is_save_admin = getArrVal($param,'is_save_admin',0);

        if (empty($gameServerIds) || !is_array($gameServerIds)) return ['code'=>2,'msg'=>$code[2]];

        $smg = '';
        $successTransfer = $failTransfer = $successUpdate = $failUpdate = [];

        foreach ($gameServerIds as $value) {
            $r = self::__action_batch_transfer_server($value, $ascription_vip, $ascription_vip_old);

            if ($r) {
                $successTransfer[] = $value;
                $smg .= 'vip 用户转移成功id：'.$value;
                if($is_save_admin){
                    $res = self::__edit_batch_transfer_server($value, $ascription_vip);
                    if ($res) {
                        $successUpdate[] = $value;
                    }else {
                        $failUpdate[] = $value;
                    }
                }else{
                    $successUpdate[] = $value;
                }
            }else {
                $failTransfer[] = $value;
            }
        }
        $smg .= "<br>vip 已经转移成功的id:".implode(',', $successTransfer).",<br>转移失败id：".implode(',', $failTransfer);
        $smg .= "<br>后台记录更新成功id:".implode(',', $successUpdate).",<br>后台记录更新失败id:".implode(',',$failUpdate );

        if (empty($successTransfer) || empty($successUpdate)) {
            $result = [
                'code' =>1,
                'msg' =>$smg
            ];
        }else {
            $result = [
                'code' =>0,
                'msg' =>$smg
            ];
        }
        return $result;
    }

    /**
     * 根据区服分配转移用户分配
     * @param int $product_game_server_id 区服分配配置id
     * @param int $ascription_vip 新分配客服id
     * @param int $ascription_vip_old 旧分配客服id
     * @return \common\model\db_statistic\ResultSet|bool
     */
    protected static function __action_batch_transfer_server($product_game_server_id = 0, $ascription_vip = 0, $ascription_vip_old = 0)
    {
        $result = false;
        $serverIds = [];

        if (empty($product_game_server_id) || empty($ascription_vip)) return $result;
        //获取对应分配的区服信息
        $AdminUserGameServerModel = new AdminUserGameServer();

        $ascriptionServerList = $AdminUserGameServerModel->where('ascription_game_server_id',$product_game_server_id)->select()->toArray();

        if (!empty($ascriptionServerList)) {
            $serverIds = array_column($ascriptionServerList , 'server_id');
        }

        $vip_game_product_id = $ascriptionServerList[0]['vip_game_product_id'];
        if($ascription_vip_old){
            $admin_user_id = $ascription_vip_old;
        }else{
            $admin_user_id = $ascriptionServerList[0]['admin_user_id'];
        }

        //获取游戏产品列表
        $GameProductModel = new GameProduct();
        $productGameInfo = $GameProductModel->where('id',$vip_game_product_id)->find();

        if(!$productGameInfo) return $result;

        $platform_id = $productGameInfo->platform_id;
        $product_id  = $productGameInfo->product_id;

        $platformGameWhere['platform_id'] = $platform_id;
        $platformGameWhere['product_id'] = $product_id;

        $PlatformGameInfoModel = new PlatformGameInfo();
        $platformGameIds = $PlatformGameInfoModel->where($platformGameWhere)->column('game_id');


        if (empty($platform_id) || empty($platformGameIds) || empty($serverIds)) return $result;

        $model = new VipUserInfo();

        //判断是否已经生成、存在已经分配了的用户
        $res = $model->findVipUserByGameAndAscription(
            $admin_user_id,
            $platform_id,
            $platformGameIds,
            $serverIds
        );

        if (empty($res)) {
            //没有就直接返回true
            return true;
        }

        $result = $model->batchTransferAscription(
            $ascription_vip,
            $admin_user_id,
            $platform_id,
            $platformGameIds,
            $serverIds
        );
        return $result;

    }

    /**
     * 更新vip_ascription_game_server_range、admin_user_game_server
     * @param $ascription_game_server_id
     * @param $ascription_vip
     * @return false|\Phalcon\Mvc\Model\Resultset\Simple
     */
    protected static function __edit_batch_transfer_server($ascription_game_server_id, $ascription_vip)
    {
        $result = false;
        if (empty($ascription_game_server_id) || empty($ascription_vip)) return $result;
        //批量转移
        $model = new AdminUserGameServer();
        $result = $model->updateAdminIdByAscId($ascription_vip, $ascription_game_server_id);

        if ($result) {
            //更新vip_ascription_game_server_range
            $model = new VipAscriptionGameServerRange();
            $data = [
                'admin_user_id' => $ascription_vip,
                'edit_admin_id' => self::$user_data['id'],
                'edit_time' =>time()
            ];
            $result = $model->where('id',$ascription_game_server_id)->update($data);
        }

        return  $result;
    }

    public static function adminGameServerList($param){

        $where = getDataByField($param,['ascription_game_server_id','admin_user_id','vip_game_product_id'],true);

        $ascription_game_server_id = getArrVal($param,'ascription_game_server_id',0);

        $c_detail = [];
        if($ascription_game_server_id){
            $VipAscriptionGameServerRange = new VipAscriptionGameServerRange();

            $c_detail = $VipAscriptionGameServerRange->where(['id'=>$ascription_game_server_id])->find();

            if($c_detail){
                $c_detail = $c_detail->toArray();
                $is_server_remainder_arr = [
                    0=>'全部',
                    1=>'单',
                    2=>'双',
                ];
                $c_detail['is_server_remainder_str'] = getArrVal($is_server_remainder_arr,$c_detail['is_server_remainder']);
                $GameProduct = new GameProduct();

                $g_p_info = $GameProduct->field('platform_id,product_name')->where(['id'=>$c_detail['vip_game_product_id']])->find();

                if($g_p_info){
                    $g_p_info = $g_p_info->toArray();
                    $platform_list = SysServer::getPlatformList();
                    $platform_info = getArrVal($platform_list,$g_p_info['platform_id'],[]);
                    $g_p_info['platform_id_str'] = getArrVal($platform_info,'name','');

                    $admin_list = SysServer::getAdminListCache();

                    $admin_info = getArrVal($admin_list,$c_detail['admin_user_id'],[]);

                    $g_p_info['admin_user_id_str'] = getArrVal($admin_info,'name','');

                    $c_detail = array_merge($c_detail,$g_p_info);

                }
            }
        }
        $model = new AdminUserGameServer();

        $count = $model->where($where)->count();

        $res = [
            'data'=>[],
            'count'=>[],
            'other_info'=>$c_detail,
        ];

        if(!$count || !$c_detail){
            return $res;
        }

        $res['count'] = $count;

        $list = $model->where($where)
            ->orderRaw('server_id*1 asc,id asc')
//            ->fetchSql()
            ->select()
            ->toArray()
        ;

        $new_list = [];
        if($list){
            $flag = 1;
            foreach ($list  as $k => $v){

                $v['server_id_num'] = $v['server_id'];
                if(!empty($c_detail['server_prefix'])) $v['server_id_num'] = str_replace($c_detail['server_prefix'],'',$v['server_id_num']);
                if(!empty($c_detail['server_suffix'])) $v['server_id_num'] = str_replace($c_detail['server_suffix'],'',$v['server_id_num']);


                if(isset($new_list[$flag])){
                    $this_server_num_new = $v['server_id_num'];

                    if($c_detail['is_server_remainder']){
                        $this_server_num_new-=2;
                    }else{
                        $this_server_num_new-=1;
                    }
                    if($this_server_num_new != $new_list[$flag]['server_end']){
                        $flag++;
                    }
                }
                self::doAdminGameServerList($new_list[$flag],$v,$c_detail);
            }
        }

        $res['data'] = $new_list;

        return $res;
    }

    protected static function doAdminGameServerList(&$item,$v,$c_detail){
        if(!isset($item)){

            if($c_detail['is_server_remainder'] && $v['server_id_num'] % $c_detail['is_server_remainder']){
                $first_server_id_num = $v['server_id_num']+1;
            }else{
                $first_server_id_num = $v['server_id_num'];
            }

            $item = [
                'server_start'=>$first_server_id_num,
                'server_end'=>$first_server_id_num,
                'count'=>1,
            ];
        }else{
            if($v['server_id_num']<$item['server_start']){
                $item['server_start'] = $v['server_id_num'];
            }

            if($v['server_id_num']>$item['server_end']){
                $item['server_end'] = $v['server_id_num'];
            }

            $item['count']++;
        }
    }

    public static function sellerKpiConfig(){

        $form_data = [];
        $form_data['list_month'] = date('Y-m');

        $group_list = SysServer::getAdminGroupListShow();

        $admin_list = SysServer::getUserListByAdminInfo(self::$user_data,['group_type'=>[QcConfig::USER_GROUP_VIP,'is_active'=>1]]);

        $product_list = SysServer::getGameProductByPower();

        return compact(
            'form_data'
            ,'group_list'
            ,'admin_list'
            ,'product_list'
        );
    }

    /**
     * 营销kpi配置-获取用户每月kpi列表
     * @param array $p_data
     * @return mixed
     */
    public static function getKpiConfigListGroup(array $p_data){

        $where = getDataByField($p_data,['group_id','admin_id','product_id','platform_id','kpi_date'],true);

        if(self::$user_data['is_admin'] == 0){
            $where[] = getWhereDataArr(self::$user_data['platform_id'],'platform_id');
        }

        $SellerKpiConfig = new SellerKpiConfig();
        $group = 'group_id,admin_id';

        $info = $SellerKpiConfig->field('group_id,admin_id,kpi_date,sum(kpi_value) AS sum_kpi_value')
            ->where(setWhereSql($where,''))
            ->order('kpi_date DESC,id DESC')
            ->group($group)
            ->select()
            ->toArray();

        $admin_list = SysServer::getAdminListCache();
        $group_list = SysServer::getAdminGroupList();
        if($info){

            foreach ($info as $key => &$value) {

                $value['kpi_date_str'] = date('Y-m',$value['kpi_date']);
                $value['admin_id_str'] = isset($admin_list[$value['admin_id']])?$admin_list[$value['admin_id']]['name']:'未知';
                $value['group_id_str'] = isset($group_list[$value['group_id']])?$group_list[$value['group_id']]['name']:'未知';
                // dd($where);
                $this_where = $p_data;
                $this_where['admin_id'] = $value['admin_id'];
                $this_where['group_id'] = $value['group_id'];
                $this_where['kpi_date'] = $value['kpi_date'];
                $value['game_product_id_str'] = self::getGameProductNames($this_where)['name'];
            }
        }

        return [$info,count($info)];
    }

    /**
     * 根据条件获取kpi游戏大类名称
     * @param $where
     * @return array
     */
    public static function getGameProductNames($param){


        $where = getDataByField($param,['group_id','admin_id','kpi_date']);

        $SellerKpiConfig = new SellerKpiConfig();

        $list = $SellerKpiConfig->field('platform_id,product_id')->where(setWhereSql($where,''))->select()->toArray();

        $list_arr = [
            'name'=>'',
            'id'=>[],
            'p_p'=>[],
        ];
        if($list){
            $game_list = SysServer::getGameProductCache();//游戏列表
            $new_game_list = arrReSet($game_list,'id_str');
            $this_name = [];
            foreach ($list as $k => $v) {
                $list_arr['id'][] = $v['product_id'];
                $this_p_p_id = $v['platform_id']."_".$v['product_id'];
                $list_arr['p_p'][] = $this_p_p_id;
                if(isset($new_game_list[$this_p_p_id])){
                    $this_name[] = $new_game_list[$this_p_p_id]['name'];
                }
            }
            if($this_name){
                $list_arr['name'] = implode(',',$this_name);
            }
        }

        return $list_arr;
    }

    /**
     * 营销kpi配置-获取某月kpi设置
     * @param string $month Y-m
     * @return array|int|mixed|string|string[]
     */
    public static function getKpiConfigByMonth(string $month){

        $group_list = SysServer::getGroupListByAdminInfo(QcConfig::USER_GROUP_VIP,self::$user_data);//分组列表

        if(!$group_list){
            return [];
        }

        $admin_list = SysServer::getAdminListCache();//用户列表

        $where = [];

        if(self::$user_data['is_admin'] == 0){
            $where[] = getWhereDataArr(self::$user_data['platform_id'],'platform_id');
        }

        $where['kpi_date'] = strtotime($month);

        $SellerKpiConfig = new SellerKpiConfig();

        $kpi_list = $SellerKpiConfig->where($where)->select()->toArray();

        foreach ($group_list as $gl_k => &$gl_v) {
            $gl_v['admin_list'] = [];
            foreach ($admin_list as $al_k => $al_v) {

                if(in_array(QcConfig::USER_GROUP_QC,$al_v['user_group_type_arr'])){
                    continue;
                }
                if($al_v['position_grade'] > QcConfig::POSITION_GRADE_LEADER){
                    continue;
                }

                if(in_array($gl_v['id'], $al_v['group_id_arr'])){
                    $al_v['kpi_list'] = [];
                    $gl_v['admin_list'][$al_v['id']] = $al_v;
                }
            }
        }

        $game_list = SysServer::getGameProductCache();//游戏列表

        $new_game_list = arrReSet($game_list,'id_str');

        foreach ($kpi_list as $kl_k => $kl_v) {
            $kl_v['p_p_id'] = $kl_v['platform_id'].'_'.$kl_v['product_id'];
            $kl_v['game_name'] = $new_game_list[$kl_v['p_p_id']]['name'];
            $group_list[$kl_v['group_id']]['admin_list'][$kl_v['admin_id']]['kpi_list'][] = $kl_v;
        }

        $group_list = array_values($group_list);
        return $group_list;
    }

    /**
     * 营销kpi配置-更新用户kpi
     * @param array $p_data
     * @return false
     */
    public static function setUsersKpi(array $p_data){
        $where = [];

        if(isset($p_data['id']) && $p_data['id']){
            $where['id'] = $p_data['id'];
        }else{
            if(!isset($p_data['admin_id']) || !$p_data['admin_id']){
                return false;
            }

            if(!isset($p_data['product_id']) || !$p_data['product_id']){
                return false;
            }

            if(!isset($p_data['platform_id']) || !$p_data['platform_id']){
                return false;
            }

            if(!isset($p_data['kpi_date']) || !$p_data['kpi_date']){
                return false;
            }

            $where['admin_id'] = $p_data['admin_id'];
            $where['kpi_date'] = $p_data['kpi_date'];
            $where['product_id'] = $p_data['product_id'];
            $where['platform_id'] = $p_data['platform_id'];

        }

        $SellerKpiConfig = new SellerKpiConfig();

        $info = $SellerKpiConfig->where($where)->find();
        unset($p_data['id']);

        if($info){
            $up_data = $p_data;
            $up_data['edit_admin_id'] = self::$user_data['id'];
            $res = $info->save($up_data);
        }else{
            $add_data = $p_data;
            $add_data['add_admin_id'] = self::$user_data['id'];

            $res = $SellerKpiConfig->create($add_data);
        }

        return $res;
    }
    /**
     * 营销kpi配置-获取用户kpi
     * @param array $p_data
     * @return array|false
     */
    public static function getUsersKpi(array $p_data){
        $where = [];

        if(!isset($p_data['admin_id']) || empty($p_data['admin_id']) ){
            return false;
        }

        if(!isset($p_data['game_product_id']) || empty($p_data['game_product_id']) ){
            return false;
        }

        if(!isset($p_data['kpi_date']) || !$p_data['kpi_date']){
            return false;
        }

        $game_product_id_param = explode('_',$p_data['game_product_id']);

        $where['product_id'] = $game_product_id_param[1];
        $where['platform_id'] = $game_product_id_param[0];

        $where['admin_id'] = $p_data['admin_id'];
        $where['kpi_date'] = $p_data['kpi_date'];

        $SellerKpiConfig = new SellerKpiConfig();

        $info = $SellerKpiConfig->where(setWhereSql($where,''))->find();

        if($info){
            return $info->toArray();
        }

        return [];
    }
    /**
     * @param $uid
     * @return array
     */
    public static function getGameListByUid($uid){


        $game_list = SysServer::getGameProductCache();

        $Admin = new Admin();

        $info = $Admin->where(['id'=>$uid])->find();

        $new_game_list = [];

        if($info){
            $user_game_list = explode(',', $info->vip_game_product_ids);

            foreach ($game_list as $k => $v) {
                if(in_array($v['id'], $user_game_list)){
                    $new_game_list[] = $v;
                }
            }
        }

        return $new_game_list;
    }

    /**
     * 营销提成-列表
     * @param $param
     * @param $page
     * @param $limit
     * @return array
     */
    public static function getSCCList($param){

        $model = new SellerCommissionConfig();

        $page = getArrVal($param,'page',1);
        $limit = getArrVal($param,'limit',20);

        $where = array_merge(SellerCommissionConfig::$common_where,getDataByField($param,['status'],true));

        if(isset($param['money_min'])){
            if($param['money_min']){
                $where[] = ['money_min','>=',$param['money_min']];
            }
        }

        if(isset($param['money_max'])){
            if($param['money_max']){
                $where[] = ['money_max','<=',$param['money_max']];
            }
        }

        if(isset($param['start_time'])){
            if($param['start_time']){
                $where[] = ['start_time','>=',$param['start_time']];
            }
        }

        if(isset($param['end_time'])){
            if($param['end_time']){
                $where[] = ['end_time','<=',$param['end_time']];
            }
        }

        if(isset($param['game_product_id']) && $param['game_product_id'] > -1){
            $where['game_product_id'] = $param['game_product_id'];
        }

        $count = $model->where($where)->count();

        if(!$count){
            return [[],0];
        }

        $list = $model->where($where);

        if($page && $limit){
            $list = $list->page($page,$limit);
        }

        $list = $list->order("id desc")->select()->toArray();

        if($list){

            $admin_list = SysServer::getAdminListCache();

            $game_list = SysServer::getGameProductCache();

            $status_arr = SellerCommissionConfig::$status_arr;

            foreach ($list as $k => $v) {

                $list[$k]['action'] = ListActionServer::checkSellerCommissionConfigListAction($v);

                $list[$k]['add_admin_id_str'] = isset($admin_list[$v['add_admin_id']])?$admin_list[$v['add_admin_id']]['name']:'';
                $list[$k]['edit_admin_id_str'] = isset($admin_list[$v['edit_admin_id']])?$admin_list[$v['edit_admin_id']]['name']:'';
                if($v['game_product_id']>0){
                    $list[$k]['game_product_id_str'] = isset($game_list[$v['game_product_id']])?$game_list[$v['game_product_id']]['name']:'未知';
                }else{
                    $list[$k]['game_product_id_str'] = '默认';
                }
                $list[$k]['status_str'] = getArrVal($status_arr,$v['status'],'');
                $list[$k]['start_time_str'] = $v['start_time']?date('Y-m-d H:i:s',$v['start_time']):'即时';
                $list[$k]['end_time_str'] = $v['end_time']?date('Y-m-d H:i:s',$v['end_time']):'永久';

            }
        }

        return [$list,$count];
    }

    public static function sellerCommissionConfigListConfig(){

        $config['status_arr'] = SellerCommissionConfig::$status_arr;

        $config['game_list'] = SysServer::getGameProductCache();//游戏列表

        return $config;
    }

    public static function sellerCommissionConfigDetail($id){

        $detail = [
            'id'=>0
        ];
        $config = [];

        if($id){

            $where = SellerCommissionConfig::$common_where;
            $where['id'] = $id;
            $model = new SellerCommissionConfig();

            $info = $model->where($where)->find();

            if($info){
                $detail = $info->toArray();
                $detail['start_time'] = date('Y-m-d H:i:s',$detail['start_time']);
                $detail['end_time'] = date('Y-m-d H:i:s',$detail['end_time']);
            }
        }

        $config['game_list'] = SysServer::getGameProductCache();

        return compact('detail','config');
    }

    /**
     * @param $p_data
     * @return array
     */
    public static function sCCSave($p_data){

        $code = [
            0=>'success',1=>'该产品开始时间金额下限重复配置',2=>'修改失败',3=>'添加失败',4=>'该产品结束时间金额下限重复配置'
            ,5=>'该产品开始时间金额上限重复配置',6=>'该产品结束时间金额上限重复配置',7=>'配置数据不存在'
        ];
        $model = new SellerCommissionConfig();

        $id = isset($p_data['id']) && !empty($p_data['id'])?$p_data['id']:0;

        $check_where = SellerCommissionConfig::$common_where;
        $check_where['game_product_id'] = $p_data['game_product_id'];
        $check_where['now_time_eq'] = $p_data['start_time'];
        $check_where['money_region'] = $p_data['money_min'];
        $check_where['neq_id'] = $id;
        $check_res = $model->where(self::sCCCommonWhere($check_where))->count();

        if($check_res){
            return ['code'=>1,'msg'=>$code[1]];
        }

        $check_where = SellerCommissionConfig::$common_where;
        $check_where['game_product_id'] = $p_data['game_product_id'];
        $check_where['now_time_eq'] = $p_data['end_time'];
        $check_where['money_region'] = $p_data['money_min'];
        $check_where['neq_id'] = $id;
        $check_res = $model->where(self::sCCCommonWhere($check_where))->count();

        if($check_res){
            return ['code'=>4,'msg'=>$code[4]];
        }

        $check_where = SellerCommissionConfig::$common_where;
        $check_where['game_product_id'] = $p_data['game_product_id'];
        $check_where['now_time_eq'] = $p_data['start_time'];
        $check_where['money_region'] = $p_data['money_max'];
        $check_where['neq_id'] = $id;
        $check_res = $model->where(self::sCCCommonWhere($check_where))->count();

        if($check_res){
            return ['code'=>5,'msg'=>$code[5]];
        }

        $check_where = SellerCommissionConfig::$common_where;
        $check_where['game_product_id'] = $p_data['game_product_id'];
        $check_where['now_time_eq'] = $p_data['end_time'];
        $check_where['money_region'] = $p_data['money_max'];
        $check_where['neq_id'] = $id;
        $check_res = $model->where(self::sCCCommonWhere($check_where))->count();

        if($check_res){
            return ['code'=>6,'msg'=>$code[6]];
        }

        $common_field = [
            'game_product_id'
            ,'money_min'
            ,'money_max'
            ,'commission_rate'
            ,'commission_num'
            ,'start_time'
            ,'end_time'
            ,'status'
            ,'desc'
        ];


        if($id){
            $where = SellerCommissionConfig::$common_where;
            $where['id'] = $id;
            $info = $model->where($where)->find();

            if(!$info){
                return ['code'=>7,'msg'=>$code[7]];
            }
            //筛选要更新数据
            $save_data = getDataByField($p_data,$common_field);
            $save_data['edit_admin_id'] = self::$user_data['id'];

            $res = $info->save($save_data);

            if(!$res) return ['code'=>2,'msg'=>$code[2]];
        }else{

            $add_data = getDataByField($p_data,$common_field);

            $add_data['edit_admin_id'] = $add_data['add_admin_id'] = self::$user_data['id'];
            $add_data['edit_time'] = time();

            $res = $model->insert($add_data);

            if(!$res) return ['code'=>3,'msg'=>$code[3]];
        }

        return ['code'=>0,'msg'=>$code[0]];

    }

    protected static function sCCCommonWhere($param){
        $conditions = '1=1';
        if(isset($param['now_time'])){

            $conditions.= ' AND start_time <='.$param['now_time'];
            $conditions.= ' AND end_time >'.$param['now_time'];
            unset($param['now_time']);
        }

        if(isset($param['now_time_eq'])){

            $conditions.= ' AND start_time <='.$param['now_time_eq'];
            $conditions.= ' AND end_time >='.$param['now_time_eq'];
            unset($param['now_time_eq']);
        }

        if(isset($param['money_region'])){

            $conditions.= ' AND money_min <='.$param['money_region'];
            $conditions.= ' AND money_max >='.$param['money_region'];
            unset($param['money_region']);
        }

        if(isset($param['neq_id'])){
            $conditions.= ' AND id !='.$param['neq_id'];
            unset($param['neq_id']);
        }

        setWhereString($conditions,$param);

        return setWhereSql($param,'');
    }

    /**
     * @param $id
     * @return bool
     */
    public static function sCCDel($id){

        $model = new SellerCommissionConfig();

        $info = $model->where(['id'=>$id])->find();

        if(!$info){
            return false;
        }

        $info->edit_admin_id = self::$user_data['id'];
        $info->is_del = 1;

        $res = $info->save();

        if(!$res) return false;

        return true;
    }
}
