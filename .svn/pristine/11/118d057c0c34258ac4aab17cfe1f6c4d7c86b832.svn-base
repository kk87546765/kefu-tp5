<?php

namespace common\server;


use common\base\BasicServer;
use common\model\db_customer\PlatformList;
use common\model\db_customer\QcConfig;
use common\model\gr_chat\Admin;
use common\model\gr_chat\AdminLogN;
use common\model\gr_chat\Menu;
use common\model\gr_chat\Role;
use common\model\gr_chat\RoleList;
use common\model\gr_chat\RoleMenu;
use common\model\gr_chat\RunLog;
use common\model\gr_chat\UserGroup;
use common\sql_server\AdminSqlServer;
use think\Db;

class AdminServer extends BasicServer
{
    public static function test(){
        dd(Db::connect(config('database.db_customer'))->table('work_sheet')->find());
    }


    /**
     * 菜单-获取layui树状结构数据
     * @return array[]
     */
    public static function getMenu(){

        $model = new Menu();
        $field = '
            m.menu_id as id
            ,m.menu_name
            ,m.p_id
            ,m.controller
            ,m.action
            ,m.is_show
            ,m.url
            ,m.type
        ';
        $list = $model->alias('m')->field($field);
        if(self::$user_data['is_admin'] == 0){//非管理（可显示、有权限）
            $list=$list->join('__ROLE_MENU_NEW__ rm','rm.menu_id = m.menu_id','LEFT')
                ->where('rm.role_id',self::$user_data['role_id']);
                //->where('is_show',1);
        }

        $list = $list
            ->order('p_id asc,sort asc,id asc')
            ->select()
            ->toArray();

        $res = [
            'menu'=>[],//前端目录结构
            'action'=>[],//前端权限操作[controller-action]
            'role'=>[],//后端模块权限[controller=>[action]]
            'all'=>[],//所有数据
        ];
        if(!$list){
            return $res;
        }
        $res['all'] = $list;

        foreach ($list as $k => $v){

            if(!$v['url'] && $v['controller'] && $v['action']){
                $v['url'] = $v['controller'].'/'.$v['action'];
            }

            if($v['type'] == 2){//操作
                if($v['url']){
                    $res['action'][] = str_replace('/','-',$v['url']);
                    $res['role'][$v['controller']][] = $v['action'];
                }
            }else{//菜单
                if($v['is_show'] == 1){
                    $res['menu'][] = $v;
                }
            }

        }

        $res['menu'] = changeTreeList($res['menu'],0,$config=['id'=>'id','title'=>'menu_name','pid'=>'p_id']);
        return $res;
    }

    public static function menuList($p_data){

        $list = self::$role_data['all'];

        if(isset($p_data['click_id']) && $p_data['click_id']){
            foreach ($list as &$item){
                if(in_array($item['id'],$p_data['click_id'])){
                    $item['spread'] = true;
                }
            }
        }

        return changeTreeList($list,0,$config=['id'=>'id','title'=>'menu_name','pid'=>'p_id']);
    }
    /**
     * 菜单-详情数据
     * @param $id
     * @return array|false|mixed|string|null
     */
    public static function getMenuDetail($id){

        $model = new Menu();

        $info = $model->where('menu_id',$id)->find();

        return $info;
    }
    /**
     * 菜单-配置数据
     * @return array
     */
    public static function menuConfig(){
        $config = [];
        $config['type_arr'] = Menu::$type_arr;
        $config['menu_type_arr'] = Menu::$menu_type_arr;

        return $config;
    }
    /**
     * 菜单-保存
     * @param $param
     * @return Menu|int|string
     */
    public static function menuSave($param){

        $model = new Menu();

        $id = getArrVal($param,'menu_id',0);

        $type = getArrVal($param,'type',1);

        if($type == 2){
            if(isset($param['controller']) && $param['controller']){
                $param['controller'] = camelize($param['controller'],2);
            }
        }

        if($id){
            $res = $model->where('menu_id',$id)->update($param);
        }else{
            $res = $model->insert($param);
        }

        return $res;
    }
    /**
     * 菜单-删除
     * @param $id
     * @return false
     */
    public static function menuDel($id){

        $model = new Menu();

        $info = $model->where('menu_id',$id)->find();

        if(!$info){
            return false;
        }

        $p_id = $info->p_id;

        $res = $info->delete();

        if($res){
            $model->where('p_id',$id)->update(['p_id'=>$p_id]);
        }

        return $res;
    }
    /**
     * 菜单-或者菜单已授权角色
     * @param $param
     * @return array
     */
    public static function getMenuRoleList($param){

        $page = getArrVal($param,'page',1);
        $limit = getArrVal($param,'limit',0);

        $where_field_rl = [
            'role_id',
            'role_name',
            'p_id',
        ];

        $where = getDataByField($param,$where_field_rl,true,'rl.');

        $where_field_rm = [
            'menu_id',
        ];

        $where = array_merge($where,getDataByField($param,$where_field_rm,true,'rm.'));

        $model = new RoleList();

        $count = $model->alias('rl')->join('__ROLE_MENU_NEW__ rm','rm.role_id = rl.role_id','LEFT')->where($where)->count();

        $res = [
            [],
            0,
        ];

        if(!$count){
            return $res;
        }

        $res[1] = $count;

        $field = '
            rl.role_id
            ,rl.role_name
            ,rl.p_id
        ';
        $model_obj = $model->alias('rl')->field($field)->join('__ROLE_MENU_NEW__ rm','rm.role_id = rl.role_id','LEFT')->where($where);

        if($page && $limit){
            $model_obj = $model_obj->page($page,$limit);
        }

        $res[0] = $model_obj->select()->toArray();

        return $res;
    }

