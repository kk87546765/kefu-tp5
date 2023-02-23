<?php
namespace common\libraries;

//TRACE、DEBUG、INFO、WARN、ERROR、FATAL。

class Logger{


    const INFO   = 'info';
    const ERROR  = 'error';
    const TRACE  = 'trace';
    const DEBUG  = 'debug';
    const WARN   = 'warn';
    const FATAL  = 'fatal';

    // 日志信息
    protected static $log = array();
    // 配置参数
    protected static $destination = '';
    //项目来源
    protected static $source = '';
    //项目来源
    protected static $trace_id = '';
    // 日志类型
    protected static $type = array('info', 'error', 'trace', 'debug', 'warn', 'fatal');

    /**
     * 日志初始化
     * @param array $config
     */
    public static function init($config = array())
    {
        $path              = isset($config['path']) ? $config['path'] : '/data/logs/';
        $filename          = isset($config['filename']) ? $config['filename'] : date('Y-m-d').'.log';
        self::$destination = $path.'/'.$filename;
        self::$source      = isset($config['source']) ? $config['source'] : 'customer.asgardu.cn/';
    }

    /**
     * 记录日志信息
     * $msg = array(
     *    'tag'=>array('k1'=>'v1','k2'=>'v2'),
     *    'msg'=>array('msg_k1'=>'msg_v1','msg_k2'=>'msg_v2'),
     * );
     * 或者
     * $msg = array(
     *      'tag'=>'k1=v1,k2=v2',
     *      'msg'=>'{"msg_k1":"msg_v1","msg_k2":"msg_v2"}'
     * );
     *
     * @param array  $msg  日志信息
     * @param string $type 信息类型等级
     * @return void
     */
    public static function record($msg, $type = 'info')
    {
        $msg['time_local'] = date('c');
        self::$log[$type][] = $msg;
    }

    /**
     * 清空日志信息
     * @return void
     */
    public static function clear()
    {
        self::$log = array();
    }

    /**
     * 保存日志信息
     * @return bool
     */
    public static function save()
    {
        $log = self::$log;
        $result = self::_save($log);
        if ($result) {
            self::$log = array();
        }

        return $result;
    }

    /**
     * 实时写入日志信息 并支持行为
     * $msg = array(
     *    'tag'=>array('k1'=>'v1','k2'=>'v2'),
     *    'msg'=>array('msg_k1'=>'msg_v1','msg_k2'=>'msg_v2'),
     * );
     * 或者
     * $msg = array(
     *      'tag'=>'k1=v1,k2=v2',
     *      'msg'=>'{"msg_k1":"msg_v1","msg_k2":"msg_v2"}'
     * );
     * @param string  $msg  调试信息
     * @param string $type 信息类型等级
     * @param bool   $force 是否强制写入
     * @return bool
     */
    public static function write($msg, $type = 'info', $force = false)
    {
        // 封装日志信息
        $msg=self::logdata($msg);
        if (true === $force || empty(self::$type)) {
            $log[$type][] = $msg;
        } elseif (in_array($type, self::$type)) {
            $log[$type][] = $msg;
        } else {
            return false;
        }
        // 写入日志
        return self::_save($log);
    }

    /**
     * 日志写入接口
     * @access public
     * @param array $log 日志信息
     * @return bool
     */
    protected static function _save($log)
    {
        $_time_local         = date('c');
        $hostname = gethostname();
        $source = self::$source;
        $destination = self::$destination;
        if (self::$trace_id){
            $_traceID=self::$trace_id;
        }else{
            $_traceID = md5($_time_local.self::_getMillisecond().$hostname.mt_rand(100, 999));
            self::$trace_id=$_traceID;
        }
        $_traceID = substr($_traceID, 0, 8);
        $path = dirname($destination);
        !is_dir($path) && mkdir($path, 0755, true);




        $log_content = '';

        foreach ($log as $level => $value) {
            foreach ($value as $val) {
                $time_local = isset($val['time_local'])?$val['time_local']:$_time_local;
                $traceid = isset($val['traceid'])?$val['traceid']:$_traceID;
                $tags = isset($val['tag'])?$val['tag']:'';
                //处理tags
                if(is_array($tags)){
                    $_tags = array();
                    foreach ($tags as $tag_key => $tag_val) {

                        if(is_array($tag_val)){
                            $tag_val = json_encode($tag_val);
                        }

                        if(is_numeric($tag_key)){
                            $_tags[] = $tag_val;
                        }else{
                            $_tags[] = $tag_key.'='.$tag_val;
                        }
                    }

                    $tags = implode(',', $_tags);
                }
                //处理msg
                $msg = isset($val['msg'])?$val['msg']:'';
                if(is_array($msg)){
                    $msg = json_encode($msg);
                }
                $log_content .= strtoupper($level).'|'.$source.'|'.$time_local.'|'.$traceid.'|'.$hostname.'|'.$tags.'|'.$msg."|\r\n";
            }
        }

        if($log_content == ''){
            return true;
        }

        return error_log($log_content,3,$destination);

    }

    protected static function _getMillisecond() {
        list($t1, $t2) = explode(' ', microtime());
        return (float)sprintf('%.0f',(floatval($t1)+floatval($t2))*1000);
    }

    /**
     * 静态调用
     * @param $method
     * @param $args
     * @return mixed
     */
    public static function __callStatic($method, $args)
    {
        if (in_array($method, self::$type)) {
            //array_push($args, $method);
            //return call_user_func_array('\\think\\Log::record', $args);
            self::record($args[0],$method);
        }
    }

    protected  static function logdata($msg){
        $log = array(
            'tag'=>self::getPosition(),
            'msg'=>$msg,
        );
        return $log;
    }
    public static function getPosition(){
        $backtrace = debug_backtrace(2);
        $last_function = array();
        foreach($backtrace as $item){
            if(isset($item['class'])){
                if($item['class'] != __CLASS__){
                    $callFunction = $item;
                    break;
                }
                $last_function = $item;
            }
        }
        $line = isset($last_function['line']) ? $last_function['line'] : '';
        $file = isset($last_function['file']) ? $last_function['file'] : '';
        $position = 'FILE:'.$file.'|LINE:'.$line;
        return $position;
    }
}
