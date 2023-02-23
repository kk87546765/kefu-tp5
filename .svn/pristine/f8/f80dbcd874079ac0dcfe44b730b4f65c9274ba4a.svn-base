<?php
/**
 * @author ambi
 * @date 2019/4/17
 */

namespace common\server\Vip;



use common\base\BasicServer;

use common\model\db_statistic\KefuUserRecharge;
use common\model\db_customer\QcConfig;
use common\model\db_customer\WorkSheet;
use common\model\db_customer\WorkSheetLog;
use common\model\db_customer\WorkSheetType;
use common\model\db_customer\WorkSheetTypeInput;
use common\server\CustomerPlatform\CommonServer;
use common\server\ListActionServer;
use common\server\Statistic\GameProductServer;
use common\server\SysServer;

class WorkSheetServer extends BasicServer
{
    //字段和名称，用于导出表头和工单日志记录
    public $FIELD_STR = [
        'base' => [
            'id' => 'ID',
            'sheet_num' => '工单唯一编号',
            'sheet_type' => '工单类型',
            'role_id' => '角色ID',
            'user_account' => '账号',
            'game_name' => '游戏',
            'server_name' => '区服',
            'uid' => '玩家UID',
            'pay_money_total' => '总充值金额',
            'user_source' => '用户来源',
            'visitor_id' => '访客ID',
            'contact' => '联系方式',
            'record_user' => '记录人',
            'follow_user' => '跟进人',
            'add_time' => '新增时间',
            'edit_time' => '更新时间',
            'apply_time' => '申请时间',
            'handle_time' => '处理时间',
            'sub_log' => '提交记录',
        ],
        1 => [//账号类型工单字段
            'sheet_item_type' => '操作类型',
            'real_name' => '玩家真实姓名',
            'reg_time' => '注册时间',
            'reg_place' => '注册地点',
            'login_place' => '常登陆地点',
            'remarks' => '备注',
            'status' => '状态',
            'reg_time_real' => '注册日期是否属实',
            'reg_place_real' => '注册地址是否属实',
            'login_place_real' => '常登陆地点是否属实',
            'order_screen_capture' => '指定订单截图',
        ],
        2 => [//问题类型工单字段
            'sheet_item_type' => '问题类型',
            'remarks' => '问题详情',
            'remarks_result' => '问题结果',
            'feedback' => '玩家反馈',
            'order_screen_capture' => '指定订单截图',
            'status' => '状态',
        ],
        3 => [//建议类型工单字段
            'detail' => '建议详情',
            'real_name' => '玩家真实姓名',
            'sheet_item_type' => '建议类型',
            'status' => '状态',
            'sheet_item_type_sub' => '建议类型子项',
        ],
        4 => [//投诉类型工单字段
            'reason' => '投诉原因',
            'need' => '投诉需求',
            'real_name' => '玩家真实姓名',
            'sheet_item_type' => '投诉类型',
            'status' => '状态',
            'sheet_item_type_sub' => '投诉平台',
        ],
        5 => [//未成年退款类型工单字段
            'guardian_contact' => '监护人联系方式',
            'guardian_name' => '监护人姓名',
            'sheet_item_type' => '类型',
            'status' => '退款进度',
            'accessories' => '附件',
        ],
    ];

    public static function indexWorkSheetInfo($param){

        $code = [0=>'success',1=>'工单类型异常',2=>'没有数据'];

        $model = new WorkSheet();
        $WorkSheetType = new WorkSheetType();

        $where = [];
        if (!empty($param['add_time'])) {
            $timeArr = explode(' - ', $param['add_time']);
            if (!empty($timeArr) && count($timeArr) > 1) {
                $where[] = ['add_time','between', [strtotime($timeArr[0]), strtotime($timeArr[1].'23:59:59')]];
            }
        }

        $returnData = [];

        $where_sheet_type=[];
        $where_sheet_type['pid'] = 0;
        $where_sheet_type['status'] = 1;
        $sheet_type_list = $WorkSheetType->where($where_sheet_type)->select()->toArray();

        if(!$sheet_type_list) return returnData(1,$code[1]);

        foreach ($sheet_type_list as $key => $value) {
            $where['sheet_type'] = $value['id'];
            $this_where = $where;

            $data = [
                'title' => $value['title'],//标题
                'id'=>$value['id'],//type_id
                'pendingNum' => 0,//待处理数量
                'inProcessNum' => 0,//处理中
                'endNum' => 0,//已结单
                'overtimeNum' => 0,//超时
                'addNum' => 0,//新增工单
            ];

            $result = $model->field('status, count(*) as count')
                ->where(setWhereSql($this_where,''))
                ->group('status')
                ->select()
                ->toArray();

            if(!$result){
                $returnData[] = $data;
                continue;
            }

            $result = array_column($result, 'count', 'status');

            foreach ($result as $result_k => $result_v){
                //新增工单
                $data['addNum'] += $result_v;
                switch ($result_k){
                    case 0://待处理数量，状态为0
                        $data['pendingNum']+= $result_v;
                        break;
                    case 1://处理中，状态为1,2,3
                    case 2:
                    case 3:
                        $data['inProcessNum']+= $result_v;
                        break;
                    case 4://已结单，状态为4
                        $data['endNum']+= $result_v;
                        break;
                    default:
                        break;
                }
            }

            //超时。最后修改时间在2天前的未结单的数量
            $this_where = $where;
            $this_where[] = ['status','<>', 4];
            $this_where[] = ['edit_time','<', strtotime("-2 day")];

            $overtimeNum = $model->where(setWhereSql($this_where,''))->count();
            !empty($overtimeNum) && $data['overtimeNum'] = $overtimeNum;



            $returnData[] = $data;
        }

        return returnData(0, $returnData);
    }

