<?php
/**
 * Created by PhpStorm.
 * User: crosstime
 * Date: 2017/10/12
 * Time: 上午11:57
 */
namespace common\server\Scripts;

use common\libraries\Common;
use common\libraries\CUtf8_PY;
use common\libraries\ElasticSearch;
use common\libraries\Logger;
use common\libraries\PinYin;
use common\libraries\Search;
use common\server\Game\BlockServer;
use common\server\Game\RoleServer;
use common\server\Platform\BanServer;
use common\server\Sdk\UserServer as sdkUserServer;

use common\sql_server\ActionBlockSqlServer;
use common\sql_server\BlockSqlServer;
use common\sql_server\KefuCommonMember;
use common\sql_server\KeywordSqlServer;
use common\sql_server\KeywordLogSqlServer;

use Quan\System\Cache\Adapter\Redis;
use Phalcon\Di;
use Quan\System\Config;
use Volc\Service\AdBlocker;

class ScriptsServer
{

    const BAN_UID = 1;
    const BAN_IP   = 2;
    const BAN_IMEI = 3;
    const BAN_USER_NAME = 4;

    const BAN = 1; //封禁
    const U_BAN = 2; //解封

    const BLOCK_LOGIN = 1;//封登录
    const BLOCK_CHAT = 2;//封禁言

    const ACTION_BLOCK = '行为封禁';

    protected $white_id = 'white_id';
    protected $BanLogicModel = '';
    protected $config,$redis = '';
    protected $block_keyword_key,$common_keyword_forbid,$block_keyword_forbid,$merge_chat_keyword,$block_resemble_key,$white_keyword_key,$white_name,
              $check_chat_game,$check_merge_chat_game,$check_action_block_game;

    public function __construct()
    {
//        $this->config = new Config();
        $this->redis = get_redis();
        $this->initKeywordList();

        $product_list = common::getProductList();
        foreach($product_list as $k=>$v){

            //白名单
            $this->white_name[$v['code']] = $this->redis->SMEMBERS($this->white_id.'_'.$v['code']);

        }


        //自动封禁适用游戏
        $this->check_chat_game = ['mori','555','shenqi','jzxjz','555fl-ll','lmzh','cyd','sxj','tjqy','y9cq','shenqiios'];

        //行为封禁适用游戏
        $this->check_action_block_game = ['mori','555','shenqi','jzxjz','555fl-ll','lmzh','cyd','sxj','tjqy','y9cq'];

        //上下文封禁适用游戏
        $this->check_merge_chat_game = ['mori','555','shenqi','jzxjz','555fl-ll','lmzh','cyd'];
    }

    public function initKeywordList(){
        $config = Common::getConfig('keyword_key');
        $this->block_keyword_key     = $config['block_keyword_key'];
        $this->common_keyword_forbid = $config['common_keyword_forbid'];
        $this->block_keyword_forbid  = $config['block_keyword_forbid'];
        $this->merge_chat_keyword    = $config['merge_chat_keyword'];
        $this->block_resemble_key    = $config['block_resemble_key'];
        $this->white_keyword_key     = $config['white_keyword_key'];

    }


    //通过elasticsearch获取聊天信息
    public function getElasticSearchSuggestInfo($start_time,$end_time)
    {
        $search = new ElasticSearch();
        $time = time();

        $dateStart = $start_time?:$time-60;
        $dateEnd = $end_time?:$time;

        $range0['range']['time']['gte']  = $dateStart;

        $range0['range']['time']['lte']  = $dateEnd;

        if($range0){
            $bool['bool']['filter']['bool']['must'][] = $range0;
        }

        $now_month = date('Ym');

        $i = 1;
        $data = [];
        $limit = 5000;

        $result = $search->search(
            [
                $search->index_name.'-'.$now_month
            ],
            $bool,
            '',
            ['time'=>['order'=>'desc']],
            1,
            $limit,
            1
        );

        $scroll_id = $result['scroll_id'];
        $data = $result['data'];

        while(true){

            $res = $search->scroll($scroll_id);
            $scroll_id = $res['scroll_id'];
            if($i>6 || empty($res['data'])){

                break;

            };

            $data = array_merge($data,$res['data']);
            $i++;


        }

        return $data;
    }



