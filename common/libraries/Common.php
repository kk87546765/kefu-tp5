<?php
/**
 * Created by PhpStorm.
 * User: crosstime
 * Date: 2017/2/28
 * Time: 下午2:16
 */
namespace common\libraries;

use common\server\Platform\Platform;
use common\server\Users\UserServer as polyUserLogics;
use common\server\Sdk\UserServer;
use common\server\SysServer;
use common\model\gr_chat\Admin;
use common\model\gr_chat\UserGroup;
use common\server\CustomerPlatform\CommonServer;

use common\sql_server\PlatformList;
use common\server\Statistic\GameProductServer as GameProduct;
use common\libraries\ElasticSearch;
use think\Config;

class Common
{

    const ASJD_CACHE_USERINFO = 'asjd_cache_userinfo';
    const POLYMERIZA_CONFIG_KEY = 'polymeriza_list';
    const PLATFORMSUFFIX = 'platformSuffix';
    const USER_GROUP_TYPE_VIP = 1; //用户组vip用户type
    const USER_GROUP_TYPE_GS = 2; //用户组GS用户type
    const USER_GROUP_TYPE_QC = 3; //用户组质检用户type
    const USER_GROUP_TYPE_CS = 4; //用户组在线客服用户type
    const ASJD_TO_SDK = 1; //阿斯加德id换sdkid
    const SDK_TO_ASJD = 2; //sdkid换阿斯加德id
    const REDIS_CACHE_PREFIX = 'youyu_customer';
    public static $test_count = 0;


    public static function checkAccountStatus($platform, $account, $type)
    {

        $config = Common::getPlatformInfoBySuffixAndCache($platform);

        $check_user_status_url = $config['check_user_status'];
        $data['account'] = $account;
        $data['type'] = $type;
        $data['time'] = time();
        $data['sign'] = md5("account={$data['account']}type={$data['type']}time={$data['time']}key={$config['url_key']}");

        $res = Curl::post($check_user_status_url, $data);

        return json_decode($res, 1);

    }


    //获取平台信息
    public static function getPlatform()
    {

        $platform_list = SysServer::getPlatformList();
        $new_platform_list = [];
        foreach ($platform_list as $k => $v) {
            $new_platform_list[$v['suffix']]['id'] = $v['platform_id'];
            $new_platform_list[$v['suffix']]['name'] = $v['name'];
            $new_platform_list[$v['suffix']]['field'] = $v['suffix'];

            if (!empty($v['config'])) {
                foreach ($v['config'] as $k1 => $v1) {
                    $new_platform_list[$v['suffix']][$k1] = $v1;
                }
            }
        }

        return $new_platform_list;
    }

    //获取指定配置文件内容
    public static function getConfig($config_name)
    {

        $config_info = Config::get($config_name);
        if (!isset($config_info[$config_name])) {
            return [];
        }
        $config_info = $config_info[$config_name];

        return $config_info;
    }

    //获取产品列表
    public static function getProductList($type = 1)
    {

        $product_list = Config::get('gamekey');

        $arr = [];
        //需要按id分组
        if ($type == 1) {
            foreach ($product_list['gamekey'] as $k => $v) {

                $arr[$v['id']]['id'] = $v['id'];
                $arr[$v['id']]['name'] = $v['name'];
                $arr[$v['id']]['code'] = $k;

            }

            //不需要按id分组
        } elseif ($type == 2) {
            foreach ($product_list['gamekey'] as $k => $v) {
                $arr[$k]['id'] = $v['id'];
                $arr[$k]['name'] = $v['name'];
                $arr[$k]['code'] = $k;

            }
        }


        return $arr;
    }

