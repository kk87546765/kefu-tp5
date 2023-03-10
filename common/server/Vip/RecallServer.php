<?php
/**
 * 系统
 */
namespace common\server\Vip;

use common\base\BasicServer;
use common\model\db_customer\{RecallCode,
    RecallCodeGroup,
    RecallCodeRe,
    RecallLink,
    RecallLinkGroup,
    RecallLinkRe,
    RecallPlan,
    RecallPlanLog};

use common\server\ListActionServer;
use common\server\SysServer;

class RecallServer extends BasicServer
{
    /*礼包码*/

    public static function codeList($params){
        $res = [
            'data'=>[],
            'count'=>0,
        ];
        $page = getArrVal($params,'page',1);
        $limit = getArrVal($params,'limit',20);
        $is_excel = getArrVal($params,'is_excel',0);
        $join_cr = 0;

        $where = getDataByField($params,['platform_id','admin_id','up_id'],true,'c.');

        if(!empty($params['code'])){
            $where[] = ['c.code','like',"%".$params['code']."%"];
        }

        if(isset($params['status']) && $params['status'] >= 0){
            $where['c.status'] = $params['status'];
        }

        if(!empty($params['p_up'])){
            $where['c.up_id'] = explode('_',$params['p_up'])[1];
        }

        if(!empty($params['group_id'])){
            $where[] = ['cr.group_code_id','=',$params['group_id']];
            $join_cr =1;
        }

        $model = new RecallCode();

        $count = $model
            ->alias('c')
            ->field('c.*');
        if($join_cr){
            $count = $count->join('recall_code_re cr','c.id = cr.code_id','right');
        }
        $count = $count->where(setWhereSql($where,''))
            ->count();

        if(!$count) return $res;

        $res['count'] = $count;

        $list = $model
            ->alias('c')
            ->field('c.*,up.up_name');
        if($join_cr){
            $list = $list->join('recall_code_re cr','c.id = cr.code_id','left');
        }
        $list = $list->join('db_statistic.up_game_product up','c.platform_id = up.platform_id AND c.up_id = up.up_id')
            ->where(setWhereSql($where,''));

        if($page && $limit){
            $list = $list->page($page,$limit);
        }
//            ->fetchSql()
        $list = $list->select()
            ->toArray();

        $admin_list = SysServer::getAdminListCache();
        $status_arr = RecallCode::$status_arr;
        $platform_list = SysServer::getPlatformList();

        foreach ($list as &$item){

            $item['action'] = ListActionServer::checkRecallCodeList($item);

            $this_admin_info = getArrVal($admin_list,$item['admin_id'],[]);
            if($this_admin_info) {
                $item['admin_id_str'] = $this_admin_info['name'];
            }else{
                $item['admin_id_str'] =  '未知';
            }

            $item['status_str'] = getArrVal($status_arr,$item['status'],'未知');

            $this_platform_info = getArrVal($platform_list,$item['platform_id'],[]);
            if($this_platform_info) {
                $item['up_name'].= '('.$this_platform_info['name'].")";
            }else{
                $item['up_name'].=  '(未知)';
            }
        }

        $res['data'] = $list;

        return $res;
    }

    public static function codeConfig(){
        $config = [];

        $config['search_config'] = [
            'platform'=>['radio'=>true],
            'up_product'=>['status'=>1,'radio'=>true,],
            'admin'=>[
                'status'=>1,
                'p_data'=>[
//                    'group_type'=>[QcConfig::USER_GROUP_VIP],
                    'is_active'=>1,
                ],
                'name'=>'admin_id',
                'radio'=>true,
            ],
        ];

        $config['status_arr'] = RecallCode::$status_arr;
        return $config;
    }

    public static function codeDetail($id){
        $info = [];

        if($id){
            $model = new RecallCode();

            $obj = $model->where('id',$id)->find();

            if($obj){
                $info = $obj->toArray();
            }
        }

        $config = [];
        $config['status_arr'] = RecallCode::$status_arr;
        $config['platform_list'] = SysServer::getPlatformListByAdminInfo(self::$user_data);

        return ['data'=>$info,'config'=>$config];
    }