    //通过elasticsearch获取聊天信息-测试
    public function getElasticSearchSuggestInfotest($start_time,$end_time){
        $search = new ElasticSearch();
        $time = time();

        $dateStart = $start_time?:$time-60;
        $dateEnd = $end_time?:$time;

        $range0['range']['time']['gte']  = $dateStart;

        $range0['range']['time']['lte']  = $dateEnd;

        if($range0){
            $bool['bool']['filter']['bool']['must'][] = $range0;
        }

        $now_month = date('Ym');

        $i = 1;
        $data = [];
        $limit = 5000;

        $result = $search->search(
            [
                $search->index_name.'-'.$now_month
            ],
            $bool,
            '',
            ['time'=>['order'=>'desc']],
            1,
            $limit,
            1
        );

        $scroll_id = $result['scroll_id'];
        $data = $result['data'];

        while(true){

            $res = $search->scroll($scroll_id);
            $scroll_id = $res['scroll_id'];
            if($i>6 || empty($res['data'])){

                break;

            };

            $data = array_merge($data,$res['data']);
            $i++;


        }

        return $data;
    }



    //处理上下文合并关键词功能
    public function dealMergeChatKeyword($info){

        //根据游戏和用户合并聊天记录
        foreach($info as $k=>$v){
            $new_arr[$v['tkey'].'_'.$v['gkey'].'_'.$v['uid']][] = $v;
        }
        //对合并的数据进行排序
        foreach($new_arr as $k5=>$v5){
            $new_arr5[$k5] =  Common::arraySort($new_arr[$k5],'time',SORT_ASC);
        }

        $product_list = Common::getProductList(1);
        foreach($product_list as $k=>$v){

            $var = 'block_keyword_set_'.$v['code'];
            $word_list = $this->redis->SMEMBERS($var);

            $preg_str = implode("|",$word_list);
            $this->$var = $preg_str;
        }


        //先排除单句包含关键词的
        foreach($new_arr5 as $k6=>$v6){

            foreach($v6 as $k7=>$v7){
                $var = 'block_keyword_set_'.$v7['gkey'];
                $preg_str = $this->$var;

                $t_content = str_replace(array(".", "+"), array("", ""), $v7['content']);
                $res = preg_match_all("/{$preg_str}/", $t_content, $keywrods);

                if($res && !empty($preg_str)){

                    unset($new_arr5[$k6][$k7]);
                }
            }
        }


        //合并上下文聊天信息
        $merge_arr = [];

        foreach($new_arr5 as $k1=>$v1){
            $content = '';
            foreach($v1 as $k2=>$v2){
                $content .= $v2['content'];
            }
            $v1[0]['content'] = $content;

            array_push($merge_arr,$v1[0]);
        }


        //判断聊天信息是否触发限制，是的话返回待处理的聊天信息
        foreach($merge_arr as $k8=>$v8){
            $res = $this->autoForbidWord($v8,'',1);

            //灰度测试
            if($res){
                //人员白名单跳过封禁
                if(in_array($v8['uid'],$this->white_name[$v8['gkey']])){
                    continue;
                }
                //判断游戏是否开启自动封禁
                elseif(in_array($v8['gkey'],$this->check_merge_chat_game)){
                    $need_deal_arr[$v8['uid']] = $res;
                }
                else{
                    continue;
                }
            }

            //正式
//            if($res){
//                $need_deal_arr[$v8['uid']] = $res;
//            }
        }

        //对违规聊天信息内容进行封禁
        if(!empty($need_deal_arr)){
            $this->autoBlockChat($need_deal_arr);
        }

    }


