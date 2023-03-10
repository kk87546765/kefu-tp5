<?php


namespace common\server\RoleNameBlock;


use common\base\BasicServer;
use common\libraries\Common;
use common\server\ElasticSearchChat\ElasticSearchChatServer;
use common\server\Game\BlockServer;
use common\server\Platform\BanServer;
use common\server\Sdk\UserServer;
use common\server\Sdk\UserServer as sdkUserServer;
use common\sql_server\RoleNameBlockSqlServer;
use common\sql_server\KefuCommonMember;
use common\server\Game\BlockServer as GameBlockServer;

use common\server\Game\RoleServer;
use common\sql_server\RoleNameBlockWaringSqlServer;
use common\sql_server\RoleNameKeywordSqlServer;


class RoleNameBlockServer extends BasicServer
{

    const STATUS_BLOCK = 1;
    const STATUS_NORMAL = 2;
    const BLOCK_TIME = 86400*365*1;
    const CHAT_TIME = 86400*10;
    const BAN_TIME = 10*86400;//禁言时长
    const BAN = 1; //封禁
    const U_BAN = 2; //解封
    const BAN_UID = 1;
    const BAN_IP   = 2;
    const BAN_IMEI = 3;
    const BAN_USER_NAME = 4;
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

        if($data['is_excel'] == 1){
            ini_set('memory_limit','1024M');
            $data['limit'] = 999999999;
        }

        $blocks = RoleNameBlockSqlServer::getList($tmp_data['where'],$data['page'],$data['limit'],$data['order']);

        if($blocks){
            foreach($blocks as $k=>$v){
                $platform_arr[$v['tkey']][] = $v;
            }

            //根据平台获取用户的包信息
            foreach($platform_arr as $k1=>$v1){
                $arr_uids = array_unique(array_column($v1,'uid'));

                $res = Common::getPlatformUserInfo($k1,$arr_uids,1);

                if(is_array($res)){
                    foreach($res as $k2=>$v2){

                        $platform = Common::getPlatformInfoBySuffixAndCache($v2['platform']);

                        $tmp_data = KefuCommonMember::getRegChannelByUid([$v2['uid']],$platform);
                        if($tmp_data){
                            $tmp_res[$v2['uid']] = $tmp_data;
                        }

                    }
                }


            }

            foreach ($blocks as &$block) {

                $block['reg_channel_id']         = isset($tmp_res[$block['uid']]['id']) ? $tmp_res[$block['uid']]['id'] : '';
                $block['game_name']              = $block['gkey'] == 'autoforbid' ? '自动封禁' : $gamelist[$block['gkey']]['name'];
                $block['type_name']              = isset($block['type']) ? ElasticSearchChatServer::$blocktypes[$block['type']] : '';
                $block['addtime']                = date('Y-m-d H:i:s',$block['addtime']);
                $block['expect_unblock_time']    = date('Y-m-d H:i:s',$block['expect_unblock_time']);
                $block['blocktime']              = round($block['blocktime']/3600,2);

                $block['unblock_time']           = $block['unblock_time'] == 0?'':date('Y-m-d H:i:s',$block['unblock_time']);
                $block['count_money']            = $block['count_money']>0? "<span style='color:red;'>{$block['count_money']}</span>": $block['count_money'];

                if($data['is_excel'] == 1){

                    unset($block['ban_type'],$block['ext'],$block['op_ip'],
                        $block['tkey'],$block['openid'],
                        $block['type_name'],$block['id'],$block['gkey'],
                        $block['type'],$block['status'],$block['imei'],$block['hit_keyword_id']);

                }
            }

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
        $count = RoleNameBlockSqlServer::getCount($tmp_data['where']);

        return $count;
    }

