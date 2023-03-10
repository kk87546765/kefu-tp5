<?php


namespace common\server\Game;

use common\base\BasicServer;
use common\libraries\{Common};
use common\server\Platform\BanServer;
use common\server\Platform\Youyu\YouyuCommonMember;

use common\server\Sdk\UserLogics as sdkUserLogics;
use common\server\Sdk\UserServer;
use common\server\RoleNameBlock\RoleNameBlockServer;
use common\sql_server\BlockSqlServer;
use common\sql_server\BatchBlockSqlServer;
use common\sql_server\KefuUserRoleSqlServer;
use common\model\gr_chat\Block;
//use Quan\Common\Models\Block;
use common\sql_server\RoleNameBlockSqlServer;
use Quan\System\Config;


class BlockServer extends BasicServer
{


    const TYPE_IP = 'IP';
    const TYPE_USER = 'USER';
    const TYPE_CHAT = 'CHAT';
    const TYPE_IMEI = "IMEI";
    const TYPE_AUTOCHAT = "AUTOCHAT";
    const TYPE_ACTIONCHAT = "ACTIONCHAT";

    const GAME_SIGN_PATH = EXTEND_PATH."/GamekeySign";

    static $game_obj = '';

    /**
     * @param array $chat_info
     * @param array $info
     * @param int $block_time
     * @return array
     */
    public static function blockOrLoginOut($chat_info=[], $info=[], $block_time = 365*86400,$type = self::TYPE_USER,$reason = '封禁')
    {


        $res = ['code' => 0, 'msg' =>''];
        if (empty($chat_info) || empty($info)) {
            $res['msg'] = '聊天信息或者用户信息有误';
            return $res;
        }

        $gamekey = Common::getGameKey();
//        $gamekey = Common::getConfig('gamekey');

        $gamekey_list = [];
        foreach($gamekey as $k1=>$v1){
            $gamekey_list[$k1] = $v1;
        }




        $admin_user = self::$user_data['username'];
        $op_ip      = self::$user_data['last_ip'];
        $time       = time();

        $userInfo = [];
        foreach ($info as $key => $value){

            foreach ($value as $v){
                $userInfo[$v['uid']]['user_name'] = $v['user_name'];
                $userInfo[$v['uid']]['sdkUid'] = $v['sdkUid'];
                $userInfo[$v['uid']]['platformSuffix'] = $key;
            }
        }

        $succ = [];
        foreach ($chat_info as $k=>$v){

            $v['blocktime']           = empty($block_time) ?: $block_time;
            $v['keyword']             = $v['content'];
            $v['rolename']            = get_magic_quotes_gpc() == false ? addslashes($v['uname']) : $v['uname'];
            $v['uname']               = get_magic_quotes_gpc() == false ? addslashes($v['uname']) : $v['uname'];
            $v['addtime']             = $time;
            $v['op_ip']               = $op_ip;
            $v['op_admin_id']         = $admin_user;
            $v['reason']              = $reason;
            $v['ban_time']            = $block_time;
            $v['reg_channel_id']      = empty($v['reg_channel_id']) ? 0 : $v['reg_channel_id'];;
            $v['expect_unblock_time'] = $v['addtime'] + $v['ban_time']; //增加预估解封日期
            $v['platform_uid']        = empty($userInfo[$v['uid']]['platformSuffix']) ? 0 : $userInfo[$v['uid']]['sdkUid']; //判断阿斯加德是否存在平台账号
            $v['platform_tkey']       = empty($userInfo[$v['uid']]['platformSuffix']) ? '' : $userInfo[$v['uid']]['platformSuffix']; //判断阿斯加德是否存在平台账号
            $uid                      = $v['uid'];
            $gkey                     = $v['gkey'] ?? '';
            $need_change_uid          = $gamekey_list[$gkey]['need_change_uid'] ?? 0;


            $v = self::dealGameParams($v,$gkey,1);

            //判断uid是否需要转换成聚合的sdkuid
            $v = self::checkNeedChangeUid($v,$need_change_uid);

            $v = self::dealGameParams($v,$gkey,2);

            //判断封禁的模式 1为踢下线+sdk封禁 2为cp封禁+sdk封禁
            if(isset($gamekey_list[$gkey]['type']) && $gamekey_list[$gkey]['type'] == 1){

                $v['ban_type'] = 1;
                $api_res = RoleServer::roleLoginOut($v);

            }elseif(isset($gamekey_list[$gkey]['type']) && $gamekey_list[$gkey]['type'] == 2){


                $v['ban_type'] = 2;
                $tmp_v = $v;
                $tmp_v['need_cp_deal'] = $gamekey_list[$gkey]['need_cp_deal'] ?? 0;

                $api_res = RoleServer::roleBlock($tmp_v);

            }
            $v['hit_keyword_id']   = isset($v['hit_keyword_id']) ? $v['hit_keyword_id'] : 0;
            $v['role_level']       = empty($v['role_level']) ? 0 : $v['role_level'];
            $v['count_money']      = empty($v['count_money']) ? '0.0' : $v['count_money'];
            $v['ip']               = empty($v['ip']) ? '' : $v['ip'];
            $v['uid']              = $uid;

            $succ[] = $v;
        }

        //还有一个操作就是操作记录入库
        BlockServer::insertBlock($succ, $type);
        $res = ['code' => 1, 'msg' =>'success'];
        return $res;
    }