    public static function getMenuRoleListConfig(){
        $config = [];
        $config['admin_role_list'] = SysServer::getRoleChildrenById(self::$user_data['role_id'],1);

        return $config;
    }

    public static function menuPowerRoleSave($role_ids,$menu_id){

        $sql = "insert into gr_role_menu_new(`menu_id`,`role_id`) values";
        if( $role_ids ){
            foreach( $role_ids as $v ){
                $sql .= "({$menu_id},{$v}),";
            }
            $sql = trim($sql,',');
        }else{
            $sql = '';
        }

        $ret = false;
        Db::startTrans();
        try {

            if(Db::table('gr_role_menu_new')->where('menu_id',$menu_id)->count()){
                $res = Db::table('gr_role_menu_new')->where('menu_id',$menu_id)->delete();
            }else{
                $res = true;
            }

            if( $sql ){
                $res2 = Db::table('gr_role_menu_new')->execute($sql);
            }else{
                $res2 = true;
            }
            if( !$res || !$res2 ){
                Db::rollback();
            }
            // 提交事务
            Db::commit();
            $ret = true;
        } catch (\Exception $e) {
            Db::rollback();
        }

        return $ret;
    }

    /**
     * 用户-登出
     * @return mixed
     */
    public static function Logout(){
        $redis = get_redis();
        $session_code = md5(config('sign_md5').self::$user_data['id']);//token存储key
        return $redis->del($session_code);
    }
    /**
     * 用户-保存
     * @param $param
     * @return false|int|string
     */
    public static function userSave($param){


        $code = [0=>'成功',1=>'信息不存在',2=>'密码长度6至20位',3=>'失败'];

        $model = new Admin();

        $id = getArrVal($param,'id',0);

        if(!$id || ($id && isset($param['password']) && $param['password'] )){
            if( strlen($param['password'])<6 || strlen($param['password'])>20 ){
                return ['code'=>2,'msg'=>$code[2]];
            }
        }

        if(isset($param['status'])){
            $param['status'] = $param['status']=="on"?1:2;
        }

        if(isset($param['is_admin'])){
            $param['is_admin'] = $param['is_admin']=="on"?1:0;
        }

        if(isset($param['password'])){
            if($param['password']){
                $param['password'] = pwd_method($param['password']);
            }else{
                unset($param['password']);
            }
        }

        if(isset($param['platform']) && $param['platform']){
            $param['platform'] = implode(',',$param['platform']);
        }
        if(isset($param['group_id']) && $param['group_id']){
            $param['group_id'] = implode(',',$param['group_id']);
        }
        if(isset($param['vip_game_product_ids']) && $param['vip_game_product_ids']){
            $param['vip_game_product_ids'] = implode(',',$param['vip_game_product_ids']);
        }

        $common_field = [
            'realname',
            'password',
            'role_id',
            'status',
            'is_admin',
            'platform',
            'group_id',
            'position_grade',
            'vip_game_product_ids',
        ];

        if(self::$user_data['is_admin'] == 0 ){
            unset($common_field['is_admin']);
        }

        if($id){
            $title = '修改';

            $data = getDataByField($param,$common_field,[]);

            $info = $model->where(['id'=>$id])->find();

            if(!$info){
                return ['code'=>1,'msg'=>$code[1]];
            }

            $ret = $info->save($data);

        }else{
            $title = '添加';

            $common_field[] = 'username';

            $data = getDataByField($param,$common_field,[]);

            $ret = $model->insert($data);

        }

        if( !$ret ){

            return ['code'=>3,'msg'=>$title.$code[3]];
        }

        SysServer::getAdminListCache(0,1);//更新缓存

        return ['code'=>0,'msg'=>$title.$code[0]];
    }
    /**
     * 用户-列表
     * @param $p_data
     * @param $page
     * @param $limit
     * @return array
     */
    public static function userList($p_data,$page,$limit){

        $config_data = self::userListConfig();

        $roles = $config_data['roles'];

        $role_id_arr = $config_data['role_id_arr'];

        $status_arr = $config_data['status_arr'];

        $count = AdminSqlServer::adminListWhere($p_data,self::$user_data,$role_id_arr)->count();

        if($count == 0){
            return [[],0];
        }

        $users = AdminSqlServer::adminListWhere($p_data,self::$user_data,$role_id_arr)
            ->page($page,$limit)
            ->order('id asc')
//            ->fetchSql(true)
            ->select()
            ->toArray()
            ;

        $config = SysServer::getAllConfigByCache();

        $position_grade_arr = $config['position_grade_arr'];

        foreach( $users as &$v ){
            if( $v['is_admin']==1 ){
                $v['role_id'] = "超级管理员";
            }else {
                $v['role_id'] = getArrVal($roles,$v['role_id'],'');
            }
            $v['last_login_time'] =  $v['last_login_time']>0?date("Y-m-d H:i:s",$v['last_login_time']):'';

            $v['position_grade_str'] = isset($position_grade_arr[$v['position_grade']])?$position_grade_arr[$v['position_grade']]:'普通';

            $v['status_str'] = getArrVal($status_arr,$v['status'],'');

            $v['action'] = ListActionServer::checkAdminListAction($v);
        }

        return [$users,$count];
    }
    /**
     * 用户-配置数据
     * @return array
     */
    public static function userListConfig(){
        $roles = [];
        $role_id_arr = [];
        $status_arr = Admin::$status_arr;

        if(self::$user_data['is_admin'] == 0){

            $role_list = SysServer::getRoleChildrenById(self::$user_data['role_id']);
        }else{
            $model = new RoleList();
            $role_list = $model->field('role_id,role_name')->select()->toArray();
        }

        if($role_list){
            foreach ($role_list as $k => $v){
                $roles[$v['role_id']] = $v['role_name'];
                $role_id_arr[] = $v['role_id'];
            }
        }

        return compact('roles','role_id_arr','status_arr');
    }
    /**
     * 用户-详情
     * @param int $id
     * @return array
     * @throws \think\Exception
     */
    public static function userDetail($id = 0){

        $admin = ['id'=>$id];

        $admin_info = self::$user_data;

        $model = new Admin();

        if($id){
            $admin_res = $model->where('id',$id)->find();

            if($admin_res){
                $admin = $admin_res->toArray();

                unset($admin['password']);
            }
        }

        if(isset($admin['platform']) && $admin['platform']){
            $admin['platform'] = explode(',',$admin['platform']);
        }else{
            $admin['platform'] = [];
        }
        if(isset($admin['vip_game_product_ids']) && $admin['vip_game_product_ids']){
            $admin['vip_game_product_ids'] = explode(',',$admin['vip_game_product_ids']);
        }else{
            $admin['vip_game_product_ids'] = [];
        }

        $config = SysServer::getAllConfigByCache();

        $position_grade_arr = $config['position_grade_arr'];

        $group_list = SysServer::getAdminGroupListShow(0);//所有

        $new_group_list = [];

        if($group_list){

            $group_id_arr = getArrVal($admin,'group_id','');

            $group_id_arr = $group_id_arr?explode(',', $admin['group_id']):[];

            foreach ($group_list as $k => $v) {

                if($admin_info['is_admin'] == 0 && !in_array($v['id'],$admin_info['group_id_arr'])){
                    continue;
                }

                if(in_array($v['id'], $group_id_arr)){
                    $v['checked'] = 1;
                }else{
                    $v['checked'] = 0;
                }
                $new_group_list[$v['type']][] = $v;
            }
        }

        // 获取所有平台
        $platform_list = SysServer::getPlatformList();

        $adminConfig = self::userListConfig();
        $roles = $adminConfig['roles'];

        return compact('position_grade_arr',
            'admin',
            'admin_info',
            'roles',
            'new_group_list',
            'platform_list'
        );
    }