    public static function getWorkSheetList($params){

        $model = new WorkSheet();
        //获取where查询条件
        $whereInfo = self::getSearchWhere($params);

        if (isset($whereInfo['code']) && $whereInfo['code'] != 0) {
            return $whereInfo;
        }

        $where = $whereInfo['where'];
        $page = $whereInfo['page'];
        $limit = $whereInfo['limit'];
        // 查询数据
        $total = $model
            ->where($where)
            ->count();

        if(!$total){
            return returnData(1,'no_info');
        }

        $list = $model
            ->where($where)
            ->order('is_top desc,id desc')
            ->page($page, (int)$limit)
            ->select()->toArray();


        if(!empty($list)){
            $admin_list = SysServer::getAdminListCache();
            $game_list = SysServer::getGameProductCache();//游戏列表
            $game_list = arrReSet($game_list,'id_str');

            $sheet_type_list = (new WorkSheetType())->where(['status'=>1])->select()->toArray();
            $sheet_type_list_new = [];
            if($sheet_type_list){
                foreach ($sheet_type_list as $item){
                    if($item['status_val']){
                        $item['status_val'] = splitToArr2($item['status_val']);
                    }else{
                        $item['status_val'] = WorkSheet::$status_arr;
                    }
                    $sheet_type_list_new[$item['id']] = $item;
                }
            }
            foreach ($list as &$v){

                $v['action'] = ListActionServer::checkWorkSheetList($v);

                $v['is_tips'] = 0;

                if($v['status'] != 4 && strtotime("-2 day") > $v['edit_time']){
                    $v['is_tips'] = 1;
                }
                $this_sheet_type_info = getArrVal($sheet_type_list_new,$v['sheet_type'],[]);
                if($this_sheet_type_info){
                    $status = $this_sheet_type_info['status_val'][$v['status']];
                    $v['sheet_type_str'] = $this_sheet_type_info['title'];
                }else{
                    $status = WorkSheet::$status_arr[$v['status']];
                    $v['sheet_type_str'] = '';
                }

                $v['status'] = '<a class="layui-btn layui-btn-xs" style="background-color: #009688;">'.$status.'</a>';
                $v['sheet_item_type'] = splitToArr($v['sheet_item_type']);
                $v['sheet_item_type_str'] = '';
                foreach ($v['sheet_item_type'] as &$tv){
                    $tv = getArrVal($sheet_type_list_new,$tv,'');
                    $v['sheet_item_type_str'] .= '<a class="layui-btn layui-btn-xs" style="background-color: #009688;">'.$tv['title'].'</a>';
                }

                $v['apply_time'] = empty($v['apply_time'])? '': date('Y-m-d', $v['apply_time']);
                $v['handle_time'] = empty($v['handle_time'])? '': date('Y-m-d', $v['handle_time']);

                $this_info = getArrVal($admin_list,$v['record_user'],[]);
                if($this_info){
                    $v['record_user_str'] = $this_info['name'];
                }else{
                    $v['record_user_str'] = '';
                }

                $this_info = getArrVal($admin_list,$v['follow_user'],[]);
                if($this_info){
                    $v['follow_user_str'] = $this_info['name'];
                }else{
                    $v['follow_user_str'] = '';
                }

                $this_info = getArrVal($game_list,$v['p_p'],[]);
                if($this_info){
                    $v['p_p_str'] = $this_info['name'];
                }else{
                    $v['p_p_str'] = '';
                }

                $this_info = GameProductServer::getPlatformGameInfoByGameId($v['game_id'],$v['platform_id']);
                if($this_info){
                    $v['p_g_str'] = $this_info->product_name;
                }
            }
        }

        return returnData(0, '获取成功', $list,['count' => $total]);
    }

    public static function listConfig(){
        $config = [];
        $config['platform_list'] = SysServer::getPlatformListByAdminInfo(self::$user_data);


        $WorkSheetType = new WorkSheetType();
        $where = [];
        $where['pid'] = 0;
        $where['status'] = 1;
        $config['sheet_type_arr'] = $WorkSheetType->where($where)->select()->toArray();

        $admin_list = SysServer::getUserListByAdminInfo(self::$user_data,[
            'group_type'=>[QcConfig::USER_GROUP_ONLINE_KF,QcConfig::USER_GROUP_VIP,QcConfig::USER_GROUP_ACCOUNT_SAFE],
            'not_group_type'=>[QcConfig::USER_GROUP_QC],
        ]);

        $config['admin_list'] = $admin_list;

        return $config;
    }

