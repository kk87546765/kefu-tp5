<?php
/**
 * 系统
 */
namespace common\server\Vip;

use common\base\BasicServer;
use common\model\db_customer\LossUser;
use common\model\db_customer\LossUserSub;
use common\server\CustomerPlatform\CommonServer;
use common\server\SysServer;
use common\libraries\Common;
use common\sql_server\LossUserSqlServer;
use think\Db;

class LossUserServer extends BasicServer
{
    /**
     * 整合手机关联账号
     * @param $platform_id
     * @param int $limit
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function unionUserByCache($platform_id,$limit = 10)
    {
        $code = [
            0=>'success',1=>'平台id不存在',2=>'平台信息不存在',3=>'没有用户数据'
        ];

        if(!$platform_id){
            return ['code'=>1,'msg'=>$code[1]];
        }

        $platform_list = SysServer::getPlatformList();

        $platform_info = [];
        foreach ($platform_list as $item){
            if($item['platform_id'] == $platform_id){
                $platform_info = $item;
                break;
            }
        }

        if(empty($platform_info)){
            return ['code'=>2,'msg'=>$code[2]];
        }

        $memberModel = CommonServer::getPlatformModel('KefuCommonMember',$platform_info['suffix']);

        $cache_name_last_user_id = 'unionUserLastId'.'_'.$platform_id;

        $last_user_id = cache($cache_name_last_user_id);

        $where = [];
        if($last_user_id){
            $where['id'] = ['>',$last_user_id];
        }

        $where['mobile'] = ['<>',''];

        $field = '
            id
            ,uid
            ,user_name AS username
            ,mobile AS phone
            ,udid
            ,reg_date
        ';

        $user_list = $memberModel->field($field)->where($where)->order('id asc')->limit($limit)->select()->toArray();

        if(!$user_list){
            return ['code'=>3,'msg'=>$code[3]];
        }

        $lossUserModel = new LossUser();

        $lossUserSubModel = new LossUserSub();

        foreach ($user_list as $item){
            $last_user_id = $item['id'];
            if(!$item['udid']){
                continue;
            }

            if(preg_match('/^0{6}/',$item['udid'])){
                continue;
            }

            $this_loss_user_info = $lossUserModel->where([
                'platform_id'=>$platform_id,
//                'udid'=>$item['udid'],
                'phone'=>$item['phone'],
            ])->find();

            if($this_loss_user_info){
                if($this_loss_user_info->udid == $item['udid']){
                    continue;
                }
                $res = $lossUserSubModel->where([
                    'platform_id'=>$platform_id,
                    'phone'=>$item['phone'],
                ])->delete();

                if(!$res){
                    continue;
                }

                $this_loss_user_info->save([
                    'udid'=>$item['udid']
                ]);
            }else{
                $lossUserModel->create([
                    'platform_id'=>$platform_id,
                    'udid'=>$item['udid'],
                    'phone'=>$item['phone'],
                    'reg_time'=>$item['reg_date'],
                ]);
            }

            $where = [];
            $where['udid'] = $item['udid'];


            $this_user_list = $memberModel->field($field)->where($where)->select()->toArray();

            if(!$this_user_list){
                continue;
            }

            $this_loss_user_sub_add = [];

            $this_loss_user_sub_arr = $lossUserSubModel->where([
                'platform_id'=>$platform_id,
                'udid'=>$item['udid'],
            ])->column('uid');

            foreach ($this_user_list as $tul){
                if(in_array($tul['uid'],$this_loss_user_sub_arr)){
                    continue;
                }
                $this_loss_user_sub_add[] = [
                    'platform_id'=>$platform_id,
                    'uid'=>$tul['uid'],
                    'str_uid'=>$tul['uid'],
                    'username'=>$tul['username'],
                    'udid'=>$tul['udid'],
                    'phone'=>$tul['phone']?$tul['phone']:$item['phone'],
                ];
            }

            if($this_loss_user_sub_add){
                $lossUserSubModel->saveAll($this_loss_user_sub_add);
            }
        }

         cache($cache_name_last_user_id,$last_user_id,3600);

        return ['code'=>0,'msg'=>$code[0],'data'=>['id'=>$last_user_id]];
    }

    /**
     * 获取已关联手机号
     * @param $platform_id
     * @param $param
     * @return mixed|object|array|bool|int|\stdClass|float|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getPhone($platform_id,$param){

        $model = new LossUserSub();

        $where = getDataByField($param,['udid','username','uid'],true);

        if(empty($where)){
            return false;
        }
        $where['platform_id'] = $platform_id;

        $info = $model->where($where)->find();

        if(!$info){
            return false;
        }

        return $info->phone;
    }

    /**
     * 查询已关联账号信息
     * @param $platform_id
     * @param $param
     */
    public static function getUnionAccount($platform_id,$param){

        $model = new LossUserSub();

        $where = getDataByField($param,['udid','username','uid','phone'],true);

        if(empty($where)){
            return false;
        }
        $where['platform_id'] = $platform_id;

        $info = $model->where($where)->select()->toArray();

        return $info;
    }

