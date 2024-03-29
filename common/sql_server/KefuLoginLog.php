<?php
/**
 * Created by PhpStorm.
 * User: crosstime
 * Date: 2018/8/14
 * Time: 下午5:31
 */
namespace common\sql_server;
use common\sql_server\BaseSqlServer;
use common\server\CustomerPlatform\CommonServer;

class KefuLoginLog extends BaseSqlServer
{

    public $common_where = [

    ];

    public function initialize()
    {
        parent::initialize();

    }



    public function getCount($where,$platform_suffix)
    {
        $model = CommonServer::getPlatformModel('KefuLoginLog',$platform_suffix);
        $count = $model->where($where)->count();
        return $count;

    }

    public function getList($where,$page=1,$limit=20,$order = 'id desc',$platform_suffix)
    {
        $model = CommonServer::getPlatformModel('KefuLoginLog',$platform_suffix);
        $list = $model->where($where)->limit($page,$limit)->order($order)->select();
        $list = isset($list) ? $list->toArray() : [];
        return $list;
    }

    public function getAllByWhere($where,$columns='*',$group=''){

        $conditions = '1=1';
        $bind = [];
        
        if($where){
            $where = array_merge($this->common_where,$where);
        }else{
            $where = $this->common_where;
        }

        // if(isset($where['month'])){
        //     if($where['month']){
        //         $time_arr = timeCondition('month',$where['month']);
        //         $conditions.= ' AND day_num >='.$time_arr['starttime'];
        //         $conditions.= ' AND day_num <='.$time_arr['endtime'];
        //     }
        //     unset($where['month']);
        // }
        
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

        $param['order'] = 'id DESC';

        $list = $this->find($param);

        return $list;

    }

    public function getOneByWhere($where,$field='*'){

        if($where){
            $where = array_merge($this->common_where,$where);
        }else{
            $where = $this->common_where;
        }

        $conditions = '1=1';
        $bind = [];

        setWhere($conditions,$bind,$where);

        $list = $this->findFirst([
            'conditions'=>$conditions,
            'bind'=>$bind,
            'order'=>'id DESC',
            'columns'=>$field,
        ]);

        return $list;

    }

    public static function getLastLoginTime($platform = '', $order = 'asc', $starTime = 0, $endTime = 0)
    {
        $result = [];
        if (!empty($platform)) {
            $suffix = '_'.$platform;
            $model = new static();
            $model->setReadConnectionService('db_db_customer'.$suffix);
        }
        $res = self::findFirst([
            'conditions' => ' login_date>'.$starTime.' and login_date <'.$endTime,
            'order' => 'login_date '.$order,
            'columns' => 'login_date',
        ]);
        if (!empty($res)) $result = $res->toArray();
        return $result;
    }

    public static function getLoginLogByUid($uid, $platform = '', $order = 'asc', $starTime = 0, $endTime = 0)
    {
        $result = [];
        if (!empty($platform)) {
            $suffix = '_'.$platform;
            $model = new static();
            $model->setReadConnectionService('db_db_customer'.$suffix);
        }
        $res = self::find([
            'conditions' => 'uid = '.$uid.' and login_date>'.$starTime.' and login_date <'.$endTime,
            'order' => 'login_date '.$order,

        ]);
        if (!empty($res)) $result = $res->toArray();

        return $result;
    }

}