    public static function getConfig(){
        $config = [];
        $config['platform_list'] = SysServer::getPlatformList();
        $config['status_arr'] = WorkSheet::$status_arr;
        $config['contact_type_arr'] = WorkSheet::$contact_type_arr;
        $config['user_source_arr'] = WorkSheet::$user_source_arr;

        $WorkSheetType = new WorkSheetType();
        $where = [];
        $where['pid'] = 0;
        $where['status'] = 1;
        $config['sheet_type_arr'] = $WorkSheetType->where($where)->select()->toArray();

        $admin_list = SysServer::getUserListByAdminInfo(self::$user_data,[
            'group_type'=>[QcConfig::USER_GROUP_ONLINE_KF,QcConfig::USER_GROUP_VIP,QcConfig::USER_GROUP_ACCOUNT_SAFE],
            'not_group_type'=>[QcConfig::USER_GROUP_QC],
        ]);

        $config['admin_list'] = $admin_list;

        return $config;
    }
    /**
     * 获取列表搜索条件
     */
    private static function getSearchWhere($params)
    {
        extract( $params );
        // 处理参数
        empty($page) && $page = 1;
        empty($limit) && $limit = 20;

        // 筛选条件
        $where = [];
        // 账号密码类型
        if(!empty($sheet_type) && $sheet_type > -1){
            $where['sheet_type'] = $sheet_type;
        }

        if(!empty($static_type)){

            switch ($static_type) {
                case 'pendingNum':
                    $where[] =['status','=',0];
                    break;
                case 'inProcessNum':
                    $where[] =['status','in',[1,2,3]];
                    break;
                case 'endNum':
                    $where[] =['status','=',4];
                    break;
                case 'overtimeNum':
                    $where[] = ['status','<>', 4];
                    $where[] = ['edit_time','<', strtotime("-2 day")];
                    break;
                default:
                    # code...
                    break;
            }
        }

        // 关键词搜索
        if(!empty($keyword)){
            $where['_string'] = "(sheet_num LIKE '%{$keyword}%' OR role_id LIKE '%{$keyword}%' OR user_account LIKE '%{$keyword}%')";;
        }
        // 筛选时间
        if(!empty($add_time)){
            $add_time_arr = explode(' - ',$add_time);
            $where[] = ['add_time','between', [strtotime($add_time_arr[0].'00:00:00'), strtotime($add_time_arr[1].'23:59:59')]];
        }
        // 工单子项类型筛选
        if(!empty($sheet_item_type)){
            $where[] = ['sheet_item_type','like', '%'.$sheet_item_type.'%'];
        }
        // 是否匹配大于2天未操作的未完结数据(警告数据)
        if(!empty($is_tips)){
            $where[] = ['status','<',4];
            $where[] = ['edit_time','<',strtotime("-2 day")];
        }

        //状态或者退款进度筛选
        if(isset($status) && $status>=0){
            $where['status'] = $status;
        }

        //申请时间
        if(!empty($apply_time)){
            $explodeArr = explode(' - ', $apply_time);
            if (!empty($explodeArr) && count($explodeArr)> 1) {
                $where[] = ['apply_time','between', [strtotime($explodeArr[0]), strtotime($explodeArr[1])]];
            }
        }

        //受理、处理时间
        if(!empty($handle_time)){
            $explodeArr = explode(' - ', $handle_time);
            if (!empty($explodeArr) && count($explodeArr)> 1) {
                $where[] = ['handle_time','between', [strtotime($explodeArr[0]), strtotime($explodeArr[1])]];
            }
        }

        if(!empty($platform_id)){
            $where['platform_id'] = $platform_id;
        }

        //显示我的工单
        if (!empty($showMyWorkSheet)) {
            if (!empty($record_user) || !empty($follow_user)) {
                return returnData(2, '查询我的工单不能选择记录人或跟进人', []);
            }

            $adminId = empty($adminId)? self::$user_data['id'] : $adminId;
            if (!empty($adminId)) {
                $where['record_user|follow_user'] = $adminId;
            } else {
                $where['record_user|follow_user'] = -1;
            }
        } else {
            //记录人和跟进人筛选
            if(!empty($record_user)) {
                $where[] = getWhereDataArr($record_user,'record_user');
            }

            if (!empty($follow_user)) {
                $where[] = getWhereDataArr($follow_user,'follow_user');
            }
        }

        return [
            'page' => $page,
            'limit' => $limit,
            'where' => setWhereSql($where,'')
        ];
    }