    public static function codeSave($param){

        $id = getArrVal($param,'id',0);

        $model = new RecallCode();
        $param['admin_id'] = self::$user_data['id'];
        if($id){
            $info = $model->where('id',$id)->find();

            if(!$info){
                return false;
            }

            $res = $info->save($param);
        }else{

            $res = $model->create($param);
        }

        return $res;
    }

    /*礼包码-分组*/

    public static function codeGroupList($params){
        $res = [
            'data'=>[],
            'count'=>0,
        ];
        $page = getArrVal($params,'page',1);
        $limit = getArrVal($params,'limit',20);
        $is_excel = getArrVal($params,'is_excel',0);

        $where = getDataByField($params,['platform_id','admin_id','up_id'],true,'cg.');

        if(!empty($params['title'])){
            $where[] = ['cg.title','like',"%".$params['title']."%"];
        }

        if(isset($params['status']) && $params['status'] >= 0){
            $where['cg.status'] = $params['status'];
        }

        if(!empty($params['p_up'])){
            $where['cg.up_id'] = explode('_',$params['p_up'])[1];
        }

        $model = new RecallCodeGroup();

        $count = $model->alias('cg')->where(setWhereSql($where,''))->count();

        if(!$count) return $res;

        $res['count'] = $count;

        $list = $model
            ->alias('cg')
            ->field('cg.*,up.up_name')
            ->join('db_statistic.up_game_product up','cg.platform_id = up.platform_id AND cg.up_id = up.up_id')
            ->where(setWhereSql($where,''))
            ->page($page,$limit)
//            ->fetchSql()
            ->select()
            ->toArray();

        $admin_list = SysServer::getAdminListCache();
        $status_arr = RecallCodeGroup::$status_arr;
        $platform_list = SysServer::getPlatformList();

        foreach ($list as &$item){

            $item['action'] = ListActionServer::checkRecallCodeGroupList($item);

            $this_admin_info = getArrVal($admin_list,$item['admin_id'],[]);
            if($this_admin_info) {
                $item['admin_id_str'] = $this_admin_info['name'];
            }else{
                $item['admin_id_str'] =  '未知';
            }

            $item['status_str'] = getArrVal($status_arr,$item['status'],'未知');
            $item['type_str'] = getArrVal(RecallCodeGroup::$type_arr,$item['type'],'未知');

            $this_platform_info = getArrVal($platform_list,$item['platform_id'],[]);
            if($this_platform_info) {
                $item['up_name'].= '('.$this_platform_info['name'].")";
            }else{
                $item['up_name'].=  '(未知)';
            }

            $this_code_arr = self::getCodeByGroupId($item['id']);

            if($this_code_arr){
                $item['code_str'] = implode(',',$this_code_arr);
            }else{
                $item['code_str'] = '';
            }
        }

        $res['data'] = $list;

        return $res;
    }

    public static function codeGroupConfig(){
        $config = [];

        $config['search_config'] = [
            'platform'=>['radio'=>true],
            'up_product'=>['status'=>1,'radio'=>true,],
            'admin'=>[
                'status'=>1,
                'p_data'=>[
//                    'group_type'=>[QcConfig::USER_GROUP_VIP],
                    'is_active'=>1,
                ],
                'name'=>'admin_id',
                'radio'=>true,
            ],
        ];

        $config['status_arr'] = RecallCodeGroup::$status_arr;
        $config['type_arr'] = RecallCodeGroup::$type_arr;
        return $config;
    }

    public static function codeGroupDetail($id){
        $info = [];

        if($id){
            $model = new RecallCodeGroup();

            $obj = $model->where('id',$id)->find();

            if($obj){
                $info = $obj->toArray();
                $info['code'] = [];
                $this_code_arr = self::getCodeByGroupId($id);
                if($this_code_arr){
                    $info['code'] =array_keys($this_code_arr);
                }
            }
        }

        $config = [];
        $config['status_arr'] = RecallCodeGroup::$status_arr;
        $config['type_arr'] = RecallCodeGroup::$type_arr;
        $config['platform_list'] = SysServer::getPlatformListByAdminInfo(self::$user_data);

        return ['data'=>$info,'config'=>$config];
    }

