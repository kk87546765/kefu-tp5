<?php


namespace common\server\Platform;

use common\base\BasicServer;
use common\libraries\Common;
use common\sql_server\ChannelInfoSqlServer;

class ChannelInfoServer extends BasicServer
{

    public $interface_url,$time,$platform,$num,$start_time,$end_time;
    public $return = ['code'=>-1,'data'=>'err'];
    /**
     * @param $params
     */
    private function dealParams($params)
    {

        $this->interface_url = Common::getPlatform();

        $this->time = time();
        $this->platform = isset($params['platform']) ? $params['platform'] : '';
        $this->end_time = isset($params['end_time']) ? strtotime(date('Y-m-d', strtotime($params['end_time']))) : strtotime(date("Y-m-d", $this->time));

        $this->start_time= isset($params['start_time']) ? strtotime(date('Y-m-d', strtotime($params['start_time']))) : strtotime(date("Y-m-d",strtotime("-1 day")));
        $this->num = isset($params['num']) ? $params['num'] : 1000;

        if ($this->start_time >= $this->end_time){
            $this->return['data'] = 'start_time must be less than the end_time';
            return $this->return;
        }
        if (!ctype_digit($this->start_time)){
            $this->return['data'] = 'start_time must be effective timestamp';
            return $this->return;
        }
        if (!ctype_digit($this->end_time)){
            $this->return['data'] = 'end_time must be effective timestamp';
            return $this->return;
        }

        $this->return['code'] = 1;
        $this->return['data'] = 'success';

        return $this->return;

    }


    public function  platformChannelInfo($params)
    {
        //获取平台列表
        $platformList = Common::getPlatformList();

        $return = $this->dealParams($params);

        if($return['code'] != 1){
            return $return;
        }

        $tmpTime = time();
        $start_time = $this->start_time;
        $end_time = $this->end_time;
        $time = $this->time;
        $num = $this->num;

        $platfrom_id = Common::getPlatformInfoBySuffixAndCache($this->platform)['platform_id'];


        if (!empty($this->platform) && in_array($platfrom_id, array_keys($platformList))){
            $platformInfo = $this->interface_url[$this->platform];
            $key = $platformInfo['url_key'];
            $sign = md5("start_time={$start_time}end_time={$end_time}time={$time}key={$key}");
            $url = $platformInfo['get_channel_info'];
            if (empty($key) || empty($url)) {
                $result = ['code'=>1, 'msg'=>'platform params error!!'];
                echo json_encode($result);die;
            }
            $data = [
                'start_time' => $start_time,
                'end_time' => $end_time,
                'platform_id' => $platformInfo['id'],
                'time' => $time,
                'page' => 1,
                'num' => $num,
                'sign' => $sign
            ];
            $this->channel_list($url,$data,$platformInfo['id']);
        }else {

            foreach ($platformList as $k=>$v) {
//                $platformInfo = $this->interface_url[$v['suffix']];
//                if (empty($platformInfo)) continue;
                $key = $v['url_key'];

                $sign = md5("start_time={$start_time}end_time={$end_time}time={$time}key={$key}");
                $url = $v['get_channel_info'];
                if (empty($key) || empty($url)) {
                    continue;
                }
                $data = [
                    'start_time' => $start_time,
                    'end_time' => $end_time,
                    'platform_id' => $v['platform_id'],
                    'time' => $time,
                    'page' => 1,
                    'num' => $num,
                    'sign' => $sign
                ];

                $this->channel_list($url,$data,$v['platform_id']);
            }
        }
        $result['runTime'] = time()-$tmpTime;
        echo json_encode($result);die;
    }


    /**
     * @param $url
     * @param $dataArr
     * @param $platform_id
     * @return bool
     */
    private function channel_list($url,$dataArr,$platform_id)
    {
        $return_res = $this->getData($url,$dataArr);

        if (!empty($return_res)){
            $dataArray = [];
            foreach ($return_res as $k => $v) {
                $reg_arr['platform_id'] = $platform_id;
                $reg_arr['channel_id'] = $v['channel_id'];
                $reg_arr['channel_name'] = empty($v['channel_name']) ? '' : $v['channel_name'];
                $reg_arr['channel_ver'] = empty($v['channel_ver']) ? '' : $v['channel_ver'];
                $reg_arr['game_name'] = empty($v['game_name']) ? '' : $v['game_name'];
                $reg_arr['game_id'] = empty($v['game_id']) ? 0 : $v['game_id'];
                $reg_arr['product_id'] = empty($v['product_id']) ? 0 : $v['product_id'];
                $reg_arr['product_name'] = empty($v['product_name']) ? '' : $v['product_name'];
                $reg_arr['edit_time'] = time();
                $reg_arr['status'] =  $v['status'];
                array_push($dataArray, $reg_arr);
            }

            if (!empty($dataArray)) {

                $res = ChannelInfoSqlServer::insertPlatformChannelInfo($dataArray);
            }
            unset($dataArray);

            if ($this->num == count($return_res)){
                $start_time = $dataArr['start_time'];
                $end_time = $dataArr['end_time'];
                $time = $dataArr['time'];
                $num = $dataArr['num'];

                $platformInfo = $this->interface_url[$this->platform];
                $key = $platformInfo['url_key'];
                $sign = md5("start_time={$start_time}end_time={$end_time}time={$time}key={$key}");
                $sign = $sign;


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
                $this->channel_list($url, $dataArr, $platform_id);
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