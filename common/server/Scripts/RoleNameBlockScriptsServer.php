<?php
/**
 * Created by PhpStorm.
 * User: crosstime
 * Date: 2017/10/12
 * Time: 上午11:57
 */
namespace common\server\Scripts;

use common\libraries\Common;
use common\libraries\Dingding;
use common\model\db_statistic\KefuUserRecharge;
use common\server\RoleNameBlock\RoleNameBlockServer;
use common\server\SysServer;

use common\sql_server\KefuUserRoleSqlServer;
use common\sql_server\RoleNameBlockSqlServer;
use common\sql_server\RoleNameBlockWaringSqlServer;
use think\cache\driver\Redis;


class RoleNameBlockScriptsServer
{

    static $redis = '';

    public static function run($data)
    {
        self::$redis = get_redis();
        if(!isset($data['platform'])) return false;

        $platform_info = Common::getPlatformInfoBySuffixAndCache($data['platform']);

        if(empty($platform_info)) return false;

        $login_time_start = file_exists(RUNTIME_PATH.'role_name_block_time.txt') ? file_get_contents(RUNTIME_PATH.'role_name_block_time.txt') : time();

        $login_time_start = $login_time_start-120;
        $login_time_end = time();

        if($login_time_end - $login_time_start>300){
            $login_time_start = time()-120;
        }

        file_put_contents(RUNTIME_PATH.'role_name_block_time.txt',$login_time_end);

        $list = KefuUserRoleSqlServer::getList($data['platform'],"login_date>={$login_time_start} and  login_date<{$login_time_end}",1,5000);
//        $list = KefuUserRoleSqlServer::getList($data['platform'],"uid=15108357",1,1);


        foreach($list as $k=>$v){

            $check_data = $v;

            $count_money = self::getUserCountMoney($v['uid'],$platform_info['platform_id']);

            $check_data['count_money'] = $count_money;
            $check_data['platform_id'] = $platform_info['platform_id'];
            $check_data['platform']    = $data['platform'];

            $check_data['role_name2']  = urlencode($v['role_name']);
            $check_data['sid']         = $v['server_id'];
            $check_data['roleid']      = $v['role_id'];
            $check_data['ext']         = '';

            $is_have = self::checkHaveBlock($check_data);
//            $is_have = false;
            if(!$is_have){

                self::checkRoleName($check_data);
            }

        }



    }


    //最后登录角色信息检测
    public static function checkRoleName($data)
    {

        $data['tkey'] = $data['platform'];

        $data['gkey'] = Common::getCustomerProduct($data['reg_gid'],$data['platform_id']);

        $res = false;
        if(!isset($data['gkey']) || empty($data['gkey'])){
//            return false;
            //没有游戏挂钩直接封禁sdk
            $data['gkey'] = 'autoforbid';
        }

        $check_return = [];
        //检测完全匹配
        $check_return = self::accurateMatching($data['gkey'],$data['role_name2']);

        //检测模糊匹配
        if($check_return['is_hit'] != 1){
            $is_fuzzy = true;
            $check_return =  self::fuzzyMatching($data['gkey'],$data['role_name2']);
        }



        if ($check_return['is_hit'] == 1) {

            $data['tmp_keyword_game'] = $check_return['tmp_keyword_game'];

            //判断命中的关键词是属于模糊还是精准
            if(isset($is_fuzzy) && $is_fuzzy == true){

                $s = RoleNameBlockServer::check_keyword_auditing($data,[$check_return['tmp_keyword']]);

            }else{

                //获取封禁关键词是否符合条件
                $return = RoleNameBlockServer::check_keyword_auto_block($data,[$check_return['tmp_keyword']]);

                if($return['status']){
                    $data['block_type']       = $return['data']['type'];
                    $data['hit_keyword_id']   = $return['data']['keyword_id'];
                    $data['tmp_keyword']      = $return['data']['tmp_keyword'];
                    $data['block_time']       = $return['data']['block_time'];
                    $data['ban_time']         = $return['data']['ban_time'];
                    $data['check_type']       = $return['data']['check_type'];


                    $tmp_res = self::checkRoleNameBlockWaring($data);


                    if($tmp_res){
                        RoleNameBlockServer::autoBlockChat($data);
                    }

                }
            }


        }
    }


    private static function checkRoleNameBlockWaring($data)
    {
        $res = true;

        //data['block_type'] 1:封号+禁言 2:禁言 3：封号
        $base_config = (new SysServer)->getAllConfigByCache();

        $time = time();

        $block_waring_money = isset($base_config['block_waring_money']) ? $base_config['block_waring_money'] : 5000;
        $block_waring_type = isset($base_config['block_waring_type']) ? $base_config['block_waring_type'] : [];


        //block_waring_type 0:block 1:chat
        if(isset($data['count_money']) && $data['count_money'] >= $block_waring_money){

            if($data['block_type'] == 1) $res = false;

            if($data['block_type'] == 2 && in_array('chat',$block_waring_type)) $res = false;

            if($data['block_type'] == 3 && in_array('block',$block_waring_type)) $res = false;

            $data['time'] = $time;

        }

        if($res == false){
            RoleNameBlockWaringSqlServer::insert($data);

            //钉钉报警
//            Dingding::checkBlockWaringDingding($data);

        }


        return $res;

    }