    /**
     * @param array $chat_info
     * @param array $info
     * @param int $type type为1时表示封接口，type为2时表示解封接口
     * @param int $block_time
     * @return array
     */
    public static function blockChat($chat_info=[], $info=[], $type = 1, $block_time = 0,$type2 = self::TYPE_CHAT,$reason = '禁言')
    {


        $res = ['code' => 0, 'succ' =>[] , 'fail' => []];
        if (empty($chat_info) || empty($info)) {
            return $res;
        }
        $admin_user = self::$user_data['username'];
        $op_ip      = self::$user_data['last_ip'];

        $time       = time();

        $userName = [];
        foreach ($info as $key => $value){
            foreach ($value as $v){
                $userInfo[$v['uid']]['user_name'] = $v['user_name'];
                $userInfo[$v['uid']]['sdkUid'] = $v['sdkUid'];
                $userInfo[$v['uid']]['platformSuffix'] = $key;
            }
        }

        $gamekey = Common::getGameKey();
//        $gamekey = Common::getConfig('gamekey');

        $gamekey_list = [];
        foreach($gamekey as $k1=>$v1){
            $gamekey_list[$k1] = $v1;
        }


        $succ = [];
        foreach ($chat_info as $k=>$v){
            $v['blocktime']           = empty($block_time) ?: $block_time;
            $v['keyword']             = $v['content'];
            $v['rolename']            = $v['uname'];
            $v['addtime']             = $time;
            $v['op_ip']               = $op_ip;
            $v['type']                = $type;
            $v['ban_time']            = !empty($block_time)?$block_time:60*60*24*10; //默认禁言10天
            $v['expect_unblock_time'] = $v['addtime'] + $v['ban_time']; //增加预估解封日期
            $v['op_admin_id']         = $admin_user;
            $v['reason']              = $reason;
            $v['reg_channel_id']      = empty($v['reg_channel_id']) ? 0 : $v['reg_channel_id'];
            $v['platform_uid']        = empty($userInfo[$v['uid']]['platformSuffix']) ? 0 : $userInfo[$v['uid']]['sdkUid']; //判断阿斯加德是否存在平台账号
            $v['platform_tkey']       = empty($userInfo[$v['uid']]['platformSuffix']) ? '' : $userInfo[$v['uid']]['platformSuffix'];

            $uid = $v['uid'];

            $gkey = $v['gkey']?? '';

            $need_change_uid = $gamekey_list[$gkey]['need_change_uid'] ?? 0;

            $need_cp_deal = $gamekey_list[$gkey]['need_cp_deal'] ?? 0;

            //前置转换
            $v = self::dealGameParams($v,$gkey,1);

            //判断uid是否需要转换成聚合的sdkuid
            $v = self::checkNeedChangeUid($v,$need_change_uid);

            //后置转换
            $v = self::dealGameParams($v,$gkey,2);

            $tmp_v = $v;
            $tmp_v['need_cp_deal'] = $need_cp_deal;
            $tmp_res = RoleServer::roleChat($tmp_v,$type);

            if ($tmp_res){
                $v['role_level']     = empty($v['role_level']) ? 0 : $v['role_level'];
                $v['count_money']    = empty($v['count_money']) ? '0.0' : $v['count_money'];
                $v['ip']             = empty($v['ip']) ? '' : $v['ip'];
                $v['ban_type']       = empty($v['ban_type']) ? 1 : $v['ban_type'];
                $v['uid']            = $uid;
                $v['hit_keyword_id'] = isset($v['hit_keyword_id']) ? $v['hit_keyword_id'] : 0;
                $succ[] = $v;
                $res['succ'][] = $k;
                $res['code'] = 1;
            }else{
                $res['fail'][] = $k;
            }



        }
        //还有一个操作就是操作记录入库
        BlockServer::insertBlock($succ, $type2);
        return $res;
    }

