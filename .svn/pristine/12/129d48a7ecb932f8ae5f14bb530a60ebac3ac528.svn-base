<?php
/**
 * User: lxx
 * Date: 2021/1/15
 * Time: 16:18
 */

/**
 * 公用的方法
 */
if (!function_exists('return_json')) {
    /**
     * 封装返回，用于调试
     *
     * @param array $data 原始数组
     * @param string $option json_encode额外参数
     * @return array
     */
    function return_json($data = [], $flag = true, $option = '')
    {
        $rs = !empty($option) ? json_encode($data, $option) : json_encode($data);
        if ($flag) {
            return json()->data($rs)->getData();
        } else {
            die($rs);
        }
    }
}

if (!function_exists('is_empty')) {
    /**
     * 核实数据是否有设置并且不能为空
     *
     * @param array $param 具体数组
     * @param array $data 核实数组
     * @param array $msg 提示数组
     * @param boolean $space 是否验证含有空格，false不验证，ture验证
     * @return string
     */
    function is_empty($param = [], $data = [], $space = true)
    {
        if (!empty($data)) {
            foreach ($data as $val) {
                if (!isset($param[$val]) or $param[$val] === '') {
                    return true;
                }
                if ($space and preg_match("/\s/", $param[$val])) {
                    return true;
                }
            }
            return false;
        } else {
            return true;
        }
    }
}

if (!function_exists('check_validate')) {
    /**
     * 验证规则
     *
     * @param array $rule 规则数组
     * @param array $data 数组值
     * @return boolean
     */
    function check_validate($rule = [], $data = [])
    {
        $validate = new \think\Validate($rule);
        return $validate->check($data);
    }
}

if (!function_exists('get_redis')) {
    /**
     * 获取redis配置
     *
     * @param string $config 配置
     * @return string
     */
    function get_redis($config = 'default')
    {
        $name = 'cache.'.$config;

        return \think\Cache::connect(config($name))->handler();
    }
}

if(!function_exists('clearZero')){
    /**
     * 清除数组中值为0的参数
     * @param array $param
     * @param array $field
     */
    function clearZero(array &$param,array $field=[]){

        if($field){
            foreach ($param as $k => $v) {
                if(!in_array($k, $field)){
                    continue;
                }
                if($v != 0){
                    continue;
                }

                unset($param[$k]);
            }
        }else{
            foreach ($param as $k => $v) {
                if($v == 0){
                    unset($param[$k]);
                }
            }
        }
    }
}
if(!function_exists('getWhereDataArr')) {
    /**
     * @param $data
     * @param $field
     * @param bool $is_in
     * @return array
     */
    function getWhereDataArr($data,$field,$is_in = true)
    {
        if(!is_array($data)){
            $data = explode(',',$data);
        }

        if(count($data) == 1){
            if($is_in){
                $this_operator = '=';
            }else{
                $this_operator = '!=';
            }
            return [$field,$this_operator,$data[0]];
        }else{
            if($is_in){
                $this_operator = 'in';
            }else{
                $this_operator = 'not in';
            }
            return [$field,$this_operator,$data];
        }
    }
}
if(!function_exists('setWhereSql')){
    /**
     * @param $p_data
     * @param string $sql
     * @return false|string
     */
    function setWhereSql($p_data,$sql = ' WHERE '){
        if(!$p_data || !is_array($p_data)){
            return false;
        }

        $conditions = [];

        foreach ($p_data as $key => $value) {
            /*
            * 0 key=>value
            * 1 [key,value]
            * 2 [key,operator,value]
            */
            if(is_string($key) && !is_numeric($key)){
                $this_data_type = 0;
                if($key == '_string'){
                    $this_data_type = 3;
                }
            }elseif(is_array($value)){
                if(count($value) == 3){
                    $this_data_type = 2;
                }else{
                    $this_data_type = 1;
                }
            }else{
                continue;
            }

            switch ($this_data_type) {
                case '1':
                    $this_key = changeWhereField($value[0]);
                    $this_value = $value[1];
                    $this_operator = '=';
                    break;
                case '2':
                    $this_key = changeWhereField($value[0]);
                    $this_value = $value[2];
                    $this_operator = $value[1];
                    break;
                case '3':
                    $this_key = changeWhereField($key);
                    $this_value = $value;
                    $this_operator = 'sql';
                    break;
                default:
                    $this_key = changeWhereField($key);
                    $this_value = $value;
                    $this_operator = '=';
                    break;
            }

            $this_operator = strtoupper($this_operator);

            switch ($this_operator) {
                case 'NOT BETWEEN':
                case 'BETWEEN':
                    if(is_array($this_value) && count($this_value) == 2){
                        $conditions[]= $this_key.' '.$this_operator." $this_value[0] AND $this_value[1]";
                    }
                    break;
                case 'IN':
                case 'NOT IN':
                    if(is_array($this_value)){
                        $conditions[]= $this_key.' '.$this_operator.' ("'.implode('","', $this_value).'")';
                    }else{
                        $this_value_arr = explode(',',$this_value);
                        $has_str_flag = 0;
                        foreach ($this_value_arr as $item){
                            if(is_string($item)){
                                $has_str_flag = 1;
                                break;
                            }
                        }

                        if($has_str_flag){
                            $conditions[]=$this_key.' '.$this_operator." ('".implode('\',\'',$this_value_arr)."')";
                        }else{
                            $conditions[]= $this_key.' '.$this_operator.' ('.$this_value.')';
                        }

                    }

                    break;
                case 'SQL':
                    $conditions[] =  $this_value;
                    break;
                default:
                    if(is_string($this_value)){
                        $this_value = "'$this_value'";
                    }
                    $conditions[]= $this_key.' '.$this_operator.' '.$this_value;
                    break;
            }

        }

        $sql.= implode(' AND ', $conditions);

        return $sql;
    }
}

