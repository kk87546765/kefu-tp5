<?php
/**
 * 系统
 */
namespace common\server\Vip;

use common\base\BasicServer;
use common\model\db_statistic\ProductPropConfig;
use common\model\db_statistic\VipUserRebateProp;
use common\server\SysServer;

class WelfareServer extends BasicServer
{
    /**
     * @param $params
     * @return array
     */
    public static function isExelUserRebateProp($params)
    {
        $where = getDataByField($params,['platform_id','uid','game_id','date'],true);

        $model = new VipUserRebateProp();

        return $model->where($where)->select()->toArray();
    }
    /**
     * @param string $start_time
     * @param string $end_time
     * @param int $platform_id
     * @return array|mixed
     */
    public static function getRebatePropInfoByDate($start_time = '',$end_time = '',$platform_id = 0)
    {
        $result = $res = [];
        if (empty($start_time) || empty($end_time) || (strtotime($start_time) > strtotime($end_time))) return $result;
        $VipUserRebateProp = new VipUserRebateProp();
        $sql = "SELECT A.*,B.title,B.content,B.prop_id FROM vip_user_rebate_prop as A INNER JOIN product_prop_config as B ON A.rebate_prop_config_id = B.id where A.date >= '".$start_time."' and A.date <= '".$end_time."'";
        if (!empty($platform_id)) {
            $sql .= " AND A.platform_id in($platform_id)";
        }

        $res = $VipUserRebateProp->query($sql);

        $adminUserInfo = SysServer::getAdminListCache();

        foreach ($res as $v) {
            $tmp = [
                'title' => $v['title'],
                'content' => $v['content'],
                'prop_id' => $v['prop_id'],
                'examine_status' => $v['examine_status'],
                'add_admin_name' => $adminUserInfo[$v['add_admin_id']]['realname'] ?? '',
                'apply_status' => $v['apply_status'] == 2 ? '已申请' : '未申请',
                'examine' => $v['examine_status'] == 1 ? '审核通过' : ($v['examine_status'] == 2 ? '拒审' : '未审核')
            ];
            $result[$v['date'].'_'.$v['platform_id'].'_'.$v['uid'].'_'.$v['game_id']][] = $tmp;
        }
        return $result;
    }
    /**
     * @param $platform_id
     * @param $uid
     * @param $date
     * @param $game_id
     * @return array
     */
    public static function getUserRebatePropList($data)
    {

        if (empty($data['platform_id']) || empty($data['uid']) || empty($data['game_id']) || empty($data['date'])) return [];
        $sql = "SELECT A.*,B.title,B.content,B.prop_id FROM vip_user_rebate_prop as A INNER JOIN product_prop_config as B ON A.rebate_prop_config_id = B.id";
        $sql .= " where A.platform_id = ".$data['platform_id']." and A.uid = ".$data['uid']." and A.game_id = ".$data['game_id']." and A.date = '".$data['date']."'";
        $VipUserRebateProp = new VipUserRebateProp();
        $result = $VipUserRebateProp->query($sql);

        $result = self::getRebatePropAdminInfo($result);
        return $result;
    }

    /**
     * @param array $data
     * @return array|mixed
     */
    public static function getRebatePropAdminInfo($data = array())
    {
        $result = [];
        if (empty($data)) return $result;
        $adminUserInfo = SysServer::getAdminListCache();
        foreach ($data as &$v) {
            $this_admin = getArrVal($adminUserInfo,$v['add_admin_id'],[]);
            $this_admin && $v['add_admin_name'] = $this_admin['realname'];
            $this_admin = getArrVal($adminUserInfo,$v['examine_admin_id'],[]);
            $this_admin && $v['examine_admin_name'] = $this_admin['realname'];

            $v['add_date'] = empty($v['add_time']) ? '' : date('Y-m-d H:i:s', $v['add_time']);
            $v['examine_date'] = empty($v['examine_time']) ? '' : date('Y-m-d H:i:s', $v['examine_time']);
            $v['apply_status'] = $v['apply_status'] == 2 ? '已申请' : '未申请';
            $v['examine'] = $v['examine_status'] == 1 ? '审核通过' : ($v['examine_status'] == 2 ? '拒审' : '未审核');
        }
        $result = $data;
        return $result;
    }

