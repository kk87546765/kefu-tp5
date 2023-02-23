<?php
namespace app\admin\controller;

use common\server\AdminServer;

class Sysmanage extends Oauth
{
    protected $no_oauth = [
        'userGroupListConfig',
        'roleGetPermissionConfig',
        'commonConfigList',
    ];

    /**
     * 获取信息
     *
     * @return string
     */
	public function index()
	{
        $this->rs['msg'] = '获取成功';
        $this->rs['data'] = [];
        return return_json($this->rs);
	}

    #菜单-列表
    public function menuList(){

        $p_data['click_id'] = $this->request->post('click_id/a',[]);

        $this->rs['data'] = AdminServer::menuList($p_data);

        return return_json($this->rs);
    }
    #菜单-详情
    public function menuDetail(){

	    $id = $this->req->post('id');
        $p_id = $this->req->post('p_id');
        $copy = $this->req->post('copy');

        $info = [];
        $info['p_id'] = $p_id;
        $info['is_show'] = 1;

	    if($id){
            $info = AdminServer::getMenuDetail($id);

            if(!$info){
                $this->rs['code'] = 2;
                $this->rs['msg'] = '数据不存在';
                return return_json($this->rs);
            }

            $info = $info->toArray();


            if($copy){
                $info['menu_id'] = 0;
                $info['menu_name'] .= '(复制)';
                $info['action'] = '';
            }
        }

        $info['menu_type'] = 1;
        $info['menu_lev1'] = 0;
        $info['menu_lev2'] = 0;

        if($info['p_id']){
            foreach ($this->role_data['all'] as $k => $v){
                if($v['id'] == $info['p_id']){
                    $info['menu_type']++;
                    $info['menu_lev1'] = $info['p_id'];
                    if($v['p_id'] > 0){
                        $info['menu_lev1'] = $v['p_id'];
                        $info['menu_lev2'] = $info['p_id'];
                        $info['menu_type']++;
                    }
                    break;
                }
            }
        }

        if(empty($info['type']) && $info['menu_type'] == 3){
            $info['type'] = 2;
        }

        $this->rs['data'] = $info;

	    $this->rs['config'] = AdminServer::menuConfig();

	    return return_json($this->rs);
    }
    #菜单-添加
    public function menuAdd(){

        $this->menuCommonCheck();

        if($this->rs['code'] != 0){
            return return_json($this->rs);
        }

        $p_data = $this->rs['data'];

        $field = [
            'menu_name',
            'controller',
            'action',
            'url',
            'is_show',
            'sort',
            'p_id',
            'type',
        ];

        $data = getDataByField($p_data,$field);

        $ret = AdminServer::menuSave($data);

        if( $ret ){
            $this->rs['msg'] = '添加成功！';
        }else{
            $this->rs['msg'] = '添加失败！';
        }

        return return_json($this->rs);
    }
    #菜单-修改
    public function menuEdit(){

        $this->menuCommonCheck();

        if($this->rs['code'] != 0){
            return return_json($this->rs);
        }

        $p_data = $this->rs['data'];

        $field = [
            'menu_id',
            'menu_name',
            'controller',
            'action',
            'url',
            'is_show',
            'sort',
            'p_id',
            'type',
        ];

        $data = getDataByField($p_data,$field);

        $ret = AdminServer::menuSave($data);

        if( $ret ){
            $this->rs['msg'] = '修改成功！';
        }else{
            $this->rs['msg'] = '修改失败！';
        }

        return return_json($this->rs);
    }
    #菜单-公共参数检查
    protected function menuCommonCheck(){
        $p_data = $this->req->post();

        /*初始化*/
        $p_data['p_id'] = 0;
        /*判断*/
        if( !$p_data['menu_name'] ){
            $this->rs['code'] = 2;
            $this->rs['msg'] = '请输入菜单名！';
            return $this->rs;
        }

        if( $p_data['menu_type']==2 && !$p_data['menu_lev1'] ){
            $this->rs['code'] = 1;
            $this->rs['msg'] = '请选择上级菜单！';
            return $this->rs;
        }

        if( $p_data['menu_type']==3 && !$p_data['menu_lev2'] ){
            $this->rs['code'] = 5;
            $this->rs['msg'] = '请选择二级菜单！';
            return $this->rs;
        }

        if($p_data['type'] == 2){
            if( !$p_data['controller'] ){
                $this->rs['code'] = 3;
                $this->rs['msg'] = '请输入控制器！';
                return $this->rs;
            }
            if( !$p_data['action'] ){
                $this->rs['code'] = 4;
                $this->rs['msg'] = '请输入方法名！';
                return $this->rs;
            }
        }else{

            if($p_data['menu_type'] > 1 && ( (!$p_data['controller'] || !$p_data['action']) && !$p_data['url'] )){
                $this->rs['code'] = 5;
                $this->rs['msg'] = '请输入控制器和操作方法或者填写链接！';
                return $this->rs;
            }
        }

        if(isset($p_data['is_show']) && $p_data['is_show'] == 'on'){
            $p_data['is_show'] = 1;
        }else{
            $p_data['is_show'] = 2;
        }

        if( $p_data['menu_type']==2 ){
            $p_data['p_id'] = $p_data['menu_lev1'];
        }
        if( $p_data['menu_type']==3 ){
            $p_data['p_id'] = $p_data['menu_lev2'];
        }
        $this->rs['data'] = $p_data;

    }
    #菜单-删除
    public function menuDel(){

        $id = $this->req->post('id');

        $ret = AdminServer::menuDel($id);

        if( $ret ){
            $this->rs['msg'] = '删除成功！';
        }else{
            $this->rs['msg'] = '删除失败！';
        }

        return return_json($this->rs);
    }
    #菜单-分配角色权限
    public function menuPowerRole(){
        $p_data = $this->getPost([
            ['menu_id','int',0],
            ['page','int',1],
            ['limit','int',0],

        ]);

        list($list,$count) = AdminServer::getMenuRoleList($p_data);

        if(!$count){
            $this->f_json('no data');
        }

        $res = [];

        foreach ($list as $item){
            $res[] = $item['role_id'];
        }
        $this->s_json($res);
    }
    #菜单-分配角色权限修改
    public function menuPowerRoleSave(){
        $p_data = $this->getPost([
            ['menu_id','int',0],
            ['role_ids','array',[]],
        ]);

        if(empty($p_data['menu_id'])){
            $this->f_json('menu_id error');
        }

        $res = AdminServer::menuPowerRoleSave($p_data['role_ids'],$p_data['menu_id']);

        if($res){
            $this->s_json();
        }else{
            $this->f_json();
        }
    }

