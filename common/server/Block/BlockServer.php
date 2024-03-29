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
    const CHAT_TIME = 86400*365*1;
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
        $blocks = BlockSqlServer::getList($tmp_data['where'],$data['page'],$data['limit'],$data['order']);

        if($blocks){
            foreach($blocks as $k=>$v){
                $platform_arr[$v['platform_tkey']][] = $v;
            }

            //根据平台获取用户的包信息
            foreach($platform_arr as $k1=>$v1){
                $arr_uids = array_unique(array_column($v1,'platform_uid'));

                $res = Common::getPlatformUserInfo($k1,$arr_uids,1);

                foreach($res as $k2=>$v2){

                    $platform = Common::getPlatformInfoBySuffixAndCache($v2['platform']);

                    $tmp_data = KefuCommonMember::getRegChannelByUid([$v2['uid']],$platform);
                    if($tmp_data){
                        $tmp_res[$v2['uid']] = $tmp_data;
                    }

                }

            }

            foreach ($blocks as &$block) {

                $block['reg_channel_name'] = isset($tmp_res[$block['platform_uid']]['channel_name']) ? $tmp_res[$block['platform_uid']]['channel_name'] : '';
                $block['reg_channel_id'] = isset($tmp_res[$block['platform_uid']]['id']) ? $tmp_res[$block['platform_uid']]['id'] : '';
                $block['game_name']     = $gamelist[$block['gkey']]['name'];

                $block['type_name']     = ElasticSearchChatServer::$blocktypes[$block['type']];
                $block['addtime']       = date('Y-m-d H:i:s',$block['addtime']);
                $block['expect_unblock_time']       = date('Y-m-d H:i:s',$block['expect_unblock_time']);
                $block['blocktime'] = $block['blocktime']/60;
//                $block['blocktime']     = $block['blocktime'] == 0?'':date('Y-m-d H:i:s',$block['blocktime']);
                $block['unblock_time']  = $block['unblock_time'] == 0?'':date('Y-m-d H:i:s',$block['unblock_time']);
                $block['count_money']   = $block['count_money']>0? "<span style='color:red;'>{$block['count_money']}</span>": $block['count_money'];

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

        if(!$data['platform_id']){
            $return['msg'] = '平台不能为空';
            return $return;
        }

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

        if ($data['status'] !== '') {
            $where .= " AND status = '{$data['status']}'";
        }


//        if ($data['op_type'] == 'admin') {
//            $where .= " AND op_admin_id = {$data['op_type']}";
//            $bind['op_admin_id'] = $op_type;
//        }else if ($op_type == 'other'){
//            $condition .= ' AND op_admin_id != :op_admin_id:';
//            $bind['op_admin_id'] = 'admin';
//        }

        $platform_list = Common::getPlatformList();


        if($data['game'] && $data['game'] !== 'common'){
            if(strpos($platform_list[$data['platform_id']]['config']['see_game_limit'],$data['game']) === false){

                $return['code'] = -1;
                $return['msg'] = '没有查看权限';
                return $return;

            };
        }else{

            $arr = explode(',',$platform_list[$data['platform_id']]['config']['see_game_limit']);

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


    public static function block()
    {
        $return = ['code' => -1, 'msg' => '操作失败', 'succ' => '', 'fail' => '', 'isBlocked' => ''];
        if (empty($data['block_time'])) {
            $block_time = self::BLOCK_TIME;
        }

        if ($data['block_time'] > self::BLOCK_TIME) {
            $data['block_time'] = self::BLOCK_TIME;
        }
//        $block_time = 365*86400;


        if ($data['ids']) {
            //elasticSearch找到对应的用户信息
            $result = Common::getbyIds($data['ids']);

            $chat_info = [];
            $fail = $isBlocked = $platformUids = $polymerizaUids = [];
            $is_block_arr = [];
            if ($result) {
                $tmp_tkey = [];
                //拼接聊天信息
                foreach ($result as $k => $v) {
                    $tmp_tkey[] = $v['tkey'];
                    //判断该内容是否已经封禁过
                    $block_info = BlockSqlServer::getBlockByUserInfo([$v['uid']], [$v['roleid']], [$v['sid']]);


                    $chat_info[$v['uid']] = $v;

                    //将重复封禁的uid存储起来
                    if (!empty($block_info)) {
                        array_push($is_block_arr, $v['uid']);
                        unset($chat_info[$v['uid']]);
                    }


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


                //先踢用户下线或者cp封禁
                GameBlockServer::blockOrLoginOut($chat_info, $info, $block_time);

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
                    $block_time,
                    '聊天封禁');
            }

            $return['code'] = 1;
            $return['code'] = '操作成功';
            $return['succ'] = implode(",", $res['succ']);
            $return['fail'] = implode(",", array_merge($res['fail'], $fail));
            $return['isBlocked'] = $is_block_str;

        } elseif ($data['blockid']) {

            $banDta = $succ = $fail = $log = [];
            $info = BlockSqlServer::getBlockById($data['blockid']);
            $new_info = [];

            $gamekey = Common::getConfig('gamekey');

            $gamekey_list = [];
            foreach ($gamekey as $k1 => $v1) {
                $gamekey_list[$k1] = $v1;
            }


            foreach ($info as $k1 => $v1) {
                $new_info[$v1['uid']]['uid'] = $v1['uid'];
                $new_info[$v1['uid']]['gkey'] = $v1['gkey'];
                $new_info[$v1['uid']]['tkey'] = $v1['tkey'];

                $uid = $v1['uid'];

                //判断uid是否需要转换成聚合的sdkuid
                $res = GameBlockServer::checkNeedChangeUid($v1, $gamekey_list[$v1['gkey']]['need_change_uid']);

                if ($gamekey_list[$v1['gkey']]['need_change_uid'] == 1) {
                    $v1['uid'] = $res['openid'];
                }


                if ($v1['type'] == "CHAT" || $v1['type'] == "AUTOCHAT" || $v1['type'] == "ACTIONCHAT") {
                    $data = $v1;
                    //聊天解禁参数
                    $data['type'] = 2;
                    $data['addtime'] = time();
                    $data['ban_time'] = 0;
                    RoleServer::roleChat($data);
                } else {
                    //如果使用的是cp封禁+sdk封禁模式则需要解封cp
                    if ($v1['ban_type'] == 2) {
                        $data = $v1;
                        $data['addtime'] = time();
                        //判断封禁还是解禁
                        $data['is_block'] = 2;
                        $data['ban_time'] = 0;
                        RoleServer::roleBlock($data);
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

            $return['code'] = 1;
            $return['msg'] = '操作成功';
            $return['succ'] = implode(",", $res['succ']);
            $return['fail'] = implode(",", array_merge($res['fail'], $fail));


            //更新成功更改封禁日志状态
            if ($res['succ']) {
                BlockSqlServer::updateBlockStatus($data['blockid'], $data['admin_user']);
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

//            $result     = Common::getbyIds($data['ids']);
            $arr = '{
    "id": "2201281405569561",
    "gkey": "y8cl",
    "tkey": "zw",
    "sid": "S63",
    "uid": "20965291",
    "uname": "飘逸游民",
    "roleid": "83941228",
    "type": "1",
    "content": "谁给的寂寞，在不？",
    "content2": "谁给的寂寞，在不？",
    "time": "1643349939",
    "ip": "222.209.27.115",
    "ip_id": "1643346638",
    "to_uid": "",
    "to_uname": "0",
    "role_level": "303",
    "imei": "",
    "count_money": 0,
    "reg_channel_id": "524024",
    "ext": "",
    "openid": "",
    "is_sensitive": 1,
    "sensitive_keyword": "在不",
    "request_time": 1643349956
  }';

            $result[] = json_decode($arr,1);
            $chat_info  = [];
            $succ       = $fail = $isBlocked =  [];
            if($result ) {
                $tmp_tkey = [];
                foreach ($result as $v) {
                    $tmp_tkey[] = $v['tkey'];
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
        } elseif ($data['blockid']) {
            $data = $succ = $fail = $log = [];
            $info = BlockSqlServer::getBlockById($data['blockid']);
            foreach ($info as $value){
                $data[$value['uid']] = $value;
            }
            //解禁言操作
            $res = GameBlockServer::blockRelieveChat($data,$data['blockid']);

            if (count($data) == count($res['fail'])){
                $result['fail'] = $res['fail'];
            }else{


                $return['code'] = 1;
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
                    $data['type'] = 2;
                    $data['addtime'] = time();
                    $data['ban_time'] = 0;
                    RoleServer::roleChat($data);
                } else {
                    //如果使用的是cp封禁+sdk封禁模式则需要解封cp
                    if ($v1['ban_type'] == 2) {
                        $data = $v1;
                        $data['addtime'] = time();
                        //判断封禁还是解禁
                        $data['is_block'] = 2;
                        $data['ban_time'] = 0;
                        RoleServer::roleBlock($data);
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