    /**
     * 配置-详情
     * @return array
     */
    public static function getCommonConfigDetail(){

        $data = SysServer::getAllConfigByCache(1);

        $key_arr = QcConfig::$normal_key_arr;

        $new_data = [];
        if($data){
            foreach ($data as $k => $v){
                if(!in_array($k ,$key_arr)){
                    continue;
                }

                if( $v && in_array( $k, QcConfig::$split_field_arr)){
                    $v = implode(',',  $v);
                }
                $new_data[$k] = $v;
            }
        }

        return $new_data;
    }
    /**
     * 配置-基础参数
     * @return array
     */
    public static function getCommonConfigBaseInfo(){
        $config = [];
        //会话来源
        $config['kf_source_arr'] = QcConfig::$kf_source_arr;

        return $config;
    }
    /**
     * 配置-保存
     * @param $p_data
     * @return int
     */
    public static function commonConfigSave($p_data){
        $flag = 0;

        if(!$p_data){
            return $flag;
        }

        $old = SysServer::getAllConfigByCache();

        $key_arr = QcConfig::$normal_key_arr;

        $new_data = [];

        foreach ($p_data as $k => $v) {
            if(!in_array($k, $key_arr)){//过滤未控制字段
                continue;
            }
            if( $old && isset($old[$k]) && $old[$k] == $v){//过滤未修改字段
                continue;
            }

            if($k=='sobot'){
                if(isset($v['company_list']) && $v['company_list']){
                    $v['company_list'] = array_values($v['company_list']);
                }
            }

            $new_data[$k] = $v;
        }

        if($new_data){
            $model = new QcConfig();
            foreach ($new_data as $k => $v) {
                $res = $model->saveByKey($k,$v);
                if($res){
                    $flag++;
                }
            }
        }

        if($flag){
            SysServer::getAllConfigByCache(1);
        }

        return $flag;
    }

