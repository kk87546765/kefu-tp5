<?php


namespace common\server\Block;

use common\base\BasicServer;
use common\libraries\Common;
use common\server\ElasticSearchChat\ElasticSearchChatServer;
use common\server\Platform\BanServer;
use common\server\Sdk\UserServer;
use common\sql_server\BlockSqlServer;
use common\sql_server\KefuCommonMember;
use common\server\Game\BlockServer as GameBlockServer;
use common\server\Sdk\UserServer as sdkUserServer;
use common\server\Game\RoleServer;



class BlockServer extends BasicServer
{

    const STATUS_BLOCK = 1;
    const STATUS_NORMAL = 2;
    const BLOCK_TIME = 86400*365*1;
    const CHAT_TIME = 86400*10;
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

        $blocks = BlockSqlServer::getList($tmp_data['where'],$data['page'],$data['limit'],$data['order']);

        if($blocks){
            foreach($blocks as $k=>$v){
                $platform_arr[$v['platform_tkey']][] = $v;
            }

            //根据平台获取用户的包信息
            foreach($platform_arr as $k1=>$v1){
                $arr_uids = array_unique(array_column($v1,'platform_uid'));

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

                $block['reg_channel_name'] = isset($tmp_res[$block['platform_uid']]['channel_name']) ? $tmp_res[$block['platform_uid']]['channel_name'] : '';
                $block['reg_channel_id'] = isset($tmp_res[$block['platform_uid']]['id']) ? $tmp_res[$block['platform_uid']]['id'] : '';
                $block['game_name']     = $gamelist[$block['gkey']]['name'];

                $block['type_name']     = isset($block['type']) ? ElasticSearchChatServer::$blocktypes[$block['type']] : '';
                $block['addtime']       = date('Y-m-d H:i:s',$block['addtime']);
                $block['expect_unblock_time']       = date('Y-m-d H:i:s',$block['expect_unblock_time']);
                $block['blocktime'] = round($block['blocktime']/3600,2);
//                $block['blocktime']     = $block['blocktime'] == 0?'':date('Y-m-d H:i:s',$block['blocktime']);
                $block['unblock_time']  = $block['unblock_time'] == 0?'':date('Y-m-d H:i:s',$block['unblock_time']);
                $block['count_money']   = $block['count_money']>0? "<span style='color:red;'>{$block['count_money']}</span>": $block['count_money'];

                if($data['is_excel'] == 1){

                    unset($block['ban_type'],$block['ext'],$block['op_ip'],
                        $block['platform_tkey'],$block['tkey'],$block['openid'],
                        $block['type_name'],$block['id'],$block['gkey'],$block['platform_uid'],
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
        $count = BlockSqlServer::getCount($tmp_data['where']);

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

        if ( $data['game'] && $data['game']!='common' ) {
            $where .= " AND gkey = '{$data['game']}'";

        }

        if ($data['keyword_id']) {
            $where .= " AND id = {$data['keyword_id']}";
        }

        if ($data['rolename']) {
            $where .= " AND rolename = '{$data['rolename']}'";
        }

        if ($data['ip']) {
            $where .= " AND ip = '{$data['ip']}'";
        }

        if ($data['reg_channel_id']) {
            $where .= " AND reg_channel_id = {$data['reg_channel_id']}";
        }

        if ($data['imei']) {
            $where .= " AND imei = '{$data['reg_channel_id']}'";
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

        if($data['game'] && $data['game'] !== 'common'){


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
            //elasticSearch找到对应的用户信息
            $result = Common::newGetbyIds($data['ids'],$data['uids']);

            $chat_info = [];
            $fail = $isBlocked = $platformUids = $polymerizaUids = [];
            $is_block_arr = [];
            $is_block_str = '';
            if ($result) {
                $tmp_tkey = [];
                //拼接聊天信息
                foreach ($result as $k => $v) {
                    $tmp_tkey[$v['tkey']][] = $v;
                    //判断该内容是否已经封禁过
                    $block_info = BlockSqlServer::getBlockByUserInfo([$v['uid']], [$v['roleid']], [$v['sid']]);


                    $chat_info[$v['uid']] = $v;

                    //将重复封禁的uid存储起来
                    if (!empty($block_info)) {
                        array_push($is_block_arr, $v['uid']);
                        unset($chat_info[$v['uid']]);
                    }


                }


                if(empty($chat_info)){
                    $return['msg']  = '没有需要封禁的用户';
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
                $tmp_res = GameBlockServer::blockOrLoginOut($chat_info, $info, $data['block_time']);

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

            $return['code'] = isset($res['code']) ? $res['code'] : 1;
            $return['msg']  = isset($res['msg'])  ? $res['msg'] : '操作成功';
            $return['succ'] = isset($res['succ']) ? implode(",", $res['succ']) : '';
            $return['fail'] = isset($res['fail']) ? implode(",", array_merge($res['fail'], $fail)) : '';
            $return['isBlocked'] = $is_block_str;



        } elseif ($data['blockid']) {

            $banDta = $succ = $fail = $log = [];
            $blockid = $data['blockid'];
            $info = BlockSqlServer::getBlockById($blockid);
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
                $new_info[$v1['uid']]['tkey'] = $v1['platform_tkey'];
                $new_info[$v1['uid']]['roleid'] = $v1['roleid'];

                $uid = $v1['uid'];

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

                if ($v1['type'] == "CHAT" || $v1['type'] == "AUTOCHAT" || $v1['type'] == "ACTIONCHAT") {

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

                BlockSqlServer::updateBlockStatus($blockid, $data['admin_user']);
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

        if ($data['ids']) {

            $result     = Common::newGetbyIds($data['ids'],$data['uids']);

            $chat_info  = [];
            $succ       = $fail = $isBlocked =  [];
            if($result ) {
                $tmp_tkey = [];
                foreach ($result as $v) {
                    $tmp_tkey[$v['tkey']][] = $v;
                    $chat_info[$v['uid']] = $v;
                }

                if(count($tmp_tkey)>1){
                    $return['msg'] = '不同平台不能同时处理';
                    return $return;
                }

                $info = UserServer::getUserInfoByMixGameUids($chat_info);

                //禁言操作
                $res = GameBlockServer::blockChat($chat_info, $info, 1, $data['block_time']);

                if (count($chat_info) == count($res['fail'])){
                    $result['fail'] = $res['fail'];
                }else{

                    $result = [
                        'status'=>false,
                        'msg'=>'操作失败',
                        'succ'=>'',
                        'fail'=>'',
                        "isBlocked"=>''
                    ];
                    $return['code'] = 1;
                    $return['msg'] = '操作成功';
                    $return['succ'] = $res['succ'];
                    $return['fail'] = $res['fail'];
                }


            }
           return $return;
        } elseif (!empty($data['blockid'])) {
            $tmp_data = $succ = $fail = $log = [];
            $info = BlockSqlServer::getBlockById([$data['blockid']]);

            foreach ($info as $value){
                $tmp_data[$value['uid']] = $value;
            }

            //解禁言操作
            $res = GameBlockServer::blockRelieveChat($tmp_data,$data['blockid']);

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
                BlockSqlServer::updateBlockStatus($blockid,$unblock_admin);
            }
        }
        return $return;
    }

}