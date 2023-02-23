<?php
namespace app\admin\controller;

use common\libraries\ApiUserInfoSecurity;
use common\libraries\Common;
use common\server\AdminServer;
use common\server\CustomerPlatform\CommonServer;
use common\server\SysServer;
use common\server\Vip\LossUserServer;
use common\server\Vip\StatisticServer as thisServer;
use think\Config;
use think\Env;

class Test extends Oauth
{
    protected $no_oauth = ['test123'];
    protected $no_login = ['test123'];

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
                'url'=>'/scripts/vipKfDay/run',
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
                'url'=>'/scripts/vipKfDay/set_time',
                'name'=>'每日用户数据统计-设置-设置统计开始日期',
                'method'=>'get',
                'is_go_on'=>0,
                'is_auto_page'=>0,
                'params'=>[
                    ['key'=>'day','def_value'=>'Y-m-d','title'=>'日期','desc'=>'日期 Ymd',],
                ],
            ],
            [
                'url'=>'/scripts/vipKfDay/clean',
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
            [
                'url'=>'/admin/Test/allUserLogout',
                'name'=>'强制后台用户下线',
                'method'=>'post',
                'is_go_on'=>0,
                'is_auto_page'=>0,
                'params'=>[
                    ['key'=>'uid','def_value'=>'','title'=>'用户id','desc'=>'不填则除当前账号其他全部下线',],
                    ['key'=>'platform_id','def_value'=>'','title'=>'平台ID','desc'=>'1,2,3,..',]
                ],
            ],
            [
                'url'=>'/admin/Test/unionUserData',
                'name'=>'用户账号整合',
                'method'=>'post',
                'is_go_on'=>1,
                'is_auto_page'=>0,
                'params'=>[
                    ['key'=>'platform_id','def_value'=>'6','title'=>'平台ID','desc'=>'1',],
                    ['key'=>'limit','def_value'=>'10','title'=>'每次执行条数','desc'=>'1',],
                ],
            ],
            [
                'url'=>'/admin/Test/unionUserDataSetUid',
                'name'=>'用户账号整合-设置起始id',
                'method'=>'post',
                'is_go_on'=>0,
                'is_auto_page'=>0,
                'params'=>[
                    ['key'=>'platform_id','def_value'=>'6','title'=>'平台ID','desc'=>'1',],
                    ['key'=>'id','def_value'=>'0','title'=>'起始id','desc'=>'kefu_commmon_member id',],
                ],
            ],
            [
                'url'=>'/admin/Test/updateUserInfo',
                'name'=>'用户账号补充统计数据',
                'method'=>'post',
                'is_go_on'=>1,
                'is_auto_page'=>0,
                'params'=>[
                    ['key'=>'platform_id','def_value'=>'6','title'=>'平台ID','desc'=>'1',],
                    ['key'=>'limit','def_value'=>'10','title'=>'每次执行条数','desc'=>'1',],
                ],
            ],
            [
                'url'=>'/admin/Test/updateUserInfoSetUid',
                'name'=>'用户账号补充统计数据-设置起始id',
                'method'=>'post',
                'is_go_on'=>0,
                'is_auto_page'=>0,
                'params'=>[
                    ['key'=>'platform_id','def_value'=>'6','title'=>'平台ID','desc'=>'1',],
                    ['key'=>'id','def_value'=>'0','title'=>'起始id','desc'=>'loss_user id',],
                ],
            ],
            [
                'url'=>'/admin/Test/phoneChangeSecurity',
                'name'=>'手机号加解密',
                'method'=>'post',
                'is_go_on'=>0,
                'is_auto_page'=>0,
                'params'=>[
                    ['key'=>'mobile','def_value'=>'','title'=>'手机','desc'=>'1',],
                    ['key'=>'type','def_value'=>'1','title'=>'操作类型','desc'=>'1加密 2 解密 ',],
                ],
            ],
            [
                'url'=>'/admin/Test/updateHistoryRoleLevel',
                'name'=>'游娱历史角色等级更新',
                'method'=>'post',
                'is_go_on'=>0,
                'is_auto_page'=>0,
                'params'=>[
                    ['key'=>'limit','def_value'=>'100','title'=>'执行限制','desc'=>'执行限制',],
                ],
            ],
            [
                'url'=>'/admin/Test/cleanCacheUserList',
                'name'=>'更新后台用户缓存',
                'method'=>'post',
                'is_go_on'=>0,
                'is_auto_page'=>0,
                'params'=>[
                ],
            ],
        ];

        $this->rs['data'] = $api_list;

        return return_json($this->rs);
    }

    public function allUserLogout(){
        $param = $this->getPost([
            ['uid','trim',''],
            ['platform_id','trim',''],
        ]);

        $list = SysServer::getUserListByAdminInfo($this->user_data,$param);

        if(!$list){
            $this->rs['code'] = 1;
            $this->rs['msg'] = '没有可管理用户';
            return return_json($this->rs);
        }
        $uid_arr = [];
        if(!empty($param['uid'])){
            $uid_arr = splitToArr($param['uid']);
        }

        $flag = 0;
        foreach ($list as $item){
            if($item['status'] != 1) continue;

            if($uid_arr){
                if(!in_array($item['id'],$uid_arr)){
                    continue;
                }
            }else{
                if($item['id'] == $this->user_data['id']){
                    continue;
                }
            }

            if(AdminServer::Logout($item['id'])){
                $flag++;
            }
        }
        $this->rs['msg'] = '成功数量：'.$flag;
        return return_json($this->rs);
    }

    public function unionUserData(){

        $param = $this->getPost([
            ['platform_id','int',0],
            ['limit','int',10],
        ]);

        $this->rs = array_merge($this->rs,LossUserServer::unionUserByCache($param['platform_id'],$param['limit']));

        return return_json($this->rs);
    }

    public function unionUserDataSetUid(){

        $param = $this->getPost([
            ['id','int',0],
            ['platform_id','int',0],
        ]);

        $cache_name_last_user_id = 'unionUserLastId'.'_'.$param['platform_id'];

        $res = cache($cache_name_last_user_id,$param['id'],3600);

        return return_json($this->rs);
    }

    public function updateUserInfo(){

        $param = $this->getPost([
            ['platform_id','int',0],
            ['limit','int',10],
        ]);

        $this->rs = array_merge($this->rs,LossUserServer::updateUserInfoByCache($param['platform_id'],$param['limit']));

        return return_json($this->rs);
    }

    public function updateUserInfoSetUid(){

        $param = $this->getPost([
            ['id','int',0],
            ['platform_id','int',0],
        ]);

        $cache_name_last_user_id = 'updateUserInfo'.'_'.$param['platform_id'];

        $res = cache($cache_name_last_user_id,$param['id'],3600);

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
        dd(LossUserServer::updateUserInfo([]));//1645969898
    }

    public function updateHistoryRoleLevel(){
        $this->setMicrotime('begin');
        $limit = $this->req->post('limit/d',100);

        $model = CommonServer::getPlatformModel('KefuUserRole','youyu');

        $id = cache('test_updateHistoryRoleLevel');

        $id = $id?$id:0;

        $sql = "SELECT r.id,t.role_level FROM kefu_user_role r INNER JOIN test2 t ON
                r.role_id = t.role_id 
                AND r.reg_gid = t.reg_gid 
                AND r.uid = t.uid 
                WHERE t.role_level > r.role_level 
                AND r.id > $id
                ORDER BY r.id ASC 
                limit $limit
        ";

        $list = $model->query($sql);
        $this->setMicrotime('select');
        $res = 0;
        if($list){
            $sql = 'UPDATE kefu_user_role SET role_level = CASE id ';
            $ids = [];
            foreach ($list as $item){
                $ids[] = $item['id'];
                $sql .= " WHEN $item[id] THEN $item[role_level]";
            }
            $sql .= ' END';
            $sql .=" WHERE id IN(".implode(',',$ids).")";

            $res = $model->execute($sql);

//            $res = $model->saveAll($list);

            $last_info = array_shift($list);
            cache('test_updateHistoryRoleLevel',$last_info['id'],360);
        }
        $this->setMicrotime('save');
        if(!$res){
            $this->rs['code'] = 1;
            $this->rs['msg'] = 'end';
        }

        return return_json($this->rs);
    }

    public function phoneChangeSecurity(){
        $param = $this->getPost([
            ['mobile','trim',''],
            ['type','trim',1],
        ]);
        if($param['type'] == 2){
            $this->rs['data'] = ApiUserInfoSecurity::decrypt($param['mobile']);
        }else{
            $this->rs['data'] = htmlspecialchars(ApiUserInfoSecurity::encrypt($param['mobile']));
        }

        return return_json($this->rs);
    }

    public function cleanCacheUserList(){

        $this->rs['data'] = SysServer::getAdminListCache(0,1);

        return return_json($this->rs);
    }
}
