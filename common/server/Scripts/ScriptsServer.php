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
use common\libraries\ElasticSearch;
use common\libraries\Logger;
use common\libraries\PinYin;
use common\server\Game\BlockServer;

use common\server\Platform\BanServer;
use common\server\Sdk\UserServer as sdkUserServer;

use common\server\SysServer;

use common\sql_server\BlockSqlServer;
use common\sql_server\BlockWaringSqlServer;
use common\sql_server\KefuCommonMember;
use common\sql_server\KeywordSqlServer;
use common\sql_server\KeywordLogSqlServer;
use common\sql_server\MonitoringUidSqlServer;
use think\Config;


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

    const BLOCK_TIME = 365*86400;//封禁时长
    const BAN_TIME = 10*86400;//禁言时长

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
        $this->BanLogicModel = new BanServer();

        foreach($product_list as $k=>$v){

            //白名单
            $this->white_name[$v['code']] = $this->redis->SMEMBERS($this->white_id.'_'.$v['code']);

        }

        $gamekey = Common::getGameKey();


        $need_check_game_arr = [];
        $need_check_action_game_arr = [];
        $need_check_merge_game_arr = [];

        foreach($gamekey as $k=>$v){
            if($v['auto_block'] == 1){
                $need_check_game_arr[] = $k;
            }

            if($v['auto_action_block'] == 1){
                $need_check_action_game_arr[] = $k;
            }

            if($v['auto_merge_block'] == 1){
                $need_check_merge_game_arr[] = $k;
            }

        }

        //关键词封禁适用游戏
        $this->check_chat_game = $need_check_game_arr;

        //行为封禁适用游戏
        $this->check_action_block_game = $need_check_action_game_arr;

        //上下文封禁适用游戏
        $this->check_merge_chat_game = $need_check_merge_game_arr;
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

                if(!isset($this->$var)){
                    continue;
                }
                $preg_str = $this->$var;
                $word_list = explode('|',$preg_str);

                $t_content = str_replace(array(".", "+", ""," "), array("", "", "",""), $v7['content2']);

                foreach ($word_list as $k0=>&$v0){

                    $new_game_word_list[$k0] =  urlencode($v0);

                    $res = preg_match("/{$new_game_word_list[$k0]}/",$t_content, $keywords);

                    if ($res && !empty($v0) && mb_strpos(urldecode($t_content),$v0) !== false) {

                        unset($new_arr5[$k6][$k7]);
                    }
                }


            }
        }

        //合并上下文聊天信息
        $merge_arr = [];


        foreach($new_arr5 as $k1=>$v1){
            $content = '';
            $content2 = '';
            foreach($v1 as $k2=>$v2){

                $content .= $v2['content'];
                $content2 .= $v2['content2'];
            }

            //将内容合并到第一条信息上
            $v1[0]['content2'] = $content2;
            $v1[0]['content'] = $content;

            array_push($merge_arr,$v1[0]);
        }

        $need_deal_arr = [];
        //判断聊天信息是否触发限制，是的话返回待处理的聊天信息
        foreach($merge_arr as $k8=>$v8){
            $res = $this->autoForbidWord($v8,'',1);

            //灰度测试
            if($res){
                //人员白名单跳过封禁
                if(isset($this->white_name[$v8['gkey']]) && in_array($v8['uid'],$this->white_name[$v8['gkey']])){
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

        }

        //对违规聊天信息内容进行封禁
        if(!empty($need_deal_arr)){
            $this->autoBlockChat($need_deal_arr);
        }

    }


    //自动封禁
    private function autoForbidWord($data,$imei,$type=0)
    {

        $res = false;
        $data['imei'] = $imei;
        $data['chat_type'] = !empty($data['type']) ? $data['type'] : 0 ;
        $data['type'] = $type;

        $gkey = isset($data['gkey']) ? $data['gkey'] : '';

        if( $data['content'] == '' || $imei == "00000000-0000-0000-0000-000000000000" || empty($gkey)){
            return false;
        }


        //检测该游戏的关键词
        $game_word_list = $this->redis->SMEMBERS($this->block_keyword_key.'_'.$data['gkey']);

        if( !empty($game_word_list) ) {

            $t_content = str_replace(array(".", "+", ""), array("", "", ""), $data['content2']);

            foreach ($game_word_list as $k0=>&$v0){

                $new_game_word_list[$k0] =  urlencode($v0);

                $res = preg_match("/{$new_game_word_list[$k0]}/",$t_content, $keywords);

                if ($res && !empty($v0) && mb_strpos(urldecode($t_content),urldecode($v0)) !== false) {

                    $data['tmp_keyword_game'] = $data['gkey'];
                    break;
                }
            }
        }

        //检测公共词库的关键词
        if(empty($res)){

            $word_list = $this->redis->SMEMBERS($this->block_keyword_key.'_'.'autoforbid');

            if(!empty($word_list)){

                $t_content = str_replace(array(".", "+",""), array("", "",""), $data['content2']);

                foreach ($word_list as $k1=>&$v1){

                    $word_list[$k1] =  urlencode($v1);

                    $res = preg_match("/{$word_list[$k1]}/",$t_content , $keywords);

                    if($res && !empty($v1) && mb_strpos(urldecode($t_content),urldecode($v1)) !== false){

                        $data['tmp_keyword_game'] = 'autoforbid';
                        break;
                    }
                }

            }

        }


        //判断是否符合谐音
        if(!empty($res) && preg_match('/[\x{4e00}-\x{9fa5}]/u', $data['content']) ){

            $pinyin_list = $this->redis->SMEMBERS($this->block_resemble_key);

            $pinyin_preg_str = implode("|",$pinyin_list);
            if(!empty($pinyin_preg_str) ){
                $pinyin = new PinYin();
                $t_content = str_replace(array("."," "), array("", ""),$pinyin->main($data['content'],true,true));

                $pinyin_preg_str = str_replace(array(".", "+"," "), array("", "",""),$pinyin_preg_str);

                $res = preg_match_all("/{$pinyin_preg_str}/", $t_content, $keywords);

            }


        }


        if ($res) {

            //触发记录
            $this->add_keyword_log($data,$keywords);

            $return = $this->check_keyword_block($data,$keywords);


            if ($return['status']) {

                $data['block_type']      = $return['data']['type'];
                $data['hit_keyword_id']  = $return['data']['keyword_id'];
                $data['tmp_keyword']     = $return['data']['tmp_keyword'];
                $data['block_time']      = $return['data']['block_time'];
                $data['ban_time']        = $return['data']['ban_time'];
                $tmp_res = $this->checkBlockWaring($data);

                if($tmp_res){
                    return $data;
                }else{
                    return false;
                }

            }else{
                return false;
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

        foreach ($keywords as $keyword){

            if(empty($keyword)){
                continue;
            }

            $add_data = [
                'uid'=>$data['uid'],
                'uname'=>isset($info[$data['uid']])?$info[$data['uid']]:'',
                'gkey'=>$data['gkey']?:'',
                'sid'=>$data['sid']?:'',
                'keyword'=>urldecode($keyword),
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
        $return = ['data'=>[],'status'=>$flag];
        foreach ($keywords as $keyword){

            if(empty($keyword)){
                continue;
            }

            $keyword = urldecode($keyword);
            $infos = KeywordSqlServer::getAllByWhere( "keyword='{$keyword}' and game='{$data['tmp_keyword_game']}'" );

            foreach($infos as $k=>$info){

                if(!empty($info['status'])){

                    $count = $this->get_keyword_count($data['uid'],$keyword,$data['tkey']);

                    if($info['num'] <= $count &&
                        $data['count_money'] >= $info['money_min']&&$data['count_money'] <= $info['money_max'] &&
                        $data['role_level'] >= $info['level_min'] && $data['role_level'] <= $info['level_max']){

                        $return['status'] = true;
                        $return['data']['type'] = $info['type'];
                        $return['data']['keyword_id'] = $info['id'];
                        $return['data']['tmp_keyword'] = $info['keyword'];
                        $return['data']['block_time'] = $info['block_time'];
                        $return['data']['ban_time'] = $info['ban_time'];
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
                if(isset($this->white_name[$v['gkey']]) && in_array($v['uid'],$this->white_name[$v['gkey']])){
                    continue;
                }
                //判断游戏是否开启自动封禁
                elseif(in_array($v['gkey'],$this->check_chat_game)){

                    //一个用户只处理一次，不管几个关键词
                    $need_deal_arr[$v['uid']] = $res;
                }
                else{

                    continue;
                }
            }

        }


        if(!empty($need_deal_arr)){
            $this->autoBlockChat($need_deal_arr);
        }

    }

    //自动封禁
    public function autoBlockChat($data){
        $chat_info = $data;

        $info = sdkUserServer::getUserInfoByMixGameUids($chat_info);

        unset($info['failUid']);

        $product_list = Common::getGameKey();

        foreach($data as $k=>$v){

            $ban_add_time = 0;
            $ban_nums = BlockSqlServer::getCount(['uid'=>$v['uid'],'tkey'=>$v['tkey'],'gkey'=>$v['gkey'],'type'=>[['=','CHAT'],['=','AUTOCHAT'],'or']]);

            //根据禁言次数累加禁言时间
            if(isset($product_list[$v['gkey']]['chat_time_limit'])){
                $ban_add_time = $ban_nums * $product_list[$v['gkey']]['chat_time_limit'];

                $ban_add_time = $ban_add_time>72 ? 72*3600 : $ban_add_time*3600;
            }

            $block_time = !empty($v['block_time']) ? $v['block_time'] : self::BLOCK_TIME;
            $ban_time = !empty($v['ban_time']) ? $v['ban_time'] : self::BAN_TIME;
            $ban_time = $ban_time + $ban_add_time;

            switch ($v['block_type']){

                //封号+禁言
                case 1:

                    BlockServer::blockChat([$v],$info,1,$ban_time,'AUTOCHAT','触发关键词自动禁言');
                    BlockServer::blockOrLoginOut([$v], $info,$block_time,'AUTO','触发关键词自动封禁');

                    //封禁用户uid
                    $this->blockUids($info,$block_time);
                    break;
                //禁言
                case 2:

                    BlockServer::blockChat([$v],$info,1,$ban_time,'AUTOCHAT','触发关键词自动禁言');
                    break;
                //封号
                case 3:

                    BlockServer::blockOrLoginOut([$v], $info,$block_time,'AUTO','触发关键词自动封禁');

                    //封禁用户uid
                    $this->blockUids($info,$block_time);
                    break;
                default:
                    break;
            }

        }
        return true;
    }



    private function checkBlockWaring($data)
    {
        $res = true;
        //data['block_type'] 1:封号+禁言 2:禁言 3：封号
        $base_config = (new SysServer)->getAllConfigByCache();

        $block_waring_money = isset($base_config['block_waring_money']) ? $base_config['block_waring_money'] : 5000;
        $block_waring_type = isset($base_config['block_waring_type']) ? $base_config['block_waring_type'] : [];

        //block_waring_type 0:block 1:chat
        if(isset($data['count_money']) && $data['count_money'] >= $block_waring_money){

            if($data['block_type'] == 1) $res = false;

            if($data['block_type'] == 2 && in_array('chat',$block_waring_type)) $res = false;

            if($data['block_type'] == 3 && in_array('block',$block_waring_type)) $res = false;

        }

        if($res == false){
            BlockWaringSqlServer::insert($data);

            //钉钉报警
            Dingding::checkBlockWaringDingding($data);
      
        }


        return $res;

    }

    //处理uid自动禁言
    public function dealMonitoringUid($chatInfo)
    {

        $keyword_key = Config::get('keyword_key')['keyword_key'];

        $info = sdkUserServer::getUserInfoByMixGameUids($chatInfo);

        unset($info['failUid']);

        foreach($chatInfo as $k=>$v){
            $monitoring_uids = $this->redis->SMEMBERS($keyword_key['platform_monitoring_uid'].'_'.$v['tkey']);

            if(!empty($monitoring_uids) && in_array($v['uid'],$monitoring_uids)){
                $where = ['uid'=>$v['uid'],'status'=>1];

                $res = MonitoringUidSqlServer::getOneByWhere($where);

               if(!empty($res)){
                   BlockServer::blockChat([$v],$info,1,$res['ban_time'],'AUTOCHAT','自动根据用户ID禁言角色');
               }
            }

        }
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

       //增加封禁金额限制
        $check_arr = $user_chat[0];
        $check_arr['block_type'] = $rule['type'] == 1 ? 3 : 2;
        $check_arr['chat_type'] = $check_arr['type'];
        $check_arr['tmp_keyword'] = '触发【后台系统】行为封禁';
        $check_arr['hit_keyword_id'] = '9999999999';
        $res = $this->checkBlockWaring($check_arr);

        if($res == false){
            return false;
        }

        //正式
//        foreach ($user_chat as $v) {
//            $chat_info[$v['uid']] = $v;
//        }

        if(empty($chat_info)){
            return false;
        }

        //判断封禁类型 1：封登录 2：封禁言
        switch ($rule['type']){
            //封登录
            case self::BLOCK_LOGIN:
                if($rule['ban_object'] == self::BAN_UID){
                    //封禁用户uid
                    $this->blockUids($info,$rule['ban_time']*60,self::ACTION_BLOCK.'触发规则id为：'.$rule['id'].','.$rule['name']);
                }elseif($rule['ban_object'] == self::BAN_IP){
                    //封禁用户ips
                    $this->blockIps($user_chat,$info,$rule['ban_time']*60,self::ACTION_BLOCK.'触发规则id为：'.$rule['id'].','.$rule['name']);
                }elseif($rule['ban_object'] == self::BAN_IMEI){
                    //封禁用户imei
                    $this->blockImeis($user_chat,$info,$rule['ban_time']*60,self::ACTION_BLOCK.'触发规则id为：'.$rule['id'].','.$rule['name']);
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

        //设置操作缓存，已处理的用户，不再进行操作
        foreach($chat_info as $key=>$value){
            if($redis->set('action_block_'.$rule['type'].'_'.$rule['ban_object'].'_'.$value['tkey'].'_'.$value['gkey'].'_'.$value['uid'],1)) {
                $redis->Expire('action_block_'.$rule['type'].'_'.$rule['ban_object'].'_'.$value['tkey'].'_'.$value['gkey'].'_'.$value['uid'], 1800);
            }
        }
    }


    //过滤白名单内容
    public function filterContent($chat_info){

        if(empty($chat_info)){
            return false;
        }
        foreach($chat_info as $k=>$v){

            $chat_info[$k] = $this->filterSpecial($v);


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
//            $chat_info[$k]['content'] = Common::replace_specialChar($chat_info[$k]['content']);
            $chat_info[$k]['content2'] = Common::replace_specialChar($chat_info[$k]['content2']);

            $chat_info[$k]['content2'] = urlencode($chat_info[$k]['content2']);

            //将中文转成数字
//            $chat_info[$k]['content'] = (string)Common::checkNatInt($chat_info[$k]['content']);
        }

        return $chat_info;

    }


    //特殊过滤cp坐标内容
    private function filterSpecial($chat_info)
    {
        $arr1 = ['nbcq','nbcqios','nbcq2zw','nbcq2youyu','dxcq','dxcq2'];
        if(in_array($chat_info['gkey'],$arr1)){
            $flag = '/\{[p|i]:.*\}/';


            $chat_info['content'] = preg_replace($flag,'',$chat_info['content']);
            $chat_info['content2'] = preg_replace($flag,'',$chat_info['content2']);
        }


        return $chat_info;
    }

    //文本相似度匹配
    public function similarityContent($a){
        try{

        }catch(\Exception $e){

        }
    }




}