    /**
     * 获取平台可关联所有账号信息
     * @param $platform_id
     * @param $param
     * @return false
     */
    public static function getAccount($platform_id,$param){

        $where = getDataByField($param,['udid','username','uid','phone'],true);

        if(!empty($where)){
            return false;
        }

        $platform_info = SysServer::getPlatformInfo($platform_id);

        if(!empty($platform_info)){
            return false;
        }

        $model = CommonServer::getPlatformModel('KefuCommonMember',$platform_info['suffix']);

        if(!empty($where['udid'])){
            return $model->where($where)->select()->toArray();
        }else{
            $info = $model->where($where)->find();

            if(!$info){
                return false;
            }

            return $model->where(['udid'=>$info['udid']])->select();
        }
    }

    /**
     * 补充流失表用户充值和最后登录数据
     * @param $platform_id
     * @param int $limit
     * @return array
     * @throws \think\db\exception\BindParamException
     * @throws \think\exception\PDOException
     */
    public static function updateUserInfoByCache($platform_id,$limit = 10){
        $code = [
            0=>'success',1=>'平台id不存在',2=>'平台信息不存在',3=>'没有用户数据'
        ];

        if(!$platform_id){
            return ['code'=>1,'msg'=>$code[1]];
        }

        $model = new LossUser();
        $where = [];
        $where['lu.platform_id'] = $platform_id;

        $cache_name_last_id = 'updateUserInfo'.'_'.$platform_id;

        $last_id = cache($cache_name_last_id);

        if($last_id){
            $where[] = ['lu.id','>',$last_id];
        }

        $sql = "SELECT
                lu.id
                ,SUM(kf.total_pay) AS account_money
                ,max(kf.last_login_time) as last_login_time
            FROM
                loss_user AS lu
            LEFT JOIN loss_user_sub AS lub ON lu.platform_id = lub.platform_id
            AND lu.phone = lub.phone
            LEFT JOIN db_statistic.kefu_user_recharge AS kf ON lub.platform_id = kf.platform_id
            AND lub.str_uid = kf.uid
                ".setWhereSql($where)."
            GROUP BY lu.id
            ORDER BY lu.id ASC
            LIMIT {$limit}
        ";

        $list = $model->query($sql);

        if(empty($list)){
            return ['code'=>3,'msg'=>$code[3]];
        }

        $up_data = [];

        foreach ($list as $item){
            $last_id = $item['id'];
            if($item['last_login_time'] || $item['account_money']){
                $item['last_login_time'] = $item['last_login_time']?$item['last_login_time']:0;
                $item['account_money'] = $item['account_money']?$item['account_money']:0;
                $up_data[] = $item;
            }
        }

        cache($cache_name_last_id,$last_id,3600);

        if($up_data){
            $res = $model->saveAll($up_data);
        }

        return ['code'=>0,'msg'=>$code[0]];
    }

    public static function updateUserInfo($param){

        $code = [
            0=>'success',1=>'没有筛选参数',2=>'平台信息不存在',3=>'没有用户数据'
        ];

        $model = new LossUser();

        $where = [];

        if(!empty($param['platform_id'])){
            $where['kf.platform_id'] = $param['platform_id'];
        }

        if(!empty($param['date'])){
            $time_arr = timeCondition('day',$param['date']);

            $where['_string'] = "(kf.last_pay_time between ".$time_arr['starttime']." AND ".$time_arr['endtime']." OR kf.last_login_time between ".$time_arr['starttime']." AND ".$time_arr['endtime'].')';
        }

        if(empty($where)){
            return ['code'=>1,'msg'=>$code[1]];
        }

        $field = '
            lu.id
            ,lu.udid
            ,SUM(kf.total_pay) AS account_money
            ,max(kf.last_login_time) as last_login_time
        ';

        $list = $model
            ->alias('lu')
            ->field($field)
            ->join('__LOSS_USER_SUB__ lub','lu.platform_id = lub.platform_id AND lu.phone = lub.phone','LEFT')
            ->join('db_statistic.kefu_user_recharge kf','kf.platform_id = lub.platform_id AND kf.uid = lub.str_uid')
            ->where(setWhereSql($where,''))
            ->group('lu.id')
//            ->fetchSql()
            ->select();

        $list = $list->toArray();

        if(!empty($list)){
            // 同步更新同设备数据信息
            foreach ($list as $item){
                $model->where('udid','=',$item['udid'])->update(getDataByField($item,['account_money','last_login_time']));
            }
//            $model->saveAll($list);
        }


        return ['code'=>0,'msg'=>$code[0]];
    }
    public static function getByWhere($data = [])
    {
        $res = LossUserSqlServer::getInfo($data);
        $res = isset($res)? $res->toArray() : [];
        return $res;
    }

    public static function getSubByWhere($data = [])
    {
        $res = LossUserSqlServer::getSubInfo($data);
        $res = isset($res)? $res->toArray() : [];
        return $res;
    }

    public static function update($data = [])
    {
        $res = LossUserSqlServer::update($data);
        return $res;
    }


    public static function insertInfo($data)
    {
        if(empty($data['udid'])){
            return false;
        }
        $res = LossUserSqlServer::insertInfo($data);
        return $res;
    }

    public static function insertInfoSub($data)
    {
   
        $res = LossUserSqlServer::insertInfoSub($data);
        return $res;
    }

    //筛选用户
    public static function screenUids($plan_log_data)
    {

        $Platform_suffex = Common::getPlatformInfoByPlatformIdAndCache($plan_log_data['platform_id']);

        //先从流失用户表筛选+产品充值表+角色等级表
        $user_info = LossUserSqlServer::screenUidsByLossUser($plan_log_data ,  $Platform_suffex);


        return $user_info;

    }


}
