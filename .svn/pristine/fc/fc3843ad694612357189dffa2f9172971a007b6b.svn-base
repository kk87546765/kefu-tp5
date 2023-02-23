<?php
namespace app\scripts\controller;

use common\base\BasicController;
use common\server\SysServer;
use traits\controller\lxxScript;


class Base extends BasicController
{
    use lxxScript;//脚本公共方法

    protected $config = [];

    public function _initialize()
    {
        parent::_initialize();

        $this->initConfig();

        $this->initFuncConfig($this->req->controller());//初始脚本方法
    }

    /**
     * 初始化基础数据
     */
    private function initConfig(){
        $platform_list = SysServer::getPlatformList();
        $this->config['platform_list'] = $platform_list;
        $this->config['platform_suffix'] = [];
        if($platform_list){
            foreach ($platform_list as $item){
                if($item['suffix'] == 'asjd') continue;
                $this->config['platform_suffix'][$item['suffix']] = $item['platform_id'];
            }
        }
    }

    /**
     * 成功返回函数
     * @param string $msg
     * @param array $data
     * @param array $extra
     */
    protected function s_json($msg = 'ok',$data = [],$extra = []){

        if(!$data && $msg && is_array($msg)){
            $this->rs['msg'] = 'ok';
            $this->rs['data'] = $msg;
        }else{
            $this->rs['msg'] = $msg;
            $this->rs['data'] = $data;
        }

        if($extra && is_array($extra)){
            $this->rs = array_merge($this->rs,$extra);
        }

        echo json_encode($this->rs);
        exit();
    }

    /**
     * 错误返回函数
     * @param string $msg
     * @param array $data
     * @param int $code
     */
    protected function f_json($msg='error',$data=[],$code=200){

        if(!$data && $msg && is_array($msg)){
            $this->rs['msg'] = 'error';
            $this->rs['data'] = $msg;
            $this->rs['code'] = $code;
        }else{
            $this->rs['msg'] = $msg;
            $this->rs['data'] = $data;
            $this->rs['code'] = $code;
        }

        echo json_encode($this->rs);
        exit();
    }
}