    /**
     * @param $info
     * @return array
     * 解除禁言
     */
    public static function blockRelieveChat($info,$blockid)
    {
        $res = ['code' => 0, 'succ' =>[] , 'fail' => []];
        if (empty($info)) {
            return $res;
        }
        $admin_user = self::$user_data['username'];
        $op_ip      = self::$user_data["last_ip"];
        $time       = time();

        $gamekey = Common::getGameKey();
//        $gamekey = Common::getConfig('gamekey');

        $gamekey_list = [];
        foreach($gamekey as $k1=>$v1){
            $gamekey_list[$k1] = $v1;
        }

        $succ = [];


        foreach ($info as $k=>$v){
            $rolename = $v['rolename'];
            $uname = $v['uname'];
            $v['blocktime'] = 0;
            $v['uname']         = $rolename;
            $v['addtime']       = $time;
            $v['op_ip']         = $op_ip;
            $v['type']          = 2;
            $v['op_admin_id']   = $admin_user;
            $v['reason']   = '聊天解封';

            $uid = isset($v['uid']) ? $v['uid']: 0;

            $gkey =  isset($v['gkey']) ? $v['gkey']: '';

            $need_change_uid = $gamekey_list[$gkey]['need_change_uid'] ?? 0 ;
            $need_cp_deal = $gamekey_list[$gkey]['need_cp_deal'] ?? 0 ;

            $v = self::dealGameParams($v,$gkey,1);

            //判断uid是否需要转换成聚合的sdkuid
            $v = self::checkNeedChangeUid($v,$need_change_uid);

            $v = self::dealGameParams($v,$gkey,2);

            $tmp_v = $v;
            $tmp_v['need_cp_deal'] = $need_cp_deal;
            $tmp_res = RoleServer::roleChat($tmp_v,2);
            if ($tmp_res){
                $v['uname'] = $uname;
                $v['role_level'] = empty($v['role_level']) ? 0 : $v['role_level'];
                $v['count_money'] = empty($v['count_money']) ? '0.0' : $v['count_money'];
                $v['ip'] = empty($v['ip']) ? '' : $v['ip'];
                $v['uid'] = $uid;
                $succ[] = $v;
                $res['succ'][] = $k;
                $res['code'] = 1;
            }else{
                $res['fail'][] = $k;
            }

        }

        if($res['succ']){
            $unblock_admin = self::$user_data['username'];
            BlockSqlServer::updateBlockStatus($blockid,$unblock_admin);
        }

        return $res;
    }



    public static function insertBlock($info, $type = ''){
        if (empty($info)) {
            return false;
        }
        foreach ($info as $k=>&$v){
            $v['type']          = $type;
        }
        //还有一个操作就是操作记录入库
        BlockSqlServer::insertBlock($info);
        return true;
    }


