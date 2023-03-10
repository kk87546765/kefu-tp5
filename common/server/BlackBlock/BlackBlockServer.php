<?php


namespace common\server\BlackBlock;

use common\base\BasicServer;
use common\libraries\ApiUserInfoSecurity;
use common\libraries\Common;
use common\libraries\Logger;
use common\server\CustomerPlatform\CommonServer;
use common\sql_server\ApiLogSqlServer;
use common\sql_server\BlackBlockSqlServer;
//use common\server\Block\BlockServer;
use common\server\Game\BlockServer;



class BlackBlockServer extends BasicServer
{
    const BLOCK_PHONE = 1;
    const BLOCK_ID_CARD = 2;
    const BLOCK_UDID = 3;

    public static $type = [
        1=>['id'=>self::BLOCK_PHONE,'name'=>'手机号'],
        2=>['id'=>self::BLOCK_ID_CARD,'name'=>'身份证'],
        3=>['id'=>self::BLOCK_UDID,'name'=>'设备号'],
    ];

    public static $all_platform = [4,6,7];


    public static $platform = [
        'zw'=>
            [
            'secret'=>'QMnBZsZKe6GZ5G9w5UBPrRHGftyYYDrC',
            'url'=>'https://mp.hnzwwl.cn/api/save_user_ban.php',
            'search' =>"https://mp.hnzwwl.cn/api/search_info_by_user_ban.php"
            ],
        'youyu'=>
            [
                'secret'=>'22nK4bwHki6yUZ0k59OMCOQfHNOIouP9',
                'url'=>'https://advapi.e7j.cn/user_ban/save',
                'search' =>"https://advapi.e7j.cn/user_ban/search"
            ],
        'bx'=>
            [
                'secret'=>'22nK4bwHki6yUZ0k59OMCOQfHNOIouP9',
                'url'=>'https://advapi.bingxuei.com/user_ban/save',
                'search' =>"https://advapi.bingxuei.com/user_ban/search"
            ],

    ];
    /***
     *获取数据列表
     */
    public static function getList($data){

        $platform_list =  Common::getPlatformList();


        $where = self::dealData($data);

        $blocks = BlackBlockSqlServer::getList($where,$data['page'],$data['limit']);


        foreach ($blocks as &$block) {

//            $block['reason_type'] = $block['reason_type'];
            $block['reason']   = $block['reason'] ;
            $block['platform'] = $platform_list[$block['platform_id']]['platform_name'];
            $block['phone'] = !empty($block['phone']) ? ApiUserInfoSecurity::decrypt($block['phone']) : '';
            if($block['type'] == 2){
                $block['check_val'] = substr_replace($block['check_val'],'****',0,3);
                $block['check_val'] = substr_replace($block['check_val'],'****',-1,1);
                $block['type'] = self::$type[$block['type']]['name'].":".$block['check_val'];
            }else{
                $block['type'] = self::$type[$block['type']]['name'].":".$block['check_val'];
            }


            if(!empty($block['phone'])){
                $block['limit_type'] = '限制手机号码:'.$block['phone'];
            }elseif(!empty($block['idcard'])){
                $block['idcard'] = substr_replace($block['idcard'],'****',0,3);
                $block['idcard'] = substr_replace($block['idcard'],'****',-1,1);
                $block['limit_type'] = '限制身份证:'.$block['idcard'];
            }elseif(!empty($block['udid'])){
                $block['limit_type'] = '限制设备号:'.$block['udid'];
            }

//            $block['add_time']             = date('Y-m-d H:i:s',$block['add_time']);
//            $block['expect_unblock_time']  = date('Y-m-d H:i:s',$block['expect_unblock_time']);
//            $block['unblock_time']         = $block['unblock_time'] == 0?'':date('Y-m-d H:i:s',$block['unblock_time']);

        }

        return $blocks;
    }

    /***
     *获取总数
     */
    public static function getCount($data){

        $tmp_data = self::dealData($data);

        $count = BlackBlockSqlServer::getCount($tmp_data);

        return $count;
    }

    private static function dealData($data){
        $condition = [];
        $tmp_data = [];

        if (!empty($data['type']) && !empty($data['account'])) {
            if($data['type'] == self::BLOCK_PHONE){
                $condition['phone'] = "{$data['account']}";
            }
            if($data['type'] == self::BLOCK_ID_CARD){
                $condition['idcard'] = "{$data['account']}";
            }
            if($data['type'] == self::BLOCK_UDID){
                $condition['udid'] =  "{$data['account']}";
            }

        }
        if (!empty($data['block_admin_id'])) {
            $condition .= " AND block_admin_id = '{$data['block_admin_id']}'";
        }
//        if (!empty($data['block_admin_id'])) {
//            $condition .= " AND block_admin_id = '{$data['block_admin_id']}'";
//        }

        return $condition;
    }