    public static function getWorkSheetDetail($params){

        $model = new WorkSheet();
        if(!isset($params['id']) || empty($params['id'])) return returnData(1, '缺失id');

        $where = [];
        $where['id'] = getArrVal($params,'id',0);

        $findData = $model
            ->where($where)
            ->find();
        if(empty($findData)) return returnData(2, '获取失败');
        $findData = $findData->toArray();
        $findData['other_info'] = json_decode($findData['other_info'],true);
//        $findData = array_merge($findData,$findData['other_info']);
        $findData['order_screen_capture'] = explode(',',$findData['other_info']['order_screen_capture']);
        $findData['order_screen_capture_full'] = [];
        foreach ($findData['order_screen_capture'] as $v){
            $findData['order_screen_capture_full'][] = [
                'url' => $v,
//                'http_url' => build_full_file_Url($v)
                'http_url' => '/'.$v,
            ];
        }
        unset($findData['other_info']['order_screen_capture']);

        $findData['sheet_item_type'] = explode(',',$findData['sheet_item_type']);
        $admin_list = SysServer::getAdminListCache();
        $this_info = getArrVal($admin_list,$findData['record_user'],[]);
        if($this_info){
            $findData['record_user'] = $this_info['name'];
        }
        $this_info = getArrVal($admin_list,$findData['follow_user'],[]);
        if($this_info){
            $findData['follow_user'] = $this_info['name'];
        }
        $platform_list = SysServer::getPlatformList();

        $platform_info = getArrVal($platform_list,$findData['platform_id'],[]);

        $KefuUserRoleModel = CommonServer::getPlatformModel('KefuUserRole',$platform_info['suffix']);

        $where = [];
        $where['role_id'] = $findData['role_id'];

        $user_role = $KefuUserRoleModel->where($where)->order('login_date desc')->find();
        if($user_role){
            $findData['role_name'] = $user_role->role_name;
        }

        return returnData(0, '获取成功',$findData);
    }

    public static function saveWorkSheet($params){

        $return = self::checkSheetAccountValue($params);

        if(!is_array($return)) return returnData(1, $return);
        return self::sheetDataSave($return);
    }

    /**
     * 工单保存数据(新增 or 编辑)
     * @param $insert
     * @param $operation_type
     * @return array
     * @throws \think\exception\DbException
     */
    public static function sheetDataSave($insert){

        $model = new WorkSheet();

        $id = getArrVal($insert,'id',0);

        if($id){
            $findData = $model->where(['id'=>$insert['id']])->find();
            if (empty($findData)) return returnData(1, '原数据获取失败');
        }

        $allow_field = [
            'sheet_num',
            'sheet_type',
            'sheet_item_type',
            'sheet_item_type_sub',
            'role_id',
            'user_account',
            'platform_id',
            'product_id',
            'game_id',
            'server_id',
            'uid',
            'pay_money_total',
            'user_source',
            'visitor_id',
            'contact',
            'status',
            'other_info',
            'is_top',
            'record_user',
            'follow_user',
            'add_time',
            'edit_time',
            'apply_time',
            'handle_time',
            'p_p',
            'p_g',
            'content',
            'contact_type',
        ];

        $save_data = getDataByField($insert,$allow_field);

        try{
            if($id){

                $res = $findData->save($save_data);

                if (empty($res)) {
                    return returnData(3, '保存失败');
                }

            } else {

                $this_obj = $model->create($save_data);
                if(!$this_obj){
                    return returnData(5, '添加失败');
                }
            }
        }catch (\Exception $e){
            return returnData(4, $e->getMessage());
        }

        return returnData(0, ($id?'保存':'添加').'成功');
    }

    /**
     * 校验工单(账号密码)
     * @param array $params
     * @param int $operation
     * @return array|string
     */
    protected static function checkSheetAccountValue($params = []){
        $operation = 1;
        if(isset($params['id']) && $params['id']){
            $operation = 0;
        }
        if(empty($params['sheet_item_type_sub'])) $params['sheet_item_type_sub'] = 0;

        if(empty($params['contact_type'])) $params['contact_type'] = 0;

        // 操作类型
        if(empty($params['sheet_item_type'])) return '操作类型不能为空';

        // 角色id
        if(empty($params['role_id'])) return '角色ID不能为空';

        // 账号
        if(empty($params['user_account'])) return '用户账号不能为空';

        // 平台
        if(empty($params['platform_id'])) return '平台不能为空';

        // 产品
        if(empty($params['product_id'])) return '产品不能为空';

        // 游戏名称
        if(empty($params['game_id'])) return '游戏不能为空';

        // 区服
        if(empty($params['server_id'])) return '区服不能为空';

        $field = [
            'id',
            'sheet_type',
            'sheet_item_type',
            'sheet_item_type_sub',
            'role_id',
            'user_account',
            'platform_id',
            'product_id',
            'game_id',
            'server_id',
            'uid',
            'pay_money_total',
            'user_source',
            'visitor_id',
            'contact',
            'status',
            'other_info',
            'is_top',
            'content',
            'contact_type',
        ];
        $return = getDataByField($params,$field);
        $return['edit_time'] = time();
        if($operation == 1){
            $return['sheet_num'] = self::createWorkSheetNum();
            $return['record_user'] = self::$user_data['id'];
            $return['add_time'] = time();

        } else {
            $return['follow_user'] = self::$user_data['id'];
        }

        // 指定的订单截图
        $order_screen_capture = getArrVal($params,'order_screen_capture',[]);
        if($order_screen_capture){
            $order_screen_capture = array_values($order_screen_capture);
        }

        $return['p_p'] = $return['platform_id'].'_'.$return['product_id'];
        $return['p_g'] = $return['platform_id'].'_'.$return['game_id'];
        $return['other_info']['order_screen_capture'] = implode(',',$order_screen_capture);

        $return['other_info'] = json_encode($return['other_info']);

        if(isset($params['apply_time']) && $params['apply_time']){
            $return['apply_time'] = strtotime($params['apply_time']);
        }

        if(isset($params['handle_time']) && $params['handle_time']){
            $return['handle_time'] = strtotime($params['handle_time']);
        }

        return $return;
    }