    /**
     * 处理一键解封（先根据封禁记录查出用户，然后根据用户查出所有封禁记录并解封）
     * @param $data
     * @return array
     */
    public static function dealUnBlockMixed($blockid){

        if(empty($blockid)) return false;

        $info = BlockSqlServer::getBlockById($blockid);


        $tmp_data = [];
        //整理需要处理的用户信息
        foreach($info as $k=>$v){
            $tmp_data[$v['gkey'].'_'.$v['uid']]['uid'] = $v['uid'];
            $tmp_data[$v['gkey'].'_'.$v['uid']]['gkey'] = $v['gkey'];
        }



        $tmp_res = [];
        foreach($tmp_data as $k1=>$v1){
            $res = BlockSqlServer::getAllChatBlockByUidAndGkey($v1['uid'],$v1['gkey']);
            if(!empty($res)){
                foreach($res as $k2=>$v2){
                    array_push($tmp_res,$v2);
                }
            }
//            $tmp_res[$v1['gkey'].'_'.$v1['uid']] = $res;
        }

        return $tmp_res;

    }

    //判断uid是否需要转换成聚合的sdkuid
    public static function checkNeedChangeUid($one_chat_info,$need_change_uid){

        if($need_change_uid == 1){
            $one_chat_info['need_change_uid'] = 1;

            include_once( self::GAME_SIGN_PATH."/{$one_chat_info['gkey']}.php");

            if(class_exists($one_chat_info['gkey'])){

                $game_model = new $one_chat_info['gkey'];

                if(empty($one_chat_info['openid']) && method_exists($game_model,'uid_to_sdkid_url')){

                    if(property_exists($game_model,'from')){
                        $game_model->from = $one_chat_info['tkey'];
                    }

                    $open_info = $game_model->uid_to_sdkid_url($one_chat_info['uid']);

                    if(is_array($open_info) && $game_model->return_form == 2){

                        foreach($open_info as $k=>$v){
                            $one_chat_info[$k] = $v;
                        }

                    }else{

                        $one_chat_info['openid'] = $open_info;

                    }
                }

                //自定义处理游戏参数
                if(method_exists($game_model,'diyData')){
                    $one_chat_info = $game_model->diyData($one_chat_info);
                }
            }

            //转换uid
            $one_chat_info['uid'] = $one_chat_info['openid'] ?? $one_chat_info['uid'];


        }else{

            $one_chat_info['need_change_uid'] = 0;
        }

        return $one_chat_info;
    }




    //对封禁记录进行解封
    public static function unblockAndChat($blockid = []){
        $banDta = $succ = $fail = $log = [];
        $info = BlockSqlServer::getBlockById($blockid);
        $new_info = [];

        $gamekey = Common::getGameKey();
//        $gamekey = Common::getConfig('gamekey');

        $gamekey_list = [];
        foreach($gamekey as $k=>$v){
            $gamekey_list[$k] = $v;
        }


        foreach($info as $k1=>$v1){
            $new_info[$v1['uid']]['uid'] = $v1['uid'];
            $new_info[$v1['uid']]['gkey'] = $v1['gkey'];
            $new_info[$v1['uid']]['tkey'] = $v1['tkey'];
            $new_info[$v1['uid']]['roleid'] = $v1['roleid'];

            $uid = $v1['uid'];

            self::dealGameParams($v1,$v1['gkey'],1);

            //判断uid是否需要转换成聚合的sdkuid
            $v1 = BlockServer::checkNeedChangeUid($v1,$gamekey_list[$v1['gkey']]['need_change_uid']);

            self::dealGameParams($v1,$v1['gkey'],2);


            if($v1['type'] == "CHAT" || $v1['type'] == "AUTOCHAT" || $v1['type'] == "ACTIONCHAT"){
                $data = $v1;
                //聊天解禁参数

                $data['addtime'] = time();
                $data['ban_time'] = 0;
                $res1 = RoleServer::roleChat($data,2); //解除禁言
                if($res1){
                    BlockSqlServer::updateBlockStatus($v1['id'],'auto');
                }

            }else{
                //如果使用的是cp封禁+sdk封禁模式则需要解封cp
                if($v1['ban_type'] == 2){
                    $data = $v1;
                    $data['addtime'] = time();
                    //判断封禁还是解禁
                    $data['ban_time'] = 0;
                    $res2 = RoleServer::roleBlock($data,2);
                    if($res2){
                        $new_info[$uid]['uid'] = $uid;
                    }

                }
            }

        }

        //处理sdk账号
        if(!empty($new_info)){
            $banDta = UserServer::getUserInfoByMixGameUids($new_info);

            $BanLogicModel = new BanServer();
            $res = $BanLogicModel->ban(
                $banDta,
                1, //对用户uid解封
                2,      //解封
                0,
                '解封');

            //更新成功更改封禁日志状态
            if($res['succ']){
                BlockSqlServer::updateBlockStatus($blockid,'auto');

            }
        }


        return true;
    }