    public static function add($data)
    {

        $arr_phone = $arr_id_card = $arr_udid = [];
        $res1 = $res2 = $res3 = false;
        $time = time();

        $add_data = [
            'block_admin_id' => $data['user_info']['id'],
            'check_val'      => $data['check_val']?? '',
            'platform_id'    => $data['platform_id'] ?? 0,
            'type'           => $data['type'] ?? 0,
            'block_time'     => $time,
            'reason'         => $data['reason'] ?? '',
            'reason_type'    => $data['reason_type'] ?? '',
            'block_ip'       => $data['ip'],
            'status'         => 1,
            'admin_id'       => $data['admin_id'] ?? '',
        ];

        $send_data = [
            'phone' => '',
            'id_card' => '',
            'udid' => '',
            'ban_reason'=>$data['reason'],
            'ban_uid'=>$data['user_info']['id'],
            'ban_ip'=>$data['ip'],
            'platform_id'=>$data['platform_id']
        ];

        $tmp_add_data0[0] = $add_data;
        if($data['type'] == self::BLOCK_PHONE){
            $send_data['phone'] = $tmp_add_data0[0]['phone'] = $data['check_val'];
        }elseif($data['type'] == self::BLOCK_ID_CARD){
            $send_data['id_card'] = $tmp_add_data0[0]['idcard'] = $data['check_val'];
        }elseif($data['type'] == self::BLOCK_UDID){
            $send_data['udid'] = $tmp_add_data0[0]['udid'] = $data['check_val'];
        }

        $res0 = BlackBlockSqlServer::add($tmp_add_data0);

        if(!empty($data['phone_val'])){
            $data['phone_val'] = json_decode($data['phone_val'],1);
            $arr_phone = array_column($data['phone_val'],'mobile');
            foreach($arr_phone as $k=>$v){
                $tmp_add_data1[$k] = $add_data;
                $tmp_add_data1[$k]['phone'] = $v;

            }

            $res1 = BlackBlockSqlServer::add($tmp_add_data1);

        }

        if(!empty($data['id_card_val'])){
            $data['id_card_val'] = json_decode($data['id_card_val'],1);
            $arr_id_card = array_column($data['id_card_val'],'id_card');
            foreach($arr_id_card as $k1=>$v1){
                $tmp_add_data2[$k1] = $add_data;
                $tmp_add_data2[$k1]['idcard'] = $v1;

            }
            $res2 = BlackBlockSqlServer::add($tmp_add_data2);

        }
        if(!empty($data['udid_val'])){
            $data['udid_val'] = json_decode($data['udid_val'],1);
            $arr_udid = array_column($data['udid_val'],'udid');
            foreach($arr_udid as $k2=>$v2){
                $tmp_add_data3[$k2] = $add_data;
                $tmp_add_data3[$k2]['udid'] = $v2;

            }
            $res3 = BlackBlockSqlServer::add($tmp_add_data3);
        }

        if($res1){
            $send_data['phone'] .= ','.implode(',',$arr_phone);
        }
        if($res2){
            $send_data['id_card'] .= ','.implode(',',$arr_id_card);
        }
        if($res3){
            $send_data['udid'] .= ','.implode(',',$arr_udid);
        }

        $send_data['phone'] = trim($send_data['phone'],',');
        $send_data['id_card'] = trim($send_data['id_card'],',');
        $send_data['udid'] = trim($send_data['udid'],',');

        foreach(self::$all_platform as $k4=>$v4){

            $send_data['platform_id'] = $v4;
            self::sendPlatformMsg($send_data,1);
        }



        return true;




    }