    private static function dealData($data){

        $return = ['code'=>-1,'msg'=>'获取失败','where'=>''];
        $where = ' 1=1 ';

//        if(!$data['platform_id']){
//            $return['msg'] = '平台不能为空';
//            return $return;
//        }

        if($data['dateStart']){
            $dateStart = strtotime($data['dateStart']);
            $where .= " AND addtime>= {$dateStart}";
        }

        if($data['dateEnd']){
            $dateEnd = strtotime($data['dateEnd']);
            $where .= " AND addtime<= {$dateEnd}";
        }

        if ( $data['game']) {
            $where .= " AND gkey = '{$data['game']}'";

        }

        if ($data['keyword_id']) {
            $where .= " AND id = {$data['keyword_id']}";
        }

        if ($data['rolename']) {
            $where .= " AND rolename = '{$data['rolename']}'";
        }

        if ($data['reg_channel_id']) {
            $where .= " AND reg_channel_id = {$data['reg_channel_id']}";
        }


        if ($data['sid']) {
            $where .= " AND sid = '{$data['sid']}'";
        }

        if ($data['uid']) {
            $where .= " AND uid ='{$data['uid']}'";
        }

        if ($data['type']) {
            $where .= " AND type = {$data['type']}";
        }

        if ($data['admin']) {
            $where .= " AND op_admin_id = '{$data['admin']}'";
        }

        if (is_numeric($data['status'])) {
            $where .= " AND status = '{$data['status']}'";
        }

        if ($data['op_type'] == 'other') {
            $where .= " AND ( type != 'AUTOCHAT' AND  type != 'AUTO')";
        }else if ($data['op_type'] == 'admin'){
            $where .= " AND ( type = 'AUTOCHAT' or  type = 'AUTO')";
        }

        $platform_list = Common::getPlatformList();


        if(!empty($data['platform_id'])){
            $arr = explode(',',$platform_list[$data['platform_id']]['config']['see_game_limit']);
        }else{
            $platform_ids = self::$user_data['platform_id'];
            $tmp = [];
            foreach ($platform_ids as $platform_id) {
                $arr = explode(',',$platform_list[$platform_id]['config']['see_game_limit']);
                $arr = array_values($arr);

                $tmp =  array_merge($tmp,$arr);

            }
            $arr = $tmp;

        }

        if($data['game'] && $data['game'] !== 'autoforbid'){


            if(in_array($data['game'],$arr) === false){

                $return['code'] = -1;
                $return['msg'] = '没有查看权限';
                return $return;

            };
        }else{

            $where .= ' AND (';
            foreach($arr as $k=>$v){

                if($k == 0){
                    $where .= " gkey = '{$v}'";
                }else{
                    $where .= " OR gkey = '{$v}'";
                    $where .= " OR gkey = 'autoforbid'";
                }


            }
            $where .= ')';
        }
        $return['code'] = 0;
        $return['where'] = $where;

        return $return;
    }


