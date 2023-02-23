<?php
namespace app\scripts\controller;

use common\model\db_statistic\SellerKpiConfig;
use common\model\db_statistic\SellWorkOrderStatisticMonth;
use common\server\Vip\SellWorkOrderServer;

class SellWorkOrder extends Base
{

    protected $func_arr = [
        ['func'=>'statisticSWODayAll','param'=>'','delay_time'=>0,'runtime'=>3600*1.5,'limit'=>0],
        ['func'=>'statisticSWODayAllExtra','param'=>'','delay_time'=>0,'runtime'=>3600*1.5,'limit'=>0],
    ];

    public function run()
    {
        $this->apiRun();
    }

    public function clean(){
        $name = $this->req->get('func/s','all');

        if($name == 'all'){
            foreach ($this->func_arr as $item){
                $page_cache_name = $item['func'].'page';
                cache($page_cache_name,null);//如果执行完解除锁定
            }
            dd($this->apiClean('all'));
        }else{
            $page_cache_name = $name.'page';

            cache($page_cache_name,null);//如果执行完解除锁定
            dd($this->apiClean($name));
        }
    }

    public function test(){
        dd($this->statisticSWODayAll()?1:2);
    }

    protected function statisticSWODayAll(){

        $cache_name = 'statisticSWODayAll';
        $page_cache_name = $cache_name.'page';

        /*判断 end*/

        /*业务前logic*/

        /*业务 begin*/
        $limit = 10;
        $page = cache($page_cache_name);
        $page = $page?$page:1;

        $time_arr = timeCondition('month',strtotime('yesterday'));//yesterday
        $month = $time_arr['starttime'];

        $where_kpi_list = [];
        $where_kpi_list['kpi_date'] = $month;
        $SellerKpiConfig = new SellerKpiConfig();
        $kpi_list = $SellerKpiConfig->where($where_kpi_list)->page($page,$limit)->group('admin_id,kpi_date')->select()->toArray();

        if($kpi_list){
            $SellWorkOrderStatisticMonth = new SellWorkOrderStatisticMonth();
            foreach ($kpi_list as $kl_v){
                $list = SellWorkOrderServer::statisticSWOMonthByAdmin($kl_v,0,0);
                if($list){
                    $add_field = $update_field = [
                        'p_p',
                        'platform_id',
                        'product_id',
                        'group_id',
                        'big_order_count',
                        'big_order_sum',
                        'maintenance_all',
                        'maintenance_count',
                        'month_user_all',
                        'month_user_count',
                        'order_count',
                        'order_sum',
                        'remark_all',
                        'remark_count',
                        'sell_commission',
                        'sum_kpi_value',
                    ];
                    $add_field[]= 'admin_id';
                    $add_field[]= 'kpi_date';
                    $add_field[]= 'type';

                    $add_data = [];
                    foreach ($list as $v){
                        $where = [];
                        $where['kpi_date'] = $v['kpi_date'];
                        $where['admin_id'] = $v['admin_id'];
                        $where['p_p'] = $v['p_p'];
                        $where['type'] = 2;

                        $this_info = $SellWorkOrderStatisticMonth->where($where)->find();
//                        dd($this_info);
                        if($this_info){

                            $this_data = getDataByField($v,$update_field);

                            $this_info->save($this_data);
                        }else{
                            $this_data = getDataByField($v,$add_field);
                            $this_data['type'] = 2;

                            $add_data[] = $this_data;
                        }
                    }

                    if($add_data){
                        $SellWorkOrderStatisticMonth->insertAll($add_data,$add_field);
                    }
                }
            }
        }else{
            //任务完全结束
            cache($page_cache_name,null);//清除分页缓存
            return true;
        }
        /*业务 end*/

        /*业务后logic*/
        //任务未结束
        $page++;
        cache($page_cache_name,$page,600);//执行成功保存分页
        return false;
    }

    protected function statisticSWODayAllExtra(){
        if(date('d')>5){
            return true;
        }


        $cache_name = 'statisticSWODayAllExtra';

        $page_cache_name = $cache_name.'page';
        /*判断 end*/

        /*业务 begin*/
        $limit = 10;
        $page = cache($page_cache_name);
        $page = $page?$page:1;

        $time_arr = timeCondition('month',strtotime('-1 month'));//上个月
        $month = $time_arr['starttime'];

        $where_kpi_list = [];
        $where_kpi_list['kpi_date'] = $month;

        $SellerKpiConfig = new SellerKpiConfig();
        $kpi_list = $SellerKpiConfig->where($where_kpi_list)->page($page,$limit)->group('admin_id,kpi_date')->select()->toArray();

        if($kpi_list){
            $SellWorkOrderStatisticMonth = new SellWorkOrderStatisticMonth();
            foreach ($kpi_list as $kl_v){

                $list = SellWorkOrderServer::statisticSWOMonthByAdmin($kl_v,0,0);
                if($list){
                    $add_field = $update_field = [
                        'p_p',
                        'platform_id',
                        'product_id',
                        'group_id',
                        'big_order_count',
                        'big_order_sum',
                        'maintenance_all',
                        'maintenance_count',
                        'month_user_all',
                        'month_user_count',
                        'order_count',
                        'order_sum',
                        'remark_all',
                        'remark_count',
                        'sell_commission',
                        'sum_kpi_value',
                    ];
                    $add_field[]= 'admin_id';
                    $add_field[]= 'kpi_date';
                    $add_field[]= 'type';

                    $add_data = [];
                    foreach ($list as $v){
                        $where = [];
                        $where['kpi_date'] = $v['kpi_date'];
                        $where['admin_id'] = $v['admin_id'];
                        $where['p_p'] = $v['p_p'];
                        $where['type'] = 2;

                        $this_info = $SellWorkOrderStatisticMonth->where($where)->find();

                        if($this_info){

                            $this_data = getDataByField($v,$update_field);
                            $this_info->save($this_data);
                        }else{

                            $this_data = getDataByField($v,$add_field);
                            $this_data['type'] = 2;
                            $add_data[] = $this_data;
                        }
                    }

                    if($add_data){
                        $SellWorkOrderStatisticMonth->insertAll($add_data);
                    }
                }
            }
        }else{
            //任务完全结束
            cache($page_cache_name,null);//清除分页缓存
            return true;
        }
        /*业务 end*/

        /*业务后logic*/
        $page++;
        cache($page_cache_name,$page,600);//执行成功保存分页
        return false;
    }

    protected function changeSWODayAllData(&$this_data){
//        dd($this_data);
        if(isset($this_data['p_p']) && $this_data['p_p']){
            $this_platform_id = [];
            $this_product_id = [];
            foreach ($this_data['p_p'] as $this_p_p_item){
                if($this_p_p_item){
                    $this_p_p_data = explode('_',$this_p_p_item);
                    $this_platform_id[$this_p_p_data[0]] = $this_p_p_data[0];
                    $this_product_id[$this_p_p_data[1]] = $this_p_p_data[1];
                }
            }
            ksort($this_platform_id);
            ksort($this_product_id);

            $this_data['platform_id'] = implode(',',$this_platform_id);
            $this_data['product_id'] = implode(',',$this_product_id);
            $this_data['p_p'] = implode(',',$this_data['p_p']);
        }

    }
}