    public static function unblock($data)
    {
        if(empty($data['ids'])){
            return false;
        }
        if(is_array($data['ids'])){
            $ids = implode(',',$data['ids']);
        }elseif (is_string($data['ids'])){
            $ids = $data['ids'];
        }

        $time = time();

        $where['a.id'] = ['in',$ids];
        $where['a.status'] =1;

        $infos = BlackBlockSqlServer::getList($where);

        $platform_arr = [];
        foreach($infos as $k=>$v){
            $platform_arr[$v['platform_id']][] = $v;
        }
        $res2 = false;
        foreach(self::$all_platform as $k1=>$v1){

            $send_data = [];
            $tmp_data = [];     
            $tmp_phone = array_column($infos,'phone');
            $tmp_id_card = array_column($infos,'idcard');
            $tmp_udid = array_column($infos,'udid');

            $send_data['phone'] = implode(',',array_unique($tmp_phone));
            $send_data['id_card'] = implode(',',array_unique($tmp_id_card));
            $send_data['udid'] = implode(',',array_unique($tmp_udid));

            $send_data['platform_id'] = $v1;

            $send_data['unban_uid'] = $data['user_info']['id'];
            $send_data['unban_ip'] = $data['ip'];
            $send_data['unban_reason'] = $data['unban_reason'] ?? '手动解封';

            $edit_data = [
                'id'=>['in',$ids],
                'unblock_reason'=>$send_data['unban_reason'],
                'unblock_admin_id'=>$data['user_info']['id'],
                'status'=>0,
                'unblock_time' => $time
            ];
            $res = self::sendPlatformMsg($send_data,2);

            if($res){
                $res2 = BlackBlockSqlServer::edit($edit_data);
            }
        }


        return $res2;

    }


    private static function sendPlatformMsg($data = [],$block_type=1)
    {


        if(empty($data)){
            return false;
        }

        $platform = Common::getPlatformInfoByPlatformIdAndCache($data['platform_id']);
        $url = self::$platform[$platform['platform_suffix']]['url'];
        $secret = self::$platform[$platform['platform_suffix']]['secret'];
        unset($data['platform_id']);
        $data['type'] = $block_type;
        $params = $data;
        ksort($params);
        $str = urldecode(http_build_query($params)) . $secret;
        $sign =md5($str);
        $params['sign'] = $sign;
        $res = curl_post($url,$params);
        $res = json_decode($res['response'],1);

//        $res['state']['code'] = 1;
        ApiLogSqlServer::add(__METHOD__,$url,['Params'=>$params,'Res'=>json_encode($res)],"黑名单功能({$block_type})");

        if($res['state']['code'] == 1){
            return true;
        }else{
            return false;
        }

    }

    public static function getBlackList($data)
    {
        $return  = ['code'=>-1 ,'msg'=>'操作失败','data'=>[]];
        if(empty($data['type']) || empty($data['value'])){
             $return['msg'] = '值不能为空';
             return $return;
        }
//        $platform = Common::getPlatformInfoByPlatformIdAndCache($data['platform_id']);
        $platform_list = Common::getPlatformList();

        $allow_platform = [4,6,7];

        $all_array = [];
        foreach($platform_list as $k=>$v){

            if(!in_array($v['platform_id'],$allow_platform)){
                continue;
            }

            $where = [];
            if($data['type'] == self::BLOCK_PHONE){
                $where['mobile'] = ApiUserInfoSecurity::encrypt($data['value']);
            }elseif($data['type'] == self::BLOCK_ID_CARD){
                $where['id_card'] = $data['value'];
            }elseif($data['type'] == self::BLOCK_UDID){
                $where['udid'] = $data['value'];
            }

            if($data['need_type'] == self::BLOCK_PHONE){
                $where['mobile'] = ['neq',''];
            }elseif($data['need_type'] == self::BLOCK_ID_CARD){
                $where['id_card'] = ['neq',''];
            }elseif($data['need_type'] == self::BLOCK_UDID){
                $where['udid'] = ['neq',''];
            }


            $res = BlackBlockSqlServer::getBlackList($v['platform_suffix'],$where);

            if(!empty($res)){

                foreach($res as $k1=>$v1){
                    if($data['need_type'] == self::BLOCK_PHONE){
                        if(!empty($v1['mobile'])){
                            $res[$k1]['mobile'] = ApiUserInfoSecurity::decrypt($v1['mobile']);
                        }
                    }
                }

                $all_array = array_merge($all_array,$res);

            }


        }


        return $all_array;
    }

    public static function getBlackListCount($data)
    {
        $return  = ['code'=>-1 ,'msg'=>'操作失败','data'=>[]];
        if(empty($data['type']) || empty($data['value']) || empty($data['platform_id'])){
            $return['msg'] = '值不能为空';
            return $return;
        }
        $platform = Common::getPlatformInfoByPlatformIdAndCache($data['platform_id']);

        if($data['type'] == self::BLOCK_PHONE){
            $where['mobile'] = ApiUserInfoSecurity::encrypt($data['value']);

        }elseif($data['type'] == self::BLOCK_ID_CARD){
            $where['id_card'] = $data['value'];
        }elseif($data['type'] == self::BLOCK_UDID){
            $where['udid'] = $data['value'];
        }

        $res = BlackBlockSqlServer::getBlackListCount($platform['platform_suffix'],$where);

        return $res;
    }

    public static function search($data)
    {

    }



}