    /**
     * 平台-列表
     * @param $p_data
     * @param $page
     * @param $limit
     * @return array
     */
    public static function getPlatformList($p_data,$page,$limit){

        $where = [];

        if($p_data['name']){
            $where['platform_name'] = ['like','%'.$p_data['name'].'%'];
        }

        $model = new PlatformList();

        $count = $model->where($where)->count();

        if($count == 0){
            return [[],0];

        }
        $list = $model->where($where)->page($page,$limit)->order('id desc')->select()->toArray();

        if($list){

            $static_arr = PlatformList::$static_arr;
            $admin_list = SysServer::getAdminListCache();
            foreach ($list as $k => $v) {
//                    $list[$k]['add_time_str'] = $v['add_time']?date('y-m-d H:i',$v['add_time']):'未知';
//                    $list[$k]['edit_time_str'] = $v['edit_time']?date('y-m-d H:i',$v['edit_time']):'未知';
                $list[$k]['add_user_id_name'] = isset($admin_list[$v['add_user_id']])?$admin_list[$v['add_user_id']]['name']:'未知';
                $list[$k]['edit_user_id_name'] = isset($admin_list[$v['edit_user_id']])?$admin_list[$v['edit_user_id']]['name']:'未知';
                $list[$k]['static_str'] = isset($static_arr[$v['static']])?$static_arr[$v['static']]:'';
                $list[$k]['action'] = ListActionServer::checkPlatformListAction($v);
            }
        }
        return [$list,$count];
    }
    /**
     * 平台-详情
     * @param $id
     * @return array|false|mixed|string|null
     */
    public static function getPlatformDetail($id){

        $model = new PlatformList();

        $info = $model->where('id',$id)->find();

        return $info;
    }
    /**
     * 平台-保存
     * @param $param
     * @return array|PlatformList|false|mixed
     */
    public static function platformSave($param){

        $model = new PlatformList();

        $id = getArrVal($param,'id',0);

        if(isset($param['config']) && $param['config']){
            $param['config'] = json_encode($param['config']);
        }

        if($id){
            $info = $model->where('id',$id)
                ->find();

            if(!$info){
                return false;
            }
            $param['edit_user_id'] = self::$user_data['id'];

            $res = $info->save($param);
        }else{
            if(isset($param['id'])) unset($param['id']);
            $param['add_user_id'] = self::$user_data['id'];

            $res = $model->allowField(true)->create($param);
        }

        //更新缓存
        SysServer::getPlatformList(1);
//        $redisModel = get_redis();
//        $key = Common::creatCacheKey(PlatformList::PLATFORM_SUFFIX_INFO_KEY);
//        $fieldArr = $redisModel->hkeys($key);
//        if (!empty($fieldArr) && is_array($fieldArr)) {
//            foreach ($fieldArr as $v) {
//                $redisModel->hdel($key,$v);
//            }
//        }

        return $res;
    }
    /**
     * 平台-删除
     * @param $id
     * @return false
     */
    public static function platformDel($id){

        $model = new PlatformList();

        $info = $model->where('id',$id)->find();

        if(!$info){
            return false;
        }

        return $info->delete();
    }

