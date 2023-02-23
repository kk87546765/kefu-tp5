<?php
/**
 * Created by PhpStorm.
 * User: crosstime
 * Date: 2018/8/14
 * Time: 下午5:31
 */
namespace common\sql_server;


use common\server\CustomerPlatform\CommonServer;

class KefuPayOrder extends BaseSqlServer
{
    public function initialize()
    {

        parent::initialize();

    }

    public $common_where = [

    ];



    /**
     * @param string $platform
     * @param string $order
     * @param int $starTime
     * @return array
     */
    public static function getPayTime($platform = '', $order = 'asc', $starTime = 0)
    {
        $result = [];
        if (!empty($platform)) {
            $suffix = '_'.$platform;
            $model = new static();
            $model->setReadConnectionService('db_db_customer'.$suffix);
        }
        $res = self::findFirst([
            'conditions' => ' pay_time<'.$starTime,
            'order' => 'pay_time '.$order,
            'columns' => 'pay_time',
        ]);
        if (!empty($res)) $result = $res->toArray();
        return $result;
    }

    /**
     * @param $where
     * @param string $columns
     * @param string $group
     * @return mixed
     */
    public function getAllByWhere($where,$columns='*',$group='',$order='id DESC'){

        $conditions = '1=1';
        $bind = [];
        
        if($where){
            $where = array_merge($this->common_where,$where);
        }else{
            $where = $this->common_where;
        }

        if(isset($where['month'])){
            if($where['month']){
                $time_arr = timeCondition('month',$where['month']);
                $conditions.= ' AND day_num >='.$time_arr['starttime'];
                $conditions.= ' AND day_num <='.$time_arr['endtime'];
            }
            unset($where['month']);
        }


        
        setWhere($conditions,$bind,$where);

        $param = [];
        $param['conditions'] = $conditions;
        if($bind){
            $param['bind'] = $bind;
        }
        if($columns){
            $param['columns'] = $columns;
        }
        if($group){
            $param['group'] = $group;
        }

        if($order){
            $param['order'] = $order;
        }

        $list = $this->find($param);

        return $list;

    }

    /**
     * 查询一条数据
     * @param $where 条件
     * @param string $field 字段
     * @return mixed
     */
    public function getOneByWhere($where,$field='*',$order = ''){

        if($where){
            $common_where = isset($this->common_where)?$this->common_where:[];
            $where = array_merge($common_where,$where);
        }else{
            $where = $this->common_where;
        }

        $conditions = '1=1';
        $bind = [];

        setWhere($conditions,$bind,$where);

        $param = [];
        $param['conditions'] = $conditions;

        if($bind){
            $param['bind'] = $bind;
        }

        if($field){
            $param['columns'] = $field;
        }

        if($order){
            $param['order'] = $order;
        }

        $list = $this->findFirst($param);

        return $list;

    }

    /**
     * 获取用户的充值累计(默认今天的)
     * @param string $suffix
     * @param int $uid
     * @param bool $is_today
     * @return int
     */
    public static function getUserOrderSum($suffix = '',$uid=0, $is_today = true)
    {
        $sum = 0;
        if (empty($suffix) || empty($uid)) return $sum;
        $model = new static();
        $model->setReadConnectionService('db_db_customer'.$suffix);
        $condition = '1=1 ';
        $bind = [];

        if ($is_today) {
            $todayTime = strtotime(date('Y-m-d'));
            $condition .= " and pay_time >=".$todayTime;
        }
        if (is_array($uid)) {
            $condition  .= " and uid in ({uid:array}) ";
            $bind['uid'] = $uid;
        }else {
            $condition .= " and uid =  :uid: ";
            $bind['uid'] = $uid;
        }
        if (!empty($bind)) {
            $res = self::findFirst([
                'conditions' => $condition,
                'bind' => $bind,
                'columns' => 'sum(amount) sum'
            ]);
            if (!empty($res['sum'])) $sum = $res['sum'];
        }
        return $sum;
    }


    public static function getPayLogByUid($uid, $platform = '', $order = 'asc', $starTime = 0, $endTime = 0)
    {
        $result = [];
        $model = CommonServer::getPlatformModel('KefuPayOrder',$platform);

        $res = $model->where('uid = '.$uid.' and pay_time>'.$starTime.' and pay_time <'.$endTime)->order('pay_time '.$order)->select();

        if (!empty($res)) $result = $res->toArray();

        return $result;
    }
}