    public static function getBlockInfo()
    {
        $time = time();
        $model =  new Block();
        $block_info_obj = $model->where("status=1 and expect_unblock_time < {$time} and expect_unblock_time !=0 and addtime>=1631581200 ")->limit(200)->select();

        $block_info_obj = empty($block_info_obj) ? [] : $block_info_obj->toArray();

        return $block_info_obj;
    }



    public static function BathBlock($data)
    {

        $time = time();
        $new_data['ban_time'] = empty($data['ban_time']) ? 3156000 : $data['ban_time'];
        //最大封禁时长为1年
        $new_data['ban_time'] = $new_data['ban_time'] > 31536000 ? 31536000 : $new_data['ban_time'];
        $new_data['gkey'] = $data['game'];
        $new_data['roleid'] = $data['roleid'];
        $new_data['sid'] = $data['sid'];
//        $new_data['is_block'] = $data['type'];
        $new_data['addtime'] = $time;
        $new_data['need_cp_deal'] = $data['need_cp_deal'];

        $res2 = false;

        //cp封禁
        $res = RoleServer::BatchRoleBlock($new_data,$data['type']);

        if($res === 1){

            //整理插入记录表数据
//            $new_data['uid'] = $new_info['uid'];
//            $new_data['rolename'] = $new_info['rolename'];
//            $new_data['tkey'] = $new_info['tkey'];

            $batch_block = $new_data;
            $batch_block['add_time'] = $time;
            $batch_block['ban_time'] = $new_data['ban_time'];
            $batch_block['expect_unblock_time'] = $time + $new_data['ban_time'];
            $batch_block['op_admin'] = $data['op_admin'];
            $batch_block['reason'] = $data['reason'];
            $batch_block['type'] = $data['type'];

            //记录入库
            $res2 = BatchBlockSqlServer::insertBatchBlock($batch_block);
        }

        return $res2;

    }

    public static function dealGameParams($chat_info,$gkey,$type)
    {

        //游戏类特殊操作,转换uid,比如神器ios需要将掌玩uid换成游娱uid
        if(isset($gkey) && file_exists(self::GAME_SIGN_PATH."/{$gkey}.php")){
            include_once( self::GAME_SIGN_PATH."/{$gkey}.php");

            if(class_exists($gkey)) {

                self::$game_obj = new $gkey;
            }
        }


        //前置置换
        if($type == 1){

            if(method_exists(self::$game_obj,'beforeChangeData') && in_array('beforeChangeData',get_class_methods(self::$game_obj))){
                //覆盖旧数据
                $chat_info = self::$game_obj->beforeChangeData($chat_info);
            }
        }

        //后置置换
        if($type == 2){

            if(method_exists(self::$game_obj,'afterChangeData') && in_array('afterChangeData',get_class_methods(self::$game_obj)) ){
                //覆盖旧数据
                $chat_info = self::$game_obj->afterChangeData($chat_info);

            }
        }

        return $chat_info;

    }