    /**
     * 用户组-列表
     * @param $p_data
     * @param $page
     * @param $limit
     * @return array
     */
    public static function getUserGroupList($p_data,$page,$limit){

        $where = getDataByField($p_data,['type','status'],true);

        if($p_data['name']){
            $where['platform_name'] = ['like','%'.$p_data['name'].'%'];
        }

        $model = new UserGroup();

        $count = $model->where($where)->count();

        $list = [];

        if($count>0){
            $list = $model->where($where)->page($page,$limit)->order('id desc')->select()->toArray();

            if($list){

                $config = self::userGroupConfig();

                $type_list = $config['type_list'];
                $status_arr = $config['status_arr'];
                $admin_list = SysServer::getAdminListCache();

                foreach ($list as $k => $v) {
                    $list[$k]['type_str'] = isset($type_list[$v['type']])?$type_list[$v['type']]:'未知';
                    // dd($admin_list[$v['add_admin_id']]);
                    $list[$k]['add_admin_id_name'] = isset($admin_list[$v['add_admin_id']])?$admin_list[$v['add_admin_id']]['name']:'';
                    $list[$k]['edit_admin_id_name'] = isset($admin_list[$v['edit_admin_id']])?$admin_list[$v['edit_admin_id']]['name']:'';
                    $list[$k]['status_str'] = getArrVal($status_arr,$v['status'],'');
                    $list[$k]['action'] = ListActionServer::checkUserGroupAction($v);
                }
            }

        }

        return [$list,$count];
    }
    /**
     * 用户组-配置数据
     * @return array
     */
    public static function userGroupConfig(){
        $config = [];
        $config['status_arr'] = UserGroup::$status_arr;
        $info = SysServer::getAllConfigByCache();
        $config['type_list'] = getArrVal($info,'user_group_type',[]);

        $admin_list = SysServer::getAdminListCache();
        $config['admin_list'] = [];
        foreach ($admin_list as $v){
            $config['admin_list'][] = $v;
        }

        return $config;
    }
    /**
     * 用户组-详情
     * @param $id
     * @return array|false|mixed|string|null
     */
    public static function getUserGroupDetail($id){

        $model = new UserGroup();

        $info = $model->where('id',$id)->find();

        return $info;
    }
    /**
     * 用户组-保存
     * @param $param
     * @return array|false|mixed
     */
    public static function userGroupSave($param){

        $model = new UserGroup();

        $id = getArrVal($param,'id',0);

        if($id){
            $info = $model->where('id',$id)->find();

            if(!$info){
                return false;
            }

            $param['edit_admin_id'] = self::$user_data['id'];

            $res = $info->save($param);

        }else{
            if(isset($param['id'])) unset($param['id']);
            $param['add_admin_id'] = self::$user_data['id'];

            $res = $model->allowField(true)->create($param);
        }

        SysServer::getAdminGroupList(0,1);//刷新缓存
        SysServer::getAdminGroupList($param['type'],1);//刷新缓存

        return $res;
    }
    /**
     * 用户组-删除
     * @param $id
     * @return false
     */
    public static function userGroupDel($id){

        $model = new UserGroup();

        $info = $model->where('id',$id)->find();

        if(!$info){
            return false;
        }

        return $info->delete();
    }
    /**
     * 用户组-获取分组adminIds
     * @param int $g_id
     * @return array
     */
    public static function getAdminIdsByGroupId(int $g_id){
        $admin_list = SysServer::getAdminListCache();

        $select_ids = [];

        if($admin_list){
            foreach ($admin_list as $k => $v) {
                if($v['group_id']){
                    $this_group_id_arr = explode(',', $v['group_id']);

                    if(in_array($g_id, $this_group_id_arr)){
                        $select_ids[] = $v['id'];
                    }
                }
            }
        }

        return $select_ids;
    }
    /**
     * 用户组-设置分组adminIds
     * @param int $g_id
     * @param array $user_data
     * @return false|int
     */
    public static function setAdminIdsByGroupId(int $g_id,array $user_data){

        if(!$g_id){
            return false;
        }

        $new_user_arr = [];

        if($user_data){
            foreach ($user_data as $k => $v) {
                $new_user_arr[] = $v;
            }
        }

        $admin_list = SysServer::getAdminListCache(0,1);

        $flag = 0;

        if($admin_list){
            $model = new Admin();
            foreach ($admin_list as $k => $v) {
                if(in_array($v['id'], $new_user_arr)){//当前分组用户

                    $this_group_id_arr = explode(',', $v['group_id']);

                    if(!in_array($g_id, $this_group_id_arr)){
                        $this_group_id_arr[] = $g_id;
                        $this_group_id_str = implode(',', array_diff($this_group_id_arr,[0]));

                        $res = $model->where('id',$v['id'])->update(['group_id'=>$this_group_id_str]);
                        if($res){
                            $flag++;
                        }
                    }

                }else{
                    if($v['group_id']){
                        $this_group_id_arr = explode(',', $v['group_id']);

                        if(in_array($g_id, $this_group_id_arr)){
                            //更新用户信息
                            if(count($this_group_id_arr) == 1){
                                $this_group_id_str = 0;
                            }else{
                                $this_group_id_str = implode(',', array_diff($this_group_id_arr,[$g_id]));
                            }
                            $res = $model->where('id',$v['id'])->update(['group_id'=>$this_group_id_str]);
                            if($res){
                                $flag++;
                            }
                        }
                    }
                }

            }
        }
        SysServer::getAdminListCache(0,1);

        return $flag;
    }