    public static function getUserCountMoney($uid = 0,$platform_id = 0)
    {
        if(empty($uid) || empty($platform_id) ){
            return 0;
        }
        $KefuUserRecharge = new KefuUserRecharge();
        $where['platform_id'] = $platform_id;
        $where['uid'] = $uid;
        $total_pay = $KefuUserRecharge->field('total_pay')->where($where)->find();
        $total_pay = isset($total_pay) ? $total_pay->toArray()['total_pay'] : 0;

        return $total_pay;

    }

    public static function checkHaveBlock($data)
    {

        if(!isset($data['uid']) || !isset($data['role_id'])) return false;

        $time = mktime(0,0,0,date('m'),date('d'),date('Y'));

        $block_log = RoleNameBlockSqlServer::getCount(['uid'=>$data['uid'],'roleid'=>$data['role_id'],'status'=>1,'addtime'=>['>=',$time]]);

        if(!empty($block_log)){
            return true;
        }else{
            return false;
        }
    }


    //检测该游戏的关键词
    public static function accurateMatching($gkey,$tmp_role_name,$check_type=1)
    {
        $return_data = ['is_hit'=>0,'tmp_keyword_game'=>'','tmp_keyword'=>'' ];
        $config = Common::getConfig('keyword_key');
        $rolenameblock_keyword_key   = $config['rolename_block_keyword_key'];

        $game_word_list = self::$redis->SMEMBERS($rolenameblock_keyword_key.'_'.$gkey.'_'.$check_type);

        if( !empty($game_word_list) ) {

            $role_name = str_replace(array(".", "+", ""), array("", "", ""), $tmp_role_name);

            foreach ($game_word_list as $k0=>&$v0){

                $new_game_word_list[$k0] =  urlencode($v0);

                if($role_name === $new_game_word_list[$k0] && urldecode($role_name) === urldecode($v0) && $v0 !==false){
                    $return_data['tmp_keyword_game'] = $gkey;
                    $return_data['tmp_keyword'] = urldecode($v0);
                    $return_data['is_hit'] = 1;
                    $return_data['check_type'] = $check_type;
                    break;
                }

            }
        }

        if($return_data['is_hit'] == 0){
            $word_list = self::$redis->SMEMBERS($rolenameblock_keyword_key.'_'.'autoforbid'.'_'.$check_type);

            if(!empty($word_list)){

                $role_name = str_replace(array(".", "+",""), array("", "",""), $tmp_role_name);

                foreach ($word_list as $k1=>&$v1){

                    $word_list[$k1] =  urlencode($v1);

                    if($role_name === $word_list[$k1] && urldecode($role_name) === urldecode($v1) &&  $v1 !==false){
                        $return_data['tmp_keyword_game'] = 'autoforbid';
                        $return_data['tmp_keyword'] = urldecode($v1);
                        $return_data['is_hit'] = 1;
                        $return_data['check_type'] = $check_type;
                        break;
                    }
                }

            }

        }


        return $return_data;


    }

    public static function fuzzyMatching($gkey,$tmp_role_name,$check_type=2)
    {
        $return_data = ['is_hit'=>0,'tmp_keyword_game'=>'','tmp_keyword'=>'' ];

        $config = Common::getConfig('keyword_key');
        $rolenameblock_keyword_key   = $config['rolename_block_keyword_key'];

        $game_word_list = self::$redis->SMEMBERS($rolenameblock_keyword_key.'_'.$gkey.'_'.$check_type);

        if( !empty($game_word_list) ) {

            $role_name = str_replace(array(".", "+", ""), array("", "", ""), $tmp_role_name);

            foreach ($game_word_list as $k0=>&$v0){

                $new_game_word_list[$k0] =  urlencode($v0);

                $res = preg_match("/{$new_game_word_list[$k0]}/",$role_name, $keywords);

                if ($res && !empty($v0) && mb_strpos(urldecode($role_name),urldecode($v0)) !== false) {

                    $return_data['tmp_keyword_game'] = $gkey;
                    $return_data['tmp_keyword'] = urldecode($v0);
                    $return_data['is_hit'] = 1;
                    $return_data['check_type'] = $check_type;
                    break;
                }
            }
        }

        //检测公共词库的关键词
        if(empty($res)){

            $word_list = self::$redis->SMEMBERS($rolenameblock_keyword_key.'_'.'autoforbid'.'_'.$check_type);

            if(!empty($word_list)){

                $role_name = str_replace(array(".", "+",""), array("", "",""), $tmp_role_name);

                foreach ($word_list as $k1=>&$v1){

                    $word_list[$k1] =  urlencode($v1);

                    $res = preg_match("/{$word_list[$k1]}/",$role_name , $keywords);

                    if($res && !empty($v1) && mb_strpos(urldecode($role_name),urldecode($v1)) !== false){

                        $return_data['tmp_keyword_game'] = 'autoforbid';
                        $return_data['tmp_keyword'] = urldecode($v1);
                        $return_data['is_hit'] = 1;
                        $return_data['check_type'] = $check_type;
                        break;
                    }
                }

            }

        }

        return $return_data;
    }


}