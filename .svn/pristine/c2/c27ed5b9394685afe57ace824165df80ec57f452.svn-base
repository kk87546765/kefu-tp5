<?php
/**
 * 系统
 */
namespace common\server;


use common\base\BasicServer;
use common\model\db_customer\QcConfig;


class ListActionServer extends BasicServer
{


    public static function checkWorkSheetLogList($data){
        $action_arr = [
            'WorkSheet'=>[
                'logDetail',
            ],
        ];

        $this_action = self::getThisActionNew($action_arr);

        $action = [];

        //权限相关逻辑
        if($this_action){

            foreach ($this_action as $k => $v) {

                if(self::$user_data['is_admin'] == 0){

                }

                $action[] = $v;
            }
        }

        return $action;
    }

    public static function checkWorkSheetTypeList($data){
        $action_arr = [
            'WorkSheet'=>[
                'typeDetail',
                'typeInputList',
            ],
        ];

        $this_action = self::getThisActionNew($action_arr);

        $action = [];

        //权限相关逻辑
        if($this_action){

            foreach ($this_action as $k => $v) {

                if(self::$user_data['is_admin'] == 0){

                }

                $action[] = $v;
            }
        }

        return $action;
    }

    public static function checkWorkSheetList($data){
        $action_arr = [
            'WorkSheet'=>[
                'detail',
                'top',
                'add',
                'edit',
            ],
        ];

        $this_action = self::getThisActionNew($action_arr);

        $action = [];

        //权限相关逻辑
        if($this_action){

            foreach ($this_action as $k => $v) {

                if(self::$user_data['is_admin'] == 0){

                }

                $action[] = $v;
            }
        }

        return $action;
    }

    public static function checkSellerCommissionConfigListAction($data){
        $action_arr = [
            'VipConfig'=>[
                'sellerCommissionConfigDetail',
                'sellerCommissionConfigDel',
            ],
        ];

        $this_action = self::getThisActionNew($action_arr);

        $action = [];

        //权限相关逻辑
        if($this_action){

            foreach ($this_action as $k => $v) {

                if(self::$user_data['is_admin'] == 0){

                }

                $action[] = $v;
            }
        }

        return $action;
    }

    public static function checkBecomeVipStandardListAction($data){
        $action_arr = [
            'VipConfig'=>[
                'becomeVipStandardDetail',
            ],
        ];

        $this_action = self::getThisActionNew($action_arr);

        $action = [];

        //权限相关逻辑
        if($this_action){

            foreach ($this_action as $k => $v) {

                if(self::$user_data['is_admin'] == 0){

                }

                $action[] = $v;
            }
        }

        return $action;
    }


    public static function checkRebateConfigListAction($data){

        $action_arr = [
            'VipConfig'=>[
                'batchDeleteRebate',
            ],
        ];

        $this_action = self::getThisActionNew($action_arr);

        $action = [];

        //权限相关逻辑
        if($this_action){

            foreach ($this_action as $k => $v) {

                if(self::$user_data['is_admin'] == 0){

                }

                $action[] = $v;
            }
        }

        return $action;
    }
    /**
     * 自助登记列表
     * @param $data 数据
     * @return array
     */
    public static function checkUserInputInfoListAction($data){

        $action_arr = [
            'Vip'=>[
                'userInputInfoDetail',
                'userInputInfoDelete',
            ],
        ];

        $this_action = self::getThisActionNew($action_arr);

        $action = [];

        //权限相关逻辑
        if($this_action){

            foreach ($this_action as $k => $v) {

                if(self::$user_data['is_admin'] == 0){

                }

                $action[] = $v;
            }
        }

        return $action;
    }

    /**
     * 平台列表
     * @param $data 数据
     * @return array
     */
    public static function checkAdminListAction($data){

        $action_arr = [
            'User'=>[
                'detail',
            ],
        ];

        $this_action = self::getThisActionNew($action_arr);

        $action = [];

        //权限相关逻辑
        if($this_action){

            foreach ($this_action as $k => $v) {

                if(self::$user_data['is_admin'] == 0){

                }

                $action[] = $v;
            }
        }

        return $action;
    }
    /**
     * 平台列表
     * @param $data 数据
     * @return array
     */
    public static function checkUserGroupAction($data){

        $action_arr = [
            'Sysmanage'=>[
                'userGroupDetail',
                'getAdminIdsByGroupId',
            ],
        ];

        $this_action = self::getThisActionNew($action_arr);

        $action = [];

        //权限相关逻辑
        if($this_action){

            foreach ($this_action as $k => $v) {

                if(self::$user_data['is_admin'] == 0){

                }

                $action[] = $v;
            }
        }

        return $action;
    }