    # 生成工单编号
    private static function createWorkSheetNum(){
        return date('YmdHis', time()) . substr(microtime(), 2, 6) . sprintf('%03d', rand(0, 999));
    }

    /**
     * 根据角色ID获取信息
     */
    public static function getInfoByRoleId($params)
    {
        $code = [
            0=>'success',1=>'请选择工单类型',2=>'请选择平台类型',3=>'请填写角色ID、UID、账号',4=>'没有查询该用户到数据',5=>'平台信息错误',6=>'没查询到角色信息',
        ];
        if(empty($params['sheet_type'])){
            return returnData(1,$code[1]);
        }
        if (empty($params['platform_id']) ) {
            return returnData(2,$code[2]);
        }
        if(empty($params['role_id']) && empty($params['uid']) && empty($params['user_name']) ){
            return returnData(3,$code[3]);
        }
        $platform_list = SysServer::getPlatformList();

        $platform_info = getArrVal($platform_list,$params['platform_id'],[]);

        if(!$platform_info) return returnData(5,$code[5]);

        $model = new KefuUserRecharge();
        $KefuUserRoleModel = CommonServer::getPlatformModel('KefuUserRole',$platform_info['suffix']);
        $where = [];
        if(isset($params['user_name']) && $params['user_name']){
            $where['uname'] = $params['user_name'];
        }
        if(isset($params['role_id']) && $params['role_id']){
            $where['role_id'] = $params['role_id'];
        }

        $user_role = $KefuUserRoleModel->where($where)->order('login_date desc')->select()->toArray();

        if(!$user_role){
            return returnData(6,$code[6]);
        }

        $where = getDataByField($params,['platform_id'],true);
        $where['uid'] = $user_role[0]['uid'];


        $result = $model->where($where)->find();

        if(!$result){
            return returnData(4,$code[4]);
        }

        $result = $result->toArray();
        $result['role_list'] = [];
        foreach ($user_role as $k => $v){
            $this_data = [];
            $this_data['value'] = $v['role_id'];
            $this_data['name'] = $v['role_id'];
            $this_data['selected'] = false;
            $this_data['role_name'] = $v['role_name'];
            if($k == 0){
                $this_data['selected'] = true;
                $result['role_id'] = $v['role_id'];
                $result['role_name'] = $v['role_name'];
                $result['server_id'] = $v['server_id'];
            }
            $result['role_list'][] = $this_data;
        }

        $result['product_id'] = 0;
        $GameProductServer = new GameProductServer();

        $product_info =$GameProductServer->getPlatformGameInfoByGameId($result['last_pay_game_id'],$result['platform_id']);

        if($product_info){
            $product_info = $product_info->toArray();
            $result['product_id'] = $product_info['product_id'];
        }

        return returnData(0, '获取成功', $result);
    }

    public static function getWorkSheetTypeList($params){

        $model = new WorkSheetType();
        //获取where查询条件

        $where = getDataByField($params,['status'],true);

        if(isset($params['pid']) && $params['pid']>-1){
            $where[]= ['pid','=',$params['pid']];
        }

        if(isset($params['title']) && $params['title']){
            $where[]= ['title','like',"%$params[title]%"];
        }

        $page = getArrVal($params,'page',1);
        $limit = getArrVal($params,'limit',20);

        // 查询数据
        $count = $model
            ->where(setWhereSql($where,''))
            ->count();

        if(!$count){
            return returnData(1,'no_info');
        }

        $list = $model
            ->where(setWhereSql($where,''))
            ->order('id desc');
        if($page && $limit){
            $list = $list->page($page, (int)$limit);
        }

        $list = $list->select()
            ->toArray()
        ;
        if($list){
            $admin_list = SysServer::getAdminListCache();

            foreach ($list as $k => $v){

                $list[$k]['action'] = ListActionServer::checkWorkSheetTypeList($v);

                if($v['pid']){
                    $list[$k]['pid_str'] = '子级';
                }else{
                    $list[$k]['pid_str'] = '顶级';
                }

                $this_info = getArrVal($admin_list,$v['admin_id'],[]);

                if($this_info){
                    $list[$k]['admin_id_str'] = $this_info['name'];
                }else{
                    $list[$k]['admin_id_str'] = '';
                }

                $list[$k]['status_str'] = getArrVal(WorkSheetType::$status_arr,$v['status'],'未知');
            }
        }

        $return = compact('list','count');
        return returnData(0, '获取成功', $return);
    }

    public static function getTypeListConfig(){

        $config = [];
        $WorkSheetType = new WorkSheetType();
        $where = [];
        $where['pid'] = 0;
        $config['sheet_type_arr'] = $WorkSheetType->where($where)->select()->toArray();

        $config['status_arr'] = WorkSheetType::$status_arr;
        $config['field_title_arr'] = WorkSheetType::$field_title_arr;
//        dd($config);
        return $config;
    }

