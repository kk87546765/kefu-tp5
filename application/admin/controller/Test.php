<?php
namespace app\admin\controller;

use common\libraries\Common;
use think\Config;
class Test
{
    protected $no_oauth = ['a'];

    public function index()
    {
Common::getConfig('gamekey');
        var_dump(Config::load('config.php')['root_namespace']);exit;
        return return_json(['code'=>0,'msg'=>'ok']);
    }

    public function scriptDetail(){

        $api_list = [
            [
                'url'=>'/api/Vip/updateAllTotalPayThirtyDayPay',
                'name'=>'历史、30天累充-执行',
                'method'=>'post',
                'is_go_on'=>1,
                'is_auto_page'=>1,
                'params'=>[
                    ['key'=>'page','def_value'=>1,'title'=>'页数','desc'=>'',],
                    ['key'=>'limit','def_value'=>10,'title'=>'分页','desc'=>'',],
                    ['key'=>'last_pay_time','def_value'=>'Y-m-d','title'=>'用户最后支付日期开始','desc'=>'更新最后支付日期>=此时间用户',],
                    ['key'=>'last_pay_time_end','def_value'=>'Y-m-d','title'=>'用户最后支付日期结束','desc'=>'更新最后支付日期<此时间用户',],
                ],
            ],
            [
                'url'=>'/api/Vip/updateUserBaseInfo',
                'name'=>'更新用户user_name空的用户基础数据',
                'method'=>'post',
                'is_go_on'=>1,
                'is_auto_page'=>0,
                'params'=>[
                    ['key'=>'limit','def_value'=>50,'title'=>'分页','desc'=>'',],
                    ['key'=>'platform','def_value'=>'ll','title'=>'平台','desc'=>'ll,mh,zw,xll,youyu...',]
                ],
            ],
            [
                'url'=>'/api/Vip/delErrorDistributeVip',
                'name'=>'更新清除分配错误用户数据',
                'method'=>'post',
                'is_go_on'=>1,
                'is_auto_page'=>1,
                'params'=>[
                    ['key'=>'page','def_value'=>1,'title'=>'页数','desc'=>'',],
                    ['key'=>'limit','def_value'=>50,'title'=>'分页','desc'=>'',],
                    ['key'=>'start_time','def_value'=>'Y-m-d','title'=>'用户最后分配时间日期开始','desc'=>'更新最后分配时间日期>=此时间用户',],
                    ['key'=>'end_time','def_value'=>'Y-m-d','title'=>'用户最后分配时间日期结束','desc'=>'更新最后分配时间日期<此时间用户',],
                    ['key'=>'platform','def_value'=>'youyu','title'=>'平台','desc'=>'ll,mh,zw,xll,youyu...',],
                ],
            ],
            [
                'url'=>'/scripts/vipkfday/run',
                'name'=>'每日用户数据统计-执行',
                'method'=>'get',
                'is_go_on'=>1,
                'is_auto_page'=>0,
                'params'=>[
                    ['key'=>'add_day','def_value'=>1,'title'=>'日期自增','desc'=>'当天脚本全部执行后日期自动增加1天，1=>是 0=>否',],
                    ['key'=>'allow_func','def_value'=>'all','title'=>'需执行函数','desc'=>'空执行全部 多个函数逗号分开',],
                ],
            ],
            [
                'url'=>'/scripts/vipkfday/set_time',
                'name'=>'每日用户数据统计-设置-设置统计开始日期',
                'method'=>'get',
                'is_go_on'=>0,
                'is_auto_page'=>0,
                'params'=>[
                    ['key'=>'day','def_value'=>'Y-m-d','title'=>'日期','desc'=>'日期 Ymd',],
                ],
            ],
            [
                'url'=>'/scripts/vipkfday/clean',
                'name'=>'每日用户数据统计-设置-清除缓存',
                'method'=>'get',
                'is_go_on'=>0,
                'is_auto_page'=>0,
                'params'=>[
                    ['key'=>'func', 'def_value'=>'all', 'title'=>'清除（函数）缓存', 'desc'=>'清除脚本执行锁、对应函数（锁、分页）缓存',],
                ],
            ],
            [
                'url'=>'/api/Sobot/update20210727',
                'name'=>'质检修复qc_num',
                'method'=>'get',
                'is_go_on'=>1,
                'is_auto_page'=>0,
                'params'=>[
                ],
            ],
            [
                'url'=>'/api/Vip/encryptMobile',
                'name'=>'加密手机号-recharge_user',
                'method'=>'post',
                'is_go_on'=>1,
                'is_auto_page'=>0,
                'params'=>[
                    ['key'=>'limit','def_value'=>50,'title'=>'执行条数','desc'=>'',],
                    ['key'=>'change','def_value'=>0,'title'=>'执行类型','desc'=>'0 加密 1解密',],
                ],
            ],
            [
                'url'=>'/api/Vip/encryptMobileByPlatform',
                'name'=>'加密手机号(平台)',
                'method'=>'post',
                'is_go_on'=>1,
                'is_auto_page'=>0,
                'params'=>[
                    ['key'=>'limit','def_value'=>50,'title'=>'执行条数','desc'=>'',],
                    ['key'=>'platform_id','def_value'=>'0','title'=>'平台','desc'=>'1 流连 2 茂宏 3 阿斯加德 4 掌玩 5 新流连 6 游娱',],
                    ['key'=>'change','def_value'=>0,'title'=>'执行类型','desc'=>'0 加密 1解密',],
                ],
            ],
            [
                'url'=>'/api/Sobot/update20211025',
                'name'=>'修复质检10月23-25数据',
                'method'=>'post',
                'is_go_on'=>1,
                'is_auto_page'=>0,
                'params'=>[
                    ['key'=>'limit','def_value'=>50,'title'=>'执行条数','desc'=>'',],
                ],
            ],
            [
                'url'=>'/api/Vip/checkUserRechargeOverTime',
                'name'=>'修复vip30天累充',
                'method'=>'post',
                'is_go_on'=>1,
                'is_auto_page'=>0,
                'params'=>[
                    ['key'=>'limit','def_value'=>100,'title'=>'执行条数','desc'=>'',],
                    ['key'=>'platform_id','def_value'=>'0','title'=>'平台','desc'=>'1 流连 2 茂宏 3 阿斯加德 4 掌玩 5 新流连 6 游娱',],
                ],
            ],
            [
                'url'=>'/api/Vip/updateRemarkTime',
                'name'=>'补充用户建联时间',
                'method'=>'post',
                'is_go_on'=>1,
                'is_auto_page'=>0,
                'params'=>[
                    ['key'=>'limit','def_value'=>100,'title'=>'执行条数','desc'=>'',],
                ],
            ],
            [
                'url'=>'/api/Vip/delErrorUser',
                'name'=>'清除订单存在，用户信息不存在用户数据',
                'method'=>'post',
                'is_go_on'=>1,
                'is_auto_page'=>0,
                'params'=>[
                    ['key'=>'limit','def_value'=>100,'title'=>'分页','desc'=>'',],
                    ['key'=>'platform_id','def_value'=>'6','title'=>'平台ID','desc'=>'1,2,3,..',]
                ],
            ],
        ];

        $this->rs['data'] = $api_list;

        return return_json($this->rs);
    }

    public function scriptTest()
    {
        sleep(1);
        $this->rs['msg'] = createNonceStr(mt_rand(5,15));
        return return_json($this->rs);
    }


    public function test123()
    {
    $obj = new \common\base\BasicController();

    var_dumP($obj);exit;
    }

}
