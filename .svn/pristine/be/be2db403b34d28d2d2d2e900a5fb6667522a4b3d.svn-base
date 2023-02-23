<?php

namespace common\server\Platform\Bx;

use common\base\BasicServer;
use common\sql_server\KefuCommonMember;

class BxCommonMember extends BasicServer
{

    public static $PlatformSuffix = 'bx';

    /**
     * @param $uids
     * @return array[]
     */
    public static function getCommonMemberInfoByuids($uids){
        $res = [
            self::$PlatformSuffix =>[],
            'failUid' => []
        ];
        $field = array('uid', 'user_name');
        $result = KefuCommonMember::getFieldInfoByUidAndSuffix($uids, self::$PlatformSuffix, $field);
        $succUid = [];
            foreach ($result as $v){
                if (in_array($v['uid'], $uids)){
                    $tmp['uid'] = $v['uid'];
                    $tmp['sdkUid'] = $v['uid'];  //多组装一个sdkUid回去处理逻辑
                    $tmp['user_name'] = $v['user_name'];
                    $res[self::$PlatformSuffix][] = $tmp;
                    $succUid[] = $v['uid'];
                }
            }


        //找出失败的uids
        foreach ($uids as $k=>$v){
            if (in_array($v, $succUid)){
                unset($uids[$k]);
            }
        }

        $res['failUid'] = $uids;

        return $res;
    }
}