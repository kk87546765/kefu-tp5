<?php


namespace common\server\Platform;


use common\base\BasicServer;
use common\libraries\Curl;
use common\libraries\Logger;
use common\libraries\Common;
use common\sql_server\BanImeiLog;
use common\sql_server\BanIpLog;
use common\sql_server\BanUserLog;
use common\sql_server\KefuCommonMember;


class BanServer extends BasicServer
{
    const CONFIG_PIATFORM = 'platform_list';

    const BAN_UID = 1;
    const BAN_IP   = 2;
    const BAN_IMEI = 3;
    const BAN_USER_NAME = 4;

    const BAN = 1; //封禁
    const U_BAN = 2; //解封

    const USER_BAN_STATIC = 0; //用户为封禁状态
    const USER_UBAN_STATIC = 1; //用户为正常状态

    public static $actionType = [
        1 => 'uid',
        2 => 'ip',
        3 => 'imei',
        4 => 'user_name'
    ];

    public static $ban_cache_key_prefix = [
        1 => 'block_uid_',
        2 => 'block_ip_',
        3 => 'block_imei_',
        4 => 'block_uname_'
    ];

    /**
     * @param array $data
     * @param int $actionType
     * @param int $type
     * @param int $banTime
     * @param string $reason
     * @return array[]
     */
    public function ban($data=[], $actionType=self::BAN_UID, $type=self::BAN, $banTime=0, $reason = '')
    {

//        return ['fail' => [], 'succ' => []];
        $actionData = [
            'uid' => '',
            'ip' => '',
            'imei' => '',
            'idfa' => '',
            'user_name' => '',
            'type' => $type,
            'action_type' => $actionType,
            'ban_time' => $banTime,
        ];
        $failUid = $succ = [];
        $time = time();
        $platform_list = Common::getPlatform();
        foreach ($data as $key=>$value) {
            if(!isset($platform_list[$key])){
                continue;
            }
            $uids = array_column($value, 'sdkUid');
            $actionData[self::$actionType[$actionType]] = implode(',', $uids);


            $resJson = $this->action_ban($actionData, $key);
            $res = json_decode($resJson, true);

            $logData = [];
            if ($res) {
                foreach ($uids as $k=>$v){
                    if (in_array($v, $res['data'])){
                        $failUid[] = $v;
                        unset($uids[$k]);
                    }else{
                        $succ[] = $v;
                        $logData['user'][] = $v;
                    }
                }

                if (!empty($logData)){
                    $logData['type'] = $type;
                    $logData['ban_time'] = $banTime;
                    $logData['reason'] = $reason;
                    $logData['action_type'] = $actionType;
                    $logData['dateline'] = time();
                    $logData['platform'] = $key;
                    $this->ban_log($logData, $key);
                }

            }else{
                $failUid = $uids;
            }
        }
        return ['fail' => $failUid, 'succ' => $succ];
    }

    /**
     * @param array $banData
     * @param string $platformSuffix
     * @return array|bool|string
     */
    public function action_ban($banData = [], $platformSuffix = '')
    {
        Logger::init([
            'path' => RUNTIME_PATH.'admin/'.__FUNCTION__,
            'filename' => date('Y-m-d', time()) ]);
        $banData['time'] = time();
        $res = ['code' => 0, 'msg' => ''];
        if (empty($banData) || ($banData['uid'] && $banData['ip'] && $banData['imei'] && $banData['user_name'])) {
            return $res;
        }

        //获取平台配置信息
        $platformConfig =Common::getPlatformInfoBySuffixAndCache($platformSuffix);

        $url_key = $platformConfig['config']['url_key'] ?? '';

        $signString = "type=".$banData['type']."action_type=".$banData['action_type']."time=".$banData['time']."key=".$url_key;
        $sign = md5($signString);

        $banData['sign'] = $sign;

        $url = $platformConfig['config']['ban_url'] ?? '';
        $curl   = new Curl();
        $res = $curl->post($url, $banData);

        Logger::write([
            'tag' => 'postRes',
            'url' => $url.'||actionDataJson:'.json_encode($banData),
            'msg' => $res,
        ]);
        return $res;
    }

