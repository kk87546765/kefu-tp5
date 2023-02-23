<?php


namespace common\Server\Platform;

use common\base\BasicServer;
use common\Libraries\Logger;
use common\server\Platform\Mh\MhCommonMember;
use common\server\Platform\Ll\LlCommonMember;
use common\server\Platform\Zw\ZwCommonMember;
use common\server\Platform\Xll\XllCommonMember;
use common\server\Platform\Youyu\YouyuCommonMember;




class Platform extends BasicServer
{
    const PLATFORM_MH = 'mh';
    const PLATFORM_LL = 'll';
    const PLATFORM_ZW = 'zw';
    const PLATFORM_XLL = 'xll';
    const PLATFORM_YOUYU = 'youyu';
    public static $commonMemberSuffix = 'CommonMember';

    public static $platformLIst = [
        1 => 'mh',
        2 => 'll',
        3 => 'zw',
        4 => 'xll',
        5 => 'youyu',
    ];

    /**
     * @param array $uids
     * @param string $platform
     * @return array|string
     */
    public static function getCommonMemberInfoByUid($uids=[], $platform='')
    {
        $res = ['failUid' => []];
        if (empty($uids)) return $res;
        $uids = array_unique($uids);
        $uidArr = $uids;

        /*if (!empty($platform)  && class_exists(ucfirst($platform).self::$commonMemberSuffix, false)){
            //指定平台标识，去到对应的平台类找对应的用户数据
            $res = ucfirst($platform).self::$commonMemberSuffix::getCommonMemberInfoByuids($uids);
            return $res;
        }else{
            $uidArr = $uids;
            $platformList = Config::getPlatformPrefixList();
            //没有确定属于哪个平台的轮询去查找，优先权重是茂宏的
            foreach (self::$platformLIst as $v){
                if (empty($uidArr)) break;
                if (
                    in_array($v, $platformList)
                    && class_exists(ucfirst($v).self::$commonMemberSuffix, false)
                )
                {
                    $className = ucfirst($v).self::$commonMemberSuffix;
                    $result = $className::getCommonMemberInfoByuids($uidArr);
                    if (!empty($result[$v])) {
                        $res[$v] = $result[$v];
                    }
                    $uidArr = $res['failUid'];
                }

            }*/
        $result = [];


        if (!empty($platform)){
            //指定平台标识去到对应的平台数据库找用户信息
            switch (strtolower($platform))
            {
                case self::PLATFORM_MH:
                    $result = MhCommonMember::getCommonMemberInfoByuids($uidArr);
                    break;
                case self::PLATFORM_LL:
                    $result = LlCommonMember::getCommonMemberInfoByuids($uidArr);
                    break;
                case self::PLATFORM_ZW:
                    $result = ZwCommonMember::getCommonMemberInfoByuids($uidArr);
                    break;
                case self::PLATFORM_XLL:
                    $result = XllCommonMember::getCommonMemberInfoByuids($uidArr);
                    break;
                case self::PLATFORM_YOUYU:
                    $result = YouyuCommonMember::getCommonMemberInfoByuids($uidArr);
                    break;
            }
            $res = $result;

        }else{

            //没有指定平台标识的轮询去到各个平台找对应的用户数据
                if (!empty($uidArr)){
                    //先找茂红的  优先权
                    $result = MhCommonMember::getCommonMemberInfoByuids($uidArr);
                    $uidArr = !empty($result['failUid']) ? $result['failUid'] : [];
                    if (!empty($result['mh'])) $res['mh'] = $result['mh'];
                }
                if (!empty($uidArr)) {
                    //找ll的
                    $result = LlCommonMember::getCommonMemberInfoByuids($uidArr);
                    $uidArr = !empty($result['failUid']) ? $result['failUid'] : [];
                    if (!empty($result['ll'])) $res['ll'] = $result['ll'];
                }
                if (!empty($uidArr)) {
                    //找Zw的
                    $result = ZwCommonMember::getCommonMemberInfoByuids($uidArr);
                    $uidArr = !empty($result['failUid']) ? $result['failUid'] : [];
                    if (!empty($result['zw'])) $res['zw'] = $result['zw'];
                }

                if (!empty($uidArr)) {
                    //找Xll的
                    $result = XllCommonMember::getCommonMemberInfoByuids($uidArr);
                    $uidArr = !empty($result['failUid']) ? $result['failUid'] : [];
                    if (!empty($result['xll'])) $res['xll'] = $result['xll'];
                }

                if (!empty($uidArr)) {
                    //找Youyu的
                    $result = YouyuCommonMember::getCommonMemberInfoByuids($uidArr);
                    $uidArr = !empty($result['failUid']) ? $result['failUid'] : [];
                    if (!empty($result['youyu'])) $res['youyu'] = $result['youyu'];
                }

                $res['failUid'] = $uidArr;

//            return $res;
        }
        return $res;
        //}
    }

    public static function updatePassword($data,$platform_info){
        $return = [
            'msg'=>'',
            'code'=>0,
        ];
        Logger::init([
            'path' => RUNTIME_PATH.'admin/'.__FUNCTION__,
            'filename' => date('Y-m-d', time())
        ]);


        $url = $platform_info['change_password'];

        if(empty($url)){
            $return['msg'] = '修改密码接口地址为空';
            return $return;
        }

        $time = time();
        $post_data = [
            'uid' => $data['uid'],
            'password' => $data['password'],
            'time' => $time,
        ];
        $sign = md5('uid='.$post_data['uid'].'password='.$post_data['password'].'time='.$time.'key='.$platform_info['url_key']);
        $post_data['sign'] = $sign;

//        $res = curl_post($url,$post_data);
//
//        Logger::write([
//            'tag' => 'data',
//            'msg' => $platform_info['field'].'||'.json_encode($post_data).'||'.$res['response']
//        ]);
//
//        $res = json_decode($res['response'],true);
//
//        $return['code'] = $res['state']['code'];
//        $return['msg'] = $res['state']['msg'];
        $return = [];
        return $return;

    }


    public static function updateMobile($data,$platform_info){
        $return = [
            'msg'=>'',
            'code'=>0,
        ];
        Logger::init([
            'path' => RUNTIME_PATH.'admin/'.__FUNCTION__,
            'filename' => date('Y-m-d', time())
        ]);


        $url = $platform_info['change_mobile'];

        if(empty($url)){
            $return['msg'] = '换绑接口地址为空';
            return $return;
        }

        $time = time();
        $post_data = [
            'uid' => $data['uid'],
            'new_phone' => $data['new_phone'],
            'old_phone' => $data['old_phone'],
            'time' => $time,
        ];
        $sign = md5('uid='.$post_data['uid'].'old_phone='.$post_data['old_phone'].'new_phone='.$post_data['new_phone'].'time='.$time.'key='.$platform_info['url_key']);
        $post_data['sign'] = $sign;

//        $res = curl_post($url,$post_data);
//
//        Logger::write([
//            'tag' => 'data',
//            'msg' => $platform_info['field'].'||'.json_encode($post_data).'||'.$res['response']
//        ]);
//
//        $res = json_decode($res['response'],true);
//
//        $return['code'] = $res['state']['code'];
//        $return['msg'] = $res['state']['msg'];

        return $return;

    }
}