    //自动封禁
    private function autoForbidWord($data,$imei,$type=0)
    {

        $data['imei'] = $imei;
        $data['type'] = $type;


        if( $data['content'] == '' || $imei == "00000000-0000-0000-0000-000000000000" ){
            return false;
        }


        //检测该游戏的关键词
        $game_word_list = $this->redis->SMEMBERS($this->block_keyword_key.'_'.$data['gkey']);

        $word_list = $this->redis->SMEMBERS($this->block_keyword_key.'_'.'autoforbid');


        $preg_str = implode("|",$game_word_list);
        $preg_str = '哈哈';
        if( !empty($preg_str) || !empty($word_list) ) {

            $t_content = str_replace(array(".", "+",""), array("", "",""), $data['content']);

            if(!empty($preg_str)){
                $res = preg_match_all("/{$preg_str}/", $t_content, $keywrods);
                if($res){
                    $data['tmp_keyword_game'] = $data['gkey'];
                }
            }


            //检测公共词库的关键词
            if(empty($res)){

                $preg_str = implode("|",$word_list);

                $t_content = str_replace(array(".", "+",""), array("", "",""), $data['content']);

                if(!empty($preg_str)){
                    $res = preg_match_all("/{$preg_str}/", $t_content, $keywrods);
                    if($res){
                        $data['tmp_keyword_game'] = 'autoforbid';
                    }
                }


            }


            //判断是否符合谐音
            if(!$res && preg_match('/[\x{4e00}-\x{9fa5}]/u', $data['content']) ){

                $pinyin_list = $this->redis->SMEMBERS($this->block_resemble_key);

                $pinyin_preg_str = implode("|",$pinyin_list);
                if(!empty($pinyin_preg_str) ){
                    $pinyin = new PinYin();
                    $t_content = str_replace(array("."," "), array("", ""),$pinyin->main($data['content'],true,true));

                    $pinyin_preg_str = str_replace(array(".", "+"," "), array("", "",""),$pinyin_preg_str);

                    $res = preg_match_all("/{$pinyin_preg_str}/", $t_content, $keywrods);

                }


            }


            if ($res) {

                //触发记录
                $this->add_keyword_log($data,$keywrods);

                $return = $this->check_keyword_block($data,$keywrods);

                if ($return['status']) {
                    $data['block_type'] = $return['type'];
                    return $data;
                }else{
                    return false;
                }
            }
        }
    }

    /**
     * 触发关键词记录
     * @param $data
     */
    private function add_keyword_log($data, $keywords){

        $keywords = array_unique($keywords);

        //如果是阿斯加德的账号不用查库，其他的需要查对应的平台库
        if($data['tkey'] != 'asjd'){
            $info = KefuCommonMember::getUnameByUid([$data['uid']],$data['tkey']);
        }


        $k = new KeywordLogSqlServer();
        foreach ($keywords[0] as $keyword){
            $add_data = [
                'uid'=>$data['uid'],
                'uname'=>isset($info[$data['uid']])?$info[$data['uid']]:'',
                'gkey'=>$data['gkey']?:'',
                'sid'=>$data['sid']?:'',
                'keyword'=>$keyword,
                'content'=>$data['content']?:'',
                'roleid'=>$data['roleid']?:'',
                'rolename'=>$data['uname']?:'',
                'role_level'=>$data['role_level']?:0,
                'count_money'=>$data['count_money']?:0,
                'addtime'=>time(),
                'type'=>$data['type']?:0,
                'tkey'=>$data['tkey']?:''
            ];

            $k->insertData($add_data);

        }
    }