    #公共配置-详情
    public function commonConfigList(){

        $this->rs['data'] = AdminServer::getCommonConfigDetail();
        $this->rs['config'] = AdminServer::getCommonConfigBaseInfo();

        return return_json($this->rs);
    }
    #公共配置-修改
    public function commonConfigSave(){

        $p_data = $this->req->post();

        $res = AdminServer::commonConfigSave($p_data);

        if($res){
            $this->rs['msg'] = '保存成功';
        }else{
            $this->rs['msg'] = '保存失败';
            $this->rs['code'] = 1;
        }

        return return_json($this->rs);
    }


    #平台-列表
    public function platformList(){

        $page = $this->req->post('page/d',1);
        $limit = $this->req->post('limit/d',20);
        $p_data['name'] = $this->req->post('name/s','');

        list($list,$count) = AdminServer::getPlatformList($p_data,$page,$limit);

        $this->rs['data'] = $list;
        $this->rs['count'] = $count;

        return return_json($this->rs);
    }
    #平台-详情
    public function platformDetail(){

        $id = $this->req->post('id');

        if($id){
            $info = AdminServer::getPlatformDetail($id);

            if(!$info){
                $this->rs['code'] = 2;
                $this->rs['msg'] = '数据不存在';
                return return_json($this->rs);
            }

            $this->rs['data'] = $info;
        }

//        $this->rs['config'] = AdminServer::platformConfig();

        return return_json($this->rs);
    }
    #平台-添加
    public function platformAdd(){

        $this->platformCommonCheck();

        if($this->rs['code'] != 0){
            return return_json($this->rs);
        }

        $ret = AdminServer::platformSave($this->rs['data']);

        if( $ret ){
            $this->rs['msg'] = '添加成功！';
        }else{
            $this->rs['msg'] = '添加失败！';
        }

        return return_json($this->rs);
    }
    #平台-修改
    public function platformEdit(){

        $this->platformCommonCheck();

        if($this->rs['code'] != 0){
            return return_json($this->rs);
        }

        $ret = AdminServer::platformSave($this->rs['data']);

        if( $ret ){
            $this->rs['msg'] = '修改成功！';
        }else{
            $this->rs['msg'] = '修改失败！';
        }

        return return_json($this->rs);
    }
    #平台-公共参数检查
    protected function platformCommonCheck(){
        $p_data = $this->req->post();

        /*初始化*/

        /*判断*/
        if( empty($p_data['platform_id']) ){
            $this->rs['code'] = 1;
            $this->rs['msg'] = '请输入平台id！';
            return $this->rs;
        }

        if( empty($p_data['platform_name'])){
            $this->rs['code'] = 2;
            $this->rs['msg'] = '请输入平台名称！';
            return $this->rs;
        }

        if( empty($p_data['platform_suffix'])){
            $this->rs['code'] = 3;
            $this->rs['msg'] = '请输入平台后缀！';
            return $this->rs;
        }

        $this->rs['data'] = $p_data;
    }
    #平台-修改
    public function platformDel(){

        $id = $this->req->post('id');

        $ret = AdminServer::platformDel($id);

        if( $ret ){
            $this->rs['msg'] = '删除成功！';
        }else{
            $this->rs['msg'] = '删除失败！';
        }

        return return_json($this->rs);

    }