    /**
     * @param array $data
     * @param bool $ifDisplayPlatformName
     * @return array
     */
    public static function getGameProduct($data = array(), $ifDisplayPlatformName = true)
    {
        $result = [];
        $res = GameProduct::getGameProductList($data);
        foreach ($res as $v) {
            $result[$v['id']] = $v;
        }
        //是否显示平台名称
        if ($ifDisplayPlatformName) {
            $platformArr = self::getPlatformList();
            foreach ($result as &$value) {
                $value['platform_name'] = $platformArr[$value['platform_id']]['platform_name'];
                $value['product_platform_name'] = $value['product_name'] . '(' . $platformArr[$value['platform_id']]['platform_name'] . ')';
            }
        }
        return $result;
    }

    /**
     * 获取满足条件的平台列表，返回以平台id为key的数组
     * @param array $data
     * @return array
     */
    public static function getPlatformList($data = array())
    {
        $result = [];
        $res = PlatformList::getPlatformList($data);
        foreach ($res as $v) {
            $result[$v['platform_id']] = $v;
        }


        return $result;
    }

    /**
     * @param string $platformSuffix
     * @return false|mixed
     */
    public static function getPlatformInfoBySuffixAndCache($platformSuffix = '')
    {
        $result = false;
        if (empty($platformSuffix)) return $result;
        $key = self::creatCacheKey(PlatformList::PLATFORM_SUFFIX_INFO_KEY);
        $field = PlatformList::PLATFORM_SUFFIX_FIELD . $platformSuffix;
        $redisModel = get_redis();
        $info = $redisModel->hget($key, $field);
        if (!empty($info)) {
            $result = json_decode($info, true);
            return $result;
        }
        $where = [
            'platform_suffix' => $platformSuffix,
//            'static' => 1
        ];
        $res = PlatformList::getPlatformList($where);
        if (!empty($res) && is_array($res)) {
            $result = $res[0];
            $redisModel->hset($key, $field, json_encode($result));
        }
        return $result;
    }

    /**
     * @param int $platformId
     * @return false|mixed
     */
    public static function getPlatformInfoByPlatformIdAndCache($platformId = 0)
    {

        $result = false;
        if (empty($platformId)) return $result;
        $key = self::creatCacheKey(PlatformList::PLATFORM_SUFFIX_INFO_KEY);

        $field = PlatformList::PLATFORM_ID_FIELD . $platformId;

        $redisModel = get_redis();

        $info = $redisModel->hget($key, $field);

        if (!empty($info)) {
            $result = json_decode($info, true);
            return $result;
        }
        $where = [
            'platform_id' => $platformId,
//            'static' => 1
        ];


        $res = PlatformList::getPlatformList($where);

        if (!empty($res) && is_array($res)) {
            $result = $res[0];

            $redisModel->hset($key, $field, json_encode($result));
        }
        return $result;
    }


    //opensearch的平台语句
    public static function composePlatform()
    {
        $config = new Config();
        $platform_list = $config->getDI()->get('config')['platform_list']->toArray();
        $platforms = array_keys($platform_list);
        $str = 'tkey:"1"';
        foreach ($platforms as $k => $v) {
            $str .= " OR tkey:\"{$v}\"";
        }

        return $str;
    }

    /**
     * @param array $uids
     * @return array
     */
    public static function getAsjdCacheUserInfo($uids = [])
    {

        $redis = get_redis();
        $configInfo = Config::get(self::POLYMERIZA_CONFIG_KEY);

        $result = [];
        $no_cache = [];

        foreach ($uids as $v) {
            $res = $redis->hget(self::ASJD_CACHE_USERINFO, $v);
            $res = json_decode($res, 1);
            if ($res) {
                $res['tkey'] = $configInfo[self::PLATFORMSUFFIX][$res['cooperationID']];
                $result[$v] = $res;
            } else {
                array_push($no_cache, $v);
            }
        }


        $no_cache_uids = implode(',', $no_cache);

        $res = self::urlGetAsjdUserInfo($no_cache_uids, self::ASJD_TO_SDK);


        if ($res['code'] == 1) {
            foreach ($res['data']['success'] as $k1 => $v1) {
                $r = $redis->hset(self::ASJD_CACHE_USERINFO, $v1['uid'], json_encode($v1));
                if ($r) {
                    $v1['tkey'] = $configInfo[self::PLATFORMSUFFIX][$v1['cooperationID']];
                    $result[$v1['uid']] = $v1;
                } else {
//                    array_push($result[$v1['uid']],json_decode([],1));
                }
            }

        }


        return $result;
    }

