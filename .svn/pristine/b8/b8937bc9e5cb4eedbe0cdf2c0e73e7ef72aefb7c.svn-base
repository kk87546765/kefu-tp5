<?php
/**
 * Created by PhpStorm.
 * User: crosstime
 * Date: 2017/10/12
 * Time: 上午11:57
 */
namespace app\admin\controller;

use common\libraries\Common;
use common\server\Sms\SmsServer;
use think\Config;


class Sms extends Oauth
{
    protected $no_oauth = ['sms'];

    public function index()
    {

    }


    public function sms()
    {
        $data['phone'] = $this->request->post('phone');
        $data['params'] = $this->request->post('params');
        $data['template_id'] = $this->request->post('template_id');
        $data['sms_type'] = $this->request->post('sms_type');
        $data['sign'] = $this->request->post('sign');

        $data['phone'] = splitToArr($data['phone'],"\n");
        $data['params'] = splitToArr($data['params'],"\n");
//        $data['phone'] = explode("\n",$data['phone']);

        $return = SmsServer::send($data);
        return return_json($return);

    }


    public function getSmsTemplate()
    {
        $a = Common::getConfig('sms');
        var_duMP($a);exit;
    }

    public function getSmsType()
    {

        $return = SmsServer::getSmsType();

        return return_json($return);
    }

}