    #用户组-列表
    public function userGroupList(){

        $page = $this->req->post('page/d',1);
        $limit = $this->req->post('limit/d',20);
        $p_data['name'] = $this->req->post('name/s','');
        $p_data['type'] = $this->req->post('type/d',0);
        $p_data['status'] = $this->req->post('status/d',0);

        list($list,$count) = AdminServer::getUserGroupList($p_data,$page,$limit);

        $this->rs['data'] = $list;
        $this->rs['count'] = $count;

        return return_json($this->rs);
    }
    #用户组-列表
    public function userGroupListConfig(){

        $this->rs['data'] = AdminServer::userGroupConfig();

        return return_json($this->rs);
    }
    #用户组-详情
    public function userGroupDetail(){

        $id = $this->req->post('id');

        if($id){
            $info = AdminServer::getUserGroupDetail($id);

            if(!$info){
                $this->rs['code'] = 2;
                $this->rs['msg'] = '数据不存在';
                return return_json($this->rs);
            }

            $this->rs['data'] = $info;
        }

        $this->rs['config'] = AdminServer::userGroupConfig();

        return return_json($this->rs);
    }
    #用户组-添加
    public function userGroupAdd(){

        $this->userGroupCommonCheck();

        if($this->rs['code'] != 0){
            return return_json($this->rs);
        }

        $ret = AdminServer::userGroupSave($this->rs['data']);

        if( $ret ){
            $this->rs['msg'] = '添加成功！';
        }else{
            $this->rs['msg'] = '添加失败！';
        }

        return return_json($this->rs);
    }
    #用户组-修改
    public function userGroupEdit(){

        $this->userGroupCommonCheck();

        if($this->rs['code'] != 0){
            return return_json($this->rs);
        }

        $ret = AdminServer::userGroupSave($this->rs['data']);

        if( $ret ){
            $this->rs['msg'] = '修改成功！';
        }else{
            $this->rs['msg'] = '修改失败！';
        }

        return return_json($this->rs);
    }
    #用户组-公共参数检查
    protected function userGroupCommonCheck(){
        $p_data = $this->req->post();

        /*初始化*/

        /*判断*/
        if( empty($p_data['name']) ){
            $this->rs['code'] = 1;
            $this->rs['msg'] = '请输入名称！';
            return $this->rs;
        }

        if( empty($p_data['type'])){
            $this->rs['code'] = 2;
            $this->rs['msg'] = '请输入类型！';
            return $this->rs;
        }

        $this->rs['data'] = $p_data;
    }
    #用户组-删除
    public function userGroupDel(){

        $id = $this->req->post('id');

        $ret = AdminServer::userGroupDel($id);

        if( $ret ){
            $this->rs['msg'] = '删除成功！';
        }else{
            $this->rs['msg'] = '删除失败！';
        }

        return return_json($this->rs);

    }
    #用户组-获取分组下的用户id
    public function getAdminIdsByGroupId(){

        $group_id = $this->req->post('group_id');

        $ids = AdminServer::getAdminIdsByGroupId($group_id);//游戏列表

        $this->rs['data'] = $ids;

        return return_json($this->rs);
    }
    #用户组-修改分组分配用户
    public function setAdminIdsByGroupId(){

        $group_id = $this->req->post('group_id');

        $uids = $this->req->post('uids/a');

        $res = AdminServer::setAdminIdsByGroupId($group_id,$uids);

        if($res){
            $this->rs['msg'] = '更新成功';
        }else{
            $this->rs['msg'] = '更新失败';
        }

        return return_json($this->rs);

    }

