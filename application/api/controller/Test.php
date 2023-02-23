<?php
namespace app\api\controller;


use common\libraries\Common;
use common\libraries\ElasticSearch;
use common\libraries\Ipip\IP4datx;
use common\model\db_customer_youyu\KefuUserRole;
use common\model\db_statistic\EveryDayOrderCount;
use common\model\db_statistic\VipUserInfo;
use common\server\Game\RoleServer;
use common\server\GetBaseInfo\GetBaseInfoServer;
use common\sql_server\GetBaseInfoSqlServer;
use extend\ApiSms;
use extend\Sms\TencentSms;
use think\Db;
use think\Config;
use think\Queue\Job;

class Test extends Base
{
   public function index()
   {

       $es = new ElasticSearch();
       $time = time();
       $dateStart = $time-60;
       $dateEnd = $time;

       $range0['range']['time']['gte']  = $dateStart;

       $range0['range']['time']['lte']  = $dateEnd;

       if($range0){
           $bool['bool']['filter']['bool']['must'][] = $range0;
       }
       $last_month = Common::GetMonth(1);
       $now_month = date('Ym');
       $next_month = Common::GetMonth(0);
       $result = $es->search(
           [
               $es->index_name.'-'.$last_month,
               $es->index_name.'-'.$now_month,
               $es->index_name.'-'.$next_month
           ],
           $bool,
           '',
           ['time'=>['order'=>'desc']],
           1,
           1
       );

       echo json_encode($result);
   }

   public function getEsStat()
   {
       $es = new ElasticSearch();
       $a = $es->getStat();
       var_duMP($a);
   }

    public function updateEveryMount(){

       set_time_limit(0);
        $flag = true;
        $i = 0;
        $limit = 100;
        $model = new EveryDayOrderCount();
        $start_flag = 0;
        while($flag){
            $offset = $i*$limit;

            $sql = "	SELECT
					id
				FROM
					every_day_order_count
					where id>{$start_flag}
					and partition_key is null
				ORDER BY
					id
				LIMIT 0,
				{$limit}";

            $ids = $model->query($sql);

            $start_flag = $ids[count($ids)-1]['id'];


            if(empty($ids)){
                dd('ok');
            }
            $ids = array_column($ids,'id');
            $ids_str = implode(',',$ids);


            $sql = "update every_day_order_count set partition_key = FROM_UNIXTIME(UNIX_TIMESTAMP(`date`),'%Y%m')  where id in({$ids_str})";

            $model->execute($sql);
            $i++;
        }

    }


    public function updateNbcqRoleid()
    {
        set_time_limit(0);
        $model = new KefuUserRole();
        $sql = "select * from db_customer_youyu.kefu_user_role where reg_time>=UNIX_TIMESTAMP('2022-5-1') and id<5244912 and  reg_gid=200124 and role_id like '038%'";
        $res = $model->query($sql);


        foreach($res as $k=>$v){
            $new_role_id = hexdec($v['role_id']);

            $sql = "select * from db_customer_youyu.kefu_user_role where uid = {$v['uid']}  and role_id = '{$new_role_id}' and reg_gid=200124 and reg_time>=UNIX_TIMESTAMP('2022-7-4')  ";

            $res1 = $model->query($sql);
            if(empty($res1)){
                $res2 = $model->save(['role_id'=>$new_role_id],['id'=>$v['id']]);
            }else{
                $model->where(['id'=>$v['id']])->delete();
            }


        }
        echo "ok";
    }



    /**
     * 用户订单接口
     */
    public function userPay()
    {
        set_time_limit(0);

        $params = $this->request->get();

        $obj = new GetBaseInfoServer();

        $return = $obj->dealParams($params);

        if($return['code'] != 1){
            $this->rs = array_merge($this->rs,$return);
            return return_json($this->rs);
        }

        $res = $obj->getUserPayTmp($obj);
        dd($res);
        $this->rs['code'] = $res;
        return return_json($this->rs);

    }

    public function test1(){

        $model = new VipUserInfo();
        $sql = "select * from vip_user_info where platform_id=6 and  game_id=200124 and role_id not like '25%'";
        $res = $model->query($sql);

        if(!empty($res)){

            foreach($res as $k=>$v){
                $tmp_model = new KefuUserRole();
                $sql2 = "select * from db_customer_youyu.kefu_user_role where uid = {$v['uid']}  and role_name = '{$v['role_name']}' and reg_gid=200124 limit 1";

                $res2 = $tmp_model->query($sql2);

                if(!empty($res2)){
                    if($v['role_id'] != $res2[0]['role_id']){
                        $model->save(['role_id'=>$res2[0]['role_id']],['id'=>$v['id']]);
                    }

                }
            }
        }

    }