    /**
     * @param array $chat_info
     * @param array $info
     * @param int $block_time
     * @return array
     */
    public static function roleNameBlockOrLoginOut($user_info=[], $info=[], $block_time = 365*86400,$type = self::TYPE_USER,$reason = '封禁')
    {


        $res = ['code' => 0, 'msg' =>''];
        if (empty($user_info) || empty($info)) {
            $res['msg'] = '用户信息有误';
            return $res;
        }

        $gamekey = Common::getGameKey();

        $gamekey_list = [];
        foreach($gamekey as $k1=>$v1){
            $gamekey_list[$k1] = $v1;
        }


        $admin_user = self::$user_data['username'];
        $op_ip      = self::$user_data['last_ip'];
        $time       = time();

        $userInfo = [];
        foreach ($info as $key => $value){

            foreach ($value as $v){
                $userInfo[$v['uid']]['user_name'] = $v['user_name'];
                $userInfo[$v['uid']]['sdkUid'] = $v['sdkUid'];
                $userInfo[$v['uid']]['platformSuffix'] = $key;
            }
        }

        $succ = [];
        foreach ($user_info as $k=>$v){

            $v['blocktime']           = empty($block_time) ?: $block_time;
            $v['keyword']             = $v['role_name'];
            $v['rolename']            = get_magic_quotes_gpc() == false ? addslashes($v['role_name']) : $v['role_name'];

            $v['addtime']             = $time;
            $v['op_ip']               = $op_ip;
            $v['op_admin_id']         = $admin_user;
            $v['reason']              = $reason;
            $v['ban_time']            = $block_time;
            $v['reg_gid']             = empty($v['reg_gid']) ? 0 : $v['reg_gid'];;
            $v['reg_channel_id']      = empty($v['reg_channel']) ? 0 : $v['reg_channel'];;
            $v['expect_unblock_time'] = $v['addtime'] + $v['ban_time']; //增加预估解封日期
            $uid                      = $v['uid'];
            $gkey                     = $v['gkey'] ?? '';

            $v['sid']                 = $v['server_id'];
            $v['roleid']              = $v['role_id'];

            $need_change_uid          = $gamekey_list[$gkey]['need_change_uid'] ?? 0;


            $v = self::dealGameParams($v,$gkey,1);

            //判断uid是否需要转换成聚合的sdkuid
            $v = self::checkNeedChangeUid($v,$need_change_uid);

            $v = self::dealGameParams($v,$gkey,2);

            //判断封禁的模式 1为踢下线+sdk封禁 2为cp封禁+sdk封禁
            if(isset($gamekey_list[$gkey]['type']) && $gamekey_list[$gkey]['type'] == 1){

                $v['ban_type'] = 1;
                $api_res = RoleServer::roleLoginOut($v);

            }elseif(isset($gamekey_list[$gkey]['type']) && $gamekey_list[$gkey]['type'] == 2){


                $v['ban_type'] = 2;
                $tmp_v = $v;
                $tmp_v['need_cp_deal'] = $gamekey_list[$gkey]['need_cp_deal'] ?? 0;

                $api_res = RoleServer::roleBlock($tmp_v);

            }else{

                $v['ban_type'] = 0;
            }


            $v['hit_keyword_id']   = isset($v['hit_keyword_id']) ? $v['hit_keyword_id'] : 0;
            $v['role_level']       = empty($v['role_level']) ? 0 : $v['role_level'];
            $v['count_money']      = empty($v['count_money']) ? '0.0' : $v['count_money'];
            $v['openid']          = $v['uid'] != $uid ? $v['uid'] : '';
            $v['uid']              = $uid;
            $v['ext']              = $v['ext'] ?? '';


            $succ[] = $v;
        }

        //还有一个操作就是操作记录入库
        RoleNameBlockServer::insertBlock($succ, $type);
        $res = ['code' => 1, 'msg' =>'success'];
        return $res;
    }