    public static function getTypeDetailConfig(){
        $config = [];
        $WorkSheetType = new WorkSheetType();
        $where = [];
        $where['pid'] = 0;
        $config['sheet_type_arr'] = $WorkSheetType->where($where)->select()->toArray();

        $config['status_arr'] = WorkSheetType::$status_arr;

        foreach (WorkSheetType::$field_title_arr as $k => $v){
            $config['field_title_arr'][$k] = [
              'title'=>$v,
              'is_show'=>1,
            ];
        }

//        dd($config);
        return $config;
    }

    public static function getWorkSheetTypeDetail($params){

        $model = new WorkSheetType();
        if(!isset($params['id']) || empty($params['id'])) return returnData(1, '缺失id');

        $where = [];
        $where['id'] = getArrVal($params,'id',0);

        $findData = $model
            ->where($where)
            ->find();
        if(empty($findData)) return returnData(2, '获取失败');
        $findData = $findData->toArray();
        $findData['field_config'] = json_decode($findData['field_config'],true);

        return returnData(0, '获取成功',$findData);
    }

    public static function saveWorkSheetType($params){

        $model = new WorkSheetType();

        $now = time();

        $id = getArrVal($params,'id',0);

        $save_data = getDataByField($params,['title','pid','children_title','status']);

        $save_data['update_time'] = $now;

        $save_data['admin_id'] = self::$user_data['id'];

        if(isset($params['field_config']) && $params['field_config']){
            $save_data['field_config'] = json_encode($params['field_config']);
        }else{
            $save_data['field_config'] = '';
        }

        if(isset($params['children_val']) && $params['children_val']){
            $save_data['children_val'] = splitToArrCheck2($params['children_val']);
        }

        if(isset($params['status_val']) && $params['status_val']){
            $save_data['status_val'] = splitToArrCheck2($params['status_val']);
        }

        if($id){
            $info = $model->where(['id'=>$id])->find();

            if(!$info){
                return returnData(1,'更新对象信息不存在');
            }

            $res = $info->save($save_data);

            if($res){
                return returnData(0,'更新成功');
            }else{
                return returnData(2,'更新失败');
            }
        }else{
            $save_data['is_del'] = 0;

            $res = $model->create($save_data);

            if($res){
                return returnData(0,'添加成功');
            }else{
                return returnData(3,'添加失败');
            }
        }
    }

    public static function getWorkSheetTypeInputList($params){

        $model = new WorkSheetTypeInput();
        //获取where查询条件

        $where = getDataByField($params,['work_sheet_type_id'],true);

        $page = getArrVal($params,'page');
        $limit = getArrVal($params,'limit');

        // 查询数据
        $count = $model
            ->where($where)
            ->count();

        if(!$count){
            return returnData(1,'no_info');
        }

        $list = $model
            ->where($where)
            ->order('id desc')
            ->page($page, (int)$limit)
            ->select()->toArray();

        if($list){
            foreach ($list as $k => $v){
                $list[$k]['action'] = ['type_input_detail'];

                $list[$k]['status_str'] = getArrVal(WorkSheetTypeInput::$status_arr,$v['status'],'未知');
            }
        }

        $return = compact('list','count');
        return returnData(0, '获取成功', $return);
    }

    public static function getTypeInputConfig(){

        $config = [];
        $config['status_arr'] = WorkSheetTypeInput::$status_arr;
        $config['upload_type_arr'] = WorkSheetTypeInput::$upload_type_arr;

        return $config;
    }

    public static function getWorkSheetTypeInputDetail($params){

        $model = new WorkSheetTypeInput();
        if(!isset($params['id']) || empty($params['id'])) return returnData(1, '缺失id');

        $where = [];
        $where['id'] = getArrVal($params,'id',0);
        $findData = $model
            ->where($where)
            ->find();
        if(empty($findData)) return returnData(2, '获取失败');
        $findData = $findData->toArray();
        if($findData['value']) $findData['value'] = json_decode($findData['value'],true);
        return returnData(0, '获取成功',$findData);
    }

    public static function saveWorkSheetTypeInput($params){
        $model = new WorkSheetTypeInput();

        $id = getArrVal($params,'id',0);

        $save_data = getDataByField($params,['work_sheet_type_id','upload_type','menu_name','status','type','parameter','value','info']);

        if($id){
            $info = $model->where(['id'=>$id])->find();
            if(!$info){
                return returnData(1,'更新对象信息不存在');
            }

            $res = $info->save($save_data);

            if($res){
                return returnData(0,'更新成功');
            }else{
                return returnData(2,'更新失败');
            }
        }else{

            $res = $model->create($save_data);

            if($res){
                return returnData(0,'添加成功');
            }else{
                return returnData(3,'添加失败');
            }
        }

    }

    public static function getWorkSheetLogList($params){

        $model = new WorkSheetLog();
        //获取where查询条件

        $where = getDataByField($params,['work_sheet_id'],true);

        $page = getArrVal($params,'page');
        $limit = getArrVal($params,'limit');

        // 查询数据
        $count = $model
            ->where($where)
            ->count();

        if(!$count){
            return returnData(1,'no_info');
        }

        $list = $model
            ->where($where)
            ->order('id desc')
            ->page($page, (int)$limit)
            ->select()->toArray();

        if($list){
            $admin_list = SysServer::getAdminListCache();
            foreach ($list as $k => $v){

                $list[$k]['action'] = ListActionServer::checkWorkSheetLogList($v);

                try {

                    $this_info = json_decode($v['file_data'],true);

                    if($this_info){

                        foreach ($this_info as $ti_k => $ti_v){
                            $list[$k]['file_data_arr'][] = '/'.$ti_v;
                        }
                    }else{
                        $list[$k]['file_data_arr'] = [];
                    }
                } catch (\Exception $e) {

                }

                $this_info = getArrVal($admin_list,$v['admin_user']);
                if($this_info){
                    $list[$k]['admin_user_str'] = $this_info['name'];
                }
            }
        }

        $return = compact('list','count');
        return returnData(0, '获取成功', $return);
    }

