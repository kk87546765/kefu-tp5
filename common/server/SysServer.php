<?php
/**
 * 系统
 */
namespace common\server;


use common\base\BasicServer;
use common\model\db_customer\PlatformList;
use common\model\db_customer\QcConfig;
use common\model\db_statistic\GameProduct;
use common\model\db_statistic\PlatformGameInfo;
use common\model\db_statistic\PlatformPaymentInfo;
use common\model\gr_chat\Admin;
use common\model\gr_chat\Menu;
use common\model\gr_chat\RoleList;
use common\model\gr_chat\RunLog;
use common\model\gr_chat\UserGroup;

class SysServer extends BasicServer
{
    /**
     * 获取所有平台信息
     * @param int $f5
     * @return array|mixed
     */
    public static function getPlatformList($f5 = 0){
        $cache_name = __CLASS__.'platform_list';

        if(!$f5){
            $info = cache($cache_name);
            if($info){
                return $info;
            }
        }

        $model = new PlatformList();

        $list = $model->field('platform_id,platform_name AS name,platform_suffix AS suffix,config')->select()->toArray();
        $info = [];
        if($list){
            foreach ($list as $v){
                $info[$v['platform_id']] = $v;
            }
        }
        if($info){
            cache($cache_name,$info,3600);
        }

        return $info;
    }

    /**
     * 根据账号获取平台列表
     * @param array $admin_info
     * @param int $f5
     * @return array
     */
    public static function getPlatformListByAdminInfo(array $admin_info = [], int $f5 = 0){

        $list = self::getPlatformList($f5);

        $list_new = [];
        foreach ($list as $k => $v){
            if($admin_info && $admin_info['is_admin']==0 && !in_array($v['suffix'],explode(',',$admin_info['platform']))){
                continue;
            }
            $list_new[$v['platform_id']] = $v;
        }

        return $list_new;
    }

    /**
     * 获取通用配置数据（缓存）
     * @param int $f5
     * @return array|mixed
     */
    public static function getAllConfigByCache($f5=0){

        $cache_name = 'admin_common_config';

        $info = cache($cache_name);

        if($info && $f5 == 0){
            return $info;
        }

        $model = new QcConfig();

        $info = $model->getAllData();

        if($info){
            cache($cache_name,$info,1800);
        }

        return $info;
    }

    /**
     * 脚本执行记录
     * @param $param
     * @return bool
     */
    public static function setRunLog($param){

        $model = new RunLog();

        $id = getArrVal($param,'id',0);

        if($id){
            $this_info = $model->where('id',$id)->find();
            if(!$this_info)
                return false;

            $save_data = getDataByField($param,['out_param']);
            $save_data['status'] = 1;
            $save_data['update_time'] = time();

            $this_info->save($save_data);
            return true;
        }else{
            $save_data = getDataByField($param,['controller','action','input_param']);
            $res = $model->create($save_data);
            if($res){
                return $res->id;
            }else{
                return false;
            }
        }
    }

    /**
     * 获取用户分组列表
     * @param int $type 1 VIP营销,2 GS内服,3 质检培训,4 在线客服
     * @param int $f5
     * @return array|int|mixed|string|string[]
     */
    public static function getAdminGroupList($type=1,$f5=0){


        $cache_name = 'getAdminGroupList'.$type;

        $info = cache($cache_name);

        if($info && $f5 == 0){
            return $info;
        }

        $Model = new UserGroup();

        $obj = $Model;

        if($type){
            $obj = $obj->where('type',$type);
        }

        $list = $obj->select()->toArray();

        $info = [];

        if($list){
            foreach ($list as $k => $v) {
                $info[$v['id']] = $v;
            }

            cache($cache_name,$info,900);
        }

        return $info;
    }

    /**
     * 获取用户分组列表-可以显示
     * @param int $type 1 VIP营销,2 GS内服,3 质检培训,4 在线客服
     * @param int $f5
     * @return array|int|mixed|string|string[]
     */
    public static function getAdminGroupListShow($type=1,$f5=0){

        $group_list = self::getAdminGroupList($type,$f5);

        foreach ($group_list as $k => $v) {
            if($v['status'] != 1){
                unset($group_list[$k]);
            }
        }

        return $group_list;
    }

    /**
     * @param int $type
     * @param array $admin_info
     * @return array|int|mixed|string|string[]
     */
    public static function getGroupListByAdminInfo(int $type=0,array $admin_info = []){
        $group_list = self::getAdminGroupListShow($type);//分组列表

        $group_list_new = [];
        if($admin_info && $admin_info['is_admin'] == 0){
            $this_arr = explode(',',$admin_info['group_id']);
            foreach ($group_list as $k => $v){
                if(in_array($v['id'],$this_arr)){
                    $group_list_new[$v['id']] = $v;
                }
            }
            return $group_list_new;
        }else{
            return $group_list;
        }
    }