    /**
     * 后台访问日志-列表
     * @param $p_data
     * @param $page
     * @param $limit
     * @return array
     */
    public static function getAdminLogList($p_data){

        $page = getArrVal($p_data,'page',1);
        $limit = getArrVal($p_data,'limit',20);

        $model = new AdminLogN();

        $where = getDataByField($p_data,['admin_id','controller','action'],true);

        if(!empty($p_data['ca'])){
            $where[] = getWhereDataArr($p_data['ca'],'concat(controller,"::",action)');
        }

        if(!empty($p_data['start_time'])){
            $where[] = ['add_time','>',strtotime($p_data['start_time'])];
        }

        if(!empty($p_data['end_time'])){
            $where[] = ['add_time','<=',strtotime($p_data['end_time'])];
        }

        $count = $model->where(setWhereSql($where,''))->count();

        if(!$count){
            return [[],0];
        }

        $list_obj = $model->where(setWhereSql($where,''));

        if($page && $limit){
            $list_obj = $list_obj->page($page,$limit);
        }

        $list = $list_obj->order('add_time desc')->select()->toArray();

        $admin_list = SysServer::getAdminListCache();

        $menu_list = SysServer::getMenuArr();

        foreach ($list as $k => &$v) {
            $v['admin_id_str'] = isset($admin_list[$v['admin_id']])?$admin_list[$v['admin_id']]['name']:'';

            if(isset($menu_list[$v['controller']][$v['action']])){
                $v['ca_str'] = $menu_list[$v['controller']][$v['action']];
            }else{
                $v['ca_str'] = '未知';
            }
            $v['data_info'] = json_decode($v['data'],true);
            $v['action_str'] = $v['action'];
            $v['action'] = ['data_info'];
        }

        return [$list,$count];
    }

    public static function getAdminLogStatistic($p_data){

        $model = new AdminLogN();

        $where = getDataByField($p_data,['admin_id','controller','action'],true);

        if(!empty($p_data['ca'])){
            $where[] = getWhereDataArr($p_data['ca'],'concat(controller,"::",action)');
        }

        if(!empty($p_data['start_time'])){
            $where[] = ['add_time','>',strtotime($p_data['start_time'])];
        }

        if(!empty($p_data['end_time'])){
            $where[] = ['add_time','<=',strtotime($p_data['end_time'])];
        }
        $count = $model->where(setWhereSql($where,''))->count();

        if(!$count) return [];

        $list_obj = $model->where(setWhereSql($where,''));

        $list = $list_obj
            ->field('controller,action,count(id) as click_num,count(DISTINCT(admin_id)) as people_nun')
            ->order('click_num DESC,people_nun DESC')
            ->group('controller,action')
            ->select()->toArray();
        if($list){
            $menu_list = SysServer::getMenuArr();
            foreach ($list as $k => &$v){
                if(isset($menu_list[$v['controller']][$v['action']])){
                    $v['ca_str'] = $menu_list[$v['controller']][$v['action']];
                }else{
                    $v['ca_str'] = '未知';
                }
                $v['action_str'] = $v['action'];
                $v['action'] = [];
            }
        }

        return $list;
    }

    /**
     * 后台访问日志-配置数据
     * @return array
     */
    public static function adminLogConfig(){
        $config_list = [];

        $config_list['admin_list'] = SysServer::getAdminListCache();

        $config_list['menu_list'] = SysServer::getMenuArr();

        return $config_list;
    }

