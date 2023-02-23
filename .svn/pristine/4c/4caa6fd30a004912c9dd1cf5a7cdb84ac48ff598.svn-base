<?php
/**
 * 系统
 */
namespace common\libraries;

class QcConversation
{
    protected $qd_data = [
        'play_id'=>'',
    ];

    /**
     * 企点会话数据匹配
     * @param string $str 会话内容
     * |-----------------------|
     * 日期    2021-03-24
     * |-----------------------|
     * 张学基(3007975776)    20:10:45
     * 亲 抱歉呢 前2000只能算VIP认证金额  不算返利额度的。
     *
     * 未登记/380-空心-8720003800000056(339766448)    20:12:41
     * 没事
     * @param string $play 玩家名字 未登记/380-空心-8720003800000056
     * @param string $kf 客服名字 张学基
     */
    public function getQDText(string $str,string $play,string $kf){

        $dateInfo = $this->getQDDateInfo($str);

        $str = $dateInfo['str'];

        $date = $dateInfo['data'];

        $data = explode($play, $str);

        $new_data = [];

        foreach ($data as $k => $v) {
            $v = trim($v);
            if(preg_match('/'.$kf.'/', $v)){

                $this_data = explode($kf, $v);

                foreach ($this_data as $k2 => $v2) {
                    $v2 = trim($v2);
                    if(!$v2){
                        continue;
                    }
                    if($k2 == 0){
                        $this_msg = $this->getQDPlayMsg($v2,$play,$date);
                        $this_msg['type'] = 1;
                        $new_data[] = $this_msg;
                    }else{
                        $this_msg = $this->getQDPlayMsg($v2,$kf,$date);
                        $this_msg['type'] = 2;
                        $new_data[] = $this_msg;
                    }

                }

            }else{
                if(!$v){
                    continue;
                }
                $this_msg = $this->getQDPlayMsg($v,$play,$date);
                $this_msg['type'] = 1;
                $new_data[] = $this_msg;
            }
        }

        $this->qd_data['text'] = $new_data;

        return $this->qd_data;

    }

    /**
     * 获取会话日期并清除日期相关字符
     * @param $str
     * @return array
     */
    protected function getQDDateInfo($str){

        $preg = '/[\s\S]*\|\-\-\-\-\-\-\-\-\-\-\-\-\-\-\-\-\-\-\-\-\-\-\-\|/';//\r\n

        $res = preg_match($preg, $str,$data);

        $this_data = '';

        if(!$res){
            return ['str'=>$str,'data'=>$this_data];
        }

        $this_data = $data[0];

        $str = str_replace($this_data, '', $str);

        $preg = '/(([0-9]{3}[1-9]|[0-9]{2}[1-9][0-9]{1}|[0-9]{1}[1-9][0-9]{2}|[1-9][0-9]{3})-(((0[13578]|1[02])-(0[1-9]|[12][0-9]|3[01]))|((0[469]|11)-(0[1-9]|[12][0-9]|30))|(02-(0[1-9]|[1][0-9]|2[0-8]))))|((([0-9]{2})(0[48]|[2468][048]|[13579][26])|((0[48]|[2468][048]|[3579][26])00))-02-29)/';

        $res = preg_match($preg, $this_data,$data);

        if($res){
            $this_data = $data[0];
        }

        return ['str'=>$str,'data'=>$this_data];
    }

    /**
     * 每条消息拆分【name(名称),time(发送时间),text(消息),type(消息类型 1玩家 2客服)】
     * @param $str
     * @param $name
     * @param $date
     * @return array
     */
    protected function getQDPlayMsg($str,$name,$date){

        $this_msg = [];

        $id_info = $this->getQDPlayId($str);

        if(!$this->qd_data['play_id'] && $id_info['data']){
            $this->qd_data['play_id'] = $id_info['data'];
        }

        $this_msg['name'] = emoji_encode($name.'('.$id_info['data'].')');

        $str = $id_info['str'];

        $time_info = $this->getQDTime($str,$date);

        $this_msg['time'] = $time_info['data'];

        $this_msg['text'] = emoji_encode($time_info['str']);

        return $this_msg;
    }

    /**
     * 获取玩家id
     * @param $str
     * @return array
     */
    protected function getQDPlayId($str){
        $preg = '/^\([0-9]+\)/';

        $this_data = '';

        $res = preg_match($preg, $str,$data);

        if(!$res){
            return ['str'=>$str,'data'=>$this_data];
        }

        $this_data_str = $data[0];

        $str = str_replace($this_data_str, '', $str);

        $str = trim($str);

        $this_data = preg_replace('/[\(\)]/', '', $this_data_str);

        return ['str'=>$str,'data'=>$this_data];
    }

    /**
     * 获取 消息发送时间
     * @param $str
     * @param $date
     * @return array
     */
    protected function getQDTime($str,$date){

        $preg = '/([01]?\d|2[0-3]):[0-5]?\d:[0-5]?\d/';

        $res = preg_match($preg, $str,$data);

        $this_data = '';

        if(!$res){
            return ['str'=>$str,'id'=>$this_data];
        }

        $this_data_str = $data[0];

        $str = str_replace($this_data_str, '', $str);

        $str = preg_replace('/^[\r\n]+/', '', $str);
        $str = preg_replace('/[\r\n]+$/', '', $str);

        $this_data = strtotime($date.' '.$this_data_str);

        return ['str'=>$str,'data'=>$this_data];
    }
}