    /**
     * 获取后台账号所有列表（缓存）
     * @param int $role_id
     * @param int $f5
     * @param array $extra
     * @return array id=>[id,name,platform,group_id]
     */
    public static function getAdminListCache($role_id=0,$f5=0,$extra = []){

        $is_active = getArrVal($extra,'is_active',0);
        $cache_name = 'admin_list'.$role_id.'_'.$is_active;

        $last_login_time = 0;
        if($is_active){
            $last_login_time = strtotime('-30 day');
        }


        if($f5 == 0){
            $info = cache($cache_name);
            if($info){
                return $info;
            }
        }

        $list = self::getAdminList($role_id);

        $info = [];

        foreach ($list as $k => $v) {

            if($is_active && $v['status'] !=1 && $v['last_login_time'] <= $last_login_time ){
                continue;
            }

            $info[$v['id']] = $v;
        }

        if($info){
            cache($cache_name,$info,600);
        }
        return $info;
    }

    /**
     * 获取所有后台账号数据
     * @param int $role_id
     * @return array
     */
    public static function getAdminList($role_id=0){

        $where = [];

        if($role_id){
            $where['role_id'] = $role_id;
        }

        $field = '
            id
            ,username
            ,realname AS name
            ,realname
            ,platform
            ,group_id
            ,position_grade
            ,status
            ,role_id
            ,last_login_time
            ,is_admin
        ';
        $list = (new Admin())->field($field)->where($where)->select();

        if($list){
            $list = $list->toArray();
            foreach ($list as &$v){
                self::dealAdminInfo($v);
            }
            return $list;
        }

        return [];
    }

    /**
     * 处理后台账号后续数据
     * @param $info
     */
    public static function dealAdminInfo(&$info){

        // 用户分组信息
        $info['user_group_name'] = '';
        $info['user_group_type'] = 0;
        $info['user_group_type_arr'] = [];
        $info['user_group_name_arr'] = [];
        $info['group_id_arr'] = [];
        if(isset($info['group_id']) &&$info['group_id']){
            $UserGroup = new UserGroup();

            $user_group_info = $UserGroup->where('id','in',$info['group_id'])->select()->toArray();

            if(!empty($user_group_info)){
                foreach ($user_group_info as $k => $v){
                    $info['group_id_arr'][] = $v['id'];
                    $info['user_group_name'] = $v['name'];
                    $info['user_group_type'] = $v['type'];

                    $info['user_group_name_arr'][] = $v['name'];
                    if(!in_array($v['type'],$info['user_group_type_arr'])){
                        $info['user_group_type_arr'][] = $v['type'];
                    }

                }
            }
        }

        //主体信息
        $info['platform_id'] = [];
        if(isset($info['platform']) && $info['platform']){

            $platform_list = self::getPlatformListByAdminInfo($info);
            if($platform_list){
                foreach ($platform_list as $k => $v){
                    $info['platform_id'][] = $v['platform_id'];
                }
            }
        }
        if(isset($info['status']) && $info['status'] == 2){
            if(isset($info['name'])){
                $info['name'] .='(离)';
            }
            if(isset($info['realname'])){
                $info['realname'] .='(离)';
            }
        }
    }

    public static function getMenuArr(){

        $menu = AdminServer::getMenu();

        $list = changeTreeList($menu['all'],0,['id'=>'id','title'=>'menu_name','pid'=>'p_id']);

        $new_list = [];
        foreach ($list as $k => $v) {

            $this_str = $v['menu_name'];
            if($v['type'] == 2) $new_list[$v['controller']][$v['action']] = $this_str;
            if(isset($v['children'])){
                foreach ($v['children'] as $k1 => $v1) {
                    $this_str1 = $this_str.'-'.$v1['menu_name'];
                    if($v1['type'] == 2) $new_list[$v1['controller']][$v1['action']] = $this_str1;
                    if(isset($v1['children'])){
                        foreach ($v1['children'] as $k2 => $v2) {
                            if($v2['type'] == 2) $new_list[$v2['controller']][$v2['action']] = $this_str1.'-'.$v2['menu_name'];
                        }
                    }
                }
            }
        }

        return $new_list;
    }