    /**
     * 检查关键词触发次数是否达到条件
     * @param $data
     * @param $keywords
     * @return bool
     */
    private function check_keyword_block($data,$keywords){
        $keywords = array_unique($keywords);


        $flag = false;
        $return = ['type'=>[],'status'=>$flag];
        foreach ($keywords[0] as $keyword){

            $keyword = urldecode($keyword);

            $info = KeywordSqlServer::getOneByWhere( "keyword='{$keyword}' and game='{$data['tmp_keyword_game']}'" );

            if(isset($info['status']) && !empty($info['status'])){

                $count = $this->get_keyword_count($data['uid'],$keyword,$data['tkey']);
                if($info['num'] <= $count &&
                    $data['count_money'] >= $info['money_min']&&$data['count_money'] <= $info['money_max'] &&
                    $data['role_level'] >= $info['level_min'] && $data['role_level'] <= $info['level_max']){
                    $return['status'] = true;
                    $return['type'][] = $info['type'];
                    break;
                }
            }elseif(isset($info['resemble_status']) && !empty($info['resemble_status'])){
                $return['status'] = true;
                break;
            }else{
                $return['status'] = false;

            }
        }

        return $return;
    }

    /**
     * 获取关键词触发次数
     * @param $uid
     * @param $keyword
     * @return mixed
     */
    private function get_keyword_count($uid,$keyword,$tkey){
        $time = time()-3600;
        $count = KeywordLogSqlServer::getCount("uid = {$uid} and tkey= '{$tkey}' and keyword= '{$keyword}' and addtime>= {$time}");
        return $count;
    }



    //处理关键词功能
    public function dealChatKeyword($chat_info){

        $need_deal_arr = [];
        //判断聊天信息是否触发限制，是的话返回待处理的聊天信息
        foreach($chat_info as $k=>$v){

            $res = $this->autoForbidWord($v,'',0);

            //灰度测试
            if($res){
                //人员白名单跳过封禁
                if(in_array($v['uid'],$this->white_name[$v['gkey']])){
                    continue;
                }
                //判断游戏是否开启自动封禁
                elseif(in_array($v['gkey'],$this->check_chat_game)){
                    $need_deal_arr[$v['uid']] = $res;
                }
                else{
                    continue;
                }
            }


            //正式
//                if($res){
//                    $need_deal_arr[$v['uid']] = $res;
//                }



        }

        if(!empty($need_deal_arr)){
            $this->autoBlockChat($need_deal_arr);
        }

    }

    //自动封禁
    public function autoBlockChat($data){
        $chat_info = $data;

        $info = sdkUserServer::getUserInfoByMixGameUids($chat_info);


        foreach($data as $k=>$v){
            foreach($v['block_type'] as $k1=>$v1){

                switch ($v1){
                    //封号+禁言
                    case 1:
//                        var_dumP(1);
                    BlockServer::blockChat($chat_info,$info,1,10*86400,'AUTOCHAT','触发关键词自动禁言');

                        BlockServer::blockOrLoginOut($chat_info, $info,365*86400,'AUTO','触发关键词自动封禁');
                    $this->BanLogicModel = new BlockServer();
                    $block_time = 365*86400;

                    //封禁用户uid
                    $this->blockUids($info,$block_time);
                        break;
                    //禁言
                    case 2:
//                        var_dumP(2);
                        BlockServer::blockChat($chat_info,$info,1,10*86400,'AUTOCHAT','触发关键词自动禁言');
                        break;
                    //封号
                    case 3:
//                        var_dumP(3);
                        BlockServer::blockOrLoginOut($chat_info, $info,365*86400,'AUTO','触发关键词自动封禁');
                    $this->BanLogicModel = new BanServer();
                    $block_time = 365*86400;

                        //封禁用户uid
                    $this->blockUids($info,$block_time);
                        break;
                    default:
                        break;
                }
            }
        }
        return true;
    }


    private function blockUids($info,$block_time = 0,$reason = '自动封禁'){

        //平台封禁操作
        $res = $this->BanLogicModel->ban(
            $info,
            self::BAN_UID,
            self::BAN,
            $block_time,
            $reason);
//        $res['succ'][] = 1850742;

        return $res['succ'];


    }