    public static function codeGroupSave($param){

        $id = getArrVal($param,'id',0);

        $model = new RecallCodeGroup();

        $common_field = [
            'platform_id',
            'up_id',
            'type',
            'title',
            'status',
        ];
        $save_data = getDataByField($param,$common_field);
        $save_data['admin_id'] = self::$user_data['id'];
        if($id){
            $info = $model->where('id',$id)->find();

            if(!$info){
                return false;
            }

            $res = $info->save($save_data);
        }else{

            $res = $model->create($save_data);
            if($res){
                $id = $res->id;
            }
        }

        if($res){
            self::combineCode($id,$param['code']);
        }


        return $res;
    }

    public static function combineCode($id,$code){
        $model = new RecallCodeRe();

        $model->where('group_code_id',$id)->delete();

        if(!empty($code)){
            $code_arr = splitToArr($code);

            $insert_data = [];

            foreach ($code_arr as $item){
                $insert_data[] = [
                    'code_id'=>$item,
                    'group_code_id'=>$id,
                ];
            }

            $model->insertAll($insert_data);
        }



    }

    public static function getCodeByGroupId($id,$field_name = 'code'){
        $model = new RecallCode();

        $field = 'c.*';

        $where = [
            'r.group_code_id'=>$id,
            'c.status'=>RecallCode::STATUS_OPEN,
        ];

        $list = $model
            ->alias('c')
            ->field($field)
            ->join('recall_code_re r','r.code_id = c.id')
            ->where($where)
            ->select()
            ->toArray();

        $res = [];

        if($list){
            foreach ($list as $item){
                $res[$item['id']] = $item[$field_name];
            }
        }

        return $res;
    }



    /*链接*/

    public static function linkList($params){
        $res = [
            'data'=>[],
            'count'=>0,
        ];
        $page = getArrVal($params,'page',1);
        $limit = getArrVal($params,'limit',20);
        $is_excel = getArrVal($params,'is_excel',0);
        $join_cr = 0;

        $where = getDataByField($params,['platform_id','admin_id','up_id'],true,'c.');

        if(!empty($params['link_ids'])){
            $where[] = ['c.id','in',$params['link_ids']];
        }

        if(!empty($params['ver_id'])){
            $where[] = ['c.ver_id','in',splitToArr($params['ver_id'])];
        }

        if(!empty($params['ver_title'])){
            $where[] = ['c.ver_title','like',"%".$params['ver_title']."%"];
        }

        if(!empty($params['link'])){
            $where[] = ['c.link','like',"%".$params['link']."%"];
        }

        if(isset($params['status']) && $params['status'] >= 0){
            $where['c.status'] = $params['status'];
        }

        if(!empty($params['p_up'])){
            $where['c.up_id'] = explode('_',$params['p_up'])[1];
        }

        if(!empty($params['group_id'])){
            $where['cr.group_link_id'] = $params['group_id'];
            $join_cr = 1;

        }

        $model = new RecallLink();

        $count = $model
            ->alias('c');
        if($join_cr){
            $count = $count->join('recall_link_re cr','c.id = cr.link_id','right');
        }
        $count = $count->where(setWhereSql($where,''))
            ->count();

        if(!$count) return $res;

        $res['count'] = $count;

        $list = $model
            ->alias('c')
            ->field('c.*,up.up_name');
        if($join_cr){
            $list = $list->join('recall_link_re cr','c.id = cr.link_id','right');
        }
        $list = $list->join('db_statistic.up_game_product up','c.platform_id = up.platform_id AND c.up_id = up.up_id')
            ->where(setWhereSql($where,''));

        if($page && $limit){
            $list = $list->page($page,$limit);
        }

//            ->fetchSql()
        $list = $list->order('id desc')->select()
            ->toArray();

        $admin_list = SysServer::getAdminListCache();
        $status_arr = RecallLink::$status_arr;
        $platform_list = SysServer::getPlatformList();

        foreach ($list as &$item){

            $item['action'] = ListActionServer::checkRecallLinkList($item);

            $this_admin_info = getArrVal($admin_list,$item['admin_id'],[]);
            if($this_admin_info) {
                $item['admin_id_str'] = $this_admin_info['name'];
            }else{
                $item['admin_id_str'] =  '未知';
            }

            $item['status_str'] = getArrVal($status_arr,$item['status'],'未知');

            $this_platform_info = getArrVal($platform_list,$item['platform_id'],[]);
            if($this_platform_info) {
                $item['up_name'].= '('.$this_platform_info['name'].")";
            }else{
                $item['up_name'].=  '(未知)';
            }
        }

        $res['data'] = $list;

        return $res;
    }