    /**
     * @param array $chat_info
     * @param array $info
     * @param int $type type为1时表示封接口，type为2时表示解封接口
     * @param int $block_time
     * @return array
     */
    public static function roleNameBlockChat($user_info=[], $info=[], $type = 1, $block_time = 0,$type2 = self::TYPE_CHAT,$reason = '禁言')
    {


        $res = ['code' => 0, 'succ' =>[] , 'fail' => []];
        if (empty($user_info) || empty($info)) {
            return $res;
        }
        $admin_user = self::$user_data['username'];
        $op_ip      = self::$user_data['last_ip'];

        $time       = time();

        $userName = [];
        foreach ($info as $key => $value){
            foreach ($value as $v){
                $userInfo[$v['uid']]['user_name'] = $v['user_name'];
                $userInfo[$v['uid']]['sdkUid'] = $v['sdkUid'];
                $userInfo[$v['uid']]['platformSuffix'] = $key;
            }
        }

        $gamekey = Common::getGameKey();

        $gamekey_list = [];
        foreach($gamekey as $k1=>$v1){
            $gamekey_list[$k1] = $v1;
        }


        $succ = [];
        foreach ($user_info as $k=>$v){
            $v['blocktime']           = empty($block_time) ?: $block_time;
            $v['keyword']             = $v['role_name'];
            $v['rolename']            = $v['role_name'];

            $v['addtime']             = $time;
            $v['op_ip']               = $op_ip;
            $v['type']                = $type;
            $v['ban_time']            = !empty($block_time)?$block_time:60*60*24*10; //默认禁言10天
            $v['expect_unblock_time'] = $v['addtime'] + $v['ban_time']; //增加预估解封日期
            $v['op_admin_id']         = $admin_user;
            $v['reason']              = $reason;
            $v['reg_channel_id']      = empty($v['reg_channel']) ? 0 : $v['reg_channel'];


            $uid = $v['uid'];

            $gkey = $v['gkey']?? '';

            $need_change_uid = $gamekey_list[$gkey]['need_change_uid'] ?? 0;

            $need_cp_deal = $gamekey_list[$gkey]['need_cp_deal'] ?? 0;

            //前置转换
            $v = self::dealGameParams($v,$gkey,1);

            //判断uid是否需要转换成聚合的sdkuid
            $v = self::checkNeedChangeUid($v,$need_change_uid);

            //后置转换
            $v = self::dealGameParams($v,$gkey,2);

            $tmp_v = $v;
            $tmp_v['need_cp_deal'] = $need_cp_deal;

            $tmp_res = RoleServer::roleChat($tmp_v,$type);

//            $tmp_res = 1;
            if ($tmp_res){
                $v['role_level']     = empty($v['role_level']) ? 0 : $v['role_level'];
                $v['count_money']    = empty($v['count_money']) ? '0.0' : $v['count_money'];
                $v['ban_type']       = empty($v['ban_type']) ? 1 : $v['ban_type'];
                $v['openid']        = $v['uid'] != $uid ? $v['uid'] : '';

                $v['uid']            = $uid;
                $v['hit_keyword_id'] = isset($v['hit_keyword_id']) ? $v['hit_keyword_id'] : 0;
                $v['ext']            = $v['ext'] ?? '';

                $succ[] = $v;
                $res['succ'][] = $k;
                $res['code'] = 1;
            }else{
                $res['fail'][] = $k;
            }
        }


        //还有一个操作就是操作记录入库
        RoleNameBlockServer::insertBlock($succ, $type2);
        return $res;
    }