    public static function getAdminLogStatisticConfig(){

        $config_list = [];

        $config_list['admin_list'] = SysServer::getAdminListCache();

        $config_list['menu_list'] = SysServer::getMenuArr();

        $form_data = [];
        $form_data['start_time'] = date('Y-m-d 00:00:00');

        $config_list['form_data'] = $form_data;

        return $config_list;
    }


    /**
     * 角色-列表
     * @param $p_data
     * @return array
     */
    public static function roleList($p_data){
        $where = [];
        $model = new RoleList();

        $role_pid = 0;
        $role_id_arr = [];
        if(self::$user_data['is_admin'] == 0){
            $admin_role_list = SysServer::getRoleChildrenById(self::$user_data['role_id']);
            $role_pid = self::$user_data['role_id'];

            if($admin_role_list){
                foreach ($admin_role_list as $item){
                    if($item['role_id']!= self::$user_data['role_id']){
                        $role_id_arr[] = $item['role_id'];
                    }
                }
            }

            if($role_id_arr){
                $where['role_id'] = ['in',$role_id_arr];
            }else{
                $where['role_id'] = 0;
            }
        }

        $list = $model->where($where)->select()->toArray();

        if($list){

            $count_user = SysServer::countUserByRoleId($role_id_arr);

            foreach ($list as &$v){
                if(isset($p_data['click_id']) && $p_data['click_id'] && in_array($v['role_id'],$p_data['click_id'])){
                    $v['spread'] = true;
                }
                $this_info = getArrVal($count_user,$v['role_id'],0);
                if($this_info){
                    $v['role_name'].="($this_info)";
                }
                //$v['action'] = ListActionServer::checkRoleListAction($v);
            }
        }
        if(isset($p_data['list_type']) && $p_data['list_type'] == 0){
            $list = changeTreeList($list,$role_pid,['id'=>'role_id','title'=>'role_name','pid'=>'p_id']);
        }

        return $list;
    }
    /**
     * 角色-详情
     * @param $p_data
     * @return array
     */
    public static function getRoleDetail($p_data){

        $role_id = getArrVal($p_data,'role_id',0);
        $p_id = getArrVal($p_data,'p_id',0);

        $where = [];
        $role = [];
        $role['role_id']= $role_id;
        $role['p_id']= $p_id;
        $model = new RoleList();

        if(self::$user_data['is_admin'] == 0 ){
            $role_list = SysServer::getRoleChildrenById(self::$user_data['role_id'],1);
            if(!$role['p_id']){
                $role['p_id'] = self::$user_data['role_id'];
            }
        }else{
            if($role_id){
                $res = $model->where(['role_id'=>$role_id])->find();
                if($res){
                    $role = $res->toArray();
                    $where['role_id'] = ['neq',$role_id];
                }
            }
            $role_list = $model->where($where)->select()->toArray();//上级角色列表
        }

        $res = $model->where(['role_id'=>$role_id])->find();

        if($res){
            $role = $res->toArray();
        }

        return [
            'data'=>$role,
            'config'=>compact('role_list'),
        ];
    }
    /**
     * 角色-保存
     * @param $p_data
     * @return RoleList|int|string
     */
    public static function roleSave($p_data){

        $model = new RoleList();

        if(isset($p_data['role_id']) && $p_data['role_id']){
            return $model->where('role_id',$p_data['role_id'])->update($p_data);
        }else{
            return $model->insert($p_data);
        }
    }
    /**
     * 角色-删除
     * @param $id
     * @return array
     */
    public static function roleDel($id){

        $code = [
            0=>'删除成功',1=>'该节点下有数据，请先处理改接点下的数据!',2=>'删除失败',3=>'数据不存在'
        ];

        $model = new RoleList();

        $count = $model->where(['p_id'=>$id])->find();

        if($count){
            return ['code'=>1,'msg'=>$code[1]];
        }

        $info = $model->where('role_id',$id)->find();

        if(!$info){
            return ['code'=>3,'msg'=>$code[3]];
        }

        $res = $info->delete();

        if($res){
            return ['code'=>0,'msg'=>$code[0]];
        }else{
            return ['code'=>2,'msg'=>$code[2]];
        }
    }
    /**
     * 角色-角色授权菜单获取
     * @param $role_id
     * @return array
     */
    public static function roleGetPermission($role_id){

        $menu_list = self::$role_data['all'];//当前账号可分配菜单

        $roleMenuModel = new RoleMenu();

        $role_id_menu_id = $roleMenuModel->where('role_id',$role_id)->column('menu_id');//目标角色已拥有菜单id

        foreach ($menu_list as $k=>$v){
            if(in_array($v['id'],$role_id_menu_id)){//分配接口默认选中 $v['type'] ==2 &&
                $menu_list[$k]['checked'] = 1;
            }
        }
        //转换成layui树状结构
        $role_menus = changeTreeList($menu_list,0,$config=['id'=>'id','title'=>'menu_name','pid'=>'p_id']);

        $model = new RoleList();

        $role = $model->where('role_id',$role_id)->find();//当期角色基础数据

        if($role) $role = $role->toArray();


        return [
            'data'=>$role,
            'config'=>compact('role_menus'),
        ];
    }
    /**
     * 角色-角色授权菜单保存
     * @param $role_id
     * @param $menu_ids
     * @return bool
     */
    public static function roleSetPermission($role_id,$menu_ids){

        $sql = "insert into gr_role_menu_new(`role_id`,`menu_id`) values";
        if( $menu_ids ){
            foreach( $menu_ids as $v ){
                $sql .= "({$role_id},{$v}),";
            }
            $sql = trim($sql,',');
        }else{
            $sql = '';
        }

        $ret = false;

        Db::startTrans();
        try{
            if(Db::table('gr_role_menu_new')->where('role_id',$role_id)->count()){
                $res = Db::table('gr_role_menu_new')->where('role_id',$role_id)->delete();
            }else{
                $res = true;
            }

            if( $sql ){
                $res2 = Db::table('gr_role_menu_new')->execute($sql);
            }else{
                $res2 = true;
            }

            if( $res && $res2 ){
                $ret = true;
            }else{
                Db::rollback();
            }
            
            // 提交事务
            Db::commit();
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
        }

        return $ret;
    }


