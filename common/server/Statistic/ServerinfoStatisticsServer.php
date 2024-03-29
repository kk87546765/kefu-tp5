<?php
/**
 * 系统
 */
namespace common\server\Statistic;


use common\base\BasicServer;
use common\sql_server\DistributionalGameServerSqlServer;
use common\sql_server\DistributionalServerSqlServer;
use common\sql_server\ServerStatisticSqlServer;
use think\Db;
use common\libraries\Common;
use common\sql_server\GarrisonProductSqlServer;

class ServerinfoStatisticsServer extends BasicServer
{

    const INSERT_NUMBER = 2000;

    public $time,$num,$platform,$start_time,$end_time,$server_info_statistics_model;


    public function dealParams($params)
    {

        $this->time = time();
        $this->platform = isset($params['platform']) ? $params['platform'] : '';
        $this->end_time = isset($params['end_time']) ? date('Y-m-d', strtotime($params['end_time'])) : date("Y-m-d", $this->time);

        $this->start_time= isset($params['start_time']) ? date('Y-m-d', strtotime($params['start_time'])) : date("Y-m-d",strtotime("-1 day"));
        $this->num = isset($num) ? $num : 1000;



    }


    //统计每个角色每个区服的注册充值情况
    public function insertIntoServerInfo()
    {

        $start_time = $this->start_time;
        $end_time = $this->end_time;


        $this->server_info_statistics_model = new ServerStatisticSqlServer();

        //获取区服用户注册信息
        $userData1 = $this->server_info_statistics_model->getServerUserRegInfo($start_time,$end_time);

        //获取用户总充值信息
        $userData2 = $this->server_info_statistics_model->getServerUserPayInfo($start_time,$end_time);

        $new_arr = [];
        $time = time();


        foreach($userData2 as $k2=>$v2){

            foreach($v2 as $k3=>$v3){
                if(empty($v3['center_product_id']) || empty($v3['server_id'])){
                    continue;
                }
                //使用中心产品id，用于区分唯一性，避免不同平台产品id相同
                $new_arr[$k2.'_'.$v3['center_product_id'].'_'.$v3['server_id']]['pay_money'] += $v3['pay_money'];
                $new_arr[$k2.'_'.$v3['center_product_id'].'_'.$v3['server_id']]['reg_num'] = 0;
                $new_arr[$k2.'_'.$v3['center_product_id'].'_'.$v3['server_id']]['role_reg_num'] = 0;
                $new_arr[$k2.'_'.$v3['center_product_id'].'_'.$v3['server_id']]['date'] =  $v3['date'];
                $new_arr[$k2.'_'.$v3['center_product_id'].'_'.$v3['server_id']]['game_id'] = $v3['game_id'];
                $new_arr[$k2.'_'.$v3['center_product_id'].'_'.$v3['server_id']]['add_time'] = $time;
                $new_arr[$k2.'_'.$v3['center_product_id'].'_'.$v3['server_id']]['center_product_id'] =  $v3['center_product_id'];
                $new_arr[$k2.'_'.$v3['center_product_id'].'_'.$v3['server_id']]['platform_id'] = $v3['platform_id'];
                $new_arr[$k2.'_'.$v3['center_product_id'].'_'.$v3['server_id']]['product_id'] = $v3['product_id'];
                $new_arr[$k2.'_'.$v3['center_product_id'].'_'.$v3['server_id']]['product_name'] = $v3['product_name'];

                $new_arr[$k2.'_'.$v3['center_product_id'].'_'.$v3['server_id']]['server_id'] = $v3['server_id'];
                $new_arr[$k2.'_'.$v3['center_product_id'].'_'.$v3['server_id']]['server_name'] = $v3['server_name'];
            }
        }

        foreach($userData1 as $k=>$v){
            foreach($v as $k1=>$v1){

                if(empty($v1['center_product_id']) || empty($v1['server_id'])){
                    continue;
                }

                if(isset($new_arr[$k.'_'.$v1['center_product_id'].'_'.$v1['server_id']])){

                    $new_arr[$k.'_'.$v1['center_product_id'].'_'.$v1['server_id']]['reg_num'] += $v1['reg_num'];
                    $new_arr[$k.'_'.$v1['center_product_id'].'_'.$v1['server_id']]['role_reg_num'] += $v1['role_reg_num'];
                    $new_arr[$k.'_'.$v1['center_product_id'].'_'.$v1['server_id']]['platform'] = $k;

                }

            }
        }


        //每2000条数据插入一次
        for ($i=0;count($new_arr) >$i*self::INSERT_NUMBER;$i++)
        {
            Db::startTrans();

            $start = empty($i*self::INSERT_NUMBER) ? 0 : $i*self::INSERT_NUMBER;
            $insertData = array_slice($new_arr, $start, self::INSERT_NUMBER);

            $res = $this->server_info_statistics_model->insertInfo($insertData);
            if($res){
                Db::commit();
            }else{
                Db::rollback();
            }
        }

        return true;
    }


    public function autoDistributeServer()
    {
        $platform_list = Common::getPlatform();

        $res = GarrisonProductSqlServer::getGarrisonProductServer($platform_list);


        foreach($res as $k=>$v){
            Db::startTrans();
            foreach($v as $k1=>$v1){

                $i = 0;
                $new_arr = [];
                foreach($v1 as $k2=>$v2){
                    $i++;
                    array_push($new_arr,$v2);

                    if($i % 1000 == 0){

                        DistributionalServerSqlServer::insertInfo($new_arr);

                        $new_arr = [];
                        Db::commit();
                        Db::startTrans();
                    }
                }

                DistributionalServerSqlServer::insertInfo($new_arr);
                Db::commit();
                Db::startTrans();
            }

        }
        Db::commit();
    }


    //自动按游戏分配区服给个人
    public function autoDistributeGameServer(){

        $platform_list = Common::getPlatform();

        $res = GarrisonProductSqlServer::getGarrisonGameServer($platform_list);


        foreach($res as $k=>$v){

            Db::startTrans();
            foreach($v as $k1=>$v1){

                $i = 0;
                $new_arr = [];
                foreach($v1 as $k2=>$v2){

                    $i++;
                    array_push($new_arr,$v2);

                    if($i % 1000 == 0){

                        DistributionalGameServerSqlServer::insertInfo($new_arr);

                        $new_arr = [];
                        Db::commit();
                        Db::startTrans();
                    }
                }

                DistributionalGameServerSqlServer::insertInfo($new_arr);
                Db::commit();
                Db::startTrans();
            }

        }
        Db::commit();

    }


}