    private function blockIps($chat_info,$info,$block_time = 0,$reason = '自动封禁'){
        $data = [];
        foreach ($info as $key=>$value){
            $tmp = [];
            foreach ($value as $k=>$v){
                $tmp[$k]['sdkUid'] = $chat_info[$v['uid']]['ip'];
            }
            $data[$key] = $tmp;
        }

//        $BanLogicModel = new BanLogic();
        $res = $this->BanLogicModel->ban(
            $data,
            self::BAN_IP,
            self::BAN,
            $block_time,
            $reason);

        return $res['succ'];
    }

    private function blockImeis($chat_info,$info,$block_time = 0,$reason = '自动封禁'){
        $data = [];
        foreach ($info as $key=>$value){
            $tmp = [];
            foreach ($value as $k=>$v){
                $tmp[$k]['sdkUid'] = $chat_info[$v['uid']]['imei'];
            }
            $data[$key] = $tmp;
        }

        //平台封禁操作
        $res = $this->BanLogicModel->ban(
            $data,
//                    self::BAN_IMEI,
            self::BAN_IMEI, //找不到对应地设备号所以就暂用封了对应地账号
            self::BAN,
            $block_time,
            $reason);

        return $res['succ'];
    }



    /**
     * 处理私聊人数规则
     */
    public function dealPrivateChat($user_chat,$limit_time,$rule){


        if(!is_array($user_chat) || empty($limit_time) || empty($rule)) return false;

        $private_chat_num = 0;

        foreach($user_chat as $k=>$v){

            //判断用户聊天信息是否符合私聊人数阈值
            if(
                $v['gkey'] == $rule['product_name'] &&
                $v['role_level'] >= $rule['min_level'] &&
                $v['role_level'] <= $rule['max_level'] &&
                $v['count_money'] >= $rule['min_money'] &&
                $v['count_money'] <= $rule['max_money'] &&
                $v['type'] == 1
            ){
                $private_chat_num += 1;
            }

        }


        if($private_chat_num >= $rule['private_chat_num']){

            $this->action_block($user_chat,$rule);

        }

    }

    /**
     * 处理重复聊天规则
     */
    public function dealRepeatMsg($user_chat,$limit_time,$rule){


        if(!is_array($user_chat) || empty($limit_time) || empty($rule)) return false;

        $repeat_msg = [];

        foreach($user_chat as $k=>$v){

            //判断用户聊天信息是否符合私聊人数阈值
            if(
                $v['gkey'] == $rule['product_name'] &&
                $v['role_level'] >= $rule['min_level'] &&
                $v['role_level'] <= $rule['max_level'] &&
                $v['count_money'] >= $rule['min_money'] &&
                $v['count_money'] <= $rule['max_money']
            ){


                $repeat_msg[$v['content']][] = $v['content'];

            }

        }

        //重复信息达到阈值
        foreach($repeat_msg as $k2=>$v2){
            if(count($v2) >= $rule['repeat_msg_num']){
                $this->action_block($user_chat,$rule);
                break;
            }
        }


//
//        if(count($repeat_msg['repeat_msg']) >= $rule['repeat_msg_num']){
//
//        }
//

    }


    /**
     * 处理重复聊天规则
     */
    public function dealFigureNum($user_chat,$limit_time,$rule){
        if(!is_array($user_chat) || empty($limit_time) || empty($rule)) return false;

//        $repeat_msg['figure_num'] = [];
        $figure_num = 0;
        foreach($user_chat as $k=>$v){

            //判断用户聊天信息是否符合私聊人数阈值
            if(
                $v['gkey'] == $rule['product_name'] &&
                $v['role_level'] >= $rule['min_level'] &&
                $v['role_level'] <= $rule['max_level'] &&
                $v['count_money'] >= $rule['min_money'] &&
                $v['count_money'] <= $rule['max_money']
            ){
                if(preg_match("/\d{$rule['limit_figure_length']}/", $v['content'])){
                    $figure_num += 1;
                }
            }

        }


        //重复信息达到阈值
        if($figure_num >= $rule['figure_num']){

            $this->action_block($user_chat,$rule);
        }


    }