    public static function linkConfig(){
        $config = [];

        $config['search_config'] = [
            'platform'=>['radio'=>true],
            'up_product'=>['status'=>1,'radio'=>true,],
            'admin'=>[
                'status'=>1,
                'p_data'=>[
//                    'group_type'=>[QcConfig::USER_GROUP_VIP],
                    'is_active'=>1,
                ],
                'name'=>'admin_id',
                'radio'=>true,
            ],
        ];

        $config['status_arr'] = RecallLink::$status_arr;
        return $config;
    }

    public static function linkDetail($id){
        $info = [];

        if($id){
            $model = new RecallLink();

            $obj = $model->where('id',$id)->find();

            if($obj){
                $info = $obj->toArray();
            }
        }

        $config = [];
        $config['status_arr'] = RecallLink::$status_arr;
        $config['platform_list'] = SysServer::getPlatformListByAdminInfo(self::$user_data);

        return ['data'=>$info,'config'=>$config];
    }

    public static function linkSave($param){

        $id = getArrVal($param,'id',0);

        $model = new RecallLink();
        $param['admin_id'] = self::$user_data['id']??0;
        if($id){
            $info = $model->where('id',$id)->find();

            if(!$info){
                return false;
            }

            $res = $info->save($param);
        }else{

            $res = $model->create($param);
        }

        return $res;
    }

    public static function linkCheckSet($param){
        $model = new RecallLink();

        $where = getDataByField($param,['platform_id','ver_id']);

        $info = $model->where($where)->find();

        if($info){
            $res = $info->save($param);
        }else{
            $res = $model->create($param);
        }

        return $res;
    }

    /*链接-分组*/

    public static function linkGroupList($params){
        $res = [
            'data'=>[],
            'count'=>0,
        ];
        $page = getArrVal($params,'page',1);
        $limit = getArrVal($params,'limit',20);
        $is_excel = getArrVal($params,'is_excel',0);

        $where = getDataByField($params,['platform_id','admin_id','up_id'],true,'cg.');

        if(!empty($params['title'])){
            $where[] = ['cg.title','like',"%".$params['title']."%"];
        }

        if(isset($params['status']) && $params['status'] >= 0){
            $where['cg.status'] = $params['status'];
        }

        if(!empty($params['p_up'])){
            $where['cg.up_id'] = explode('_',$params['p_up'])[1];
        }

        $model = new RecallLinkGroup();

        $count = $model->alias('cg')->where(setWhereSql($where,''))->count();

        if(!$count) return $res;

        $res['count'] = $count;

        $list = $model
            ->alias('cg')
            ->field('cg.*,up.up_name')
            ->join('db_statistic.up_game_product up','cg.platform_id = up.platform_id AND cg.up_id = up.up_id')
            ->where(setWhereSql($where,''))
            ->page($page,$limit)
//            ->fetchSql()
            ->select()
            ->toArray();

        $admin_list = SysServer::getAdminListCache();
        $status_arr = RecallLinkGroup::$status_arr;
        $platform_list = SysServer::getPlatformList();

        foreach ($list as &$item){

            $item['action'] = ListActionServer::checkRecallLinkGroupList($item);

            $this_admin_info = getArrVal($admin_list,$item['admin_id'],[]);
            if($this_admin_info) {
                $item['admin_id_str'] = $this_admin_info['name'];
            }else{
                if($item['admin_id']){
                    $item['admin_id_str'] =  '未知';
                }else{
                    $item['admin_id_str'] =  '自动';
                }
            }

            $item['status_str'] = getArrVal($status_arr,$item['status'],'未知');

            $this_platform_info = getArrVal($platform_list,$item['platform_id'],[]);
            if($this_platform_info) {
                $item['up_name'].= '('.$this_platform_info['name'].")";
            }else{
                $item['up_name'].=  '(未知)';
            }

            $this_link_arr = self::getLinkByGroupId($item['id']);

            if($this_link_arr){
                $item['link_str'] = implode(',',$this_link_arr);
            }else{
                $item['link_str'] = '';
            }
        }

        $res['data'] = $list;

        return $res;
    }

