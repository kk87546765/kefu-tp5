<?php
/**
 * 系统
 */
namespace common\server\Statistic;


use common\base\BasicServer;
use common\libraries\Curl;
use common\model\db_customer\PlatformList as PlatformListModel;
use common\model\db_statistic\PlatformGameInfo;
use common\model\db_statistic\GameProduct;
use common\model\db_statistic\SellerCommissionConfig;
use common\server\SysServer;



class GameProductServer extends BasicServer
{
    /**
     * 根据子类game_id获取游戏产品信息
     * @param $game_id
     * @param $platform_id
     * @return object
     */
    public static function getPlatformGameInfoByGameId($game_id,$platform_id){


        $where = compact('game_id','platform_id');

        $model = new PlatformGameInfo();

        return $model->where($where)->find();
    }

    /**
     * 获取 vip_game_product表主键id
     * @param $platform_id
     * @param $product_id
     * @return int|mixed
     */
    public static function getVGPIdByPlatformIdProductId($platform_id,$product_id){

        $where = [];
        $where['platform_id'] = $platform_id;
        $where['product_id'] = $product_id;

        $GameProduct = new GameProduct();

        $info = $GameProduct->where($where)->find();

        $id = 0;

        if($info){
            $id = $info->id;
        }

        return $id;
    }

    /**
     * @param array $data
     * @return array
     */
    public static function getGameProductList($data=array())
    {
        $conditions = '1 ';
        $bind = $result = [];
        foreach ($data as $k=>$v) {
            if (is_array($v) && !empty($v)) {
                $conditions .= "and $k in ({".$k.":array}) ";
                $bind[$k] = $v;
            }elseif (is_array($v) && empty($v)) {
                continue;
            }else {
                $conditions .= "and $k = :$k: ";
                $bind[$k] = $v;
            }
        }

        if (empty($bind)) {
            $res = GameProduct::all();
        }else {
            $res = GameProduct::all([
                'conditions' =>$conditions,
                'bind' => $bind,
            ]);
        }

        if (!empty($res)) $result = $res->toArray();
        return $result;
    }

    /**
     * 获取产品列表
     * @param $where
     * @return mixed
     */
    public static function getProductList($where)
    {
        $model = new GameProduct();
        $list = $model
            ->field('id,platform_id,product_id,concat(platform_id,"_",product_id) as p_p,product_name')
            ->where($where)
            ->order('platform_id desc,product_id desc')
            ->select()
            ->toArray();
        return $list;
    }

    /**
     * 获取游戏列表 by GameProduct-id
     * @param $id
     * @return array
     */
    public static function getGameListByGPId($id){

        $code = [
            0=>'ok',1=>'no id',2=>'no product info',3=>'no data'
        ];

        if(!$id){
            return ['code'=>1,'msg'=>$code[1]];
        }

        $GameProduct = new GameProduct();

        $info = $GameProduct->where(['id'=>$id])->find();

        if(!$info){
            return ['code'=>2,'msg'=>$code[2]];
        }

        $info = $info->toArray();

        $model = new PlatformGameInfo();

        $where = getDataByField($info,['platform_id','product_id']);

        $field = '
            platform_id
            ,product_id
            ,game_id
            ,concat(platform_id,"_",product_id) as p_p
            ,concat(platform_id,"_",game_id) as p_g
            ,game_name
        ';

        $list = $model->field($field)->where($where)->order('platform_id desc,game_id desc')->select()->toArray();

        if(!$list){
            return ['code'=>3,'msg'=>$code[3]];
        }

        return ['code'=>0,'data'=>$list];
    }

    /**
     * 获取游戏提成
     * @param $platform_id
     * @param $product_id
     * @param $sell_amount
     * @param $param
     * @return float|int|mixed
     */
    public static function getProductCommissionByPlatformIdProductId($platform_id,$product_id,$sell_amount,$param = []){

        $res = 0;
        if($param){
            $where = $param;
        }else{
            $where = [];
        }
        $where[] = ['money_min','<=',$sell_amount];
        $where[] = ['money_max','>=',$sell_amount];

        $game_product_id = self::getVGPIdByPlatformIdProductId($platform_id,$product_id);

        if(!$game_product_id){
            $where['game_product_id'] = 0;
        }else{
            $where[] = ['game_product_id','in',[$game_product_id,0]];
        }

        $SellerCommissionConfig = new SellerCommissionConfig();

        $info = $SellerCommissionConfig->where(setWhereSql($where,''))->order('game_product_id DESC')->find();

        if($info){
            if($info->commission_rate > 0){
                $res = round($info->commission_rate*$sell_amount,2);
            }elseif($info->commission_num > 0){
                $res = $info->commission_num;
            }
        }
        return $res;
    }