if(!function_exists('changeWherefield')){
    /**
     * 设置_string参数
     * @param $string
     * @param $where
     * @param $operator
     */
    function changeWhereField($string) {
        if(preg_match('/[^1-9a-zA-Z_]/',$string)){
            return $string;
        }else{
            return '`'.$string.'`';
        }
    }
}
if(!function_exists('setWhereString')){
    /**
     * 设置_string参数
     * @param $string
     * @param $where
     * @param $operator
     */
    function setWhereString($string,&$where,$operator = 'AND') {
        if(isset($where['_string'])){
            $where['_string'] .= " $operator ".$string;
        }else{
            $where['_string'] = $string;
        }
    }
}
if(!function_exists('setIntoSql')){
    function setIntoSql($table,$key_arr,$list){
        if(!$key_arr || !$list) return '';

        $sql = 'INSERT INTO '.$table;

        $sql.= ' ('.implode(',', $key_arr).') ';

        $val_arr = [];
        foreach ($list as $k => $v) {
            $this_sql = '(\'';
            foreach ($key_arr as $ka_k => $ka_v) {
                if($this_sql == '(\''){
                    $this_sql .=$v[$ka_v];
                }else{
                    $this_sql .='\',\''.$v[$ka_v];
                }
            }
            $this_sql .= '\')';
            $val_arr[] = $this_sql;
        }

        $sql.='VALUES '.implode(',', $val_arr);

        return $sql;
    }
}
if(!function_exists('setDelSql')){
    /**
     * 生成删除sql
     * @param $table
     * @param $where
     * @param int $limit
     * @return string
     */
    function setDelSql($table,$where,$limit = 100){
        if(!$where) return '';
        $sql = 'DELETE FROM '.$table;

        $sql.= setWhereSql($where);

        if($limit){
            $sql.=' LIMIT '.$limit;
        }
        return $sql;
    }
}
if(!function_exists('setUpdateSql')){
    /**
     * 生成删除sql
     * @param $table
     * @param $where
     * @param int $limit
     * @return string
     */
    function setUpdateSql($table,$where,$data,$limit = 1000){

        if(!$data){
            return '';
        }

        $sql = 'UPDATE '.$table.' SET ';

        $update_value = [];
        foreach ($data as $k => $v){
            if(is_string($v)) {
                $update_value[] = "$k = '$v'";
            }elseif(is_array($v)){
                $update_value[] = "$k = $v[0]";
            }else{
                $update_value[] = "$k = $v";
            }
        }

        if(!$update_value){
            return '';
        }
        $sql.= implode(',',$update_value);

        if($where){
            $sql.= setWhereSql($where);
        }

        if($limit){
            $sql.=' LIMIT '.$limit;
        }
        return $sql;
    }
}
/*
* 转换layer-tree树状结构数据
*
*/
if(!function_exists('changeTreeList')){

    function changeTreeList($data,$pid=0,$config=['id'=>'id','title'=>'name','pid'=>'pid']){
        $children = [];
        foreach ($data as $k => $v) {
            if($v[$config['pid']] == $pid){
                unset($data[$k]);//减少循环次数
                $thischild = changeTreeList($data,$v[$config['id']],$config);
                $thischild && $v['children'] = $thischild;
                $v['title'] = $v[$config['title']];
                $v['id'] = $v[$config['id']];
                $children[] = $v;
            }
        }

        return $children;
    }
}

