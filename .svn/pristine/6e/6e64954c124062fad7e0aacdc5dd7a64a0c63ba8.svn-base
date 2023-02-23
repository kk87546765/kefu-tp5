<?php


namespace common\sql_server;


use common\model\gr_chat\BanUserLog as BanUserLogModel;
use common\server\Platform\BanServer;

class BanUserLog extends BaseSqlServer
{


    public static function insertLog($info=[],$type=1, $platformSuffix=''){

        $sql = "insert into gr_ban_user_log(`uid`,`user_name`,`admin_user`,`dateline`,`type`,`ban_time`,`reason`,`platform`) values";

        foreach( $info as $v ){
            $time = empty($v['dateline'])?time() : $v['dateline'];
            $ban_time = empty($v['ban_time'])? 0 : $v['ban_time'];
            $uid = empty($v['uid']) ? 0 : $v['uid'];
            $user_name = empty($v['user_name']) ? '' : $v['user_name'];
            $platform = empty($v['platform']) ? '' : $v['platform'];
            $sql .= "('{$uid}',".
                "'{$user_name}',".
                "'{$v['admin_user']}',".
                "{$time},".
                "{$type},".
                "{$ban_time},".
                "'{$v['reason']}',".
                "'{$platform}'),";
        }

        $sql = trim($sql,",");

        $model = new BanUserLogModel();

        $ret = $model->execute($sql);
        return $ret;
    }


    public static function getList($where,$offset,$limit,$order)
    {
        $model = new BanUserLogModel();
        $res = $model->where($where)->limit($offset.','.$limit)->order($order)->select();
        return $res;
    }


    public static function getCount($where)
    {
        $model = new BanUserLogModel();
        $res = $model->where($where)->count();
        return $res;

    }


    public static function platformBan($data)
    {

        $data['ban_time'] = $data['ban_time']*60; //转换为秒

        $users = explode("\n",$data['ban_list']);
        $users = array_unique($users);

        $ban_server = new BanServer();

        $ban_server->ban();

        $banData = [
            'uid' => '',
            'ip'  => '',
            'imei' => '',
            'idfa' => '',
            'type' => '',
            'action_type' => '',
            'ban_time' => '',
            'time' => '',
        ];


        //如果是选择封账号需要去找到对应的UID(平台id)
        if ($data['ban_account_radio'] == 1){
            /*$commonMember = KefuCommonMember::getUidByUserName($users);
            $users = array_unique(array_keys($commonMember));*/
            $banData['user_name'] = $users = implode(',', $users);
            $actionType = 1; //封user_name标识符
        }else{
            $banData['uid'] = $users = implode(',', $users);
        }



        $resJson = BanServer::action_ban($banData);
        $res = json_decode($resJson, true);


//            if ($res['state']['code'] == 1){
        if ($res){
            $userArr = explode(',', $users);

            if (count($userArr) == count($res['data'])){
                $resultArr['status'] = 0;
                $resultArr['msg'] = '平台全部操作失败！！';
                return $resultArr;
            }

            if (!empty($res['data'])){
                //去掉失败的
                foreach ($userArr as $k => $v){
                    if (in_array($v, $res['data'])){
                        unset($userArr[$k]);
                    }
                }
                $resultArr = [
                    'status' => 1,
                    'msg'    => "部分操作失败，失败的有（".implode(',', $res['data'])."）",
                ];
            }

            $tmpTime = time();
            $logData = [
                'user' => $userArr,
                'type' => $data['ban_type'],
                'ban_time' =>  $data['ban_time'],
                'reason'  => $data['reason'],
                'action_type' => $data['actionType'],
                'dateline' => $data['tmp_time'],
                'platform' => $data['platform']
            ];


            //更新本地的数据及redis缓存
            $ban_server::ban_log($logData);
            $resultArr['status'] = 1;
        }

    }

}