    public static function updateProductInfo($platformInfo,$param){

        $time = time();
        $num = 10000;

        $platform_id = getArrVal($platformInfo,'platform_id',0);
        $platform_config = $platformInfo['config'];
        $key = $platform_config['url_key'];
        $url = $platform_config['game_product_list'];

        if (empty($key) || empty($url)) {
            return ['code'=>1,'msg'=>'no key or no url'];
        }

        $sign = self::getGameProductSign($param['start_time'],$param['end_time'],$time,$platform_config['url_key']);

        $data = getDataByField($param,['start_time','end_time']);
        $data = array_merge($data,compact('num','time','sign'));
        $data['platform_id'] = $platform_id;
        $data['page'] = getArrVal($param,'page',1);


        $return_res = self::getData($url,$data);

        sleep(1);

        if($return_res['code'] != 0) return $return_res;

        $dataArray = [];
        $model = new GameProduct();

        $data_list = getArrVal($return_res,'data',[]);

        if(!$data_list){
            return ['code'=>1,'msg'=>'data list'];
        }

        foreach ($data_list as $k => $v) {
            $this_product_id = getArrVal($v,'product_id',0);
            if(!$this_product_id) continue;
            $where = [];
            $where['platform_id'] = $platform_id;
            $where['product_id'] = $this_product_id;

            $this_info = $model->where($where)->find();

            if(!$this_info){
                $reg_arr = [];
                $reg_arr['platform_id'] = $platform_id;
                $reg_arr['product_id'] = $this_product_id;
                $reg_arr['product_name'] = empty($v['product_name']) ? '' : $v['product_name'];
                $reg_arr['add_time'] =  $time;
                $reg_arr['static'] =  empty($v['status']) ? 2 : 1;
                array_push($dataArray, $reg_arr);
                continue;
            }

            $save_data = [];

            if(!empty($v['product_name']) && $this_info->product_name != $v['product_name']){
                $save_data['product_name'] = $v['product_name'];
            }

            if(!empty($v['status']) && $this_info->static != $v['status']){
                $save_data['static'] = $v['status'];
            }

            if(!$save_data) continue;

            $this_info->save($save_data);
        }

        if (!empty($dataArray)) {
            $res = $model->insertAll($dataArray);
        }

        $res = ['code'=>0,'data'=>['ok:'.$platform_id]];

        if(count($data_list) == $num){
            $param['page'] = $data['page']+1;
            $res['data'] = array_merge($res['data'],self::updateProductInfo($platformInfo,$param));
        }

        return $res;
    }

    public static function updateGameInfo($platformInfo,$param){

        $time = time();
        $num = 10000;

        $platform_id = getArrVal($platformInfo,'platform_id',0);
        $platform_config = $platformInfo['config'];
        $key = $platform_config['url_key'];
        $url = $platform_config['game_list'];

        if (empty($key) || empty($url)) {
            return ['code'=>1,'msg'=>'no key or no url'];
        }

        $sign = self::getGameProductSign($param['start_time'],$param['end_time'],$time,$platform_config['url_key']);

        $data = getDataByField($param,['start_time','end_time']);
        $data = array_merge($data,compact('num','time','sign'));
        $data['platform_id'] = $platform_id;
        $data['page'] = getArrVal($param,'page',1);

        $return_res = self::getData($url,$data);

        sleep(1);

        if($return_res['code'] != 0) return $return_res;

        $dataArray = [];
        $model = new PlatformGameInfo();

        $data_list = getArrVal($return_res,'data',[]);

        if(!$data_list){
            return ['code'=>1,'msg'=>'data list'];
        }

        foreach ($data_list as $k => $v) {
            $this_game_id = getArrVal($v,'gid',0);
            if(!$this_game_id) continue;
            $where = [];
            $where['platform_id'] = $platform_id;
            $where['game_id'] = $this_game_id;

            $this_info = $model->where($where)->find();

            if(!$this_info){
                $reg_arr = getDataByField($v,['game_name','product_id','product_name']);
                $reg_arr['game_id'] = $this_game_id;
                $reg_arr['platform_id'] = $platform_id;
                $reg_arr['add_time'] =  $time;
                $reg_arr['static'] =  empty($v['status']) ? 2 : 1;
                array_push($dataArray, $reg_arr);
                continue;
            }

            $save_data = [];

            if(!empty($v['game_name']) && $this_info->game_name != $v['game_name']){
                $save_data['game_name'] = $v['game_name'];
            }

            if(!empty($v['product_id']) && $this_info->product_id != $v['product_id']){
                $save_data['product_id'] = $v['product_id'];
            }

            if(!empty($v['product_name']) && $this_info->product_name != $v['product_name']){
                $save_data['product_name'] = $v['product_name'];
            }

            if(!empty($v['status']) && $this_info->static != $v['status']){
                $save_data['static'] = $v['status'];
            }

            if(!$save_data) continue;

            $this_info->save($save_data);
        }

        if (!empty($dataArray)) {
            $res = $model->insertAll($dataArray);
        }

        $res = ['code'=>0,'data'=>['ok:'.$platform_id]];

        if(count($data_list) == $num){
            $param['page'] = $data['page']+1;
            $res['data'] = array_merge($res['data'],self::updateGameInfo($platformInfo,$param));
        }

        return $res;
    }


    /**
     * @param $url
     * @param array $opt
     * @return array|mixed
     */
    private static function getData($url,$opt=[])
    {
        $return_str = Curl::post($url,urldecode(http_build_query($opt)));

        if(!$return_str){
            return ['code'=>1,'data'=>['msg'=>'接口返回错误']];
        }

        $data = json_decode($return_str,1);

        if($data['state']['code']==1){
            return ['code'=>0,'data'=>$data['data']];
        } else {
            //需要记录错误信息
            return ['code'=>1,'data'=>['msg'=>$data['state']['msg']]];
        }
    }

    private static function getGameProductSign($start_time,$end_time,$time,$key){
        return md5("start_time={$start_time}end_time={$end_time}time={$time}key={$key}");
    }
}