    /**
     * @param array $uids
     * @return array|false
     */
    public static function getSDKUserInfo($uids = [])
    {

        if (!is_array($uids)) return false;

        $configInfo = Config::get(self::POLYMERIZA_CONFIG_KEY);

        $result = [];

        $str_uids = implode(',', $uids);
        $res = self::urlGetAsjdUserInfo($str_uids, self::SDK_TO_ASJD);

        if ($res['code'] == 1) {
            foreach ($res['data']['success'] as $k1 => $v1) {
                $v1['tkey'] = $configInfo[self::PLATFORMSUFFIX][$v1['cooperationID']];
                $result[$v1['uid']] = $v1;
            }

        }

        return $result;
    }

    /**
     * https://us.asgardstudio.cn/api/get_sdkuid.php?uid=1&appid=2&time=2&sign=3
     * params $uids string
     * params $type int 1代表阿斯加德id换平台id，2代表平台id换阿斯加德id
     */
    public static function urlGetAsjdUserInfo($uids, $type = self::ASJD_TO_SDK)
    {

        $time = time();
        $key = 'c1b5d4dc0084b4db0af583f8d399d980';
        $sign = md5($uids . $time . $key);
        $post_data = [
            'uid' => $uids,
            'time' => $time,
            'sign' => $sign,
            'type' => $type
        ];
        $url = "https://us.asgardstudio.cn/api/get_sdkuid.php";
        $res = self::curl_init_post($url, $post_data);

        return json_decode($res, 1);
    }

    public static function replace_specialChar($strParam)
    {
        $regex = "/\/|\～|\，|\。|\！|\？|\“|\”|\【|\】|\『|\』|\：|\；|\《|\》|\’|\‘|\ |\·|\~|\!|\@|\#|\\$|\%|\^|\&|\*|\(|\)|\_|\+|\{|\}|\:|\<|\>|\?|\[|\]|\,|\.|\/|\;|\'|\`|\-|\=|\\\|\|/";
        return preg_replace($regex, "", $strParam);
    }


    /**
     * 二维数组根据某个字段排序
     * @param array $array 要排序的数组
     * @param string $keys 要排序的键字段
     * @param string $sort 排序类型  SORT_ASC     SORT_DESC
     * @return array 排序后的数组
     */
    public static function arraySort($array, $keys, $sort = SORT_DESC)
    {
        $keysValue = [];
        foreach ($array as $k => $v) {
            $keysValue[$k] = $v[$keys];
        }
        array_multisort($keysValue, $sort, $array);
        return $array;
    }


