<?php


namespace common\server\Platform;

use common\base\BasicServer;
use common\libraries\Common;
use common\sql_server\PlatformGameInfoSqlServer;
use common\sql_server\EveryDayOrderCountSqlServer;
class GameInfoServer extends BasicServer
{


    public $start_time,$end_time,$num,$platform,$time,$interface_url;
    public $return = ['code'=>-1,'data'=>['msg'=>'err']];


    /**
     * @param $params
     */
    public function dealParams($params)
    {

        $this->interface_url = Common::getPlatform();

        $this->time = time();
        $this->platform = isset($params['platform']) ? $params['platform'] : '';
        $this->end_time = isset($params['end_time']) ? strtotime(date('Y-m-d', strtotime($params['end_time']))) : strtotime(date("Y-m-d", $this->time));

        $this->start_time= isset($params['start_time']) ? strtotime(date('Y-m-d', strtotime($params['start_time']))) : strtotime(date("Y-m-d",strtotime("-1 day")));
        $this->num = isset($num) ? $num : 1000;

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



    public function insertPlatformGame($params)
    {
        $return = $this->dealParams($params);
        if($return['code'] != 1){
            return $return;
        }
        //获取平台游戏信息
        $model = new EveryDayOrderCountSqlServer();
        $data = $model->getPlatformGame($this->start_time, $this->end_time);
        if (!empty($data)) {
            $gameModel = new PlatformGameInfoSqlServer();
            $res = $gameModel->insertPlatformGameInfo($data);
        }
    }


    public function platformGameList(array $params)
    {
        $return = $this->dealParams($params);
        if($return['code'] != 1){
            return $return;
        }
        //获取平台列表
        $platformList = Common::getPlatformList();

        if (!empty($this->platform) && in_array($this->platform, array_keys($platformList))){

            $platformInfo = $this->interface_url[$this->platform];

            $key = isset($platformInfo['url_key']) ? $platformInfo['url_key'] :'';

            $sign = md5("start_time={$this->start_time}end_time={$this->end_time}time={$this->time}key={$key}");

            $url = isset($platformInfo['game_list']) ? $platformInfo['game_list'] :''; ;

            if (empty($key) || empty($url)) {
                $this->return['data']['msg'] = 'platform params error!!';
                return $return;
            }
            $data = [
                'start_time' => $this->start_time,
                'end_time' => $this->end_time,
                'platform_id' => $platformInfo['id'],
                'time' => $this->time,
                'page' => 1,
                'num' => $this->num,
                'sign' => $sign
            ];
            $this->game_list($url,$data,$platformInfo['id']);
        }else {

      
            foreach ($platformList as $k=>$v) {

                $platformInfo = $this->interface_url[$v['platform_suffix']];

                if (empty($platformInfo)) continue;
                $key = isset($platformInfo['url_key']) ? $platformInfo['url_key'] :'';

                $sign = md5("start_time={$this->start_time}end_time={$this->end_time}time={$this->time}key={$key}");
                $url = isset($platformInfo['game_list']) ? $platformInfo['game_list'] :''; ;
                if (empty($key) || empty($url)) {
                    continue;
                }
                $data = [
                    'start_time' => $this->start_time,
                    'end_time' => $this->end_time,
                    'platform_id' => $platformInfo['id'],
                    'time' => $this->time,
                    'page' => 1,
                    'num' => $this->num,
                    'sign' => $sign
                ];

                $this->game_list($url,$data,$platformInfo['id']);
            }
        }

        $this->return['code'] = 1;
        $this->return['data'] = 'success';
        return $this->return;
    }

    /**
     * @param $url
     * @param $dataArr
     * @param $platform_id
     * @return bool
     */
    private function game_list($url,$dataArr,$platform_id)
    {
        $tmpTime = time();
        $return_res = $this->getData($url,$dataArr);
        if (!empty($return_res)){
            $dataArray = [];
            foreach ($return_res as $k => $v) {
                $reg_arr['platform_id'] = $platform_id;
                $reg_arr['game_id'] = $v['gid'];
                $reg_arr['game_name'] = empty($v['game_name']) ? '' : $v['game_name'];
                $reg_arr['product_id'] = empty($v['product_id']) ? 0 : $v['product_id'];
                $reg_arr['product_name'] = empty($v['product_name']) ? '' : $v['product_name'];
                $reg_arr['add_admin_id'] =  0;
                $reg_arr['add_time'] =  $tmpTime;
                $reg_arr['edit_admin_id'] =  0;
                $reg_arr['edit_time'] =  0;
                $reg_arr['static'] =  empty($v['status']) ? 2 : 1;
                array_push($dataArray, $reg_arr);
            }

            if (!empty($dataArray)) {
                $gameModel = new PlatformGameInfoSqlServer();
                $res = $gameModel->insertPlatformGameInfo($dataArray);
            }
            unset($dataArray);
            if ($this->num == count($return_res)){
                $start_time = $dataArr['start_time'];
                $end_time = $dataArr['end_time'];
                $time = $dataArr['time'];
                $num = $dataArr['num'];
                $sign = $dataArr['sign'];
                $dataArr = [
                    'start_time' => $start_time,
                    'end_time' => $end_time,
                    'platform_id' => $platform_id,
                    'time' => $time,
                    'page' => $dataArr['page'] + 1,
                    'num' => $num,
                    'sign' => $sign
                ];
                sleep(1);
                $this->game_list($url, $dataArr, $platform_id);
            }else{
                if (!empty($res)) {
                    return true;
                } else {
                    return false;
                }
            }
        }else{
            return false;
        }
    }

    /**
     * @param $url
     * @param array $opt
     * @return array|mixed
     */
    private function getData($url,$opt=[])
    {
        $return_str = Common::curl_init_post($url,$opt,30);

        $data = json_decode($return_str,1);
        if($data['state']['code']==1){
            return $data['data'];
        } else {
            //需要记录错误信息
            return [];
        }

    }
}