    /**
     * @param array $logData
     * @param string $platformSuffix
     */
    public function ban_log($logData = [], $platformSuffix = ''){
        $admin_user = self::$user_data['username'];

        $insertData = [];
        if ($logData['action_type'] == self::BAN_UID || $logData['action_type'] == self::BAN_USER_NAME){
            //账号
            foreach ($logData['user'] as $v){

                $tmpArr = [];
                $tmpArr['admin_user'] = $admin_user;
                if ($logData['action_type'] == self::BAN_UID){
                    $tmpArr['uid'] = $v;
                }else{
                    $tmpArr['user_name'] = $v;
                }

                $tmpArr['ban_time'] = $logData['ban_time'];
                $tmpArr['reason'] = $logData['reason'];
                $tmpArr['dateline'] = $logData['dateline'];
                $tmpArr['type'] = $logData['type'];
                $insertData[] = $tmpArr;
            }
            if (!empty($insertData)){
                BanUserLog::insertLog($insertData,$logData['type'], $platformSuffix);

                $fieldValue = $logData['type'] == self::BAN ? self::USER_BAN_STATIC : self::USER_UBAN_STATIC;

                if ($logData['action_type'] == self::BAN_USER_NAME){
                    $inArr = array_column($insertData, 'user_name');
                }else{
                    $inArr = array_column($insertData, 'uid');
                }

                KefuCommonMember::updateCommonMemberStatus($fieldValue, $inArr, $logData['action_type'], $platformSuffix);
            }
        }elseif ($logData['action_type'] == self::BAN_IP){
            //ip
            foreach ($logData['user'] as $v){

                $tmpArr = [];
                $tmpArr['admin_user'] = $admin_user;
                $tmpArr['ip'] = $v;
                $tmpArr['ban_time'] = $logData['ban_time'];
                $tmpArr['reason'] = $logData['reason'];
                $tmpArr['dateline'] = $logData['dateline'];
                $tmpArr['type'] = $logData['type'];
                $insertData[] = $tmpArr;
            }
            if (!empty($insertData)) {
                BanIpLog::insertLog($insertData, $logData['type'], $platformSuffix);
                $fieldValue = $logData['type'] == self::BAN ? self::USER_BAN_STATIC : self::USER_UBAN_STATIC;
                $inArr = array_column($insertData, 'ip');
                KefuCommonMember::updateCommonMemberStatus($fieldValue, $inArr, $logData['action_type'], $platformSuffix);
            }
        }elseif ($logData['action_type'] == self::BAN_IMEI){
            //imei
            foreach ($logData['user'] as $v){

                $tmpArr = [];
                $tmpArr['admin_user'] = $admin_user;
                $tmpArr['imei'] = $v;
                $tmpArr['ban_time'] = $logData['ban_time'];
                $tmpArr['reason'] = $logData['reason'];
                $tmpArr['dateline'] = $logData['dateline'];
                $tmpArr['type'] = $logData['type'];
                $insertData[] = $tmpArr;
            }
            if (!empty($insertData)) {
                BanImeiLog::insertLog($insertData, $logData['type'], $platformSuffix);
                $fieldValue = $logData['type'] == self::BAN ? self::USER_BAN_STATIC : self::USER_UBAN_STATIC;
                $inArr = array_column($insertData, 'imei');
                KefuCommonMember::updateCommonMemberStatus($fieldValue, $inArr, $logData['action_type'], $platformSuffix);
            }
        }
    }

    /**
     * @param $actionType
     * @param $info
     * @param int $banTime
     * @param string $platformSuffix
     * @return bool
     */
    private function setBanCache($actionType, $info, $banTime = 0, $platformSuffix = '')
    {
        $key = $this->creatCacheKey($actionType, $info, $platformSuffix);
        $redisModel = get_redis();
        if($redisModel->get($key)){
            return false;
        }

        if($redisModel->set($key,1)){
            if($banTime){
                $redisModel->Expire($key,$banTime);
            }
            return true;
        }else{
            return false;
        }
    }

    /**
     * @param $actionType
     * @param $info
     * @return bool
     */
    private function deleteCache($actionType, $info, $platformSuffix = '')
    {
        $key = $this->creatCacheKey($actionType, $info, $platformSuffix);
        $redisModel = get_redis();
        if($redisModel->delete($key,1)){
            return true;
        }else{
            return false;
        }
    }

    /**
     * @param $actionType
     * @param $info
     * @return string
     */
    private function creatCacheKey($actionType, $info, $platformSuffix = '')
    {
        $platformSuffix = !empty($platformSuffix) ? $platformSuffix : $_SESSION['platform_key'];
        return $platformSuffix.'_'.self::$ban_cache_key_prefix[$actionType].$info;
    }
}