    #角色-列表
    public function roleList(){

        $p_data['click_id'] = $this->req->post('click_id/a');
        $p_data['list_type'] = $this->req->post('list_type/d',0);

        $list = AdminServer::roleList($p_data);

        $this->rs['data'] = $list;

        return return_json($this->rs);

    }
    #角色-详情
    public function roleDetail(){

        $p_data['role_id'] = $this->req->post('role_id/d',0);
        $p_data['p_id'] = $this->req->post('p_id/d',0);

        $this->rs = array_merge($this->rs,AdminServer::getRoleDetail($p_data));

        return return_json($this->rs);
    }
    #角色-添加
    public function roleAdd(){

        $this->roleCommonCheck();

        if($this->rs['code'] != 0){
            return return_json($this->rs);
        }

        $p_data = $this->rs['data'];

        $field = [
            'role_name',
            'p_id',
        ];

        $data = getDataByField($p_data,$field);

        $ret = AdminServer::roleSave($data);

        if( $ret ){
            $this->rs['msg'] = '添加成功！';
        }else{
            $this->rs['msg'] = '添加失败！';
        }

        return return_json($this->rs);
    }
    #角色-修改
    public function roleEdit(){

        $this->roleCommonCheck();

        if($this->rs['code'] != 0){
            return return_json($this->rs);
        }

        $p_data = $this->rs['data'];

        if(!$p_data['role_id']){
            $this->rs['code'] = 2;
            $this->rs['msg'] = 'role_id not exit';
            return return_json($this->rs);
        }

        $field = [
            'role_id',
            'role_name',
            'p_id',
        ];

        $data = getDataByField($p_data,$field);

        $ret = AdminServer::roleSave($data);

        if( $ret ){
            $this->rs['msg'] = '修改成功！';
        }else{
            $this->rs['msg'] = '修改失败！';
        }

        return return_json($this->rs);
    }
    #角色-公共参数检查
    protected function roleCommonCheck(){

        $p_data['role_name'] = $this->req->post('role_name/s','');
        $p_data['role_id'] = $this->req->post('role_id/d',0);
        $p_data['p_id'] = $this->req->post('p_id/d',0);

        /*判断*/
        if( !$p_data['role_name'] ){
            $this->rs['code'] = 1;
            $this->rs['msg'] = '请输入角色名！';
            return $this->rs;
        }

        $this->rs['data'] = $p_data;

    }
    #角色-删除
    public function roleDel(){

        $id = $this->req->post('id/d',0);

        if(!$id){
            $this->rs['msg'] = 'id not exit！';
            return return_json($this->rs);
        }

        $ret = AdminServer::roleDel($id);

        return return_json(array_merge($this->rs,$ret));
    }
    #角色-获取授权菜单
    public function roleGetPermission(){

        $role_id = $this->req->post('role_id/d',0);

        $this->rs = array_merge($this->rs,AdminServer::roleGetPermission($role_id));

        return return_json($this->rs);
    }
    public function roleGetPermissionConfig(){
        $this->rs['data'] = AdminServer::getMenuRoleListConfig();
        return return_json($this->rs);
    }
    #角色-授权菜单
    public function roleSetPermission(){

        $role_id  = $this->request->post('role_id/d', 0);
        $menu_ids = $this->request->post('menu_ids/a');

        $res = AdminServer::roleSetPermission($role_id,$menu_ids);

        if( $res ){
            $this->rs['msg'] = '授权成功';
        }else{
            $this->rs['msg'] = '授权失败';
            $this->rs['code'] = 1;
        }

        return return_json($this->rs);
    }
}
