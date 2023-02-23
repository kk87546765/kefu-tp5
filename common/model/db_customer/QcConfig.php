<?php

namespace common\model\db_customer;


use common\base\BasicModel;

class QcConfig extends BasicModel
{
    protected $connection = 'database.db_customer';
    protected $table = 'qc_config';
    protected $failException = true; //是否验证抛出异常
    protected $resultSetType = 'collection';
    protected $pk = 'id';
    protected $autoWriteTimestamp = false;

    public static $config_arr = [
        ['name'=>'咨询游戏','key'=>'game_type','type'=>'normal','rule'=>[]],
        ['name'=>'质检等级','key'=>'qc_level','type'=>'normal','rule'=>[]]
    ];

    public static $config_other_arr = [
        ['name'=>'质检类型','key'=>'qc_type','type'=>'source','rule'=>[]],
        ['name'=>'会话类型','key'=>'question_type','type'=>'source','rule'=>[]],
        ['name'=>'客服未解决原因','key'=>'kf_nosolve_res','type'=>'source','rule'=>[]],
        ['name'=>'问题未解决原因','key'=>'question_nosolve_res','type'=>'source','rule'=>[]],

        ['name'=>'扣分项','key'=>'qc_score_dec','type'=>'score','rule'=>[
            'val'=>[
                ['key'=>'reason','name'=>'原因','type'=>'text','class'=>'layui-col-sm6'],
                ['key'=>'score','name'=>'分数','type'=>'number','class'=>'layui-col-sm3','min'=>-100,'max'=>0]
            ]
        ]],
        ['name'=>'加分项','key'=>'qc_score_inc','type'=>'score','rule'=>[
            'val'=>[
                ['key'=>'reason','name'=>'原因','type'=>'text','class'=>'layui-col-sm6'],
                ['key'=>'score','name'=>'分数','type'=>'number','class'=>'layui-col-sm3','min'=>0,'max'=>100]
            ]
        ]],
    ];
    /*保存字段控制-过滤*/
    public static $normal_key_arr = [
        'test',
//        'qc_role_config',
        'user_group_type',
        'position_grade_arr',
        'game_product_statistic_day',
        'sobot',
        'admin_version',
        'api_ip_write_open',
        'api_ip_write',
        'api_ip_black_open',
        'api_ip_black',
        'block_waring_type',
        'block_waring_money',
        'platform_recall',
    ];

    /*保存字段控制-数组*/
    public static $array_key_arr = [
        'sobot',
        'platform_recall',
    ];

    /*需要逗号拆分字段*/
    public static $split_field_arr = [
        'user_group_type','position_grade_arr','api_ip_write','api_ip_black'
    ];

    /*
    * 注 即使改动，对应编号不能修改
    */
    public static $kf_source_arr =[
        1=>'客服-智齿',//客服-智齿
//        2=>'客服-企点',
        3=>'VIP-企点',
        4=>'VIP-微信',
        5=>'GS-内服',
        6=>'GS-微信等',
        7=>'VIP-企业Q',
        8=>'掌玩-智齿',
        9=>'VIP-返利',
    ];

    /*来源 begin*/
    const KF_SOURCE_SOBOT = 1;
    const KF_SOURCE_VIP_QD = 3;
    const KF_SOURCE_SOBOT_ZW = 8;
    public static $kf_score_arr =[
        -1=>'未评价',
        1=>'1星',
        2=>'2星',
        3=>'3星',
        4=>'4星',
        5=>'5星',
    ];
    /*来源 end*/

    /*用户分组 begin*/
    const USER_GROUP_VIP = 1;//VIP营销
    const USER_GROUP_GS = 2;//GS内服
    const USER_GROUP_QC = 3;//质检培训
    const USER_GROUP_ONLINE_KF = 4;//在线客服
    const USER_GROUP_ACCOUNT_SAFE = 5;//账号、风控
    /*用户分组 end*/

    /*职位 begin*/
    const POSITION_GRADE_NORMAL = 1;//专员
    const POSITION_GRADE_LEADER = 2;//组长
    const POSITION_GRADE_DEPARTMENT_MANAGER = 3;//主管
    const POSITION_GRADE_MANAGER = 4;//经理
    const POSITION_GRADE_DIRECTOR = 5;//总监
    const POSITION_GRADE_ACCOUNT_SAFE = 6;//负责人
    /*职位 end*/

    public function setValAttr($value,$data){
        if(!isset($data['key'])){
            dd($this,0);
            dd($data);
        }

        if(in_array($data['key'], self::$split_field_arr)){
            $val = splitToArr($value);

            $new_arr = [];

            foreach ($val as $k => $v){
                $new_arr[$k+1] = $v;
            }

            $value = $new_arr;
        }

        return json_encode($value);
    }

    public function getValAttr($value,$data){
        $res = json_decode($value,true);

        return $res;
    }

    public function getAllData(){

        $info = $this->select()->toArray();

        $new_data = [];

        if($info){
            foreach ($info as $k => $v) {
                $new_data[$v['key']] = $v['val'];
            }
        }

        return $new_data;
    }

    public function saveByKey($key,$val){

        $thisInfo = $this->where('key',$key)->find();

        if($thisInfo){
            $thisInfo->val = $val;
            $res = $thisInfo->save();
        }else{

            $add_data = [];
            $add_data['key'] = $key;
            $add_data['val'] = $val;

            $res = $this->create($add_data);
        }

        return $res;
    }
}
