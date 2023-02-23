<?php


namespace common\server\UpdateServerOpenTime;

use common\base\BasicServer;
use common\libraries\Common;
use common\server\Game\BlockServer;
use common\sql_server\ServerListSqlServer;

class UpdateServerOpenTimeServer extends BasicServer
{

    public $return = ['code'=>-1,'data'=>['msg'=>'err']];

    protected $gameToProduct = [
        'shenqi'=>['platform_id'=>4,'product_id'=>13]
//        'shenqi2'=>['platform_id'=>4,'product_id'=>13],['platform_id'=>6,'product_id'=>43],
//        'shenqi3'=>['platform_id'=>4,'product_id'=>13],['platform_id'=>6,'product_id'=>43],
//        'shenqi4'=>['platform_id'=>4,'product_id'=>13],['platform_id'=>6,'product_id'=>43],
    ];
    //线上
    protected $interface_url = [];
    private $key,$time,$platform,$start_time,$end_time,$platform_list;
    /**
     * @param $params
     */
    private function dealParams($params)
    {

        $this->interface_url = Common::getPlatform();

        $this->time = time();

        $this->platform = isset($platform) ? $platform : '';
        $this->platform = 'zw';
        $this->end_time = isset($end_time) ? $end_time : $this->time;

        $this->start_time= isset($start_time) ? $start_time : $this->time-300;

//        $this->platform = $params[0];//平台参数
        $this->key = $this->interface_url[$this->platform]['url_key'];

        if(empty($this->platform)) {
            $this->return['data']['msg'] = 'platform params error';
            return $this->return;

        }
        if ($this->start_time >= $this->end_time){
            $this->return['data']['msg'] = 'start_time must be less than the end_time';
            return $this->return;
        }
        if (!ctype_digit($this->start_time)){
            $this->return['data']['msg'] = 'start_time must be effective timestamp';
            return $this->return;
        }
        if (!ctype_digit($this->end_time)){
            $this->return['data']['msg'] = 'end_time must be effective timestamp';
            return $this->return;
        }
        $this->return['code'] = 1;
        $this->return['data']['msg'] = 'success';
        return $this->return;
    }

    public function updateServerOpenTime($params)
    {

        $return = $this->dealParams($params);

        if($return['code'] != 1){
            return $return;
        }
        $platform_list = common::getPlatformList();

        foreach ($platform_list as $v){

            if($v['platform_suffix'] != 'asjd'){
                $this->platform_list[$v['platform_suffix']] = $v['platform_id'];
            }
        }

        foreach($this->gameToProduct as $k=>$v){

            $platform_suffix = array_search($v['platform_id'], $this->platform_list);

            $server_list = $this->getCpServerList($k,$platform_suffix);

            if($server_list){
                self::updateOpenTime(['server_list'=>$server_list,'platform_id'=>$v['platform_id'],'platform_suffix'=>$platform_suffix,'product_id'=>$v['product_id']]);
            }
        }
        $this->return['code'] = 1;
        $this->return['data']['msg'] = 'success';
        return $this->return;
    }


    public static function updateOpenTime($data)
    {
        if(empty($data['server_list'])){
            return false;
        }
        ServerListSqlServer::updateOpenTime($data);
    }

    //获取cp的区服列表
    private function getCpServerList($gameAbbr,$platform_suffix)
    {

        $tmp_game_abbr = $gameAbbr;

        $gamekey = Common::getGameKey();
//        $gamekey = Common::getConfig('gamekey');
        if($gameAbbr == 'shenqi' || $gameAbbr == 'shenqi2' || $gameAbbr == 'shenqi3' || $gameAbbr == 'shenqi4'){
            $gameAbbr = 'shenqi';
        }

        $game_info = $gamekey[$gameAbbr];

        $post_data = [];
        if('shenqi' == $gameAbbr){
            $platform_suffix = '';
        }
        $post_data['tkey'] = $platform_suffix;
        $server_list = [];

        $post_data['gkey'] = $tmp_game_abbr;
        $post_data['start_time'] = mktime(0,0,0,date('m'),date('d')-10,date('Y'));
        $post_data['end_time'] = mktime(0,0,0,date('m'),date('d'),date('Y'));
        $time = time();
        $post_data['time'] = $time;

        $sign = md5($post_data['gkey'].$post_data['tkey'].$time.$game_info['key']);

        $post_data['sign'] = $sign;

        $data = Common::curl_init_post($game_info['server_list'],$post_data);

        $data = json_decode($data,1);

        if($data['state']['code'] != 100){
            return $server_list;
        }

        $server_list = $data['data'];

        return $server_list;

    }

}