    public static function block($data)
    {


        $return = ['code' => -1, 'msg' => '操作失败', 'succ' => '', 'fail' => '', 'isBlocked' => ''];


        if (empty($data['block_time']) || (isset($data['block_time']) && $data['block_time']> self::BLOCK_TIME)) {
            $data['block_time'] = self::BLOCK_TIME;
        }



        if ($data['ids']) {
            $tmp_ids = implode(',',$data['ids']);
            $result = RoleNameBlockWaringSqlServer::getList("id in ({$tmp_ids})");

            $chat_info = [];
            $fail = $isBlocked = $platformUids = $polymerizaUids = [];
            $is_block_arr = [];
            $is_block_str = '';
            if ($result) {
                $tmp_tkey = [];
                //拼接聊天信息
                foreach ($result as $k => $v) {
                    $tmp_tkey[$v['platform']][] = $v;
                    //判断该内容是否已经封禁过
                    $block_info = RoleNameBlockSqlServer::getBlockByUserInfo([$v['uid']], [$v['role_id']], [$v['server_id']]);

                    $chat_info[$v['uid']] = $v;
                    $chat_info[$v['uid']]['tkey'] = $v['platform'];

                    //将重复封禁的uid存储起来
                    if (!empty($block_info)) {
                        array_push($is_block_arr, $v['uid']);
                        unset($chat_info[$v['uid']]);
                    }

                }

                if(empty($chat_info)){
                    $return['msg']  = '用户还未解除封禁';
                    $return['code'] = 0;
                    return $return;
                }

                if (count($tmp_tkey) > 1) {
                    $return['msg'] = '不同平台不能同时处理';
                    return $return;
                }


                $info = sdkUserServer::getUserInfoByMixGameUids($chat_info);

                if (!empty($info['failUid'])) {
                    $fail = $info['failUid'];
                }
                unset($info['failUid']);


                if(empty($info)){
                    $return['msg']  = '该用户不存在，请到平台处理';
                    $return['code'] = 0;
                    return $return;
                }

                //先踢用户下线或者cp封禁
                $tmp_res = GameBlockServer::roleNameBlockOrLoginOut($chat_info, $info, $data['block_time']);

                //如果用户找不到就提示错误
                if($tmp_res['code'] == 0){
                    $return['msg']  = $tmp_res['msg'];
                    $return['code'] = 0;
                    return $return;
                }

                $is_block_str = '';
                if (!empty($is_block_arr)) {
                    $is_block_str = implode(',', $is_block_arr);
                }

                //平台封禁操作
                $BanLogicModel = new BanServer();
                $res = $BanLogicModel->ban(
                    $info,
                    self::BAN_UID,
                    self::BAN,
                    $data['block_time'],
                    '聊天封禁');
            }

            if($res['succ']){

                RoleNameBlockWaringServer::updateStatus($data['ids']);
            }
            $return['code'] = isset($res['code']) ? $res['code'] : 1;
            $return['msg']  = isset($res['msg'])  ? $res['msg'] : '操作成功';
            $return['succ'] = isset($res['succ']) ? implode(",", $res['succ']) : '';
            $return['fail'] = isset($res['fail']) ? implode(",", array_merge($res['fail'], $fail)) : '';
            $return['isBlocked'] = $is_block_str;



        } elseif ($data['blockid']) {

            $banDta = $succ = $fail = $log = [];
            $blockid = $data['blockid'];
            $info = RoleNameBlockSqlServer::getBlockById($blockid);
            $new_info = [];


//            $gamekey = Common::getConfig('gamekey');
            $gamekey = Common::getGameKey();

            $gamekey_list = [];
            foreach ($gamekey as $k1 => $v1) {
                $gamekey_list[$k1] = $v1;
            }


            foreach ($info as $k1 => $v1) {
                $new_info[$v1['uid']]['uid'] = $v1['uid'];
                $new_info[$v1['uid']]['gkey'] = $v1['gkey'];
                $new_info[$v1['uid']]['tkey'] = $v1['tkey'];
                $new_info[$v1['uid']]['roleid'] = $v1['roleid'];

                $uid = $v1['uid'];

                if(isset($gamekey_list[$v1['gkey']])){

                    //判断uid是否需要转换成聚合的sdkuid
                    $res = GameBlockServer::checkNeedChangeUid($v1, $gamekey_list[$v1['gkey']]['need_change_uid']);

                    if (isset($gamekey_list[$v1['gkey']]['need_change_uid']) && $gamekey_list[$v1['gkey']]['need_change_uid'] == 1) {
                        $v1['uid'] = $res['openid'];
                    }


                    //后置操作
                    $v1 = GameBlockServer::dealGameParams($v1,$v1['gkey'],2);

                    $tmp_data = $v1;
                    if ( isset($gamekey_list[$v1['gkey']]['need_cp_deal']) && $gamekey_list[$v1['gkey']]['need_cp_deal'] == 1) {

                        $tmp_data['need_cp_deal'] = 1;
                    }
                }else{
                    $new_info[$uid]['is_direct_game'] = 1;
                }


                if ($v1['type'] == "CHAT" || $v1['type'] == "AUTOCHAT" ) {

                    //聊天解禁参数
                    $tmp_data['addtime'] = time();
                    $tmp_data['ban_time'] = 0;

                    RoleServer::roleChat($tmp_data,2);
                } else {
                    //如果使用的是cp封禁+sdk封禁模式则需要解封cp
                    if ($v1['ban_type'] == 2) {
                        $tmp_data['addtime'] = time();
                        //判断封禁还是解禁
                        $tmp_data['ban_time'] = 0;
                        RoleServer::roleBlock($tmp_data,2);
                    }
                }

                $new_info[$uid]['uid'] = $uid;

            }



            $banDta = UserServer::getUserInfoByMixGameUids($new_info);

            $BanLogicModel = new BanServer();
            $res = $BanLogicModel->ban(
                $banDta,
                self::BAN_UID,
                self::U_BAN,
                0,
                '解封');

            $return['code'] = 0;
            $return['msg'] = '操作成功';
            $return['succ'] = implode(",", $res['succ']);
            $return['fail'] = implode(",", array_merge($res['fail'], $fail));


            //更新成功更改封禁日志状态
            if ($res['succ']) {

                //批量更新最近一个月相同用户角色的封禁记录
                $time = mktime(0,0,0,date('m')-1,date('d'),date('Y'));
                $all_need_unblock_info = RoleNameBlockSqlServer::getList("uid={$new_info[$uid]['uid']} and roleid='{$new_info[$uid]['roleid']}' and gkey='{$new_info[$uid]['gkey']}' and tkey='{$new_info[$uid]['tkey']}' and addtime>{$time}",1,5000);
                if(!empty($all_need_unblock_info)){
                    $blockid = array_column($all_need_unblock_info,'id');
                }

                RoleNameBlockSqlServer::updateRoleNameBlockStatus($blockid, $data['admin_user']);

            }
        }

        return $return;
    }

