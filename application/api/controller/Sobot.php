<?php
namespace app\api\controller;
use common\server\Qc\SobotServer;
use common\server\SysServer;
use common\server\Qc\QcQuestionServer;
use think\Log;


class Sobot extends Base
{
    public $config = [
        'company_id'=>0,
        'app_key'=>'test',
        'open'=>0,
        'check_sign'=>0,
        'set_log'=>0,
        'company_list'=>[],
    ];

    private $code = [
        1=>'没有配置参数',
        2=>'接口停止使用',
        3=>'签名校验错误',
        4=>'type错误',
        5=>'没有配置公司信息',
    ];

    public function index()
    {
        $post_data = file_get_contents('php://input', 'r');

        $post_data = json_decode($post_data,true);

        $type = isset($post_data['type'])?$post_data['type']:'';

        $content = isset($post_data['content'])?$post_data['content']:'';

        $sys_code = isset($post_data['sys_code'])?$post_data['sys_code']:'';

//        $type = $this->req->getPost('type','trim','');
//        $content = $this->req->getPost('content');
//        $sys_code = $this->req->getPost('sys_code','trim','');

        $bast_config = $this->base_config;

        if(!$bast_config || !isset($bast_config['sobot']) || !$bast_config['sobot'] ){
            $this->rs['code'] = 1;
            $this->rs['msg'] = $this->code[1];
            return return_json($this->rs);
        }

        foreach ($bast_config['sobot'] as $k => $v){
            $this->config[$k] = $v;
        }

        if($this->config['set_log']){
            Log::init([
                'path' => RUNTIME_PATH.'api/'.$this->req->controller().'/'.$this->req->action(),
                'filename' => date('Y-m-d') ]);

            $log_data=compact('type','sys_code','content');

            Log::write([
                'tag' => 'post_data',
                'msg' => json_encode($log_data)
            ]);
        }


        if(!$this->config['open']){
            $this->rs['code'] = 2;
            $this->rs['msg'] = $this->code[2];
            return return_json($this->rs);
        }

        if($this->config['check_sign']){
            $this->checkSign();
        }



        //https://www.sobot.com/developerdocs/service/online_service.html#_3-5%E5%9C%A8%E7%BA%BF%E8%81%8A%E5%A4%A9%E6%B6%88%E6%81%AF
        $type_arr = [
            'conversation',//会话消息
            'evaluation',//评价消息
            'user',//用户信息
            'userinfo',//访客信息
            'msg',//聊天消息
            'summary',//服务总结

        ];
        if(!in_array($type,$type_arr)){
            $this->rs['code'] = 4;
            $this->rs['msg'] = $this->code[4];
            return return_json($this->rs);
        }

        if($this->config['set_log']){
            Log::init([
                'path' => RUNTIME_PATH.'api/'.$this->req->controller().'/'.$this->req->action().$type.$this->config['company_id'],
                'filename' => date('Y-m-d') ]);

            $log_data=compact('type','sys_code','content');

            Log::write([
                'tag' => 'post_data',
                'msg' => json_encode($log_data)
            ]);
        }

        $SobotServer = new SobotServer();
//        dd($_POST);
        $res = $SobotServer->$type($content);

        $this->rs['code'] = 0;
        $this->rs['msg'] = $this->code[0];
        return return_json($this->rs);

    }

    public function update20210727(){
        $res = QcQuestionServer::update20210727();

        $this->rs['msg'] = $res['msg'];
        $this->rs['code'] = $res['code'];
        return return_json($this->rs);
    }
    public function update20211025(){
        $res = QcQuestionServer::update20211025();
        $this->rs['msg'] = $res['msg'];
        $this->rs['code'] = $res['code'];
        return return_json($this->rs);
    }
    /**
     * 检查
     */
    protected function checkSign(){

        $time_stamp = $this->req->header('X-Log-TimeStamp');
        $random_code = $this->req->header('X-Log-RandomCode');
        $sign = $this->req->header('X-Log-Sign');

        if(!$this->config['company_list']){
            $this->rs['code'] = 5;
            $this->rs['msg'] = $this->code[5];
            return return_json($this->rs);
        }

        foreach ($this->config['company_list'] as $v){

            $str = $v['company_id'].$time_stamp.$random_code.$v['app_key'];

            $str_md = md5($str);

            if($str_md == $sign){
                $this->config['company_id'] = $v['company_id'];
                $this->config['app_key'] = $v['app_key'];
                $this->config['type'] = $v['app_key'];
                return true;
            }
        }

        $this->rs['code'] = 3;
        $this->rs['msg'] = $this->code[3];
        return return_json($this->rs);

    }
}
