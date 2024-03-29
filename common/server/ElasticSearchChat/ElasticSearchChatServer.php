<?php


namespace common\server\ElasticSearchChat;

use common\base\BasicServer;
use common\libraries\Common;
use common\libraries\Ipip\IP4datx;


use common\libraries\ElasticSearch;
use common\sql_server\BlockSqlServer;



class ElasticSearchChatServer extends BasicServer
{


    const TYPE_IP = 'IP';
    const TYPE_USER = 'USER';
    const TYPE_CHAT = 'CHAT';
    const TYPE_IMEI = "IMEI";
    const TYPE_AUTO = "AUTO";
    const TYPE_ACTION = "ACTION";
    const TYPE_AUTOCHAT = "AUTOCHAT";
    const TYPE_ACTIONCHAT = "ACTIONCHAT";


    static $blocktypes = [
        self::TYPE_CHAT => '禁言',
        self::TYPE_IP => '禁IP',
        self::TYPE_USER => '封用户',
        self::TYPE_IMEI => '封IMEI',
        self::TYPE_AUTO => '自动封禁',
        self::TYPE_AUTOCHAT => '自动禁言',
        self::TYPE_ACTIONCHAT => '行为封禁',

    ];

    static $types = [
        1  => '私聊',
        2  => '喇叭',
        3  => '邮件',
        4  => '世界',
        5  => '国家',
        6  => '工会/帮会',
        7  => '队伍',
        8  => '附近',
        9  => '其他',
        10 => '跨服',
    ];

    static $redis_set_name = 'account_registration';

    //混服游戏
    static $mix_game = [
        'shenqi'=>['youyu','zw'],
        'shenqiios'=>['youyu','zw'],
    ];

    static $gamelist = [];
    /***
     *获取数据列表
     */
    public static function getList($data){

        self::$gamelist = Common::getProductList(2);

        $return = self::dealData($data);

        if($return['code'] != 0){

            return $return;
        }

        $es = new ElasticSearch();
        $last_month = Common::GetMonth(1);
        $now_month = date('Ym');
        $next_month = Common::GetMonth(0);
        $result = $es->search(
            [
                $es->index_name.'-'.$last_month,
                $es->index_name.'-'.$now_month,
                $es->index_name.'-'.$next_month
            ],
            $data['data'],
            '',
            ['time'=>['order'=>'desc']],
            $data['page'],
            $data['limit']
        );





        $platform_list = Common::getPlatform();
        $redis = get_redis();
        $messages = [];
        foreach ($result['data'] as &$v) {


            $v['date'] = date('Y-m-d H:i:s', $v['time']);
            $v['typename'] = self::$types[$v['type']];
            $v['gkey'] = self::$gamelist[$v['gkey']]['name'];
            $v['platform_key'] = $platform_list[$v['tkey']]['name'];
            $v['attribution'] = implode('', IP4datx::find($v['ip']));
            $v['ip'] = $v['ip']."[{$v['attribution']}]";
            $v['is_account_registration'] =$redis->Sismember(self::$redis_set_name,trim($v['sid'],'S').'_'.$v['roleid'].'_'.$v['uname']);

            $status = BlockSqlServer::getBlockByUid([$v['uid']],[$v['roleid']]);

            $v['status'] = $status[$v['uid']];

        }

        $return['code'] = 0;
        $return['msg'] = '获取成功';
        $return['data'] = $result['data'];
        $return['total'] = 1;
        return $return;
    }

    /***
     *获取总数
     */
    public static function getCount($data){

        $tmp_data = self::dealData($data);

        $count = BlockSqlServer::getCount($tmp_data);

        return $count;
    }