    public static function linkGroupConfig(){
        $config = [];

        $config['search_config'] = [
            'platform'=>['radio'=>true],
            'up_product'=>['status'=>1,'radio'=>true,],
            'admin'=>[
                'status'=>1,
                'p_data'=>[
//                    'group_type'=>[QcConfig::USER_GROUP_VIP],
                    'is_active'=>1,
                ],
                'name'=>'admin_id',
                'radio'=>true,
            ],
        ];

        $config['status_arr'] = RecallLinkGroup::$status_arr;
        return $config;
    }

    public static function linkGroupDetail($id){
        $info = [];

        if($id){
            $model = new RecallLinkGroup();

            $obj = $model->where('id',$id)->find();

            if($obj){
                $info = $obj->toArray();
                $info['link'] = [];
                $this_link_arr = self::getLinkByGroupId($id);
                if($this_link_arr){
                    $info['link'] =array_keys($this_link_arr);
                }
            }
        }

        $config = [];
        $config['status_arr'] = RecallLinkGroup::$status_arr;
        $config['platform_list'] = SysServer::getPlatformListByAdminInfo(self::$user_data);

        return ['data'=>$info,'config'=>$config];
    }

    public static function linkGroupSave($param){

        $id = getArrVal($param,'id',0);

        $model = new RecallLinkGroup();

        $common_field = [
            'platform_id',
            'up_id',
            'title',
            'status',
        ];
        $save_data = getDataByField($param,$common_field);
        $save_data['admin_id'] = self::$user_data['id'];
        if($id){
            $info = $model->where('id',$id)->find();

            if(!$info){
                return false;
            }
            $flag = 0;
            foreach ($save_data as $k => $v){
                if($v != $info[$k]){
                    $flag++;
                }
            }

            if($flag){
                $res = $info->save($save_data);
            }else{
                $res = 1;
            }

        }else{

            $res = $model->create($save_data);
            if($res){
                $id = $res->id;
            }
        }

        if($res){
            self::combineLink($id,$param['link']);
        }


        return $res;
    }

    public static function combineLink($id,$link){
        $model = new RecallLinkRe();

        $model->where('group_link_id',$id)->delete();

        if(!empty($link)){
            $link_arr = splitToArr($link);

            $insert_data = [];

            foreach ($link_arr as $item){
                $insert_data[] = [
                    'link_id'=>$item,
                    'group_link_id'=>$id,
                ];
            }

            $model->insertAll($insert_data);
        }



    }

    public static function getLinkByGroupId($id,$field_name = 'link'){
        $model = new RecallLink();

        $field = 'c.*';

        $where = [
            'r.group_link_id'=>$id,
            'c.status'=>RecallLink::STATUS_OPEN,
        ];

        $list = $model
            ->alias('c')
            ->field($field)
            ->join('recall_link_re r','r.link_id = c.id')
            ->where($where)
            ->select()
            ->toArray();

        $res = [];

        if($list){
            foreach ($list as $item){
                $res[$item['id']] = $item[$field_name];
            }
        }

        return $res;
    }


    public static function getLinkByVerId($platform_id,$ver_id,$field='link')
    {
        $model = new RecallLink();
        $list = $model

            ->field('*')
            ->where("platform_id = {$platform_id}  and ver_id in ({$ver_id})")
            ->select()
            ->toArray();

        $res = [];

        if($list){
            foreach ($list as $item){
                $res[$item['id']] = $item[$field];
            }
        }

        return $res;
    }

    /*计划*/