    public static function getLogConfig(){

        $config = [];

        return $config;
    }
    public static function getWorkSheetLogDetail($param){

        $where = getDataByField($param,['id']);

        $model = new WorkSheetLog();

        $info = $model->where($where)->find();

        if(!$info){
            return false;
        }

        $info = $info->toArray();
        if($info['file_data']){
            $info['file_data'] = json_decode($info['file_data'],true);
            if($info['file_data'] && is_array($info['file_data'])){
                $this_info = [];
                foreach ($info['file_data'] as $v){
                    $this_info[] = [
                        'http_url'=>'/'.$v,
                        'url'=>$v,
                    ];
                }
                $info['file_data'] = $this_info;
            }
        }else{
            $info['file_data'] = [];
        }

        return $info;
    }
    public static function saveWorkSheetLog($params){
        $model = new WorkSheetLog();

        $id = getArrVal($params,'id',0);

        $save_data = getDataByField($params,['content','operation_type',]);

        $save_data['admin_user'] = self::$user_data['id'];

        $save_data['file_data'] = json_encode($params['file_data']);

        if($id){
            $info = $model->where(['id'=>$id])->find();
            if(!$info){
                return returnData(1,'更新对象信息不存在');
            }

            $res = $info->save($save_data);

            if($res){
                return returnData(0,'更新成功');
            }else{
                return returnData(2,'更新失败');
            }
        }else{
            $work_sheet_id = getArrVal($params,'work_sheet_id',0);

            if(!$work_sheet_id){
                return returnData(4,'参数错误');
            }
            $WorkSheet = new WorkSheet();
            $work_sheet_info = $WorkSheet->where(['id'=>$work_sheet_id])->find();
            if(!$work_sheet_info){
                return returnData(5,'工单信息不存在');
            }
            $save_data['work_sheet_id'] = $work_sheet_info->id;
            $save_data['sheet_type'] = $work_sheet_info->sheet_type;
            $res = $model->create($save_data);

            if($res){
                $work_sheet_info->save(['follow_user'=>self::$user_data['id']]);
                return returnData(0,'添加成功');
            }else{
                return returnData(3,'添加失败');
            }
        }

    }

    /**
     * radio 和 checkbox规则的判断
     * @param $data
     * @return bool
     */
    public static function valiDateRadioAndCheckbox($data)
    {
        $option = [];
        $option_new = [];
//        $data['parameter'] = str_replace("\r\n", "\n", $data['parameter']);//防止不兼容
//        $parameter = explode("\n", $data['parameter']);
        $data['parameter'] = splitToArrCheck2( $data['parameter']);
        $parameter = splitToArr2($data['parameter']);
        if (count($parameter) < 2) {
            return returnData(1,'请输入正确格式的配置参数');
        }
        foreach ($parameter as $k => $v) {
            if (isset($v) && !empty($v)) {
                $option[$k] = explode('=>', $v);
            }
        }
        if (count($option) < 2) {
            return returnData(2,'请输入正确格式的配置参数');
        }
        $bool = 1;
        foreach ($option as $k => $v) {
            $option_new[$k] = $option[$k][0];
            foreach ($v as $kk => $vv) {
                $vv_num = strlen($vv);
                if (!$vv_num) {
                    $bool = 0;
                }
            }
        }
        if (!$bool) {
            return returnData(3,'请输入正确格式的配置参数');
        }
        $num1 = count($option_new);//提取该数组的数目
        $arr2 = array_unique($option_new);//合并相同的元素
        $num2 = count($arr2);//提取合并后数组个数
        if ($num1 > $num2) {
            return returnData(5,'请输入正确格式的配置参数');
        }
        return returnData(0,'ok');
    }