    private static function dealData($data){

        $return = ['code'=>-1,'msg'=>'获取失败','data'=>''];
        $data['edate'] = empty($data['dateEnd'])?date("Y-m-d 23:59:59"):$data['dateEnd'];

        $platform_info = Common::getPlatformInfoByPlatformIdAndCache($data['platform_id']);
        $data['tkey'] = $platform_info['platform_suffix'];

        $bool = [];
        $range = [];
        $range1 = [];
        $range2 = [];
        $need_search_arr = [];
        $need_search_arr[] = 'asjd';

        if(empty($data['tkey'])){
            $return['msg'] = '平台必选';
            return $return;
        }


        if($data['game'] && array_key_exists($data['game'],self::$mix_game) ){

            foreach(self::$mix_game[$data['game']] as $k=>$v){
                $need_search_arr[] = $v;
            }
        }else{
            $need_search_arr[] = $data['tkey'];

        }

        if($data['tkey']) {
            $need_search_arr = [];
            $need_search_arr[] = $data['tkey'];

            if($data['tkey'] == 'youyu'){
                $need_search_arr = [];
                $need_search_arr[] = 'youyu';
                $need_search_arr[] = 'll';
                $need_search_arr[] = 'mh';
                $need_search_arr[] = 'asjd';
            }

        }


        $bool['bool']['must'][]['terms']['tkey'] = $need_search_arr;

        if($data['dateStart']){
            $range0['range']['time']['gte']  = strtotime($data['dateStart']);
//                $bool['filter']['bool']['must'][]['range']['time']['gte'] = strtotime($dateStart);
        }

        if($data['dateEnd']){
            $range0['range']['time']['lte']  = strtotime($data['dateEnd']);
//                $bool['filter']['bool']['must'][]['range']['time']['lte'] = strtotime($edate);
        }

        if($range0){
            $bool['bool']['filter']['bool']['must'][] = $range0;
        }

        if ($data['keyword']) {
            $bool['bool']['filter']['bool']['must'][]['match_phrase']['content'] = $data['keyword'];
        }

        if ( $data['game'] && $data['game']  != 'common') {

            $bool['bool']['filter']['bool']['must'][]['term']['gkey'] = $data['game'];
        }

        if ($data['uid']) {
            $bool['bool']['filter']['bool']['must'][]['term']['uid'] = $data['uid'];
        }

        if ($data['imei']) {
            $bool['bool']['filter']['bool']['must'][]['term']['imei'] = $data['imei'];
        }

        if ($data['ip']) {
            $bool['bool']['filter']['bool']['must'][]['term']['ip'] = $data['ip'];
        }

        if ($data['uname']) {
            $bool['bool']['filter']['bool']['must'][]['match_phrase']['uname'] = $data['uname'];
        }


        if ($data['type']) {
            $bool['bool']['filter']['bool']['must'][]['term']['type'] = $data['type'];
        }

        if($data['level_min']){
            $range1['range']['role_level']['gte'] = (int)$data['level_min'];
//                $bool['filter']['bool']['must'][]['range']['role_level']['gte'] = (int)$level_min;

        }

        if($data['level_max']){
            $range1['range']['role_level']['lte'] = (int)$data['level_max'];
//                $bool['filter']['bool']['must'][]['range']['role_level']['lte'] = (int)$level_max;
        }

        if($range1){
            $bool['bool']['filter']['bool']['must'][] = $range1;
        }


        if($data['money_min']){
            $range2['range']['count_money']['gte'] = $data['money_min'];
//                $bool['filter']['bool']['must'][]['range']['count_money']['gte'] = $money_min;
        }

        if($data['money_max']){
            $range2['range']['count_money']['lte'] = $data['money_max'];
//                $bool['filter']['bool']['must'][]['range']['count_money']['lte'] = $money_max;
        }

        if($range2){
            $bool['bool']['filter']['bool']['must'][] = $range2;
        }


        if ($data['sid']) {
            $sids = trim($data['sid']);
            $sids = str_replace(' ',',',$data['sid']);

            $sids = explode(',',$sids);
            $bool['bool']['filter']['bool']['must'][]['terms']['sid'] = $sids;

        }

        $platform_list = Common::getPlatform();

        if($data['game']){
            if(strpos($platform_list[$data['tkey']]['see_game_limit'],$data['game']) === false){
                $return['msg'] = '没有查看权限';
                return $return;
            };
        }else{

            $arr = explode(',',$platform_list[$data['tkey']]['see_game_limit']);

            $bool['bool']['filter']['bool']['must'][]['terms']['gkey'] = $arr;

        }
        $return['code'] = 0;
        $return['data'] = $bool;

        return $return;



    }



}