    /**
     * 处理累计字符
     */
    public function dealLimitCharLength($user_chat,$limit_time,$rule){
        if(!is_array($user_chat) || empty($limit_time) || empty($rule)) return false;

//        $repeat_msg['figure_num'] = [];
        $limit_char_length = 0;
        foreach($user_chat as $k=>$v){

            //判断用户聊天信息是否符合私聊人数阈值
            if(
                $v['gkey'] == $rule['product_name'] &&
                $v['role_level'] >= $rule['min_level'] &&
                $v['role_level'] <= $rule['max_level'] &&
                $v['count_money'] >= $rule['min_money'] &&
                $v['count_money'] <= $rule['max_money']
            ){

                $limit_char_length += mb_strlen($v['content']);
            }

        }


        //重复信息达到阈值
        if($limit_char_length >= $rule['limit_char_length']){
            $this->action_block($user_chat,$rule);
        }

    }

    //行为封禁实际操作
    private function action_block($user_chat,$rule){

        $redis = get_redis();
        $this->BanLogicModel = new BanServer();
        $info = sdkUserServer::getUserInfoByMixGameUids($user_chat);

        //灰度测试
        foreach ($user_chat as $v) {

            //灰度测试
            //人员白名单跳过封禁
            if(in_array($v['uid'],$this->white_name[$v['gkey']])){
                continue;
            }
            //判断游戏是否开启自动封禁
            elseif(in_array($v['gkey'],$this->check_action_block_game)){
                $chat_info[$v['uid']] = $v;
            }
            else{
                continue;
            }
        }


        //正式
//        foreach ($user_chat as $v) {
//            $chat_info[$v['uid']] = $v;
//        }

        //判断封禁类型 1：封登录 2：封禁言
        switch ($rule['type']){
            //封登录
            case self::BLOCK_LOGIN:
                if($rule['ban_object'] == self::BAN_UID){
                    //封禁用户uid
                    $success = $this->blockUids($info,$rule['ban_time']*60,self::ACTION_BLOCK.'触发规则id为：'.$rule['id'].','.$rule['name']);
                }elseif($rule['ban_object'] == self::BAN_IP){
                    //封禁用户ips
                    $success = $this->blockIps($user_chat,$info,$rule['ban_time']*60,self::ACTION_BLOCK.'触发规则id为：'.$rule['id'].','.$rule['name']);
                }elseif($rule['ban_object'] == self::BAN_IMEI){
                    //封禁用户imei
                    $success = $this->blockImeis($user_chat,$info,$rule['ban_time']*60,self::ACTION_BLOCK.'触发规则id为：'.$rule['id'].','.$rule['name']);
                }

                //设置操作缓存，已处理的用户，不再进行操作
                if(!empty($success)){
                    foreach($success as $key=>$value){
                        if($redis->set('action_block_'.$rule['ban_object'].'_'.$value,1)) {
                            $redis->Expire('action_block_'.$rule['ban_object'].'_'.$value, 1800);
                        }
                    }
                }

                //拼接聊天信息
                BlockServer::blockOrLoginOut($chat_info, $info,$rule['ban_time']*60,'ACTION','触发规则id为：'.$rule['id'].','.$rule['name']);
                break;
            //封禁言
            case self::BLOCK_CHAT:
                BlockServer::blockChat($chat_info,$info,1,$rule['ban_time']*60,'ACTIONCHAT','触发规则id为：'.$rule['id'].','.$rule['name']);
                break;
            default:
                return false;

        }
    }


