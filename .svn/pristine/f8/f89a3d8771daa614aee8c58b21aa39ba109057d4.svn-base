<?php
/**
 * 系统
 */
namespace common\libraries;

class ApiVerification
{
    const API_CODE_SUCCESS = 0; //接口请求成功

    const API_CODE_FAIL = 1001;  //接口失败

    const API_CODE_PARAMS_ERROR = 1002;  //参数错误

    const API_CODE_CONNECT_TIME_OUT = 1003;  //连接超时

    const API_CODE_SIGN_ERROR = 1004;  //sign签名失败

    const API_REQUEST_TIME_OUT = 30;  //接口连接超时设置s/秒

    public static $codeMsg = [

        self::API_CODE_SUCCESS => 'Interface request successful', //接口请求成功

        self::API_CODE_FAIL => 'Interface request failed!!', //接口请求失败

        self::API_CODE_PARAMS_ERROR => 'Interface params error!!', //请求参数错误

        self::API_CODE_CONNECT_TIME_OUT => 'The request has expired!!', //请求过期

        self::API_CODE_SIGN_ERROR => 'Sign failed!!', //sign签名失败
    ];

    protected $key = 'test';

    protected $time = 0;

    public function __construct($config = [])
    {
        $this->time = time();

        if($config){
            foreach ($config as $k => $v){
                $this->$k = $v;
            }
        }

    }

    /**
     * 验签
     * 1.校验时间
     * 2.校验签名
     * @param $params
     * @return array
     */
    public function checkSign($params){

        //时效性验证 判断请求是否过期，过期时间30秒
        if (empty($params['time']) || (int)$params['time'] < ($this->time - self::API_REQUEST_TIME_OUT)) {
            return self::getCodeData(self::API_CODE_CONNECT_TIME_OUT);
        }

        $sign = self::createSign($params,$this->key);

        if (!$params['sign'] || $sign != $params['sign']) {
            return self::getCodeData(self::API_CODE_SIGN_ERROR);
        }

        return self::getCodeData(self::API_CODE_SUCCESS);
    }

    /**
     * 检查参数 是否存在 是否不为空
     * @param $params
     * @param array $verify_keys
     * @return array
     */
    public static function checkParam($params,$verify_keys = []){
        //必传参数验证
        if (!empty($verify_keys)) {
            $verify_keys = array_unique($verify_keys);
            krsort($verify_keys);
            foreach ($verify_keys as $val) {
                if (!isset($params[$val]) || empty($params[$val])) {
                    return self::getCodeData(self::API_CODE_PARAMS_ERROR,'lack param :'.$val);
                }
            }
        }

        return self::getCodeData(self::API_CODE_SUCCESS);
    }

    /**
     * 生成sign签名验证
     * 1、传入的全部参数（剔除原有的sign字段），php ksort()根据键，以升序对关联数组进行排序
     * 2、以数组的值拼接成字符串
     * 3、数组拼接好的字符串拼接验证秘钥key
     * 4、对拼接好的字符串md5加密生成sign签名
     * 5、返回sign签名
     * @param array $data
     * @param string $signKey
     * @return string
     */
    public static function createSign($data = array(), $signKey = '')
    {
        $result = '';
        if (empty($data) || empty($signKey)) {
            return $result;
        }
        if (isset($data['sign'])) {
            unset($data['sign']);
        }
        ksort($data);
        $signStr = '';
        foreach ($data as $key=>$val) {
            $signStr .= $val;
        }
        $signStr .= $signKey;

        if (empty($signStr)) return $result;

        $result = md5($signStr);
        return $result;
    }

    public static function getCodeData($code,$msg = '',$data = []){
        $res = [];
        $res['code'] = $code;

        if($msg){
            $res['msg'] = $msg;
        }else{
            $res['msg'] = getArrVal(self::$codeMsg,$code,'undefined error!');
        }

        if($data){
            $res['data'] = $data;
        }


        return $res;
    }
}