    function request_by_curl($remote_server, $post_string) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $remote_server);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array ('Content-Type: application/json;charset=utf-8'));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // 线下环境不用开启curl证书验证, 未调通情况可尝试添加该代码
        // curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
        // curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }

    public function test321()
    {
        $url='https://oapi.dingtalk.com/robot/send?access_token=263971d43c8db34cf453d735ace8aba46fc41072e4198a73b5778936606ed0e8';

        $webhook = $url;
        $message="【业务警告】我就是我, 是不一样的烟火";
        $data = array ('at'=>['atMobiles'=>['13660468105']],'msgtype' => 'text','text' => array ('content' => $message));
        $data_string = json_encode($data);

        $result = $this->request_by_curl($webhook, $data_string);
        echo $result;
    }


    public function  test123()
    {
        $objSms = ApiSms::init('tencent');
        $rs = $objSms::sendSms(['15625055357'], ['123321'],'1342129');

        var_duMP($rs);

    }


    public function testAdb()
    {

        set_time_limit(0);
        ini_set('memory_limit', '4096M');
        $config = [
            'type'=>'mysql',
            'hostname'=>'am-wz9io856e5t1fh25w167320.ads.aliyuncs.com',
            'username'=>'kefurw',
            'password'=>'TFNgl!p4VJZdaq3$dS6j',
            'database'=>'adb_demo2',
            'charset'=>'utf8',
            'params'=>[
                \PDO::ATTR_EMULATE_PREPARES => true,
            ]

        ];
        $db = Db::connect($config);
        $db->execute('set names utf8');

        $log = RUNTIME_PATH.'adb_time.txt';
        $start_time = file_get_contents($log);
        if(empty($start_time)){
            $start_time = strtotime('2022-08-10 10:58:15');
        }

        $start_time = $start_time;
        $end_time = $start_time+60;
        $result = $this->getElasticSearchSuggestInfo($start_time,$end_time);

        $new_arr = array_chunk($result,1000);
        $res = false;

        foreach($new_arr as $k2=>$v2){

            $sql = "insert into adb_demo2.chat_message(`source_id`,`gkey`,`tkey`,`sid`,`uid`,`uid_str`,`uname`,`uname2`,`roleid`,`type`,`content`,`content2`,`time`,

                    `ip`,`to_uid`,`to_uname`,`to_uname2`,`role_level`,`imei`,`count_money`,`openid`,`is_sensitive`,`sensitive_keyword`,`request_time`,`date`) values";
            foreach($v2 as $k=>$v){

                $v['date'] = date('Y-m');
                $v['gkey'] = !empty($v['gkey']) ?  $v['gkey'] : '';
                $v['tkey'] = !empty($v['tkey']) ?  $v['tkey'] : '';
                $v['sid'] = !empty($v['sid']) ?  $v['sid'] : '';
                $v['uid'] = !empty($v['uid']) ?  $v['uid'] : 0;
                $v['uid_str'] = !empty($v['uid_str']) ?  $v['uid_str'] : '';
                $v['uname'] = !empty($v['uname']) ?  $v['uname'] : '';
                $v['roleid'] = !empty($v['roleid']) ?  $v['roleid'] : '';
                $v['type'] = !empty($v['type']) ?  $v['type'] : 0;
                $v['content'] = !empty($v['content']) ?  $v['content'] : '';
                $v['content2'] = !empty($v['content2']) ?  $v['content2'] : '';
                $v['time'] = !empty($v['time']) ?  $v['time'] : 0;
                $v['ip'] = !empty($v['ip']) ?  $v['ip'] : '';
                $v['to_uid'] = !empty($v['to_uid']) ?  $v['to_uid'] : 0;
                $v['to_uname'] = !empty($v['to_uname']) ?  $v['to_uname'] : '';
                $v['role_level'] = !empty($v['role_level']) ?  $v['role_level'] : 0;
                $v['count_money'] = !empty($v['count_money']) ?  $v['count_money'] : 0;
                $v['imei'] = !empty($v['imei']) ?  $v['imei'] : '';
                $v['imei'] = !empty($v['imei']) ?  $v['imei'] : '';
                $v['openid'] = !empty($v['openid']) ?  $v['openid'] : 0;
                $v['is_sensitive'] = !empty($v['is_sensitive']) ?  $v['is_sensitive'] : 0;
                $v['sensitive_keyword'] = !empty($v['sensitive_keyword']) ?  $v['sensitive_keyword'] : '';
                $v['request_time'] = !empty($v['request_time']) ?  $v['request_time'] : 0;

                $sql .= "('{$v['id']}'," .
                    "'{$v['gkey']}'," .
                    "'{$v['tkey']}'," .
                    "'{$v['sid']}'," .
                    " {$v['uid']}," .
                    "'{$v['uid_str']}'," .
                    "'{$v['uname']}'," .
                    "'{$v['uname']}'," .
                    "'{$v['roleid']}'," .
                    " {$v['type']}," .
                    "'{$v['content']}'," .
                    "'{$v['content2']}'," .
                    "'{$v['time']}'," .
                    "'{$v['ip']}'," .
                    " {$v['to_uid']}," .
                    " '{$v['to_uname']}'," .
                    " '{$v['to_uname']}'," .
                    " {$v['role_level']}," .
                    "'{$v['imei']}'," .
                    " {$v['count_money']}," .
                    " {$v['openid']}," .
                    " {$v['is_sensitive']}," .
                    "'{$v['sensitive_keyword']}'," .
                    "'{$v['request_time']}'," .
                    "'{$v['date']}' ),";



            }
            $sql = trim($sql,",");

            $res = $db->execute($sql);

        }
        if($res && !empty($result)){
            file_put_contents($log,$end_time);
        }




    }

    //通过elasticsearch获取聊天信息
    public function getElasticSearchSuggestInfo($start_time,$end_time)
    {
        $search = new ElasticSearch();
        $time = time();

        $dateStart = $start_time?:$time-60;
        $dateEnd = $end_time?:$time;

        $range0['range']['time']['gte']  = $dateStart;

        $range0['range']['time']['lt']  = $dateEnd;

        if($range0){
            $bool['bool']['filter']['bool']['must'][] = $range0;
        }

        $now_month = date('Ym');
        $now_month = '202207';
        $i = 1;
        $data = [];
        $limit = 5000;

        $result = $search->search(
            [
                $search->index_name.'-'.$now_month
            ],
            $bool,
            '',
            ['time'=>['order'=>'desc']],
            1,
            $limit,
            1
        );

        $scroll_id = $result['scroll_id'];
        $data = $result['data'];

        while(true){

            $res = $search->scroll($scroll_id);
            $scroll_id = $res['scroll_id'];
            if( empty($res['data'])){

                break;

            };

            $data = array_merge($data,$res['data']);
            $i++;


        }

        return $data;
    }


    public function testRds()
    {
        $db_info = Config::get('database')['rds_test'];

        $model = Db::connect($db_info);
        $sql = "insert into test1 value(1,'2')";
        $res = $model->query($sql);
        var_duMP($res);
    }


    public function changenb()
    {

        $uid = $this->request->get('uid/d', 0);
        $uid_to_openid = $this->request->get('uid_to_openid/d', 1);

        include_once(EXTEND_PATH."/GamekeySign/nbcq2youyu.php");

        $game_model = new \nbcq2youyu();
        if($uid_to_openid == 1){
            $res = $game_model->sdkid_to_uid_url($uid);
        }else{
            $res = $game_model->uid_to_sdkid_url($uid);
        }


        var_duMP($res);exit;
    }


    public function test222(){
        $a = IP4datx::find('66.117.31.255');

        if(empty($a[2]) || $a[2] != '广州'){
            echo 222;exit;
        }
        var_duMP($a);
    }


    public function blockChatByTS()
    {
        set_time_limit(0);
        $time = time();
        $model = new EveryDayOrderCount();
        $sql = 'select * from test.test1 order by uid asc';
        $res = $model->query($sql);
        foreach($res as $k=>&$v){
            $v['server_id'] = "S". $v['server_id'];
        }

        $chat_data = [];
        $chat['gkey'] = 'ts';
        $chat['tkey'] = 'bx';
//        $chat['type'] = 2;
        $chat['ban_time'] = 0;
        $chat['addtime'] = $time;

        $i = 0;
        foreach($res as $k1=>$v1){

            $tmp_chat = $chat;
            $tmp_chat['roleid'] = $v1['role_id'];
            $tmp_chat['sid'] = $v1['server_id'];
            $tmp_chgaat['rolename'] = $v1['uname'];
            $tmp_chat['uid'] = $v1['uid'];

            if($i%100 == 0){
                sleep(1);
            }
//            $tmp_chat['roleid'] = 11010124;
//            $tmp_chat['sid'] = 'S73';
//            $tmp_chat['rolename'] = '地道口味';
//            $tmp_chat['uid'] = 18737318;

            $res = RoleServer::roleChat($tmp_chat,2);

            $i++;

        }



    }


    public function testQueue()
    {

    }





}