    public static function curl_init_post($url, $params, $timeout = 180, $header = array())
    {
        $ch = curl_init();
        // 设置 curl 相应属性
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        if ($header) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header); //定义请求类型
        }
        $returnTransfer = curl_exec($ch);

        curl_close($ch);

        return $returnTransfer;
    }


    //中文转数字映射
    static function checkNatInt($str)
    {

        $map = array(
            '零' => '0', '一' => '1', '二' => '2', '三' => '3', '四' => '4', '五' => '5', '六' => '6', '七' => '7', '八' => '8', '九' => '9',
            '〇' => '0', '壹' => '1', '贰' => '2', '叁' => '3', '肆' => '4', '伍' => '5', '陆' => '6', '柒' => '7', '捌' => '8', '玖' => '9',
            '①' => 1, '②' => 2, '③' => 3, '④' => 4, '⑤' => 5, '⑥' => 6, '⑦' => '7', '⑧' => 8, '⑨' => 9,
            '零' => '0', '两' => '2',
            '仟' => '千', '佰' => '百', '拾' => '十',
            '万万' => '亿'
        );

        $str = str_replace(array_keys($map), array_values($map), $str);


//        $str = self::checkString($str, '/([\d亿万千百十]+)/u',$str);
        $str = self::checkString($str, '', $str);

        $func_c2i = function ($str, $plus = false) use (&$func_c2i) {
            if (false === $plus) {
                $plus = array('亿' => 100000000, '万' => 10000, '千' => 1000, '百' => 100, '十' => 10,);
            }

            $i = 0;

            if ($plus)
                foreach ($plus as $k => $v) {
                    $i++;
                    if (strpos($str, $k) !== false) {
                        $ex = explode($k, $str, 2);
                        $new_plus = array_slice($plus, $i, null, true);

                        $l = $func_c2i($ex[0], $new_plus);
                        $r = $func_c2i($ex[1], $new_plus);


                        if ($l == 0) $l = 1;
                        return $l * $v + $r;
                    }
                }

            return $str;
        };

        return $func_c2i($str);

    }

    //获取各个平台的用户信息
    static function getPlatformUserInfo($platform, $uid = [], $type = 1)
    {

        if (empty($platform) || empty($uid)) return false;

        $new_uid_info_arr = [];
        $str_uids = implode(',', $uid);
        if ($platform !== 'asjd') {

            //根据模式，切换查询用户状态的方式，1是查中心的数据库里拉取的数据，2是直接查平台的用户状态
            if ($type == 1) {
                $info = self::getUserInfoBySql($platform, $str_uids);
            } elseif ($type == 2) {
                $info = self::getUserInfoByUrl($platform, $str_uids);
            }


            if ($info) {
                foreach ($info as $k => $v) {
                    $new_uid_info_arr[$v['uid']]['uid'] = $v['uid'];
                    $new_uid_info_arr[$v['uid']]['status'] = $v['status'];
                    $new_uid_info_arr[$v['uid']]['platform'] = $v['platform'];
                }
            }


        } else {

            $info = polyUserLogics::getUserInfoByUid($uid);

            unset($info['fail']);

            foreach ($info as $k => $v) {
                $platform = $k;
                $arr_uids = array_column($v, 'sdkUid', 'uid');

                foreach ($arr_uids as $k => $v) {

                    //根据模式，切换查询用户状态的方式，1是查中心的数据库里拉取的数据，2是直接查平台的用户状态 （由于中心的数据库不全，所以暂时直接查询平台）
                    if ($type == 1) {
                        $info1 = self::getUserInfoBySql($platform, $v);
                    } elseif ($type == 2) {
                        $info1 = self::getUserInfoByUrl($platform, $v);

                    }
                    if ($info1) {
                        $new_uid_info_arr[$k]['sdkUid'] = $v;
                        $new_uid_info_arr[$k]['status'] = isset($info1[0]['status']) ? $info1[0]['status'] : 0;
                        $new_uid_info_arr[$k]['platform'] = isset($info1[0]['platform']) ? $info1[0]['platform'] : '';
                    }

                }
            }
        }
        return $new_uid_info_arr;
    }

    static function getUserInfoByUrl($platform, $uid)
    {

        if (empty($platform) || empty($uid)) return false;
        $info = self::checkAccountStatus($platform, $uid, 1);

        $res = [];
        if ($info['state']['code'] == 1) {
            foreach ($info['data'] as $k => $v) {
                $res[$k]['uid'] = $v['account'];
                $res[$k]['status'] = $v['status'];
            }

            return $res;
        } else {
            return false;
        }

    }

    static function getUserInfoBySql($platform, $uid)
    {

        $kefu_model = CommonServer::getPlatformModel('KefuCommonMember', $platform);

        $sql = "SELECT uid,`status`,'{$platform}' as `platform`,`reg_channel` from db_customer_{$platform}.kefu_common_member where uid in({$uid})";

        $info = $kefu_model->query($sql);

        $info = isset($info[0]) ? $info : '';

        return $info;
    }

    static function checkString($var, $check = '', $default = '')
    {
        if (!is_string($var)) {
            if (is_numeric($var)) {
                $var = (string)$var;
            } else {
                return $default;
            }
        }


        if ($check) {
            return (preg_match($check, $var, $ret) ? $ret[1] : $default);
        }

        return $var;
    }

    /**
     * 获取后台用户列表，可以筛选已经分配了用户组的用户
     * @param array $userWhereArr
     * @param array $GroupWhereArr
     * @param false $isExistGroup
     * @return array
     */
    public static function getAdminUserList($userWhereArr = [], $GroupWhereArr = [], $isExistGroup = false)
    {
        $groupIds = $resultArr = [];
        $userList = Admin::getAdminUserInfo($userWhereArr);
        //组装数据
        if ($isExistGroup) {
            $groupArr = UserGroup::getUserGroupList($GroupWhereArr);
            $groupIds = array_column($groupArr, 'id');
            foreach ($userList as $value) {
                $tmpArr = explode(',', $value['group_id']);
                foreach ($tmpArr as $v) {
                    if (in_array($v, $groupIds)) {
                        $resultArr[$value['id']] = $value;
                    }
                }
            }
        } else {
            foreach ($userList as $value) {
                $resultArr[$value['id']] = $value;
            }
        }

        return $resultArr;
    }

    /**
     * 返回固定前缀的cache key
     * @param string $keySuffix
     * @return string
     */
    public static function creatCacheKey($keySuffix = '')
    {
        $result = self::REDIS_CACHE_PREFIX;
        if (!empty($keySuffix)) $result .= "_" . $keySuffix;

        return $result;
    }

    public static function GetMonth($sign = "1")
    {
        //得到系统的年月
        $tmp_date = date("Ym");
        //切割出年份
        $tmp_year = substr($tmp_date, 0, 4);
        //切割出月份
        $tmp_mon = substr($tmp_date, 4, 2);
        $tmp_nextmonth = mktime(0, 0, 0, $tmp_mon + 1, 1, $tmp_year);
        $tmp_forwardmonth = mktime(0, 0, 0, $tmp_mon - 1, 1, $tmp_year);
        if ($sign == 0) {
            //得到当前月的下一个月
            return $fm_next_month = date("Ym", $tmp_nextmonth);
        } else {
            //得到当前月的上一个月
            return $fm_forward_month = date("Ym", $tmp_forwardmonth);
        }
    }


    /**
     * elasticSearch获取指定id
     * @param string $keySuffix
     * @return string
     */
    public static function getbyIds($ids)
    {
        $es = new ElasticSearch();
        foreach ($ids as $v) {
            $query['bool']['should'][]['match']['id'] = $v;

        }
        if (isset($query)) {
            $query['bool']['minimum_should_match'] = 1; //should至少返回一个值
        }

        $last_month = Common::GetMonth(1);
        $now_month = date('Ym');
        $next_month = Common::GetMonth(0);

        $result = $es->search(
            [
                $es->index_name . '-' . $last_month,
                $es->index_name . '-' . $now_month,
                $es->index_name . '-' . $next_month
            ],
            $query,
            '',
            ['time' => ['order' => 'desc']]

        );

        return empty($result) ? [] : $result['data'];
    }


    //下划线转驼峰
    public static function camelize($uncamelized_words, $separator = '_')
    {
        $uncamelized_words = $separator . str_replace($separator, " ", strtolower($uncamelized_words));
        return ltrim(str_replace(" ", "", ucwords($uncamelized_words)), $separator);
    }


    //驼峰命名转下划线命名
    public static function  uncamelize($camelCaps,$separator='_')
    {
        return strtolower(preg_replace('/([a-z])([A-Z])/', "$1" . $separator . "$2", $camelCaps));
    }
}