    public static function blockChat($data)
    {

        $return = ['code' => -1, 'msg' => '操作失败', 'succ' => '', 'fail' => '', 'isBlocked' => ''];

        if(empty($data['block_time'])){
            $data['block_time'] = self::CHAT_TIME;
        }

        if($data['block_time'] > self::BLOCK_TIME){
            $data['block_time'] = self::BLOCK_TIME;
        }

        if (!empty($data['blockid'])) {
            $tmp_data = $succ = $fail = $log = [];
            $info = RoleNameBlockSqlServer::getBlockById([$data['blockid']]);

            foreach ($info as $value){
                $tmp_data[$value['uid']] = $value;
            }

            //解禁言操作
            $res = GameBlockServer::roleNameBlockRelieveChat($tmp_data,$data['blockid']);

            if (count($data) == count($res['fail'])){
                $result['fail'] = $res['fail'];
            }else{


                $return['code'] = 0;
                $return['msg'] = '操作成功';
                $return['succ'] = $res['succ'];
                $return['fail'] = $res['fail'];
            }

        }

        return $return;
    }



    //自动封禁
    public static function autoBlockChat($data){

        $tmp_info[] = $data;

        $info = sdkUserServer::getUserInfoByMixGameUids($tmp_info);

        unset($info['failUid']);


        foreach($tmp_info as $k=>$v){


            $block_time = !empty($v['block_time']) ? $v['block_time'] : self::BLOCK_TIME;
            $ban_time = !empty($v['ban_time']) ? $v['ban_time'] : self::BAN_TIME;


            switch ($v['block_type']){

                //封号+禁言
                case 1:

                    BlockServer::roleNameBlockChat([$v],$info,1,$block_time,'AUTOCHAT','触发关键词自动禁言');
                    BlockServer::roleNameBlockOrLoginOut([$v], $info,$ban_time,'AUTO','触发关键词自动封禁');

                    //封禁用户uid
                    self::blockUids($info,$block_time);
                    break;
                //禁言
                case 2:

                    BlockServer::roleNameBlockChat([$v],$info,1,$ban_time,'AUTOCHAT','触发关键词自动禁言');
                    break;
                //封号
                case 3:

                    BlockServer::roleNameBlockOrLoginOut([$v], $info,$block_time,'AUTO','触发关键词自动封禁');

                    //封禁用户uid
                    self::blockUids($info,$block_time);
                    break;
                default:
                    break;
            }

        }
        return true;
    }


    /**
     * 查询触发关键词是否符合条件
     * @param $data
     * @param $keywords
     * @return bool
     */
    public static function check_keyword_auto_block($data,$keywords){

        $keywords = array_unique($keywords);

        $flag = false;
        $return = ['data'=>[],'status'=>$flag];
        foreach ($keywords as $keyword){

            if(empty($keyword)){
                continue;
            }

            $keyword = urldecode($keyword);

            $infos = RoleNameKeywordSqlServer::getAllByWhere( "keyword='{$keyword}' and game='{$data['tmp_keyword_game']}' and check_type=1" );

            foreach($infos as $k=>$info){

                if(!empty($info['status'])){

                    if(
                        $data['count_money'] >= $info['money_min']&&$data['count_money'] <= $info['money_max'] &&
                        $data['role_level'] >= $info['level_min'] && $data['role_level'] <= $info['level_max']){
                        $return['status'] = true;
                        $return['data']['type'] = $info['type'];
                        $return['data']['keyword_id'] = $info['id'];
                        $return['data']['tmp_keyword'] = $info['keyword'];
                        $return['data']['block_time'] = $info['block_time'];
                        $return['data']['ban_time'] = $info['ban_time'];
                        $return['data']['check_type'] = 1;
//                        $return['data']['ban_time'] = $info['ban_time'];
                        break 2;
                    }
                    $return['status'] = false;
                }else{
                    $return['status'] = false;

                }
            }

        }


        return $return;
    }