    /**
     * @param $info
     * @return array
     * 解除禁言
     */
    public static function roleNameBlockRelieveChat($info,$blockid)
    {
        $res = ['code' => 0, 'succ' =>[] , 'fail' => []];
        if (empty($info)) {
            return $res;
        }
        $admin_user = self::$user_data['username'];
        $op_ip      = self::$user_data["last_ip"];
        $time       = time();

        $gamekey = Common::getGameKey();
//        $gamekey = Common::getConfig('gamekey');

        $gamekey_list = [];
        foreach($gamekey as $k1=>$v1){
            $gamekey_list[$k1] = $v1;
        }

        $succ = [];


        foreach ($info as $k=>$v){
            $rolename = $v['rolename'];
            $uname = $v['uname'];
            $v['blocktime'] = 0;
            $v['uname']         = $rolename;
            $v['addtime']       = $time;
            $v['op_ip']         = $op_ip;
            $v['type']          = 2;
            $v['op_admin_id']   = $admin_user;
            $v['reason']   = '聊天解封';

            $uid = isset($v['uid']) ? $v['uid']: 0;

            $gkey =  isset($v['gkey']) ? $v['gkey']: '';

            $need_change_uid = $gamekey_list[$gkey]['need_change_uid'] ?? 0 ;
            $need_cp_deal = $gamekey_list[$gkey]['need_cp_deal'] ?? 0 ;

            $v = self::dealGameParams($v,$gkey,1);

            //判断uid是否需要转换成聚合的sdkuid
            $v = self::checkNeedChangeUid($v,$need_change_uid);

            $v = self::dealGameParams($v,$gkey,2);

            $tmp_v = $v;
            $tmp_v['need_cp_deal'] = $need_cp_deal;
            $tmp_res = RoleServer::roleChat($tmp_v,2);
//            $tmp_res =1;
            if ($tmp_res){
                $v['uname'] = $uname;
                $v['role_level'] = empty($v['role_level']) ? 0 : $v['role_level'];
                $v['count_money'] = empty($v['count_money']) ? '0.0' : $v['count_money'];
                $v['ip'] = empty($v['ip']) ? '' : $v['ip'];
                $v['uid'] = $uid;
                $succ[] = $v;
                $res['succ'][] = $k;
                $res['code'] = 1;
            }else{
                $res['fail'][] = $k;
            }

        }

        if($res['succ']){
            $unblock_admin = self::$user_data['username'];

            RoleNameBlockSqlServer::updateROleNameBlockStatus($blockid,$unblock_admin);


        }

        return $res;
    }


    //对封禁记录进行解封
    public static function roleNameUnblockAndChat($blockid = []){

        $banDta = $succ = $fail = $log = [];

        $info = RoleNameBlockSqlServer::getBlockById($blockid);
        $new_info = [];

        $gamekey = Common::getGameKey();
//        $gamekey = Common::getConfig('gamekey');

        $gamekey_list = [];
        foreach($gamekey as $k=>$v){
            $gamekey_list[$k] = $v;
        }


        foreach($info as $k1=>$v1){
            $new_info[$v1['uid']]['uid'] = $v1['uid'];
            $new_info[$v1['uid']]['gkey'] = $v1['gkey'];
            $new_info[$v1['uid']]['tkey'] = $v1['tkey'];
            $new_info[$v1['uid']]['roleid'] = $v1['roleid'];

            $uid = $v1['uid'];

            self::dealGameParams($v1,$v1['gkey'],1);

            //判断uid是否需要转换成聚合的sdkuid
            $v1 = BlockServer::checkNeedChangeUid($v1,$gamekey_list[$v1['gkey']]['need_change_uid']);

            self::dealGameParams($v1,$v1['gkey'],2);


            if($v1['type'] == "CHAT" || $v1['type'] == "AUTOCHAT" || $v1['type'] == "ACTIONCHAT"){
                $data = $v1;
                //聊天解禁参数

                $data['addtime'] = time();
                $data['ban_time'] = 0;
//                $res1 = RoleServer::roleChat($data,2); //解除禁言
                $res1 = false;
                if($res1){
                    RoleNameBlockSqlServer::updateRoleNameBlockStatus($v1['id'],'auto');
                }

            }else{
                //如果使用的是cp封禁+sdk封禁模式则需要解封cp
                if($v1['ban_type'] == 2){
                    $data = $v1;
                    $data['addtime'] = time();
                    //判断封禁还是解禁
                    $data['ban_time'] = 0;
                    $res2 = RoleServer::roleBlock($data,2);
//                    $res2 = true;
                    if($res2){
                        $new_info[$uid]['uid'] = $uid;
                    }

                }
            }

        }

        //处理sdk账号
        if(!empty($new_info)){
            $banDta = UserServer::getUserInfoByMixGameUids($new_info);

            $BanLogicModel = new BanServer();
            $res = $BanLogicModel->ban(
                $banDta,
                1, //对用户uid解封
                2,      //解封
                0,
                '解封');

            //更新成功更改封禁日志状态
            if($res['succ']){
                RoleNameBlockSqlServer::updateRoleNameBlockStatus($blockid,'auto');

            }
        }


        return true;
    }


}