if(!function_exists('splitToArr')){
    /**
     * 逗号拆分转数组
     * @param $str
     * @param $preg
     * @return false|string[]
     */
    function splitToArr($str,$preg = ','){

        if($preg == ''){
            $preg = checkStrSplitHandle($str);
        }

        if($preg == ','){
            $str = str_replace('，', ',', $str);
        }

        if($preg == "\n"){
            $str = str_replace("\r\n", "\n", $str);//防止不兼容
        }

        $arr = explode($preg, $str);

        return $arr;
    }

}
if(!function_exists('splitToArr2')){
    /**
     * 'a=>1,b=>2' 转数组
     * @param $str
     * @param $preg
     * @return array
     */
    function splitToArr2($str,$preg = ','){

        $arr = splitToArr($str,$preg);


        $new_arr = [];
        foreach ($arr as $k => $v) {

            if($v){
                $this_info = explode('=>', $v);
                if(count($this_info) != 2){
                    continue;
                }

                $new_arr[$this_info[0]] = $this_info[1];
            }
        }

        return $new_arr;
    }
}
if(!function_exists('splitToArrCheck2')){
    /**
     * @param $str string
     * @param string $preg
     * @return string
     */
    function splitToArrCheck2(string $str,$preg =','){

        $res = '';

        $arr = splitToArr2($str);

        if($arr){
            $new_arr = [];
            foreach ($arr as $k => $v) {
                $new_arr[] = "$k=>$v";
            }
            $res = implode($preg, $new_arr);
        }

        return $res;
    }
}
if(!function_exists('checkStrSplitHandle')){
    function checkStrSplitHandle($str){
        $preg_arr = [
            ",",
            "\n",
            "\r",
            "\r\n",
            " ",
            "\/",
            "=>",
        ];

        $this_handle = '';
        $flag = 0;

        foreach ($preg_arr as $k => $v) {
            $res = preg_match_all("/$v/", $str,$data);
            if($res && $res >= $flag){
                $flag = $res;
                $this_handle = $v;
            }
        }

        return $this_handle;
    }
}
/*
* 打印
*
*/
if(!function_exists('dd')){

    function dd($data,$die=1){
        $env_status = \think\Env::get('env_status','product');

        if($env_status == 'product'){
            return false;
        }
        echo "<pre>";
        print_r($data);

        if($die){
            die;
        }
    }
}