    /**
     * 检查关键词触发次数是否达到条件，插入人工审核列表
     * @param $data
     * @param $keywords
     * @return bool
     */
    public static function check_keyword_auditing($data,$keywords){

        $keywords = array_unique($keywords);

        $flag = false;
        $return = ['data'=>[],'status'=>$flag];
        foreach ($keywords as $keyword){

            if(empty($keyword)){
                continue;
            }

            $keyword = urldecode($keyword);

            $infos = RoleNameKeywordSqlServer::getAllByWhere( "keyword='{$keyword}' and game='{$data['tmp_keyword_game']}' and check_type=2" );

//            $tmp_data = [];

            foreach($infos as $k=>$info){

                if(!empty($info['status'])){

                    if(
                        $data['count_money'] >= $info['money_min']&& $data['count_money'] <= $info['money_max'] &&
                        $data['role_level'] >= $info['level_min'] && $data['role_level'] <= $info['level_max']){

                        $tmp_data = $data;
                        $tmp_data['block_type']      = $info['type'];
                        $tmp_data['hit_keyword_id']  = $info['id'];
                        $tmp_data['tmp_keyword']     = $info['keyword'];
                        $tmp_data['block_time']      = $info['block_time'];
                        $tmp_data['ban_time']        = $info['ban_time'];
                        $tmp_data['check_type']      = 2;
                        $tmp_data['time']            = time();

                        RoleNameBlockWaringSqlServer::insert($tmp_data);

                        break 2;
                    }
                    $return['status'] = false;
                }else{
                    $return['status'] = false;

                }
            }

        }


        return $return;
    }






    public static function unblockMixed($data)
    {
        $return = ['code' => -1, 'msg' => '操作失败', 'succ' => '', 'fail' => '', 'isBlocked' => ''];
        $blockid = $data['blockid'];
        if ($data['blockid']) {
            $info = GameBlockServer::dealUnBlockMixed($blockid);
            $new_info = [];
            $blockid = [];

            foreach ($info as $k1 => $v1) {

                $new_info[$v1['uid']]['uid'] = $v1['uid'];
                $new_info[$v1['uid']]['gkey'] = $v1['gkey'];
                $new_info[$v1['uid']]['tkey'] = $v1['tkey'];
                array_push($blockid,$v1['id']);

                if ($v1['type'] == "CHAT" || $v1['type'] == "AUTOCHAT" || $v1['type'] == "ACTIONCHAT") {
                    $data = $v1;
                    //聊天解禁参数
                    $data['addtime'] = time();
                    $data['ban_time'] = 0;
                    RoleServer::roleChat($data,2);
                } else {
                    //如果使用的是cp封禁+sdk封禁模式则需要解封cp
                    if ($v1['ban_type'] == 2) {
                        $data = $v1;
                        $data['addtime'] = time();

                        $data['ban_time'] = 0;
                        RoleServer::roleBlock($data,2);
                    }
                }

            }

            $banDta = UserServer::getUserInfoByMixGameUids($new_info);

            $BanLogicModel = new BanServer();
            $res = $BanLogicModel->ban(
                $banDta,
                self::BAN_UID,
                self::U_BAN,
                0,
                '解封');

            $return['code'] = 0;
            $return['msg'] = '操作成功';
            $return['succ'] = implode(",", $res['succ']);
            $return['fail'] = implode(",", $res['fail']);
            $return['isBlocked'] = '';

            //更新成功更改封禁日志状态
            if ($res['succ']) {
                $unblock_admin = $data['admin_user'];
                RoleNameBlockSqlServer::updateROleNameBlockStatus($blockid,$unblock_admin);
            }
        }
        return $return;
    }


    public static function insertBlock($info, $type = ''){
        if (empty($info)) {
            return false;
        }
        foreach ($info as $k=>&$v){
            $v['type']          = $type;
        }
        //还有一个操作就是操作记录入库
        RoleNameBlockSqlServer::insertBlock($info);
        return true;
    }


    private static function blockUids($info,$block_time = 0,$reason = '自动封禁'){
        $BanLogicModel = new BanServer();
        //平台封禁操作
        $res = $BanLogicModel->ban(
            $info,
            self::BAN_UID,
            self::BAN,
            $block_time,
            $reason);
        return $res['succ'];

    }

    public static function getBlockInfo()
    {
        $time = time();
        $model =  new RoleNameBlockSqlServer();
        $block_info_obj = $model->getList("status=1 and expect_unblock_time < {$time} and expect_unblock_time !=0",1,200);
        return $block_info_obj;
    }

}