    //过滤白名单内容
    public function filterContent($chat_info){

        if(empty($chat_info)){
            return false;
        }
        foreach($chat_info as $k=>$v){

            //去除包含白名单的聊天信息
            $white_list = $this->redis->SMEMBERS($this->white_keyword_key);

            //存在白名单词则处理
            if($white_list){
                $white_list_str = implode("|",$white_list);
                $t_content = str_replace(array(".", "+"), array("", ""), $v['content']);
                $res = preg_match_all("/{$white_list_str}/", $t_content, $keywrods);

                if($res){
                    unset($chat_info[$k]);
                    continue;
                }
            }


            //去除特殊符号
            $chat_info[$k]['content'] = Common::replace_specialChar($chat_info[$k]['content']);

            //将中文转成数字
//            $chat_info[$k]['content'] = (string)Common::checkNatInt($chat_info[$k]['content']);
        }

        return $chat_info;

    }


    public function insertBlockLog($chat_info,$type = 'AUTO'){
        $time = time();
        $admin_user = 'auto';
        $op_ip      = '127.0.0.1';
        foreach ($chat_info as $k=>$v){

            $v['blocktime'] = empty($block_time) ?: $block_time*24*60*60;
            $v['keyword']       = $v['content'];
            $v['rolename']      = $v['uname'];
            $v['uid']           = $v['uid'];
            $v['uname']         = '';
            $v['addtime']       = $time;
            $v['op_ip']         = $op_ip;
            $v['op_admin_id']   = $admin_user;
            $v['tkey']          = $v['tkey'];

            $v['role_level'] = empty($v['role_level']) ? 0 : $v['role_level'];
            $v['count_money'] = empty($v['count_money']) ? '0.0' : $v['count_money'];
            $v['ip'] = empty($v['ip']) ? '' : $v['ip'];
            $succ[] = $v;
        }
        Block::insertBlock($succ,$type);
    }


    //文本相似度匹配
    public function similarityContent($a){
        try{

        }catch(\Exception $e){

        }
    }


    public function checkChatTest($chat_info){
        Logger::init([
            'path' => RUNTIME_PATH.'admin/'.__FUNCTION__,
            'filename' => date('Y-m-d', time())
        ]);

        $client = AdBlocker::getInstance();
        $client->setAccessKey("AKLTMWUyNWI0NTc2NzUzNDkwMTgxMGQ5NzU4MGYwYzVjNDg");
        $client->setSecretKey("T1RjNE9XUmtNMlkwTkRBNE5EWTNPV0UxWWprMlltUmpNelE0T0dVMU9EWQ==");
        $type = [1=>'single',2=>'world',3=>'single',4=>'other',5=>'guild',6=>'guild',7=>'other',8=>'world',9=>'other',10=>'world'];
        if(empty($chat_info)){
            return false;
        }

        foreach($chat_info as $k=>$v){
            if($v['gkey'] !== 'shenqi'){
                continue;
            }
            $pay_num = empty($v['count_money']) ? -1 : 3;
            $params = [
                'account_id'=>$v['uid'],
                'chat_text'=> strip_tags($v['content']),
                'channel_type'=> $type[$v['type']],
                'server_id'=>$v['sid'],
                'operate_time'=>$v['time'],
                'ip'=>$v['ip'],
                'device_fp'=>md5($v['imei']),
                'sender_role_id'=>$v['roleid'],
                'sender_nickname'=>"{$v['uname']}",
                'sender_role_level'=>$v['role_level'],
                'sender_role_ce'=>$v['count_money'],
                'sender_pay_total'=>$pay_num,
                'sender_role_create_time'=>$v['ip_id'],
                'receiver_account_id'=>$v['to_uid'],
                'receiver_role_id'=>$v['to_uname'],
                'receiver_nickname'=>$v['to_uname'],

            ];


            $str = json_encode($params);
            if(!empty($str)){
                $res = $client->adBlock(238209, "chat", $str);

                $res = json_decode($res,1);

                Logger::write([
                    'tag' => 'actionDataJson',
                    'msg' => json_encode($res),
                    'data' => $str
                ]);

                if($res['Result']['Code'] == 0 && $res['Result']['Data']['Decision'] == 'BLOCK'){
                    $chat_info[$k]['need_block'] == 1;
                }
            }

        }

        return $chat_info;
    }

}