    public static function propRebateConfig(){
        $config = [];
        $config['apply_status_arr'] = VipUserRebateProp::$apply_status_arr;
        $config['examine_status_arr'] = VipUserRebateProp::$examine_status_arr;


        return $config;
    }

    public static function propRebateExamine($param){

        $code = [0=>'success',1=>'数据不存在',2=>'更新失败'];
        $VipUserRebateProp = new VipUserRebateProp();

        $where = [];
        $where[] = ['id','in',$param['ids']];

        $list = $VipUserRebateProp->where($where)->select();

        if(!$list->toArray()){
            return ['code'=>1,'msg'=>$code[1]];
        }

        $flag = 0;
        $data = [];
        $data['examine_admin_id'] = self::$user_data['id'];
        $data['examine_status'] = $param['examine_status'];
        $data['examine_time'] = time();
        foreach ($list as $v){
            $res = $v->save($data);
            if($res){
                $flag++;
            }
        }

        if(!$flag){
            return ['code'=>2,'msg'=>$code[2]];
        }

        return ['code'=>0,'msg'=>$code[0]];
    }
    public static function propRebateApply($param){

        $code = [0=>'success',1=>'数据不存在',2=>'更新失败'];
        $VipUserRebateProp = new VipUserRebateProp();

        $where = [];
        $where[] = ['id','in',$param['ids']];

        $list = $VipUserRebateProp->where($where)->select();

        if(!$list->toArray()){
            return ['code'=>1,'msg'=>$code[1]];
        }

        $flag = 0;
        $data = [];
        $data['admin_id'] = self::$user_data['id'];
        $data['apply_status'] = $param['apply_status'];

        foreach ($list as $v){
            $res = $v->save($data);
            if($res){
                $flag++;
            }
        }

        if(!$flag){
            return ['code'=>2,'msg'=>$code[2]];
        }

        return ['code'=>0,'msg'=>$code[0]];
    }

    public static function propRebateDetail(){

    }

    public static function propRebateDetailConfig(){
        $config = [];
        $config['title'] = self::getRebatePropTitle();
        //$config['content'] = self::getRebatePropContent();
        return $config;
    }

    /**
     * @param $param
     * @return bool
     */
    public static function propRebateSave($param)
    {

        if (
            empty($param['platform_id']) ||
            empty($param['uid']) ||
            empty($param['date']) ||
            empty($param['game_id']) ||
            empty($param['rebate_prop_config_id'])
        )
            return false;

        $model = new VipUserRebateProp();

        $param['add_admin_id'] = self::$user_data['id'];

        return $model->create($param);
    }

    /**
     * @return array
     */
    public static function getRebatePropTitle()
    {
        $model = new ProductPropConfig();

        $res = $model
            ->field('title_md5,title')
            ->where('status',1)
            ->group('title_md5')
            ->select();

        if (empty($res)) return [];

        $result = [];

        $list = $res->toArray();

        foreach ($list as $v) {
            $result[$v['title_md5']] = $v;
        }

        return $result;
    }

    /**
     * @param string $title_md5
     * @return array
     */
    public static function getRebatePropContent($title_md5 = '')
    {
        $result = [];
        $model = new ProductPropConfig();
        $where = [];
        $where['status'] = 1;

        if (!empty($title_md5)) {
            $where['title_md5'] = $title_md5;
        }
        $res = $model->where($where)->select();

        if (empty($res)) return $result;

        $list = $res->toArray();

        foreach ($list as $v) {
            $result[$v['id']] = $v;
        }

        return $result;
    }
}
