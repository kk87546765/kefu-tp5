<?php
namespace app\api\controller;
use common\server\Qc\SobotServer;
use common\server\Qc\QcQuestionServer;
use common\server\SysServer;
use common\server\Vip\RecallServer;
use think\Log;
use think\Request;


class Recall extends Base
{
    public $config = [
        'platform_id'=>0,
        'platform'=>'',
        'open'=>0,
        'check_sign'=>0,
        'set_log'=>0,
        'platform_info'=>[],
    ];

    private $code = [
        1=>'没有配置参数',
        2=>'接口停止使用',
        3=>'签名校验错误',
        4=>'缺少平台参数',
        5=>'平台参数错误',
        6=>'缺少参数',
        7=>'配置有误，请联系管理员',
        8=>'操作失败',
    ];

    public function __construct(Request $request = null)
    {
        parent::__construct($request);

        $post_data = $request->param();

        if(!$post_data){
            $this->rs['code'] = 6;
            $this->rs['msg'] = $this->code[6];
            return return_json($this->rs,0);
        }

        $bast_config = $this->base_config;

        if(!$bast_config || !isset($bast_config['platform_recall']) || !$bast_config['platform_recall'] ){
            $this->rs['code'] = 1;
            $this->rs['msg'] = $this->code[1];
            return return_json($this->rs);
        }

        $this->config = mergeData($this->config,$bast_config['platform_recall']);

        if($this->config['set_log']){
            Log::init([
                'path' => RUNTIME_PATH.'api/'.$this->req->controller().'/'.$this->req->action(),
                'filename' => date('Y-m-d') ]);

            Log::write([
                'tag' => 'post_data',
                'msg' => json_encode($post_data)
            ]);
        }

        if(!$this->config['open']){
            $this->rs['code'] = 2;
            $this->rs['msg'] = $this->code[2];
            return return_json($this->rs);
        }

        $platform = getArrVal($post_data,'platform','');

        if(!$platform){
            $this->rs['code'] = 4;
            $this->rs['msg'] = $this->code[4];
            return return_json($this->rs,0);
        }

        $platform_list = SysServer::getPlatformList();

        $platform_info = [];

        foreach ($platform_list as $item){
            if($item['suffix'] == $platform){
                $platform_info = $item;
                break;
            }
        }

        if(!$platform_info){
            $this->rs['code'] = 5;
            $this->rs['msg'] = $this->code[5];
            return return_json($this->rs,0);
        }

        $this->config['platform_id'] = $platform_info['platform_id'];
        $this->config['platform'] = $platform;
        $this->config['platform_info'] = getArrVal($platform_info,'config',[]);

        if($this->config['check_sign']){
            $this->checkSign();
        }
    }

    public function verInfo(){

        $post_data = $this->req->param();

        $post_data = getDataByField($post_data,[
            'up_id',
            'ver_id',
            'ver_title',
            'link',
        ]);

        $post_data['status'] = 1;
        $post_data['platform_id'] = $this->config['platform_id'];
        $post_data['link'] = urldecode($post_data['link']);

        $res = RecallServer::linkCheckSet($post_data);

        if(!$res){
            $this->rs['code'] = 8;
            $this->rs['msg'] = $this->code[8];
        }

        return return_json($this->rs);
    }

    /**
     * 检查
     */
    protected function checkSign(){

        $time_stamp = $this->req->param('time','');

        $sign = $this->req->param('sign','');

        if(!$time_stamp){
            $this->rs['code'] = 6;
            $this->rs['msg'] = $this->code[6].'(time)';
            return return_json($this->rs,0);
        }

        if(!$sign){
            $this->rs['code'] = 6;
            $this->rs['msg'] = $this->code[6].'(sign)';
            return return_json($this->rs,0);
        }

        if(!isset($this->config['platform_info']['url_key'])){
            $this->rs['code'] = 7;
            $this->rs['msg'] = $this->code[7].'(key)';
            return return_json($this->rs,0);
        }

        $str = $time_stamp.$this->config['platform_info']['url_key'];

        $str_md = md5($str);

        if($str_md !== $sign){
            $this->rs['code'] = 3;
            $this->rs['msg'] = $this->code[3];
            return return_json($this->rs,0);
        }
    }
}