    /**
     * 后台访问日志-列表
     * @param $p_data
     * @param $page
     * @param $limit
     * @return array
     */
    public static function getRunLogList($p_data){

        $page = getArrVal($p_data,'page',1);
        $limit = getArrVal($p_data,'limit',20);

        $model = new RunLog();

        $where = getDataByField($p_data,['controller','action'],true);

        if(!empty($p_data['ca'])){
            $where[] = getWhereDataArr($p_data['ca'],'concat(controller,"::",action)');
        }

        if(!empty($p_data['start_time'])){
            $where[] = ['add_time','>',strtotime($p_data['start_time'])];
        }

        if(!empty($p_data['end_time'])){
            $where[] = ['add_time','<=',strtotime($p_data['end_time'])];
        }

        if(!empty($p_data['status'])){
            $where[] = ['status','=',$p_data['status']-1];
        }

        $count = $model->where(setWhereSql($where,''))->count();

        if(!$count){
            return [[],0];
        }

        $list_obj = $model->where(setWhereSql($where,''));

        if($page && $limit){
            $list_obj = $list_obj->page($page,$limit);
        }

        $list = $list_obj->order('add_time desc')->select()->toArray();

        foreach ($list as $k => &$v) {
            $v['data_info']['input_param_data'] = json_decode($v['input_param'],true);
            $v['data_info']['out_param_data'] = json_decode($v['out_param'],true);
            $v['status_str'] = getArrVal(RunLog::STATUS_ARR,$v['status'],'');
        }

        return [$list,$count];
    }

    public static function getRunLogListConfig(){
        $config = [];
        $config['status_arr'] = RunLog::STATUS_ARR;
        $config['form_data'] = [
            'start_time'=>date('Y-m-d 00:00:00'),
        ];

        return $config;
    }

    public static function getRunLogStatistic($p_data){

        $model = new RunLog();

        $where = getDataByField($p_data,['controller','action'],true);

        if(!empty($p_data['ca'])){
            $where[] = getWhereDataArr($p_data['ca'],'concat(controller,"::",action)');
        }

        if(!empty($p_data['status'])){
            $where[] = ['status','=',$p_data['status']-1];
        }

        if(!empty($p_data['start_time'])){
            $where[] = ['add_time','>',strtotime($p_data['start_time'])];
        }

        if(!empty($p_data['end_time'])){
            $where[] = ['add_time','<=',strtotime($p_data['end_time'])];
        }
        $count = $model->where(setWhereSql($where,''))->count();

        if(!$count) return [];

        $list_obj = $model->where(setWhereSql($where,''));

        $field = '
            controller
            ,action
            ,count(id) as click_num
        ';

        foreach (RunLog::STATUS_ARR as $k =>$v){
            $field.=',sum(CASE WHEN status='.$k.' THEN 1 ELSE 0 END) as '.$v;
        }

        $list = $list_obj
            ->field($field)
            ->order('click_num DESC')
            ->group('controller,action')
            ->select()
            ->toArray();
        if($list){
//            foreach ($list as $k => &$v){
//
//            }
        }

        return $list;
    }
}