    /**
     * 平台列表
     * @param $data 数据
     * @return array
     */
    public static function checkPlatformListAction($data){

        $action_arr = [
            'Sysmanage'=>[
                'platformDetail',
                'platformDel',
            ],
        ];

        $this_action = self::getThisActionNew($action_arr);

        $action = [];

        //权限相关逻辑
        if($this_action){

            foreach ($this_action as $k => $v) {

                if(self::$user_data['is_admin'] == 0){

                }

                $action[] = $v;
            }
        }

        return $action;
    }
    /**
     * 流失用户
     * @param array $data 数据
     * @return array
     */
    public static function checkAdminUserGameServerListAction($data = array()){

        $action_arr = [
            'VipConfig'=>[
                'serverManageDetail',
                'serverManageDelete',
                'adminGameServerList',
            ],
        ];

        $this_action = self::getThisActionNew($action_arr);

        $action = [];

        //权限相关逻辑
        if($this_action){

            foreach ($this_action as $k => $v) {

                if(self::$user_data['is_admin'] == 0){

//                    if($v == 'sell_work_order_qc_first' && $data['status'] != 0){
//                        continue;
//                    }
                }

                $action[] = $v;
            }
        }

        return $action;
    }
    /**
     * vip用户管理
     * @param $data
     * @return array
     */
    public static function checkVipUserInfoListAction($data){

        $action_arr = [
            'Vip'=>[
                'decryptMobile',
                'vipUserOtherInfoDetail',
                'vipDistributionDelete',
            ],
        ];

        $this_action = self::getThisActionNew($action_arr);

        $action = [];

        //权限相关逻辑
        if($this_action){

            foreach ($this_action as $k => $v) {

                if(self::$user_data['is_admin'] == 0){

//                    if($v == 'sell_work_order_qc_first' && $data['status'] != 0){
//                        continue;
//                    }
                }

                $action[] = $v;
            }
        }

        return $action;
    }
    /**
     * vip流失用户
     * @param $data
     * @return array
     */
    public static function checkWashUserListAction($data){

        $action_arr = [
            'Vip'=>[
                'decryptMobile',
            ],
        ];

        $this_action = self::getThisActionNew($action_arr);

        $action = [];

        //权限相关逻辑
        if($this_action){

            foreach ($this_action as $k => $v) {

                if(self::$user_data['is_admin'] == 0){

//                    if($v == 'sell_work_order_qc_first' && $data['status'] != 0){
//                        continue;
//                    }
                }

                $action[] = $v;
            }
        }

        return $action;
    }
    /**
     * 角色
     * @param $data 数据
     * @return array
     */
    public static function checkRoleListAction($data){

        $action_arr = [
            'Sysmanage'=>[
                'add_role',
                'update_role',
                'role_detail',
                'set_permission',
                'del_role',
            ],
        ];

        $this_action = self::getThisActionNew($action_arr);

        $action = [];

        //权限相关逻辑
        if($this_action){

            foreach ($this_action as $k => $v) {

                if(self::$user_data['is_admin'] == 0){

//                    if($v == 'sell_work_order_qc_first' && $data['status'] != 0){
//                        continue;
//                    }
                }

                $action[] = $v;
            }
        }

        return $action;
    }
    /**
     * vip福利
     * @param $data 数据
     * @return array
     */
    public static function checkVipUserOtherInfoAction($data){

        $action_arr = [
            'VipWelfare'=>[
                'userWelfareAdd',
                'userWelfareList',
            ],
        ];

        $this_action = self::getThisActionNew($action_arr);

        $action = [];

        //权限相关逻辑
        if($this_action){

            foreach ($this_action as $k => $v) {

                if(self::$user_data['is_admin'] == 0){

                }

                $action[] = $v;
            }
        }

        return $action;
    }
    /**
     * vip福利
     * @param $data 数据
     * @return array
     */
    public static function checkVipUserWelfareAction($data){

        $action_arr = [
            'Vip'=>[
                'userWelfareDetail',
            ]
        ];

        $this_action = self::getThisActionNew($action_arr);

        $action = [];

        //权限相关逻辑
        if($this_action){

            foreach ($this_action as $k => $v) {

                if(self::$user_data['is_admin'] == 0){
//                    if($v == 'sell_work_order_qc_first' && $data['status'] != 0){
//                        continue;
//                    }
                }

                $action[] = $v;
            }
        }

        return $action;
    }
    /**
     * 工单
     * @param array $data 数据 判断必须字段{status}
     * @return array
     */
    public static function checkSellWorkOrderAction(array $data){

        $action_arr = [
            'Vip'=>[
                'sellWorkOrderDetail',
                'sellWorkOrderAdd',
                'sellWorkOrderEdit',
                'sellWorkOrderQcFirst',
                'sellWorkOrderQcSecond',
            ]
        ];

        $this_action = self::getThisActionNew($action_arr);

        $action = [];
        //权限相关逻辑
        if($this_action){

            foreach ($this_action as $k => $v) {

//                if(self::$user_data['is_admin'] == 0){

                    if($v == 'sellWorkOrderQcFirst' && $data['status'] != 0){
                        continue;
                    }

                    if($v == 'sellWorkOrderQcSecond' && $data['status'] != 1){
                        continue;
                    }

                    if($v == 'sellWorkOrderAdd' && $data['id'] > 0){
                        continue;
                    }

                    if($v == 'sellWorkOrderEdit' && $data['status'] >= 0){
                        continue;
                    }
//                }

                $action[] = $v;
            }
        }

        return $action;
    }

