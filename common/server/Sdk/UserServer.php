<?php


namespace common\server\Sdk;


use common\base\BasicServer;
use common\server\Platform\Platform;
use common\libraries\Common;
use common\server\Users\UserServer as polyUserLogics;

class UserServer extends BasicServer
{

//    public static $mixGame =['jzxjz','shenqi','bxcq','cs','shenqiios','rxhj','y8cl','csios','tjqy','y9cq','y9cqjh','y9cqios']; //没有接阿斯加德聚合的游戏标识

    /**
     * @param $data
     * @return array[]
     */
    public static function getUserInfoByMixGameUids($data)
    {

        $res = ['failUid' =>[]];
        if (empty($data)) return $res;

        $platformInfo = $polymerizaInfo = $result = $platformUids = $polymerizaUids =  [];

        $tmp_platform = '';

        //筛选出没有接聚合的游戏uid
        foreach ($data as $v) {

            //特殊判断九州仙剑传
            if($v['gkey'] == 'jzxjz'){
                if($v['tkey'] == 'asjd'){
                    $polymerizaUids[] = $v['uid'];
                }else{
                    $platformUids[] = $v['uid'];
                }
                continue;
            }

//            $gamkey = common::getConfig('gamekey');
            $gamkey = Common::getGameKey();

            if(!isset($gamkey[$v['gkey']]) || (isset($gamkey[$v['gkey']]) && $gamkey[$v['gkey']]['is_direct_game'] == 1)){

                $tmp_platform = $v['tkey'];
                $platformUids[] = $v['uid'];

            }elseif(isset($gamkey[$v['gkey']]) && $gamkey[$v['gkey']]['is_direct_game'] == 0){
                $polymerizaUids[] = $v['uid'];
            }



        }


        //经过聚合的游戏去聚合接口获取对应的平台标识
        if (!empty($polymerizaUids)){
            $polymerizaInfo = polyUserLogics::getUserInfoByUid($polymerizaUids);

            if(isset($polymerizaInfo['failUid'])){
                $res['failUid'] = array_merge($res['failUid'], $polymerizaInfo['failUid']);
                unset($polymerizaInfo['failUid']);
                unset($polymerizaInfo['fail']);
            }


            //获取真正平台的user_name
            foreach ($polymerizaInfo as $k=>$v){

                $tmp = Platform::getCommonMemberInfoByUid(array_column($v, 'sdkUid'), $k);

                //循环判断用户接口返回的和数据库返回的信息是否匹配
                foreach($v as $k1=>$v1){
                    foreach($tmp as $k2=>$v2){
                        foreach($v2 as $k3=>$v3){
                            if($v3['sdkUid'] == $v1['sdkUid']){
                                $polymerizaInfo[$k][$k1]['user_name'] = $v3['user_name'];
                            }
                        }

                    }
                }

//                $polymerizaInfo[$k] = $tmp[$k];

            }

        }

        //特殊没有进过聚合的游戏轮询去到对应的平台数据库找到对应的平台标识
        if (!empty($platformUids)){
            $platformInfo = Platform::getCommonMemberInfoByUid($platformUids, $tmp_platform);


            //存在用户表找不到的就从角色表查询
            if(isset($platformInfo['failUid'])){

                $get_uid_by_roleid = Platform::getCommonMemberInfoByUidAndRoleID($data,$platformInfo['failUid'],$tmp_platform);

                if(!empty($get_uid_by_roleid['failUid'])){

                    $res['failUid'] = array_merge($res['failUid'], $get_uid_by_roleid['failUid']);
                    unset($get_uid_by_roleid['failUid']);
                }

                unset($platformInfo['failUid']);

                $platformInfo = array_merge_recursive($platformInfo, $get_uid_by_roleid);


            }

        }


        //组装数据
        foreach ($polymerizaInfo as $key=>$value){
            foreach ($value as $v){
                $tmp['uid'] = $v['uid'];
                $tmp['sdkUid'] = $v['sdkUid'];
                $tmp['user_name'] = $v['user_name'];
                $res[$key][] = $tmp;
            }
        }

        //组装数据
        foreach ($platformInfo as $key=>$value){
            foreach ($value as $v){
                $tmp['uid'] = $v['uid'];
                $tmp['sdkUid'] = $v['sdkUid'];
                $tmp['user_name'] = $v['user_name'];
                $res[$key][] = $tmp;
            }
        }

        return $res;

    }
}