    public static function planList($params){
        $res = [
            'data'=>[],
            'count'=>0,
        ];
        $page = getArrVal($params,'page',1);
        $limit = getArrVal($params,'limit',20);
        $is_excel = getArrVal($params,'is_excel',0);

        $where = getDataByField($params,['platform_id','admin_id','recall_up_id','loss_up_id','execute_type'],true,'p.');

        if(!empty($params['plan_ids'])){
            $where[] = ['p.id','in',$params['plan_ids']];
        }

        if(!empty($params['title'])){
            $where[] = ['p.title','like',"%".$params['title']."%"];
        }

        if(isset($params['status']) && $params['status'] >= 0){
            $where['p.status'] = $params['status'];
        }

        if(!empty($params['p_recall_up'])){
            $where['p.recall_up_id'] = explode('_',$params['p_recall_up'])[1];
        }

        if(!empty($params['p_loss_up'])){
            $where['p.loss_up_id'] = explode('_',$params['p_loss_up'])[1];
        }

        $model = new RecallPlan();

        $count = $model->alias('p')->where(setWhereSql($where,''))->count();

        if(!$count) return $res;

        $res['count'] = $count;

        $field = '
            p.*
            ,up2.up_name as recall_up_name
            ,cg.title as code_title
            ,lg.title as link_title
        ';

        $list = $model
            ->alias('p')
            ->field($field)
            ->join('recall_code_group cg','p.platform_id = cg.platform_id AND p.recall_code_group_id = cg.id','left')
            ->join('recall_link_group lg','p.platform_id = lg.platform_id AND p.recall_ver_group_id = lg.id','left')
//            ->join('db_statistic.up_game_product up','p.platform_id = up.platform_id AND p.loss_up_id = up.up_id','left')
            ->join('db_statistic.up_game_product up2','p.platform_id = up2.platform_id AND p.recall_up_id = up2.up_id','left')
            ->where(setWhereSql($where,''));

        if($page && $limit){
            $list = $list->page($page,$limit);
        }
        $list = $list->order('p.id desc')->select()
            ->toArray();

        $admin_list = SysServer::getAdminListCache();
        $platform_list = SysServer::getPlatformList();

        foreach ($list as &$item){

            $item['action'] = ListActionServer::checkRecallPlanList($item);

            $this_admin_info = getArrVal($admin_list,$item['admin_id'],[]);

            if($this_admin_info) {
                $item['admin_id_str'] = $this_admin_info['name'];
            }else{
                $item['admin_id_str'] =  '未知';
            }

            $item['status_str'] = getArrVal(RecallPlan::$status_arr,$item['status'],'未知');
            $item['execute_type_str'] = getArrVal(RecallPlan::$execute_type_arr,$item['execute_type'],'未知');

            $this_platform_info = getArrVal($platform_list,$item['platform_id'],[]);

            if($this_platform_info) {
                $item['platform_id_str']= $this_platform_info['name'];
            }else{
                $item['platform_id_str']= '未知';
            }

            $item['account_money_area'] = $item['min_account_money'].'~'.$item['max_account_money'];
            $item['loss_up_money_area'] = $item['min_loss_up_money'].'~'.$item['max_loss_up_money'];
            $item['loss_day_area'] = $item['min_loss_day'].'~'.$item['max_loss_day'];
            $item['level_area'] = $item['min_level'].'~'.$item['max_level'];
//            $item['link'] = '<a href="link_group_detail.html?id='.$item['recall_ver_group_id'].'">'.$item['link_title'].'</a>';
//            $item['code'] = '<a href="code_group_detail.html?id='.$item['recall_code_group_id'].'">'.$item['code_title'].'</a>';

            $item['loss_up_name'] = implode(',',SysServer::getUpNameById($item['loss_up_id'],$item['platform_id']));
        }

        $res['data'] = $list;

        return $res;
    }

    public static function planConfig(){
        $config = [];

        $config['search_config'] = [
            'platform'=>['radio'=>true],
            'up_product'=>[
                'status'=>1,
                'radio'=>true,
                'name'=>'p_recall_up',
                'title'=>'召回产品'
            ],
            'admin'=>[
                'status'=>1,
                'p_data'=>[
//                    'group_type'=>[QcConfig::USER_GROUP_VIP],
                    'is_active'=>1,
                ],
                'name'=>'admin_id',
                'radio'=>true,
            ],
        ];

        $config['status_arr'] = RecallPlan::$status_arr;
        $config['execute_type_arr'] = RecallPlan::$execute_type_arr;
        return $config;
    }

    public static function planDetail($id){
        $info = [];

        if($id){
            $model = new RecallPlan();

            $obj = $model->where('id',$id)->find();

            if($obj){
                $info = $obj->toArray();
            }
        }

        $config = [];
        $config['status_arr'] = RecallPlan::$status_arr;
        $config['execute_type_arr'] = RecallPlan::$execute_type_arr;
        $config['platform_list'] = SysServer::getPlatformListByAdminInfo(self::$user_data);

        return ['data'=>$info,'config'=>$config];
    }

