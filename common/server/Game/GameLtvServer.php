<?php


namespace common\server\Game;


use common\base\BasicServer;
use common\libraries\{Logger,Curl,Common};


class GameLtvServer extends BasicServer
{
    public static function getList($data)
    {

        $data = self::dealData($data);
        $res = self::gety9cqLtv($data);
        return $res;

    }

    private static function dealData($data)
    {
        $data['start_time'] = strtotime($data['start_time']);
        $data['end_time'] = strtotime($data['end_time']);
        return $data;
    }

    //获取y9传奇的ltv数据
    private static function gety9cqLtv($data)
    {
        $new_array = [];
        $res = '{}';

        if(file_exists(EXTEND_PATH.'/GamekeySign/y9cqjh.php')){

            include_once EXTEND_PATH.'/GamekeySign/y9cqjh.php';

            if(class_exists('y9cqjh')){
                $obj = new \y9cqjh();

                if(method_exists($obj,'ltvList')){
                    $res = $obj->ltvList($data);

                }
            }

        }

        $res = json_decode($res,1);

        if(!empty($res['data'])){

            if($data['type'] == 1){
                foreach($res['data']['ltv'] as $k=>$v){

                    foreach($v as $k1=>$v1){
                        $tmp = [];
                        $tmp['open_time'] = $k;
                        $tmp['sid'] = $k1;
                        $tmp['count'] = count($v1);

                        foreach ($v1 as $key=>$value){
                            $tmp['day-'.$key] = $value;
                        }
                        array_push($new_array,$tmp);
                    }
                }
            }elseif($data['type'] == 2){

                foreach($res['data']['least'] as $k=>$v){

                    foreach($v as $k1=>$v1){
                        $tmp = [];
                        $tmp['open_time'] = $k;
                        $tmp['sid'] = $k1;
                        $tmp['count'] = count($v1);

                        foreach ($v1 as $key=>$value){
                            $tmp['day-'.$key] = $value;
                        }
                        array_push($new_array,$tmp);
                    }
                }

            }
        }

        return $new_array;
    }
}