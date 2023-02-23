<?php
/**
 * @author ambi
 * @date 2019/4/17
 */

namespace common\server\GameProduct;
use common\base\BasicServer;
use common\libraries\Common;
use common\sql_server\GameProductSqlServer;
class GameProductScriptsServer extends BasicServer
{


    public function dealParams($params)
    {
        $return = ['code'=>-1,'data'=>'err'];

        $this->interface_url = Common::getPlatform();

        $this->time = time();
        $this->platform = isset($platform) ? $platform : '';
        $this->end_time = isset($end_time) ? strtotime(date('Y-m-d', strtotime($end_time))) : strtotime(date("Y-m-d", $this->time));

        $this->start_time= isset($start_time) ? strtotime(date('Y-m-d', strtotime($start_time))) : strtotime(date("Y-m-d",strtotime("-1 day")));
        $this->num = isset($num) ? $num : 1000;


        if ($this->start_time >= $this->end_time){

            $return['data'] = 'start_time must be less than the end_time';

            return $return;

        }
        if (!ctype_digit($this->start_time)){

            $return['data'] = 'start_time must be effective timestamp';

            return $return;

        }
        if (!ctype_digit($this->end_time)){

            $return['data'] = 'end_time must be effective timestamp';

            return $return;

        }

        $return['code'] = 0;
        $return['data'] = 'ok';

        return $return;


    }


    public function getGameProduct($obj)
    {


        $tmpTime = time();
        //获取平台列表
        $platformList = Common::getPlatform();
        if (!empty($obj->platform) && in_array($obj->platform, array_keys($platformList))){
            $platformInfo = $obj->interface_url[$obj->platform];
            $key = $platformInfo['url_key'];
            $sign = md5("start_time={$obj->start_time}end_time={$obj->end_time}time={$obj->time}key={$key}");
            $url = $platformInfo['game_product_list'];


            if (empty($key) || empty($url)) {
                $result = ['code'=>1, 'msg'=>'platform params error!!'];
                return $result;
            }
            $data = [
                'start_time' => $obj->start_time,
                'end_time' => $obj->end_time,
                'platform_id' => $platformInfo['id'],
                'time' => $obj->time,
                'page' => 1,
                'num' => $obj->num,
                'sign' => $sign
            ];
            $this->product_list($url,$data,$platformInfo['id']);
        }else {


            foreach ($platformList as $k=>$v) {

                $platformInfo = $obj->interface_url[$v['field']];

                if (empty($platformInfo) || !isset($platformInfo['url_key'])) continue;

                $key = $platformInfo['url_key'];

                $sign = md5("start_time={$obj->start_time}end_time={$obj->end_time}time={$obj->time}key={$key}");
                $url = $platformInfo['game_product_list'];
                if (empty($key) || empty($url)) {
                    continue;
                }
                $data = [
                    'start_time' => $obj->start_time,
                    'end_time' => $obj->end_time,
                    'platform_id' => $platformInfo['id'],
                    'time' => $obj->time,
                    'page' => 1,
                    'num' => $obj->num,
                    'sign' => $sign
                ];

                $this->product_list($url,$data,$platformInfo['id']);
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
    private function product_list($url,$dataArr,$platform_id)
    {
        $tmpTime = time();

        $return_res = $this->getData($url,$dataArr);
        if (!empty($return_res)){
            $dataArray = [];
            foreach ($return_res as $k => $v) {

                $reg_arr['platform_id'] = $platform_id;
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
                $gameModel = new GameProductSqlServer();
                $res = $gameModel->insertGameProductInfo($dataArray);
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
                $this->product_list($url, $dataArr, $platform_id);
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
        list($res,$return_str) = $this->post_curl($url,urldecode(http_build_query($opt)),3);
//        var_dump($return_str);die;
        if($res==200){
            $data = json_decode($return_str,1);
            if($data['state']['code']==1){
                return $data['data'];
            } else {
                //需要记录错误信息
                //return $data['state']['msg'];
            }
        } else {
            //说明请求网络有问题，
            return [];
        }
    }


    /**
     * 模拟post提交函数
     * @param $post_url
     * @param $post_arr
     * @param int $timeOut
     * @param bool $cookie
     * @param bool $header
     * @return array
     */
    protected function post_curl($post_url, $post_arr, $timeOut = 0, $cookie = false, $header = false)
    {
        $timeOut = $timeOut ?: VALUE_TIMEOUT;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $post_url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_arr);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        if (strpos($post_url, 'https') !== false) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        }
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeOut);
        $header && curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        $cookie && curl_setopt($ch, CURLOPT_COOKIE, $cookie);
        $response = curl_exec($ch);    //这个是读到的值
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        unset($ch);
        return array($httpCode, $response);
    }
}