    public static function planSave($param){

        $id = getArrVal($param,'id',0);

        $model = new RecallPlan();
        $param['admin_id'] = self::$user_data['id'];
        if($id){
            $info = $model->where('id',$id)->find();

            if(!$info){
                return false;
            }

            $res = $info->save($param);
        }else{
            $param['status'] = RecallPlan::STATUS_OPEN;
            $res = $model->create($param);
        }

        return $res;
    }

    public static function planChangeStatus($ids,$status){

        $model = new RecallPlan();

        return $model->where('id','in',$ids)->update(['status'=>$status==RecallPlan::STATUS_OPEN?:RecallPlan::STATUS_CLOSE]);
    }


    /*计划日志*/

    public static function planLogList($params){
        $res = [
            'data'=>[],
            'count'=>0,
        ];
        $page = getArrVal($params,'page',1);
        $limit = getArrVal($params,'limit',20);
        $is_excel = getArrVal($params,'is_excel',0);

        $where = getDataByField($params,['platform_id','recall_up_id','loss_up_id','execute_type'],true,'p.');

        if(!empty($params['plan_ids'])){
            $where[] = ['p.id','in',$params['plan_ids']];
        }

        if(!empty($params['title'])){
            $where[] = ['p.title','like',"%".$params['title']."%"];
        }

        if(!empty($params['status'])){
            $where['p.status'] = $params['status'];
        }

        if(!empty($params['p_recall_up'])){
            $where['p.recall_up_id'] = explode('_',$params['p_recall_up'])[1];
        }

        if(!empty($params['p_loss_up'])){
            $where['p.loss_up_id'] = explode('_',$params['p_loss_up'])[1];
        }

        if(!empty($params['add_time'])){
            $add_time = explode(' - ',$params['add_time']);
            $where[] = ['p.add_time','between',[strtotime($add_time[0]),strtotime($add_time[1])+3600*24]];
        }

        $model = new RecallPlanLog();

        $count = $model->alias('p')->where(setWhereSql($where,''))->count();

        if(!$count) return $res;

        $res['count'] = $count;

        $field = '
            p.*
            ,up2.up_name as recall_up_name
        ';

        $list = $model
            ->alias('p')
            ->field($field)
            ->join('db_statistic.up_game_product up2','p.platform_id = up2.platform_id AND p.recall_up_id = up2.up_id','left')
            ->where(setWhereSql($where,''));

        if($page && $limit){
            $list = $list->page($page,$limit);
        }
        $list = $list->order('p.id desc')->select()
            ->toArray();

        $admin_list = SysServer::getAdminListCache();
        $platform_list = SysServer::getPlatformList();

        foreach ($list as &$item){

            $item['action'] = [];

            $item['status_str'] = getArrVal(RecallPlanLog::$status_arr,$item['status'],'未知');
            $item['execute_type_str'] = getArrVal(RecallPlan::$execute_type_arr,$item['execute_type'],'未知');

            $this_platform_info = getArrVal($platform_list,$item['platform_id'],[]);

            if($this_platform_info) {
                $item['platform_id_str']= $this_platform_info['name'];
            }else{
                $item['platform_id_str']= '未知';
            }

            $item['account_money_area'] = $item['min_account_money'].'~'.$item['max_account_money'];
            $item['loss_up_money_area'] = $item['min_loss_up_money'].'~'.$item['max_loss_up_money'];
            $item['loss_day_area'] = $item['min_loss_day'].'~'.$item['max_loss_day'];
            $item['level_area'] = $item['min_level'].'~'.$item['max_level'];

            $item['loss_up_name'] = implode(',',SysServer::getUpNameById($item['loss_up_id'],$item['platform_id']));
        }

        $res['data'] = $list;

        return $res;
    }

    public static function planLogConfig(){
        $config = [];

        $config['search_config'] = [
            'platform'=>['radio'=>true],
            'up_product'=>[
                'status'=>1,
                'radio'=>true,
                'name'=>'p_recall_up',
                'title'=>'召回产品'
            ],
            'admin'=>[
                'status'=>1,
                'p_data'=>[
//                    'group_type'=>[QcConfig::USER_GROUP_VIP],
                    'is_active'=>1,
                ],
                'name'=>'admin_id',
                'radio'=>true,
            ],
        ];

        $config['status_arr'] = RecallPlanLog::$status_arr;
        $config['execute_type_arr'] = RecallPlan::$execute_type_arr;
        return $config;
    }

}