    /**
     * 质检池
     * @param $data 数据 判断必须字段{status,admin_id,qc_question_log_id}
     * @return array
     */
    public static function checkQcQuestionAction($data){


        $this_status = getArrVal($data,'status',0);
        $this_admin_id = getArrVal($data,'admin_id',0);
        $this_qc_question_log_id = getArrVal($data,'qc_question_log_id',0);

        $action_arr = array(
            'Qc'=>[
                'logDetail',//保存
                'detail',//会话详情
                'setConversationNeedUpdate',//设置会话更新
                'delAdminIds',//取消分配
            ]
        );

        $this_action = self::getThisActionNew($action_arr);

        $action = [];

        if(!$this_action){
            return $action;
        }

        foreach ($this_action as $k => $v) {

            if($v == 'rejectappeal' && $this_status !=1){
                continue;
            }

            if($v == 'delAdminIds' && ($this_admin_id == 0 || $this_qc_question_log_id > 0) ){
                continue;
            }
            if($v == 'setConversationNeedUpdate' && !in_array($data['kf_source'],[QcConfig::KF_SOURCE_SOBOT,QcConfig::KF_SOURCE_SOBOT_ZW])){
                continue;
            }

            $action[] = $v;
        }

        return $action;
    }
    /**
     * 质检记录操作控制
     * @param $data
     * @return array
     */
    public static function checkQcQuestionLogAction($data){

        //判断参数这里拿出来以防传参报错
        $this_status = getArrVal($data,'status',0);
        $this_id = getArrVal($data,'id',0);

        $action_arr = array(
            'Qc'=>[
//                'log',//列表
                'logAdd',//添加
                'logDetail',//详情
                'logEdit',//保存
                'logDel',//保存
                'appeal',//申诉
                'doAppealFirst',//申诉一审
                'doAppealSecond',//申诉二审
            ]
        );

        $this_action = self::getThisActionNew($action_arr);

        $action = [];

        if(!$this_action){
            return $action;
        }

        foreach ($this_action as $k => $v) {

            if ($v == 'doAppealFirst' && ($this_status != 1 || self::$user_data['position_grade'] < QcConfig::POSITION_GRADE_LEADER )) {
                continue;
            }
            if ($v == 'doAppealSecond' && $this_status != 2) {
                continue;
            }
            if ($v == 'appeal' && ($this_status >0 || !$this_id)) {
                continue;
            }
            if ($v == 'logEdit' && !$this_id){
                continue;
            }
            if ($v == 'logAdd' && $this_id){
                continue;
            }

            $action[] = $v;
        }

        return $action;
    }


    /**
     * 筛选权限参数
     * @param $action_arr
     * @return array
     */
    protected static function getThisActionNew($action_arr){

        $this_action = [];
        $role_action = getArrVal(self::$role_data,'role',[]);

        //获取用户权限列表
        if( self::$user_data && self::$user_data['is_admin'] ){
            foreach ($action_arr as $k => $v) {
                $this_action = array_merge($this_action,$v);
            }

        }else{
            if($role_action){
                foreach ($action_arr as $k => $v){
                    foreach ($v as $k1 => $v1){
                        if(!isset($role_action[$k])){
                            break;
                        }

                        if(!in_array($v1,$role_action[$k])){
                            continue;
                        }
                        $this_action[] = $v1;
                    }
                }
            }
        }
        return $this_action;
    }

    public static function test(){

        dd(self::$user_data);

    }
}