    /**
     * 递归找到角色所有下级数据
     * @param $role_id
     * @param int $get_self
     * @return array
     */
    public static function getRoleChildrenById($role_id,$get_self = 0){
        $list = [];

        $model = new RoleList();

        if($get_self == 1){
            $res = $model->where(['role_id'=>$role_id])->find();
            if($res){
                $list[] = $res->toArray();
            }
        }

        $res = $model->where(['p_id'=>$role_id])->select()->toArray();

        if($res){

            $list = array_merge($list,$res);

            foreach ($res as $item){
                $list = array_merge($list,self::getRoleChildrenById($item['role_id']));
            }
        }
        return $list;
    }

    /**
     * 统计非超级管理员各权角色限组人数
     * @param $role_id
     * @return mixed
     */
    public static function countUserByRoleId($role_id){

        $param = [];

        if(self::$user_data['is_admin'] == 0){
            $param['role_id'] = $role_id;
        }

        $admin_list = self::getUserListByAdminInfo(self::$user_data,$param);

        $new_count = [];
        if($admin_list){
            foreach ($admin_list as $item){
                if($item['status'] !=1 ){
                    continue;
                }
                if(!isset($new_count[$item['role_id']])){
                    $new_count[$item['role_id']] = 0;
                }
                $new_count[$item['role_id']]++;
            }
        }

        return $new_count;
    }

    /**
     * 根据权限获取用户列表
     * @param array $admin_info
     * @param array $param 条件 platfrom_id mix
     */
    public static function getUserListByAdminInfo(array $admin_info,array $param=[]){
        $platform_id_arr = [];
        $group_type_arr = [];
        $role_id_arr = [];
        $admin_list_extra = [];
        $group_id_arr = [];
        $not_group_type = [];

        if(isset($param['not_group_type']) && $param['not_group_type']){
            if(is_array($param['not_group_type'])){
                $not_group_type = $param['not_group_type'];
            }else{
                $not_group_type= explode(',',$param['not_group_type']);
            }
        }

        if(isset($param['platform_id']) && $param['platform_id']){
            if(is_array($param['platform_id'])){
                $platform_id_arr = $param['platform_id'];
            }else{
                $platform_id_arr = explode(',',$param['platform_id']);
            }
        }
        if(isset($param['group_id']) && $param['group_id']){
            if(is_array($param['group_id'])){
                $group_id_arr = $param['group_id'];
            }else{
                $group_id_arr = explode(',',$param['group_id']);
            }
        }
        if(isset($param['role_id']) && $param['role_id']){
            if(is_array($param['role_id'])){
                $role_id_arr = $param['role_id'];
            }else{
                $role_id_arr = explode(',',$param['role_id']);
            }
        }
        if(isset($param['group_type']) && $param['group_type']){
            if(is_array($param['group_type'])){
                $group_type_arr = $param['group_type'];
            }else{
                $group_type_arr = explode(',',$param['group_type']);
            }
        }
        if(isset($param['c_action']) && $param['c_action']){
            $c_action_arr = explode('-',$param['c_action']);
            $Menu = new Menu();
            $this_where = [];
            $this_where['m.controller'] = camelize($c_action_arr[0],2);
            $this_where['m.action'] = $c_action_arr[1];
            $sql = 'SELECT rm.role_id FROM gr_menu m LEFT JOIN  gr_role_menu_new rm ON m.menu_id = rm.menu_id '.setWhereSql($this_where);
            $role_info = $Menu->query($sql);
            if($role_info){
                foreach ($role_info as $item){
                    $role_id_arr[] = $item['role_id'];
                }
            }
        }
        if(getArrVal($admin_info,'is_admin',0)==0){
            if($platform_id_arr){
                $this_info = arrMID($platform_id_arr,$admin_info['platform_id']);
                if(!$this_info['ai_com']){
                    $platform_id_arr = $this_info['ai_com'];
                }
            }else{
                $platform_id_arr = $admin_info['platform_id'];
            }
            //20210916 管理一下不显示离职用户
            $admin_info_position_grade = getArrVal($admin_info,'position_grade',1);
            if($admin_info_position_grade < QcConfig::POSITION_GRADE_MANAGER){
                $admin_list_extra['is_active'] = 1;
            }
        }

        $admin_list = self::getAdminListCache(0,0,$admin_list_extra);

        foreach ($admin_list as $k =>$v){
            $admin_list[$k]['name'] .= "[$v[username]]";
        }

        if($platform_id_arr){
            foreach ($admin_list as $k => $v){
                $this_info = arrMID($v['platform_id'],$platform_id_arr);
                if(!$this_info['ai_com']){
                    unset($admin_list[$k]);
                }
            }
        }

        if($group_id_arr){
            foreach ($admin_list as $k => $v){
                $this_info = arrMID(explode(',',$v['group_id']),$group_id_arr);
                if(!$this_info['ai_com']){
                    unset($admin_list[$k]);
                }
            }
        }

        if($group_type_arr){
            foreach ($admin_list as $k => $v){
                $this_info = arrMID($v['user_group_type_arr'],$group_type_arr);
                if(!$this_info['ai_com']){
                    unset($admin_list[$k]);
                }
            }
        }

        if($role_id_arr){
            foreach ($admin_list as $k => $v){
                if(!in_array($v['role_id'],$role_id_arr)){
                    unset($admin_list[$k]);
                }
            }
        }

        if($not_group_type){
            foreach ($admin_list as $k => $v){
                $this_info = arrMID($v['user_group_type_arr'],$not_group_type);
                if($this_info['ai_com']){
                    unset($admin_list[$k]);
                }
            }
        }

        return $admin_list;

    }

