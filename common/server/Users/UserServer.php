<?php


namespace common\server\Users;


use Common\libraries\Logger;

use Common\libraries\Curl;

class UserServer
{
    const POLYMERIZA_CONFIG_KEY = 'polymeriza_list';

    /**
     * @param $uids
     * @return array[]
     */
    public static function getUserInfoByUid($uids)
    {
        Logger::init([
            'path' => RUNTIME_PATH.'admin/'.__FUNCTION__,
            'filename' => date('Y-m-d', time()) ]);
     $res = [
         'fail'=>[]
     ];
     if (empty($uids)) return $res;

     $configInfo = Config::items(self::POLYMERIZA_CONFIG_KEY);
     $time = time();
     $key = $configInfo['configKey']['getPlatformKey'];
     $url = $configInfo['configKey']['getPlatformUrl'];
     $platformSuffix = $configInfo['platformSuffix'];

     if (is_array($uids)) $uids = implode(',', $uids);
     $sign = self::createSign($uids, $time, $key);
     $data = [
         'uid' => $uids,
         'time' => $time,
         'sign' => $sign,
     ];
     $url .= '?'.http_build_query($data);
     $curl = new Curl();
     $resultJson = $curl->getUrl($url, true);
        Logger::write([
            'tag' => 'postRes',
            'data' => $data,
            'url' => $url,
            'msg' => $resultJson,
        ]);
     $result = json_decode($resultJson, true);
     if ($result['code'] == 1){
         foreach ($result['data']['success'] as $v){
             $tmp['uid'] = $v['uid'];  //聚合用户uid
             $tmp['sdkUid'] = $v['sdkUID']; //平台用户对应的uid
             $res[$platformSuffix[$v['cooperationID']]][] = $tmp;
         }
         if (!empty($result['data']['fail'])) {
             $res['fail'] = $result['data']['fail'];
         }
     }
     return $res;

    }

    /**
     * @param $uids
     * @param $time
     * @param $key
     * @return string
     */
    public static function createSign($uids, $time, $key)
    {
        $result = '';
        if (empty($uids) || empty($time) || empty($key)) return $result;

        $signStr = $uids.$time.$key;

        $sign = md5($signStr);
        return $sign;
    }


}