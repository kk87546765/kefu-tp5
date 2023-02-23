<?php
/**
 * 系统
 */
namespace common\server\Statistic;


use common\base\BasicServer;
use common\model\db_statistic\UserPrivacyInfo;
use common\server\CustomerPlatform\CommonServer;
use common\server\SysServer;


class UserPrivacyServer extends BasicServer
{
    /**
     * 录入用户信息
     * @param array $data
     * @return bool
     */
    public static function userInput($data = array())
    {
        $result = false;
        $platform = $data['platform'];
        //找出平台标识对应的平台id
        $platform_list = SysServer::getPlatformList();
        $platformInfo = [];
        foreach ($platform_list as $item){
            if($item['suffix'] == $platform){
                $platformInfo = $item;
                break;
            }
        }

        if (empty($platformInfo)) {
            return $result;
        }
        $platform_id = $platformInfo['platform_id'];

        $uid = $data['uid'];

        //判断用户id是否存在
        $model = CommonServer::getPlatformModel('KefuCommonMember',$platform);

        $userInfo = $model->where('uid',$uid)->count();

        if (!$userInfo) {
            return $result;
        }

        //组装要插入的数据
        $insertData = [
            'platform_id' => $platform_id,
            'uid' => $uid,
            'game_id' => $data['game_id'],
            'product_id' => $data['product_id'],
            'server_id' => $data['server_id'],
            'server_name' => !empty($data['server_name']) ? addslashes($data['server_name']) : '',
            'role_id' => !empty($data['role_id']) ? $data['role_id'] : 0,
            'role_name' => !empty($data['role_name']) ? addslashes($data['role_name']) : '',
            'qq' => !empty($data['qq']) ? $data['qq'] : '',
            'phone_num' => !empty($data['phone_num']) ? $data['phone_num'] : '',
            'birthday' => !empty($data['birthday']) ? $data['birthday'] : 0,
            'wechat' => !empty($data['wechat']) ? $data['wechat'] : '',
            'add_admin_id' => -1,
        ];

        $model = new UserPrivacyInfo();
        $info = $model->where(['platform_id'=>$platform_id,'uid'=>$uid])->find();

        if ($info) {
            $res = $info->save($insertData);
        }else {
            $res = $model->create($insertData);
        }
        if ($res)  $result = true;

        return $result;
    }
}