    /**
     * 获取所有游戏
     * @param int $f5
     * @return array|mixed
     */
    public static function getPlatformGameInfoCache($f5 = 0){
        $cache_name = __CLASS__.'platform_game_info';

        if(!$f5){
            $info = cache($cache_name);
            if($info){
                return $info;
            }
        }

        $model = new PlatformGameInfo();

        $list = $model->select()->toArray();
        $info = [];
        if($list){
            foreach ($list as $v){
                $info[$v['platform_id'].'_'.$v['game_id']] = $v;
            }
        }
        if($info){
            cache($cache_name,$info,3600);
        }

        return $info;
    }

    /**
     * 根据平台获取产品
     * @param int $platform_id 平台id
     * @param int $f5 0 读取缓存 1 读取数据库
     * @return array
     */
    public static function getGameProductCache($platform_id = 0,$f5 = 0){

        $cache_name = __CLASS__.'AllGameProduct_'.$platform_id;

        $info = cache($cache_name);

        if($info && $f5 == 0){
            return $info;
        }

        $model = new GameProduct();

        $where = [];

        if($platform_id){
            $where['platform_id'] = $platform_id;
        }

        $info = $model->field('id,product_name AS name,platform_id,product_id')->where($where)->select()->toArray();

        $new_data = [];

        if($info){
            $platform_list = self::getPlatformList();
            foreach ($info as $k => $v) {

                if(isset($platform_list[$v['platform_id']])){
                    $v['name'].='('.$platform_list[$v['platform_id']]['name'].')';
                }else{
                    $v['name'].='(未知平台)';
                }

                $v['id_str'] = $v['platform_id'].'_'.$v['product_id'];

                $new_data[$v['id']] = $v;
            }

            cache($cache_name,$new_data,1800);
        }

        return $new_data;
    }

    /**
     * 获取用户产品（有权限）
     * @return array
     */
    public static function getGameProductByPower(){

        $admin_info = self::$user_data;

        $platform_arr = getArrVal($admin_info,'platform_id',[]);

        if(!$platform_arr){
            return [];
        }
        $list = [];
        foreach ($platform_arr as $v){
            $list = array_merge($list,self::getGameProductCache($v));
        }

        return $list;
    }

    /**
     * 获取分组下用户
     * @param $group_ids
     * @param int $type 1 用户 2 用户id
     * @return array
     */
    public static function getAdminListByGroupIds($group_ids,$type = 1)
    {
        $list = [];
        $admin_list = self::getUserListByAdminInfo(self::$user_data,['group_id'=>$group_ids]);
        if(!is_array($group_ids)){//单个分组
            $group_ids = explode(',',$group_ids);
        }
        foreach ($admin_list as $k =>$v ){
            if(!$v['group_id']){
                continue;
            }
            $this_group_ids = explode(',',$v['group_id']);
            $this_res = arrMID($this_group_ids,$group_ids);
            if($this_res['ai_com']){
                if($type == 1){
                    $list[$v['id']] = $v;
                }else{
                    $list[] = $v['id'];
                }
            }
        }
        return $list;
    }

    /**
     * 获取平台支付渠道信息
     * @param $platform_id
     * @return array
     */
    public static function getPaymentInfoByPlatformId($platform_id){
        $model = new PlatformPaymentInfo();

        $list = $model->field('payment_id,payment_name')->where(['platform_id'=>$platform_id])->select()->toArray();

        if(!$list){
            return [];
        }

        $new_list = [];

        foreach ($list as $item){
            $new_list[$item['payment_id']] = $item['payment_name'];
        }

        return  $new_list;
    }
}