    public static function getSheetTypeConfig($id,$work_sheet_id = 0){
        $model = new WorkSheetType();
        $WorkSheetTypeInput = new WorkSheetTypeInput();
        $WorkSheet = new WorkSheet();
        $type_info = $model->where(['id'=>$id])->find();

        if(!$type_info){
            return returnData(1,'没有数据');
        }
        $work_sheet_info = [];
        if($work_sheet_id){
            $this_info = $WorkSheet->where(['id'=>$work_sheet_id])->find();
            if($this_info){
                $work_sheet_info = $this_info->toArray();
            }
        }

        $type_info = $type_info->toArray();
        $type_info_parent = [];
        $type_input_list_parent = [];

        $where = [];
        $where['work_sheet_type_id'] = $type_info['id'];
        $where['status'] = 1;

        $type_input_list = $WorkSheetTypeInput->where($where)->order('id asc')->select()->toArray();


        if($type_info['pid']){
            $type_info_parent = $model->where(['id'=>$type_info['pid']])->find();
            if($type_info_parent) $type_info_parent = $type_info_parent->toArray();

            $where = [];
            $where['work_sheet_type_id'] = $type_info['pid'];
            $where['status'] = 1;
            $type_input_list_parent = $WorkSheetTypeInput->where($where)->order('id asc')->select()->toArray();
        }

        $r_data = self::getChildrenInfo($type_info,$type_info_parent);

        $r_data = array_merge($r_data,self::getStatusInfo($type_info,$type_info_parent));

        $r_data = array_merge($r_data,self::getFieldConfig($type_info,$type_info_parent));

        $r_data = array_merge($r_data,self::getInputConfig($type_input_list,$type_input_list_parent,$work_sheet_info));

        $params = [];
        $params['page'] = 0;
        $params['limit'] = 1;
        $params['pid'] = $type_info['id'];
        $sheet_item_type_res = self::getWorkSheetTypeList($params);
        if($sheet_item_type_res['code'] == 0){
            $r_data['sheet_item_type_list'] = $sheet_item_type_res['data']['list'];
        }else{
            $r_data['sheet_item_type_list'] = [];
        }

        return returnData(0,$r_data);

    }

    protected static function getStatusInfo($c_arr,$p_arr){

        $r_arr = [
            'status_val'=>WorkSheet::$status_arr,
        ];

        if($p_arr && $p_arr['status_val']){
            $this_info = splitToArr2($p_arr['status_val']);
            if($this_info){
                $r_arr['status_val'] = $this_info;
            }
        }

        if($c_arr && $c_arr['status_val']){
            $this_info = splitToArr2($c_arr['status_val']);
            if($this_info){
                $r_arr['status_val'] = $this_info;
            }
        }

        return $r_arr;
    }

    protected static function getChildrenInfo($c_arr,$p_arr){
        $r_arr = [
            'children_title'=>'',
            'children_val'=>[],
        ];

        if($p_arr && $p_arr['children_val']){
            $this_info = splitToArr2($p_arr['children_val']);
            if($this_info){
                $r_arr['children_title'] = $p_arr['children_title'];
                $r_arr['children_val'] = $this_info;
            }
        }

        if($c_arr && $c_arr['children_val']){
            $this_info = splitToArr2($c_arr['children_val']);
            if($this_info){
                $r_arr['children_title'] = $c_arr['children_title'];
                $r_arr['children_val'] = $this_info;
            }
        }

        return $r_arr;
    }

    protected static function getFieldConfig($c_arr,$p_arr){
        $r_arr = [
            'field_config'=>[],
        ];

        if($p_arr && $p_arr['field_config']){
            $r_arr['field_config'] = json_decode($p_arr['field_config'],true);
        }

        if($c_arr && $c_arr['field_config']){
            $this_info = json_decode($c_arr['field_config'],true);
            if($r_arr['field_config']){
                foreach ($this_info as $k => $v){
                    $r_arr['field_config'][$k] = $v;
                }
            }else{
               $r_arr['field_config'] = $this_info;
            }
        }

        return $r_arr;
    }

    protected static function getInputConfig($c_arr,$p_arr,$work_sheet_info = []){
        $r_arr = [
            'input_config'=>[],
        ];
        $arr = [];
        $other_info = [];
        if($work_sheet_info && !empty($work_sheet_info['other_info'])) $other_info = json_decode($work_sheet_info['other_info'],true);
        if($p_arr){
            foreach ($p_arr as $k => $v){
                $v = self::changeInputConfigVal($v);
                $arr[$v['menu_name']] = $v;
            }
        }
        if($c_arr){
            foreach ($c_arr as $k => $v){
                $v = self::changeInputConfigVal($v);
                $arr[$v['menu_name']] = $v;
            }
        }

        if($arr){
            foreach ($arr as $k => $v){
                if($other_info && isset($other_info[$v['menu_name']])){
                    $this_value = $other_info[$v['menu_name']];
                    if(in_array($v['type'],['radio','checkbox'])){
                        $this_value = splitToArr($this_value);
                        foreach ($v['parameter'] as $k1 => $v1){
                            if(in_array($v1['value'],$this_value)){
                                $v['parameter'][$k1]['selected'] = true;
                            }else{
                                $v['parameter'][$k1]['selected'] = false;
                            }
                        }
                    }
                    $v['value'] = $this_value;
                }
                $r_arr['input_config'][] = $v;
            }
        }

        return $r_arr;
    }

    protected static function changeInputConfigVal($v){
        $v['value'] = json_decode($v['value'],true);
        if(in_array($v['type'],['radio','checkbox'])){
            $this_value = splitToArr($v['value']);
            $parameter = splitToArr2($v['parameter']);
            $this_arr = [];
            foreach ($parameter as $k1 => $v1){
                $this_info = [];
                $this_info['value'] = $k1;
                $this_info['name'] = $v1;
                if(in_array($k1,$this_value)){
                    $this_info['selected'] = true;
                }else{
                    $this_info['selected'] = false;
                }
                $this_arr[]= $this_info;
            }
            $v['parameter'] = $this_arr;
        }

        return $v;
    }

}