if(!function_exists('getDataByField')){
    /**
     * 数据筛选
     * @param $data
     * @param array $field
     * @param int $true
     * @param string $pre
     * @return array
     */
    function getDataByField($data,$field = [],$true = 0,$pre=''){
        if(!is_array($data) || !$data || !is_array($field)){
            return [];
        }

        if(!$field){
            return $data;
        }
        $new_data = [];
        foreach ($data as $k => $v) {
            if(in_array($k, $field)&& (!$true || !empty($v))){
                $new_data[$pre.$k] = $v;
            }
        }

        return $new_data;
    }
}
if(!function_exists('curl_post')){
    /**
     * 模拟POST方式发送数据
     * @param $url   目的URL
     * @param null $data  发送的数据
     * @return mixed|string  返回数据
     */
    function curl_post($post_url, $post_arr, $timeOut = 0, $cookie = false, $header = false) {

        $timeOut = $timeOut ?: 10;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $post_url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_arr);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        if (strpos($post_url, 'https') !== false) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        }
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeOut);
        $header && curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        $cookie && curl_setopt($ch, CURLOPT_COOKIE, $cookie);
        $response = curl_exec($ch);    //这个是读到的值
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        unset($ch);
        return compact('httpCode','response');
    }
}
if(!function_exists('curl_get')){
    /**
     * 模拟GET方式发送数据
     * @param $url  目的URL
     * @return mixed
     */
    function curl_get($url, $proxy='') {
        $ch = curl_init ();
        if($proxy) {
            $url_last = $proxy .= '?path=' . urlencode($url);
        } else {
            $url_last = $url;
        }
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt ( $ch, CURLOPT_URL, $url_last );
        curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );

        $output = curl_exec ( $ch );
        if (curl_errno ( $ch )) {
            return curl_error ( $ch );
        }
        curl_close ( $ch );
        return $output;
    }
}
if(!function_exists('createNonceStr')){
    /**
     * 获取随机字符串
     *
     * @param number $length
     * @return string
     */
    function createNonceStr($length = 16,$chars_new = '') {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        if($chars_new) $chars = $chars_new;
        $str = "";
        for($i = 0; $i < $length; $i ++) {
            $str .= substr ( $chars, mt_rand ( 0, strlen ( $chars ) - 1 ), 1 );
        }
        return $str;
    }
}
if(!function_exists('time_tran')){
    /**
     * 将时间转换为人性化时间
     * @param $the_time timestamp
     * @return str
     */
    function time_tran($the_time) {
        $now_time = time();
        $dur = $now_time - $the_time;
        if($dur < 0){
            return "刚刚";
        }else if($dur < 60){
            return floor($dur)."秒前";
        }else if($dur < 3600){
            return floor($dur / 60)."分钟前";
        }else if($dur < 86400){
            return floor($dur / 3600)."小时前";
        }else{
            return "一天前";
        }
    }
}
if(!function_exists('timeInterval')){
    /*
     * 计算两日期的间隔天数
     * $inTime 开始时间 yyyy-mm-dd
     * $outTime 结束时间 yyyy-mm-dd
     */
    function timeInterval($inTime,$outTime){
        //    $date_1 = date('Y-m-d');
        //    $date_2= '2012-07-16';
        $date1_arr = explode("-",$inTime);
        $date2_arr = explode("-",$outTime);
        $day1 = mktime(0,0,0,$date1_arr[1],$date1_arr[2],$date1_arr[0]);   //返回一个日期的 UNIX 时间戳。然后使用它来查找该日期的天   参数是小时 分 秒 月 日 年  mktime(hour,minute,second,month,day,year,is_dst);
        $day2 = mktime(0,0,0,$date2_arr[1],$date2_arr[2],$date2_arr[0]);
        $days = round(($day2 - $day1)/3600/24);
        return $days;
    }
}
if(!function_exists('timeDifferDay')){
    /*
     * 计算两时间戳的间隔天数
     * $inTime 开始时间 timestamp
     * $outTime 结束时间 timestamp
     */
    function timeDifferDay($inTime,$outTime){
        return floor(abs($inTime - $outTime)/3600/24);
    }
}
if(!function_exists('weekTime')){
    /**
     * 查找时间戳所在星期
     * @param $time str|timestamp
     * @return bool
     */
    function weekTime($time,$t=false){
        $timeA = $time;
        if($t) $timeA = strtotime($time);
        $n = date('N', $timeA);
        $str = "一二三四五六日";
        $m  =  '周'.mb_substr($str,$n-1,1,'utf-8');
        return $m;
    }
}
if(!function_exists('isphone')){
    /**
     * 是否手机
     * @param $phone str
     * @return bool
     */
    function isphone($phone){
        if(preg_match("/^1\d{10}$/",$phone)){
            // echo "是手机号码";
            return true;
        }else{
            // echo "不是手机号码";
            return false;
        }
    }
}
if(!function_exists('isweixin')){
    /**
     * 是否微信
     * @return bool
     */
    function isweixin(){
        if(stripos($_SERVER['HTTP_USER_AGENT'],'MicroMessenger') == false){
            return 0;
        }else{
            return 1;
        }
    }
}
if(!function_exists('isMobile')){
    /**
     * 是否手机端
     * @return bool
     */
    function isMobile() {
        // 如果有HTTP_X_WAP_PROFILE则一定是移动设备
        if (isset($_SERVER['HTTP_X_WAP_PROFILE'])) {
            return 1;
        }
        // 如果via信息含有wap则一定是移动设备,部分服务商会屏蔽该信息
        if (isset($_SERVER['HTTP_VIA'])) {
            // 找不到为flase,否则为true
            return stristr($_SERVER['HTTP_VIA'], "wap") ? 1 : 0;
        }
        // 脑残法，判断手机发送的客户端标志,兼容性有待提高。其中'MicroMessenger'是电脑微信
        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            $clientkeywords = array('nokia','sony','ericsson','mot','samsung','htc','sgh','lg','sharp','sie-','philips','panasonic','alcatel','lenovo','iphone','ipod','blackberry','meizu','android','netfront','symbian','ucweb','windowsce','palm','operamini','operamobi','openwave','nexusone','cldc','midp','wap','mobile','MicroMessenger');
            // 从HTTP_USER_AGENT中查找手机浏览器的关键字
            if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT']))) {
                return 1;
            }
        }
        // 协议法，因为有可能不准确，放到最后判断
        if (isset ($_SERVER['HTTP_ACCEPT'])) {
            // 如果只支持wml并且不支持html那一定是移动设备
            // 如果支持wml和html但是wml在html之前则是移动设备
            if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html')))) {
                return 1;
            }
        }
        return 0;
    }
}
if(!function_exists('isios')){
    /**
     * 是否ios
     * @return bool
     */
    function isios(){
        if(strpos($_SERVER['HTTP_USER_AGENT'], 'iPhone')||strpos($_SERVER['HTTP_USER_AGENT'], 'iPad')){

            return 1;

        }elseif(strpos($_SERVER['HTTP_USER_AGENT'], 'Android')){

            return 0;

        }else{

            return 0;

        }
    }
}
if(!function_exists('xml_to_json')){
    /**
     * xml 转 json
     * @return json
     */
    function xml_to_json($source,$cdata = 0,$obj = '') {
        if(is_file($source)){ //传的是文件，还是xml的string的判断
            if($cdata == 0){
                $xml_array=simplexml_load_file($source);
            }else{
                // echo "string";die;
                $xml_array = simplexml_load_file($source, 'SimpleXMLElement', LIBXML_NOCDATA);
            }
        }else{

            if($cdata == 0){
                $xml_array=simplexml_load_string($source);
            }else{
                // echo "string";die;
                $xml_array = simplexml_load_string($source, 'SimpleXMLElement', LIBXML_NOCDATA);
            }

        }
        if($obj == ''){
            return json_encode($xml_array);
        }else{
            return json_encode($xml_array->$obj);
        }
    }
}
if(!function_exists('get_rule_time')){
    /**
     * 根据规则获取时间条件
     * @return timestamp
     */
    function get_rule_time($num,$times=1){
        switch ($num)
        {
            case 1:
                $sqltime = strtotime('today');//今天
                break;
            case 2:
                $sqltime = strtotime(date('Y-m-d H').':0:0');//每小时
                break;
            case 3:
                $sqltime = strtotime(date('Y-m-d H:i').':0')*$times;//每多少分钟
                break;
            case 5:
                $sqltime = strtotime(date('Y-m'));//每个月
                break;
            case 6:
                $sqltime = strtotime(date('Y').'0101');//每年
                break;
            default:
                $sqltime=0;
        }

        return $sqltime;
    }
}
if(!function_exists('is_https')){
    /**
     * 是否https
     * @return bool
     */
    function is_https() {
        if ( !empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off') {
            return true;
        } elseif ( isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https' ) {
            return true;
        } elseif ( !empty($_SERVER['HTTP_FRONT_END_HTTPS']) && strtolower($_SERVER['HTTP_FRONT_END_HTTPS']) !== 'off') {
            return true;
        }
        return false;
    }
}
if(!function_exists('getMonthNum')){
    /**
     * 计算两个日期相差多少个月
     * @param $endtime
     * @param $date2
     * @param string $tags
     * @return number
     */
    function getMonthNum( $endtime, $starttime, $tags='-' ){
        $endtime = explode($tags,$endtime);
        $starttime = explode($tags,$starttime);
        return abs($endtime[0] - $starttime[0]) * 12 + abs($endtime[1] - $starttime[1]);
    }
}
if(!function_exists('ispc')){
    /**
     * 是否是PC
     * @return str
     */
    function ispc(){
        $agent = strtolower($_SERVER['HTTP_USER_AGENT']);
        $is_pc = (strpos($agent, 'windows nt')) ? true : false;
        if($is_pc){
            return true;
        }else{
            return false;
        }
    }
}
if(!function_exists('getTodayRemainingTime')){
    /**
     * 今天剩余时间
     * @return str
     */
    function getTodayRemainingTime(){

        return strtotime('Today')+24*3600-time();
    }
}
if(!function_exists('timeCondition')){
    /**
     * 本（日 周 月 年)
     * @return str
     */
    /**
     * @param $type pre_day/day/week/last_week/month/year
     * @param string $time
     * @return array [starttime=>'Y-m-d 00:00:00',endtime=>'Y-m-d 23:59:59']
     */
    function timeCondition($type,$time=''){
        if(!$time){
            $time = time();
        }
        $time_arr=array();
        $today = date('Y-m-d H:i:s',mktime(0,0,0,date('m'),date('d'),date('Y')));
        $w = date('w',strtotime($today));
        switch ($type)
        {
            case "pre_day":
                //前天
                $time_arr['starttime'] = mktime(0,0,0,date('m'),date('d')-$w,date('Y'));
                $time_arr['endtime'] = mktime(23,59,59,date('m'),date('d')-$w,date('Y'));
                break;
            case "day":
                $nowtime=date("Y-m-d",$time);
                $time_arr['starttime']=strtotime("$nowtime 00:00:00");
                $time_arr['endtime']=strtotime("$nowtime 23:59:59");
                break;
            case "week":
                $time_arr['starttime']=mktime(0,0,0,date('m'),date('d')-$w+1,date('Y'));
                $time_arr['endtime']=mktime(23,59,59,date('m'),date('d')+(7-$w),date('Y'));
                break;
            case "last_week":
                $time_arr['starttime'] = mktime(0,0,0,date('m'),date('d')-$w+1-7,date('Y'));
                $time_arr['endtime'] = mktime(23,59,59,date('m'),date('d')-$w,date('Y'));
                break;
            case "month":
                $timestamp = strtotime(date('Y-m-d',$time));
                $mdays = date( 't', $timestamp );
                $time_arr['starttime'] = strtotime(date( 'Y-m-1 00:00:00', strtotime(date('Y-m-d',$time)) ));
                $time_arr['endtime'] = strtotime(date( 'Y-m-' . $mdays . ' 23:59:59', $timestamp ));
                break;
            case "year":
                $year=strtotime(date('Y-01-01 00:00:00'));
                $time_arr['starttime']=$year;
                break;
            default:
                ;
        }
        return $time_arr;
    }
}
if(!function_exists('checkNumberValue')){
    /**
     * 验证数值，默认小数点后两位
     * @return str
     */
    function checkNumberValue($value,$number=2){
        $result=preg_match("/^\d+(?:\.\d{0,$number})?$/",$value);
        return $result;
    }
}
if(!function_exists('replacePhone')){
    /**
     * 替换手机文本
     * @return str
     */
    function replacePhone($str,$replaceStr = '****')
    {
        $match = array();
        $rs = preg_match("/1\d{10}$/",$str, $match);
        if($rs && $match[0]) {
            $phone = $match[0];
            $replacePhone = substr($phone, 0, 3) . $replaceStr . substr($phone, -4);
            $str = str_replace($phone, $replacePhone, $str);
        }
        return $str;
    }
}
if(!function_exists('gettimesection')){
    /**
     * 获取时间区间
     * @param $num 数量
     * @param $type 单位 week day month
     * @param ago 前多少个星期
     * @return arr
     */
    function gettimesection($num=1,$type='week',$ago=0){

        $num = intval($num);
        $ago = intval($ago);
        if($num<1) return false;

        $type=in_array($type, array('week','day','month'))?$type:'week';

        $y=date("Y",time());

        $m=date("m",time());

        if($ago){
            if($type=='month'){
                if($m>$ago){

                    $maf =$m -$ago+1;

                }else{

                    $maf =$m -$ago+12+1;

                    $y-=1;

                }
                $endtime = mktime(0,0,0,$maf,1,$y);
            }elseif($type=='day'){

                $endtime = strtotime('today') - 3600*24*($ago-1);

            }else{
                $endtime = strtotime('-'.$ago.' week Monday');
            }
        }else{
            $endtime = strtotime('tomorrow');
        }

        if($type=='day'){

            $starttime = $endtime-3600*24*$num;

        }elseif($type=='month'){
            if($ago==0){
                $num--;
                $maf = $m;
            }
            if($maf>$num){

                $maf =$maf -$num;

            }else{

                $maf =$maf -$num+12;

                $y-=1;

            }
            $starttime = mktime(0,0,0,$maf,1,$y);
        }else{
            $starttime = strtotime('-'.($num+$ago).' week Monday');
        }
        return ['stime'=>$starttime,'etime'=>$endtime];
    }
}
if(!function_exists('changeTwoVal')){
    /**
     * 交换2个变量的值
     * @return arr
     */
    function changeTwoVal(&$a,&$b)
    {
        $c = $a;
        $a = $b;
        $b = $c;

        return true;
    }
}
if(!function_exists('str_limit')){
    /**
     * 截取某长度字符
     * @param        $value
     * @param int    $limit
     * @param string $end
     * @return string
     */
    function str_limit($value, $limit = 100, $end = '...')
    {
        if (mb_strwidth($value, 'UTF-8') <= $limit) {
            return $value;
        }

        return rtrim(mb_strimwidth($value, 0, $limit, '', 'UTF-8')).$end;
    }
}
if(!function_exists('getPicFileOne')){
    /**
     * 根据链接抓取图片并保存下来
     * @param str    $pic_url
     * @param str    $dir
     * @return string
     */
    function getPicFileOne($pic_url,$dir = ''){
        // return '';
        // $this->pr123("Downloading ".$pic_url." ");
        $pathinfo = pathinfo($pic_url);

        if(!in_array(strtolower($pathinfo['extension']), array('jpg','jpeg','bmp','gif','png','bmp'))){
            // $this->pr123('file is wrong');
            return '';
        }
        $pic_name = $pathinfo['basename'];
        if($dir!=''){
            if(!file_exists($dir)){
                mkdir($dir, 0777, true);
                // $this->pr123('make dir');
            }
            $pic_name=$dir.'/'.$pic_name;
        }
        if(!file_exists($pic_name)){
            // $this->pr123("Downloading pic_name:".$pic_name." ");
            // die;
            $img_read_fd = fopen($pic_url,"r");
            $img_write_fd = fopen($pic_name,"w");
            $img_content = "";
            while(!feof($img_read_fd)){
                $img_content .= fread($img_read_fd,1024);

            }
            fwrite($img_write_fd,$img_content);
            fclose($img_read_fd);
            fclose($img_write_fd);
            // $this->pr123("[Download OK] ");
        }else{
            // $this->pr123("file is exist pic_name:".$pic_name." ");
        }

        return $pic_name;
    }
}
if(!function_exists('addZeroNum')){
    /**
     * 补零
     * @param str    $num
     * @param int    $length
     * @return string
     */
    function addZeroNum($num,$length = 3){

        $num_length = strlen($num);

        $need_num = $length - $num_length;

        if($need_num>0){
            for ($i=0; $i < $need_num; $i++) {
                $num = '0'.$num;
            }
        }

        return $num;
    }
}
if(!function_exists('convertUrlQuery')){
    /**
     * url参数转数组
     * @param str    $query
     * @return array
     */
    function convertUrlQuery($query){

        $queryParts = explode('&', $query);

        $params = array();
        foreach ($queryParts as $param) {
            $item = explode('=', $param);
            $params[$item[0]] = $item[1];
        }

        return $params;
    }
}
if(!function_exists('countPresent')){
    /**
     * 计算百分比
     * @param  $child 分子
     * @param  $parent 分母
     * @param int  $limit 保留小数
     * @return string
     */
    function countPresent($child,$parent,$limit = 2){

        if(!$parent || !$child){
            return 0;
        }
        $parent = floatval($parent);
        $child = floatval($child);

        return round($child/$parent*100,$limit);
    }
}
if(!function_exists('arrMID')){
    /**
     * 计算2个数组交集以及对应交集的差集
     * @param array    $old_arr 旧数组
     * @param array    $new_arr 新数组
     * @return array
     * @example [1,2,3],[2,4,5]  return [ai_com=>[2],ad_del=>[1,3],ad_add=>[4,5]]
     */
    function arrMID($old_arr,$new_arr){
        //交集-共有元素
        $ai_com = array_intersect($old_arr, $new_arr);
        //差集-删除元素
        $ad_del = array_diff($old_arr, $ai_com);
        //差集-新增元素
        $ad_add = array_diff($new_arr, $ai_com);

        return compact('ai_com','ad_del','ad_add');
    }
}
if(!function_exists('arrReSet')){
    /**
     * 按数据中某键值整理数组
     * @param array    $list 需要整理数组
     * @param string    $key 字段
     * @return array
     * @example [[a=>1,b=>2],[a=>2,b=>3]...],a  return [1=>[a=>1,b=>2],2=>[a=>2,b=>3]...]
     */
    function arrReSet($list,$key){
        $new_list = [];
        foreach ($list as $v){
            if(!$v|| !isset($v[$key])){
                continue;
            }
            $new_list[$v[$key]] = $v;
        }

        return $new_list;
    }
}
if(!function_exists('excelOutput')){
    /**
     * @param array $data 必须 导出数据  格式2维数组
     * @param array $field_config 非必须 $field_config=>array(['field','name'],['field','name']...) 控制输出列顺序名称
     * @param string $title 非必须 $title 标题 默认'file'
     * @return file
     */
    function excelOutput($data,$field_config=array(),$title=''){

//    $title = $title?$title:'file';
//    $title.=date('YmdH');
//        import('Org.PHPExcel','','.php');
//        $excel = new \PHPExcel();
//        $excel->getProperties()->setCreator("Puppy");
//        $excel->getProperties()->setTitle('User');
//        $excel->getProperties()->setSubject($title);
//        //处理数据
//        $row_arr = array(
//            1=>'A',2=>'B',3=>'C',4=>'D',5=>'E',6=>'F',7=>'G',8=>'H',9=>'I',10=>'J',
//            11=>'K',12=>'L',13=>'M',14=>'N',15=>'O',16=>'P',17=>'Q',18=>'R',19=>'S',20=>'T',
//            21=>'U',22=>'V',23=>'W',24=>'X',25=>'Y',26=>'Z',27=>'AA',28=>'AB',29=>'AC',30=>'AD'
//        ,31=>'AE',32=>'AF',33=>'AG',34=>'AH',35=>'AI'
//        );
//        $field = array();
//        $field_rank = array();
//        $field_type = array();
//
//        if(!isset($data[0])){
//            return false;
//        }
//
//        $data_first = $data[0];
//
//        if( !is_array($field_config) || !$field_config ){
//            $field_flag = 1;
//            foreach ($data_first as $d0_k => $d0_v) {
//                $field[$field_flag] = $d0_k;
//                $field_rank[$d0_k] = $field_flag;
//                $field_type[$d0_k] = 'normal';
//                $field_flag++;
//            }
//        }else{
//            if(isset($field_config[0])){
//                $field_config_flag = 1;
//            }else{
//                $field_config_flag = 0;
//            }
//
//
//            foreach ($field_config as $fc_k => $fc_v) {
//
//                $thisfc_k = $field_config_flag?$fc_k+1:$fc_k;
//
//                $field[$thisfc_k] = $fc_v[1];
//
//                $field_rank[$fc_v[0]] = $thisfc_k;
//
//                if(isset($fc_v[2])){
//                    $field_type[$fc_v[0]] = $fc_v[2];
//                }else{
//                    $field_type[$fc_v[0]] = 'normal';
//                }
//            }
//        }
//        // echo "<pre>";
//        // print_r($field);
//        // print_r($field_rank);
//        // print_r($field_type);
//        // die;
//        foreach ($field as $f_k => $f_v) {
//            $thiskey = $row_arr[$f_k].'1';
//            $thisval = $f_v;
//            $excel->getActiveSheet()->setCellValue($thiskey,$thisval);
//
//            $excel->getActiveSheet()->getColumnDimension($thiskey)->setAutoSize(true);
//        }
//
//
//        $flag = 2;
//
//        foreach ($data as $key => $value) {
//
//            foreach ($value as $k => $v) {
//                if(!isset($field_rank[$k])){
//                    continue;
//                }
//                $flag_row = $field_rank[$k];
//                if($field_type[$k] == 'html'){
//                    $excel->getActiveSheet()->getStyle($row_arr[$flag_row].$flag)->getAlignment()->setWrapText();
//                }
//                $excel->getActiveSheet()->setCellValue($row_arr[$flag_row].$flag,$v);
//
//            }
//            $flag++;
//        }
//        // die;
//        //输出
//        header('Content-Type: application/vnd.ms-excel');
//        header('Content-Disposition: attachment;filename="'.$title.'.xls"');
//        header('Cache-Control: max-age=0');
//        // If you're serving to IE 9, then the following may be needed
//        header('Cache-Control: max-age=1');
//        // If you're serving to IE over SSL, then the following may be needed
//        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
//        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
//        header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
//        header('Pragma: public'); // HTTP/1.0
//        $objWriter = new \PHPExcel_Writer_Excel5($excel);
//        $objWriter->save('php://output');
    }
}
if(!function_exists('getArrVal')){
    /**
     * 获取数组中某值
     * @param array $arr 需要整理数组
     * @param string $key 字段
     * @param mixed $def 默认值
     * @return fix
     */
    function getArrVal(array $arr,string $key,$def = ''){

        if(isset($arr[$key])){
            $def = $arr[$key];
        }

        return $def;
    }
}
if(!function_exists('strToUtf8')){
    function strToUtf8($str){
        $encode = mb_detect_encoding($str, array("ASCII",'UTF-8',"GB2312","GBK",'BIG5'));
        if($encode == 'UTF-8'){
            return $str;
        }else{
            return mb_convert_encoding($str, 'UTF-8', $encode);
        }
    }
}
if(!function_exists('emoji_encode')) {
    function emoji_encode($nickname)
    {
        $strEncode = '';
        $length = mb_strlen($nickname, 'utf-8');
        for ($i = 0; $i < $length; $i++) {
            $_tmpStr = mb_substr($nickname, $i, 1, 'utf-8');
            if (strlen($_tmpStr) >= 4) {
                // $strEncode .= '[[EMOJI:'.rawurlencode($_tmpStr).']]';
                $strEncode .= '';
            } else {
                $strEncode .= $_tmpStr;
            }
        }
        return $strEncode;
    }
}
if(!function_exists('getHtmlImgSrc')) {
    function getHtmlImgSrc($str)
    {
        if(!$str){
            return [];
        }
        $preg = "/<img.*?src=[\"|\']?(.*?)[\"|\']?\s.*?>/i";

        preg_match_all($preg,$str,$res);

        return $res;
    }
}
if(!function_exists('returnData')) {
    function returnData($code,$msg='',$data = [],$extra = [])
    {

        if(!$data && $msg && is_array($msg)){
            $res['msg'] = 'ok';
            $res['data'] = $msg;
            $res['code'] = $code;
        }else{
            $res = compact('msg','data','code');
        }

        if($extra && is_array($extra)){
            $res = array_merge($res,$extra);
        }

        return $res;
    }
}
if(!function_exists('logTime')) {
    function logTime(&$arr=[],$code,$type='start')
    {
        if($type == 'end'){
            $arr[$code]['end'] = time();
            if(isset($arr[$code]['start']) && $arr[$code]['start']){
                $arr[$code]['count'] = $arr[$code]['end']-$arr[$code]['start'];
            }
        }else{
            $arr[$code]['start'] = time();
            $arr[$code]['start_str'] = date('Y-m-d H:i:s',$arr[$code]['start']);
        }

        return $arr;
    }
}
if(!function_exists('pwd_method')) {
    function pwd_method($str)
    {
        $str = md5($str);
//        $str = md5($str);
        return $str;
    }
}
if(!function_exists('arr_to_lower')) {
    function arr_to_lower($arr)
    {
        $new_arr = [];
        foreach ($arr as $k =>$v){
            $this_key = strtolower($k);
            if(is_array($v)){
                $new_arr[$this_key] = arr_to_lower($v);
            }else{
                $new_arr[$this_key] = strtolower($v);
            }
        }
        return $new_arr;
    }
}
if(!function_exists('camelize')){
    /**
     * 下划线转驼峰
     * 思路:
     * step1.原字符串转小写,原字符串中的分隔符用空格替换,在字符串开头加上分隔符
     * step2.将字符串中每个单词的首字母转换为大写,再去空格,去字符串首部附加的分隔符.
     * @param string $str 字符
     * @param int $type 转换类型 1小驼峰 2大驼峰
     * @param string $separator 替换符
     * @return string
     */
    function camelize(string $str,$type = 1,$separator='_')
    {
        $str = uncamelize($str);
        if($type == 2){
            $str = $separator. str_replace($separator, " ", $separator.strtolower($str));
        }else{
            $str = $separator. str_replace($separator, " ", strtolower($str));
        }
        $str = ltrim(str_replace(" ", "", ucwords($str)), $separator );

        return $str;
    }
}
if(!function_exists('unCamelize')){
    /**
     * 驼峰命名转下划线命名
     * 思路:
     * step1.元字符大写前拼接空格
     * step2.移除字符首位可能出现的空格,空格换成分隔符,然后全部转小写
     * @param string $camelCaps 字符
     * @param string $separator 替换符
     * @return string
     */
    function unCamelize(string $camelCaps,$separator='_')
    {
        $str = preg_replace('/([A-Z])/', ' '. "$1", $camelCaps);
        $str = strtolower(str_replace(' ', $separator, ltrim($str)));
        //dd(compact('camelCaps','str'));
        return $str;
    }
}
