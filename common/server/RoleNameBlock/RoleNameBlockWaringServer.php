<?php


namespace common\server\RoleNameBlock;

use common\base\BasicServer;
use common\libraries\Common;

use common\sql_server\RoleNameBlockWaringSqlServer;
//use common\server\ElasticSearchChat\ElasticSearchChatServer;



class RoleNameBlockWaringServer extends BasicServer
{

    /***
     *获取数据列表
     */
    public static function getList($data){

        $return = ['code'=>-1,'msg'=>'获取失败','data'=>[]];
        $gamelist = Common::getProductList(2);

        $tmp_data = self::dealData($data);
        if($tmp_data['code'] != 0){
            $return['msg'] = $tmp_data['msg'];
            $return['code'] = $tmp_data['code'];
            return $return;
        }
        $blocks = RoleNameBlockWaringSqlServer::getList($tmp_data['where'],($data['page']-1)*$data['limit'],$data['limit'],$data['order']);
        $platform_list = Common::getPlatform();
        foreach ($blocks as $k=>&$v){
            $v['time'] = !empty($v['time']) ? date('Y-m-d H:i:s',$v['time']) : '';
            $v['platform_key'] = $platform_list[$v['platform']]['name'];
            $v['platform_gkey'] = $v['gkey'] == 'autoforbid' ? '自动识别' : $gamelist[$v['gkey']]['name'];
            $v['status'] = $v['status'] == 0 ? '未审核' : ($v['status'] == 1 ? '已封禁' : ($v['status'] == 2 ? '已删除' : "未知"));

//            if($v['gkey'] == 'y9cqjh' && !empty($v['ext']) && $v['tkey'] == 'zw'){
//                $v['platform_key'] = '渠道平台';
//                $v['channel_id'] = $v['ext'];
//            }
        }





        $return['code'] = 0;
        $return['msg'] = '获取成功';
        $return['data'] = $blocks;

        return $return;
    }

    /***
     *获取总数
     */
    public static function getCount($data){

        $return = ['code'=>-1,'msg'=>'获取失败','data'=>[]];
        $tmp_data = self::dealData($data);
        if($tmp_data['code'] != 0){
            $return['msg'] = $tmp_data['msg'];
            $return['code'] = $tmp_data['code'];
            return $return;
        }
        $count = RoleNameBlockWaringSqlServer::getCount($tmp_data['where']);

        return $count;
    }

    private static function dealData($data){

        $return = ['code'=>-1,'msg'=>'获取失败','where'=>''];
        $where = ' 1=1 ';

//        if(!isset($data['platform_id'])){
//            $return['msg'] = '平台不能为空';
//            return $return;
//        }


        if ($data['tkey']) {
            $where .= " AND platform ='{$data['tkey']}'";
        }

        if($data['dateStart']){
            $dateStart = strtotime($data['dateStart']);
            $where .= " AND time>= {$dateStart}";
        }

        if($data['dateEnd']){
            $dateEnd = strtotime($data['dateEnd']);
            $where .= " AND time<= {$dateEnd}";
        }

        if ( $data['game'] && $data['game']!='common' ) {
            $where .= " AND gkey = '{$data['game']}'";

        }

        if ($data['uname']) {
            $where .= " AND role_name = '{$data['uname']}'";
        }


        if (!empty($data['money_min'])) {
            $where .= " AND count_money >= {$data['money_min']}";
        }

        if (!empty($data['money_max'])) {
            $where .= " AND count_money <= {$data['money_max']}";
        }

        if (!empty($data['level_min'])) {
            $where .= " AND role_level >= {$data['level_min']}";
        }

        if (!empty($data['level_max'])) {
            $where .= " AND role_level <= {$data['level_max']}";
        }

        if ($data['sid']) {
            $where .= " AND server_id = '{$data['sid']}'";
        }

        if ($data['uid']) {
            $where .= " AND uid ='{$data['uid']}'";
        }

        if ($data['content']) {
            $where .= " AND content like '%{$data['content']}%'";
        }


        if ($data['status']>=0) {
            $where .= " AND status ='{$data['status']}'";
        }



        $return['code'] = 0;
        $return['where'] = $where;

        return $return;
    }


    #获取所有菜单
    public static function insert($data){
        $res = RoleNameBlockWaringSqlServer::insert($data);
        return $res;
    }

    public static function updateStatus(array $ids,$status=1)
    {
        $succ = [];
        $fail = [];
        foreach($ids as $k=>$v){
            $update_date['id'] = $v;
            $update_date['status'] = $status;
            $res = RoleNameBlockWaringSqlServer::edit($update_date);
            if($res !== false){
                $succ[] = $update_date['id'];
            }else{
                $fail[] = $update_date['id'];
            }
        }
        $data['succ'] = $succ;
        $data['fail'] = $fail;
        return $data;
    }

    public static function del(array $ids)
    {
        $str_ids = implode(',',$ids);
        $res = RoleNameBlockWaringSqlServer::delete("id in ({$str_ids})");
        return $res;
    }


}