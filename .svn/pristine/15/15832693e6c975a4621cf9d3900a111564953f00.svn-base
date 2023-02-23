<?php
/**
 * @author ambi
 * @date 2019/4/17
 */

namespace common\server\GameProduct;
use common\base\BasicServer;
use common\model\Statistic\AdminUserGameServer;
use common\model\Statistic\SellerCommissionConfig;

use common\model\Statistic\GameProduct;
use common\model\Statistic\PlatformGameInfo;
use common\model\Statistic\VipGameProduct;


class GameProductServer extends BasicServer
{

    public $admin_info;





    /**
     * 根据子类game_id获取游戏产品信息
     * @param $game_id
     * @param $platform_id
     * @return object
     */
    public function getPlatformGameInfoByGameId($game_id,$platform_id){

    	$bind = [];
    	$conditions = '1=1';
        $where = compact('game_id','platform_id');
    	
		setWhere($conditions,$bind,$where);

        $model = new PlatformGameInfo();

        $param = compact('conditions','bind');

        $info = $model->findFirst($param);

    	return $info;
    }

    /**
     * 根据子类game_id获取所有游戏子类信息
     * @param $game_id
     * @param $platform_id
     * @return mixed
     */
    public function getAllPlatformGameInfoByGameId($game_id,$platform_id){

        $bind = [];
        $conditions = '1=1';
        $where = compact('game_id','platform_id');

        setWhere($conditions,$bind,$where);

        $model = new PlatformGameInfo();

        $param = compact('conditions','bind');

        $info = $model->findFirst($param);

        if(!$info){
           return [];
        }

        $where = [];
        $where['platform_id'] = $platform_id;
        $where['product_id'] = $info->product_id;

        $list = $model->find($where);

        if($list){
            $list = $list->toArray();
        }

        return $list;
    }

    public function getAllGameInfoByWhere($where){

        $model = new GameProduct();

        $list = $model->getAllByWhere($where,'*','platform_id ASC,id DESC');

        return $list;
    }

    /**
     * 根据父类product_id获取所有游戏子类信息
     * @param $game_id
     * @param $platform_id
     * @return mixed
     */
    public function getAllPlatformGameInfoByProductId($product_id,$platform_id){

        $bind = [];
        $conditions = '1=1';
        $where = compact('product_id','platform_id');

        clearZero($where,['platform_id','product_id']);//清除值为0参数

        setWhere($conditions,$bind,$where);

        $model = new PlatformGameInfo();

        $param = compact('conditions','bind');

        $list = $model->find($param);

        return $list;
    }

    /**
     * 根据平台获取大类
     * @param int $platform_id 平台id
     * @param int $f5 0 读取缓存 1 读取数据库
     * @return array
     */
    public static function getAllGameProductCache($platform_id = 0,$f5 = 0){

        $VipStatisticServer = new VipStatisticServer();

        $cache_name = 'AllGameProduct_'.$platform_id;

        $info = $VipStatisticServer->cache($cache_name);

        if($info && $f5 == 0){
            return $info;
        }

        $model = new GameProduct();

        $where = [];
        if($platform_id){
            $where['platform_id'] = $platform_id;
        }
        
        // if($this->admin_info && $this->admin_info->is_admin == 0){
        //     $where['']
        // }

        $info = $model->getAllByWhere($where,'id,product_name AS name,platform_id,product_id');

        $new_data = [];

        if($info){
            $platform_list = SysLogic::getPlatformListCache();
            foreach ($info as $k => $v) {
                
                if(isset($platform_list[$v['platform_id']])){
                    $v['name'].='('.$platform_list[$v['platform_id']]['name'].')';
                }else{
                    $v['name'].='(未知平台)';
                }

                $v['id_str'] = $v['platform_id'].'_'.$v['product_id'];

                $new_data[$v['id']] = $v;
            }

            $VipStatisticServer->cache([$cache_name,$new_data,1800]);
        }

        return $new_data;
    }

    public static function getGameProductByAdminInfo(int $platform_id = 0,array $admin_info = [],int $f5 = 0){
        $list = self::getAllGameProductCache($platform_id,$f5);//分组列表
        $list_new = [];
        if($admin_info && $admin_info['is_admin'] == 0){
            foreach ($list as $k => $v){
                if(in_array($v['platform_id'],$admin_info['platform_id'])){
                    $list_new[$v['id']] = $v;
                }
            }
            return $list_new;
        }else{
            return $list;
        }
    }

    /**
     * 获取客服分配区服
     * @param $param
     * @return array
     */
    public function getServerIdByWhere($param){

        $server_id = [];

        $GameProduct = new GameProduct();

        $where = getDataByField($param,['platform_id','product_id','product_name']);

        $game_product_info = $GameProduct->getOneByWhere($where);

        if(!$game_product_info){
            return $server_id;
        }

        $where = getDataByField($param,['admin_user_id']);
        $where['vip_game_product_id'] = $game_product_info->id;

        $AdminUserGameServer = new AdminUserGameServer();

        $info = $AdminUserGameServer->getAllByWhere($where,'server_id');

        if($info){
            foreach ($info as $v){
                $server_id[] = $v['server_id'];
            }
        }

        return $server_id;

    }

    /**
     * 获取 vip_game_product表主键id
     * @param $platform_id
     * @param $product_id
     * @return int|mixed
     */
    public function getVGPIdByPlatformIdProductId($platform_id,$product_id){

        $where = [];
        $where['platform_id'] = $platform_id;
        $where['product_id'] = $product_id;

        $GameProduct = new GameProduct();

        $info = $GameProduct->getOneByWhere($where);

        $id = 0;

        if($info){
            $id = $info->id;
        }

        return $id;
    }
    /**
     * 获取游戏提成
     * @param $platform_id
     * @param $product_id
     * @param $sell_amount
     * @param $param
     * @return float|int|mixed
     */
    public function getProductCommissionByPlatformIdProductId($platform_id,$product_id,$sell_amount,$param = []){

        $res = 0;
        if($param){
            $where = $param;
        }else{
            $where = [];
        }
        $where[] = ['money_min','<=',$sell_amount];
        $where[] = ['money_max','>=',$sell_amount];

        $game_product_id = $this->getVGPIdByPlatformIdProductId($platform_id,$product_id);

        if(!$game_product_id){
            $where['game_product_id'] = 0;
        }else{
            $where[] = ['game_product_id','in',[$game_product_id,0]];
        }

        $SellerCommissionConfig = new SellerCommissionConfig();

        $info = $SellerCommissionConfig->getOneByWhere($where,'*','game_product_id DESC');

        if($info){
            if($info->commission_rate > 0){
                $res = round($info->commission_rate*$sell_amount,2);
            }elseif($info->commission_num > 0){
                $res = $info->commission_num;
            }
        }
        return $res;
    }
}