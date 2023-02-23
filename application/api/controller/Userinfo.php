<?php
namespace app\api\controller;


use common\libraries\ApiVerification;
use common\server\Statistic\UserPrivacyServer;

class Userinfo extends Base
{
    public function userInputInfo()
    {
        //接收参数
        $data = $this->request->post();

        $this->checkParam($data,['uid','platform','game_id','product_id','server_id','qq','phone_num','birthday','sign']);
        //签名验证
        $this->checkSing($data);
        //插入数据
        $res = UserPrivacyServer::userInput($data);
        if ($res) {
            $code_msg = ApiVerification::getCodeData(ApiVerification::API_CODE_SUCCESS);
        }else {
            $code_msg = ApiVerification::getCodeData(ApiVerification::API_CODE_FAIL);
        }

        $this->rs = array_merge($this->rs,$code_msg);

        return return_json($this->rs);

    }
}
