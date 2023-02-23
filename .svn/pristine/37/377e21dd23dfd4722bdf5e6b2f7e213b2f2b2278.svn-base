<?php
/**
 * 系统
 */
namespace common\server\Qc;

use common\base\BasicServer;
use common\libraries\QcConversation;
use common\model\db_customer\QcAppealLog;
use common\model\db_customer\QcConfig;
use common\model\db_customer\QcQuestion;
use common\model\db_customer\QcQuestionLog;
use common\model\db_customer\QcQuestionScoreLog;
use common\model\db_customer\QcSellQuestion;
use common\model\db_customer\QdConversation;
use common\model\db_customer\SobotConversation;
use common\model\db_customer\SobotMsg;
use common\model\db_customer\SobotSummary;
use common\model\db_customer\SobotUsers;
use common\model\gr_chat\Admin;
use common\model\gr_chat\UserGroup;
use common\server\AdminServer;
use common\server\ListActionServer;
use common\server\SysServer;
use Symfony\Component\DomCrawler\Crawler;
use think\Db;


class QcQuestionServer extends BasicServer
{
    public static function update20210727(){

        $code = [0=>'success',1=>"end"];

        $model = new QcQuestion();

        $where = [];
        $where['qc_num'] = ['like','CONCAT%'];
        $list = $model->where($where)->limit(100)->select();

        if(!$list->toArray()){
            return ['code'=>1,'msg'=>$code[1]];
        }
        $flag = 0;
        $count = 0;
        foreach ($list as $v){
            $count++;
            $preg = '/[0-9]{1,}/';
            $res = preg_match($preg, $v->qc_num,$data);

            if($res){
                $res = $v->save(['qc_num'=>$data[0].$v->id]);
                if($res) $flag++;
            }
        }

        return ['code'=>0,'msg'=>"总：$count 改：$flag"];
    }

    public static function update20211025(){

        $code = [0=>'success',1=>"end"];

        $model = new SobotMsg();

        $list = $model->where("(LOCATE('\_',msg) OR LOCATE('\%',msg))")->limit(100)->select();

        if(!$list->toArray()){
            return ['code'=>1,'msg'=>$code[1]];
        }

        $flag = 0;
        $count = 0;

        $need_update_cid = [];

        foreach ($list as $v){
            if(!in_array($v->cid,$need_update_cid)){
                $need_update_cid[] = $v->cid;
                $count++;
            }
            $this_msg = str_replace('\_','_',$v->msg);
            $this_msg = str_replace('\%','\_',$this_msg);

            if($this_msg != $v->msg){
                $v->save(['msg'=>$this_msg]);
                $flag++;
            }
        }
        if($need_update_cid){
            $SobotConversation = new SobotConversation();
            $where = [];
            $where['cid'] = ['in',$need_update_cid];
            $where['need_update'] = 0;
            $SobotConversation->where($where)->update(['need_update'=>1]);
        }

        return ['code'=>0,'msg'=>"总：$count 改：$flag"];
    }

    /**
     * 质检池-列表
     * @param $p_data
     * @return array
     */
    public static function list($p_data){
        $type = getArrVal($p_data,'type',0);
        $page = getArrVal($p_data,'page',1);
        $limit = getArrVal($p_data,'limit',20);

        $where = getDataByField($p_data,['admin_id','question_type','game_type','text_id','partnerid'],true);
        if($type == 1){
            $where[] = ['qc_question_log_id','=',0];
            $where[] = ['admin_id','>',0];
            if(self::$user_data['is_admin'] == 0){
                if(!in_array(QcConfig::USER_GROUP_QC,self::$user_data['user_group_type_arr'])){
                    //非质检
//                        self::self::$$user_data_platform_id = SysLogic::getAdminPlatformId(self::self::$$user_data);
                    if(self::$user_data['platform_id']){
                        $where[] = ['kf_platform','in',self::$user_data['platform_id']];
                    }else{
                        $where[] = ['kf_platform','=',-1];
                    }
                }else{
                    //质检
                    if(self::$user_data['position_grade'] == QcConfig::POSITION_GRADE_NORMAL){
                        $where['admin_id'] = self::$user_data['id'];
                    }elseif(self::$user_data['position_grade'] == QcConfig::POSITION_GRADE_LEADER){

                        $ids = AdminServer::getAdminIdsByGroupId(self::$user_data['group_id']);
                        if(!$ids){
                            $ids = [0];
                        }
                        $where[] = ['admin_id','in',$ids];
                    }
                }
            }
        }

        if($p_data['kf_score']){
            $this_data = explode(',',$p_data['kf_score']);

            if(count($this_data) == 1){
                $where['kf_score'] = $this_data[0];
            }elseif(count($this_data) > 1){
                $where[] = ['kf_score','in',$this_data];
            }
        }

        if($p_data['kf_id']){
            $this_data = explode(',',$p_data['kf_id']);

            if(count($this_data) == 1){
                $where['kf_id'] = $this_data[0];
            }elseif(count($this_data) > 1){
                $where[] = ['kf_id','in',$this_data];
            }
        }

        if($p_data['kf_source']){

            $this_data = explode(',',$p_data['kf_source']);

            if(count($this_data) == 1){
                $where['kf_source'] = $this_data[0];
            }elseif(count($this_data) > 1){
                $where[] = ['kf_source','in',$this_data];
            }
        }

        if($p_data['kf_platform']){

            $this_data = explode(',',$p_data['kf_platform']);

            if(count($this_data) == 1){
                $where['kf_platform'] = $this_data[0];
            }elseif(count($this_data) > 1){
                $where[] = ['kf_platform','in',$this_data];
            }
        }

        if($p_data['server_start']){
            $where[] = ['server_start','>=',strtotime($p_data['server_start'])];
        }

        if($p_data['server_end']){
            $where[] = ['server_start','<',strtotime($p_data['server_end'])];
        }


        if($p_data['server_long_start']){
            $where[] = ['server_long','>=',$p_data['server_long_start']];
        }


        if($p_data['server_long_end']){
            $where[] = ['server_long','<',$p_data['server_long_end']];
        }


        if($p_data['set_admin_status']){
            if($p_data['set_admin_status'] == 1){
                $where[] = ['admin_id','=',0];
            }elseif($p_data['set_admin_status'] == 2){
                $where[] = ['admin_id','>',0];
            }
        }


        if($p_data['set_qc_status']){
            if($p_data['set_qc_status'] == 1){
                $where[] = ['qc_question_log_id','=',0];
            }elseif($p_data['set_qc_status'] == 2){
                $where[] = ['qc_question_log_id','!=',0];
            }
        }


        if(!empty($p_data['text'])){
            $where[] = ['text','like',"%{$p_data[text]}%"];
        }

        $model = new QcQuestion();

        $data = [];

        $where_sql = setWhereSql($where,'');

        $count =$model->where($where_sql)->count();

        if(!$count){
            return compact('data','count');
        }

        $data = $model->where($where_sql)->order('id desc');

        if($page && $limit){
            $data = $data->page($page,$limit);
        }

        $data = $data->select()->toArray();

        $kf_source_arr = QcConfig::$kf_source_arr;
        $kf_score_arr = QcConfig::$kf_score_arr;

        $admin_list = SysServer::getAdminListCache();

        foreach ($data as $k => $v) {

            $data[$k]['kf_score_str'] = isset($kf_score_arr[$v['kf_score']])?$kf_score_arr[$v['kf_score']]:'';
            $data[$k]['kf_source_str'] = isset($kf_source_arr[$v['kf_source']])?$kf_source_arr[$v['kf_source']]:'未知';
            $data[$k]['server_end_str'] = $v['server_end']?date('y-m-d H:i',$v['server_end']):'超时';
            $data[$k]['server_start_str'] = $v['server_start']?date('y-m-d H:i',$v['server_start']):'未知';
            $data[$k]['kf_name'] = isset($admin_list[$v['kf_id']])?$admin_list[$v['kf_id']]['name']:'未知';

            if($v['admin_id']){
                $data[$k]['admin_id_str'] = isset($admin_list[$v['admin_id']])?$admin_list[$v['admin_id']]['name']:'未知用户';
            }else{
                $data[$k]['admin_id_str'] = '未分配';
            }

            $data[$k]['is_qc'] = $v['qc_question_log_id']>0?'已质检':'未查看';

            $data[$k]['action'] = ListActionServer::checkQcQuestionAction($v);
        }

        return compact('data','count');
    }

    /**
     * 质检池-详情-相关会话列表
     * @param $id
     * @return array
     */
    public static function contactList($id){

        $code = [
            0=>'success',21=>'no contact id'
        ];
        $list = [];

        $limit = 5;

        $res = self::detail($id);

        if($res['code']){
            return $res;
        }
        $detail = $res['data'];

        if(!$detail['partnerid'] && !$detail['text_user_id']){
            return ['code'=>21,'msg'=>$code[21]];
        }

        $common_where = [];
        $common_where[] = ['kf_source','=',$detail['kf_source']];
        if($detail['text_user_id']){
            $common_where[] = ['text_user_id','=',$detail['text_user_id']];
        }else{
            $common_where[] = ['partnerid','=',$detail['partnerid']];
        }

        $where = $common_where;
        $where[] = ['server_start','<',$detail['server_start']];

        $model = new QcQuestion();

        $list2 = $model->where(setWhereSql($where,''))->order('server_start DESC')->limit(2)->select();


        if($list2){
            $list2 = $list2->toArray();
            $list = array_merge($list,array_reverse($list2));
            $limit = $limit-count($list2);
        }

        $where = $common_where;
        $where[] = ['server_start','>=',$detail['server_start']];

        $list1 = $model->where(setWhereSql($where,''))->order('server_start ASC')->limit($limit)->select();

        if($list1){
            $list = array_merge($list,$list1->toArray());
        }

        foreach ($list as $k => $v) {
            $list[$k]['server_end_str'] = $v['server_end']?date('y-m-d H:i',$v['server_end']):'超时';
            $list[$k]['server_start_str'] = $v['server_start']?date('y-m-d H:i',$v['server_start']):'未知';
            $list[$k]['is_qc'] = $v['qc_question_log_id']>0?'已质检':'未查看';

            $list[$k]['text_html'] = self::lxxJsonToHtml($v['text']);
            if(!empty($list[$k]['text_html'])){
                self::kfTestAddImg($list[$k]['text_html']);
            }
        }

        return ['code'=>0,'msg'=>$code[0],'data'=>$list];
    }

    /**
     * 质检池-详情-会话其他关联信息
     * @param array $p_data
     * @return array
     */
    public static function conversationOtherInfo(array $p_data){
        $info = [];

        if(!$p_data['text_id']){
            return $info;
        }

        if(in_array($p_data['kf_source'],[QcConfig::KF_SOURCE_SOBOT,QcConfig::KF_SOURCE_SOBOT_ZW])){//智齿

            $SobotConversation = new SobotConversation();
            $SobotUsers = new SobotUsers();
            $SobotSummary = new SobotSummary();

            $text_id = $p_data['text_id'];

            $conversation_field = [
                'cid'=>['title'=>'会话id','val'=>''],
                'staff_name'=>['title'=>'最后接待客服','val'=>''],
                'solved'=>['title'=>'是否解决','val'=>''],//1-解决，0-未解决，-1 未开启
                'session_human_duration'=>['title'=>'人工接待时间（秒）','val'=>''],//毫秒
                'robot_name'=>['title'=>'机器人名称','val'=>''],
                'city_name'=>['title'=>'城市','val'=>''],
                'offline_type'=>['title'=>'会话结束方式','val'=>''],//1 客服离线，2 客户被客服移除 3 客户被客服加入黑名单 4 客户会话超时 5 客户关闭了聊天页面 6 客户打开新的页面
                'start_time'=>['title'=>'会话开始时间','val'=>''],
                'end_time'=>['title'=>'会话结束时间','val'=>''],
                'partnerid'=>['title'=>'合作方用户ID','val'=>''],
                'staff_reply_msg_count'=>['title'=>'人工回复数','val'=>''],
                'robot_reply_msg_count'=>['title'=>'机器人回复数','val'=>''],
                'consult_staff_msg_count'=>['title'=>'咨询人工消息数','val'=>''],
                'consult_robot_msg_count'=>['title'=>'咨询机器人消息数','val'=>''],
                'transfer_tohuman_time'=>['title'=>'机器人转人工时间','val'=>''],////毫秒
                'sources'=>['title'=>'来源','val'=>''],//0 pc；1 微信；2 sdk；3 微博；4 移动网站；9 企业微信；10 微信小程序
                'os'=>['title'=>'终端','val'=>''],//0 其他 1 Windows XP；2 Windows 7；3 Windows 8；4 Windows Vista；5 Windows 其他；6 Linux；7 macOS；8 Android；9 iOS；11 Windows 2000；12 Windows 10 ；其他 其他
                'evaluation_remark'=>['title'=>'评论备注信息','val'=>''],
                'evaluation_date_time'=>['title'=>'评论时间','val'=>''],//毫秒

            ];
            $where = [];
            $where['cid'] = $text_id;
            $this_field = [];
            foreach ($conversation_field as $k => $v){
                $this_field[] = $k;
            }

            $field = implode(',',$this_field);

            $conversation_info = $SobotConversation->field($field)->where(setWhereSql($where,''))->find();

            if($conversation_info){
                $conversation_info = $conversation_info->toArray();
                foreach ($conversation_info as $k => $v){

                    if($v){
                        if($k == 'session_human_duration'){
                            $v = ceil($v/1000);
                        }elseif( in_array($k,['transfer_tohuman_time','evaluation_date_time','start_time','end_time']) ){
                            $v = date('Y-m-d H:i:s',substr($v,0,10));
                        }
                    }

                    switch ($k) {
                        case 'solved':
                            $v = getArrVal([1=>'解决',0=>'未解决',-1=>'未开启'],$v,'');
                            break;
                        case 'offline_type':
                            $v = getArrVal([1=>'客服离线',2=>'客户被客服移除',3=>'客户被客服加入黑名单',4=>'客户会话超时',5=>'客户关闭了聊天页面',6=>'客户打开新的页面'],$v,'');
                            break;
                        case 'sources':
                            $v = getArrVal([0=>'pc',1=>'微信',2=>'sdk',3=>'微博',4=>'移动网站',9=>'企业微信',10=>'微信小程序'],$v,'');
                            break;
                        case 'os':
                            $v = getArrVal([
                                0=>'其他',
                                1=>'Windows XP',
                                2=>'Windows 7',
                                3=>'Windows 8',
                                4=>'Windows Vista',
                                5=>'Windows 其他',
                                6=>'Linux',
                                7=>'macOS',
                                8=>'Android',
                                9=>'iOS',
                                11=>'Windows 2000',
                                12=>'Windows 10',
                            ],$v,'其他');
                            break;
                        default:

                            break;
                    }

                    $conversation_field[$k]['val'] = $v;
                }
                $info[] = ['title'=>'会话信息','data'=>$conversation_field];

                $summary_field = [
                    'operation_name'=>['title'=>'业务单元名称','val'=>''],
                    'req_type_name'=>['title'=>'业务类型名称列表','val'=>''],//
                    'summary_description'=>['title'=>'备注','val'=>''],//
                ];
                $where = [];
                $where['cid'] = $text_id;
                $this_field = [];
                foreach ($summary_field as $k => $v){
                    $this_field[] = $k;
                }

                $field = implode(',',$this_field);

                $summary_info = $SobotSummary->field($field)->where($where)->find();

                if($summary_info){
                    $summary_info = $summary_info->toArray();
                    foreach ($summary_info as $k => $v){
                        $summary_field[$k]['val'] = $v;
                    }
                    $info[] = ['title'=>'会话统计信息','data'=>$summary_field];
                }

                if($conversation_info['partnerid']){
                    $user_field = [
//                        'partnerid'=>['title'=>'合作方ID','val'=>''],
                        'remark'=>['title'=>'备注','val'=>''],//
                        'summary_params'=>['title'=>'统计参数','val'=>''],//
                        'nick'=>['title'=>'用户昵称','val'=>''],//
                        'sources'=>['title'=>'来源','val'=>''],//0 pc；1 微信；2 sdk；3 微博；4 移动网站；9 企业微信；10 微信小程序
                        'visitorids'=>['title'=>'访客ID','val'=>''],//
                    ];
                    $where = [];
                    $where['partnerid'] = $conversation_info['partnerid'];
                    $this_field = [];
                    foreach ($user_field as $k => $v){
                        $this_field[] = $k;
                    }

                    $field = implode(',',$this_field);

                    $user_info = $SobotUsers->field($field)->where($where)->find();

                    if($user_info){
                        $user_info = $user_info->toArray();
                        foreach ($user_info as $k => $v){
                            $user_field[$k]['val'] = $v;
                        }
                        $info[] = ['title'=>'用户信息','data'=>$user_field];
                    }
                }
            }

        }

        return $info;
    }

    /**
     * 质检池-配置
     * @return array
     */
    public static function config(){

        $config = [];
        $config['kf_source_arr'] = QcConfig::$kf_source_arr;
        $config['kf_score_arr'] = QcConfig::$kf_score_arr;

        $system_config = SysServer::getAllConfigByCache();

        foreach (QcConfig::$config_arr as $k => $v) {
            $config[$v['key']] = getArrVal($system_config,$v['key'],[]);
        }

        $config['game_type'] = getArrVal($system_config,'game_type',[]);

        $config['qc_admin_list'] = SysServer::getUserListByAdminInfo(self::$user_data,['group_type'=>QcConfig::USER_GROUP_QC]);

        return $config;
    }

    /**
     * 质检池-详情
     * @param $id
     * @return array
     * @throws \think\Exception
     */
    public static function detail($id){

        $code = [
            0=>'ok',1=>'未知会话'
        ];

        $model = new QcQuestion();

        $info = $model->where('id',$id)->find();

        if(!$info){
            return ['code'=>1,'msg'=>$code[1]];
        }

        $info = $info->toArray();

        $info['text'] = self::lxxJsonToHtml($info['text']);

        if(!empty($info['text'])){
            self::kfTestAddImg($info['text']);
        }

        return ['code'=>0,'msg'=>$code[0],'data'=>$info];
    }

    /**
     * 申诉拒审
     * @param $id
     * @param $p_data
     * @return false
     */
    public static function doAppeal($id,$p_data){

        $QcAppealLog = new QcAppealLog();

        $log_save_data = getDataByField($p_data,['id','status']);

        $appeal_data = [];
        $appeal_data['log_id'] = $id;
        $appeal_data['reason'] = $p_data['reason'];
        $appeal_data['admin_id'] = self::$user_data['id'];
        $appeal_data['status'] = $p_data['status'];
        $appeal_data['add_time'] = time();

        //reject_appeal_reason
        if($p_data['status'] == -1){
            $log_save_data['reject_appeal_reason'] = $p_data['reason'];
        }elseif($p_data['status'] == 1){
            $log_save_data['appeal_reason'] = $p_data['reason'];
        }
        $QcQuestionLog = new QcQuestionLog();
        // 检查记录
        $info = $QcQuestionLog->checkAppealById($id,$p_data['status']);

        if(!$info){
            return false;
        }

        $appeal_log_info_id = $QcAppealLog->insertGetId($appeal_data);

        if(!$appeal_log_info_id){
            return false;
        }

        $log_save_data['appeal_log_id'] = $appeal_log_info_id;

        if(isset($log_save_data['id'])) unset($log_save_data['id']);
        // 修改记录
        return $info->save($log_save_data);
    }

    /**
     * 质检池-列表-分配质检
     * @param $ids
     * @param $admin_id
     * @return false
     */
    public static function addAdminId($ids,$admin_id){

        $model = new QcQuestion();
        return $model->where('id','in',$ids)->update(['admin_id'=>$admin_id]);

    }

    /**
     * 质检池-列表-取消分配
     * @param $id
     * @return array
     */
    public static function delAdminId($id){

        $code = [
            0=>'success',1=>'数据不存在',2=>'请先删除对应直接记录',3=>'修改失败'
        ];
        $model = new QcQuestion();

        $info = $model->where('id',$id)->find();

        if(!$info){
            return ['code'=>1,'msg'=>$code[1]];
        }

        if($info->qc_question_log_id>0){
            return ['code'=>2,'msg'=>$code[2]];
        }
        /*修改质检记录*/
        $info->admin_id = 0;

        $res = $info->save();

        if(!$res){
            return ['code'=>3,'msg'=>$code[3]];
        }

        return ['code'=>0,'msg'=>$code[0]];
    }

    /**
     * 获取来源关联配置数据
     * @param $p_data
     * @return array
     */
    public static function getSourceConfig($p_data){

        if(!isset($p_data['kf_source'])
            || !$p_data['kf_source']
        ){
            return [];
        }

        $kf_source_str = '_'.$p_data['kf_source'];
        $model = new QcConfig();

        $where = [];
        $this_key_arr = [];
        if(isset($p_data['keys']) && $p_data['keys'] ){
            if(!is_array($p_data['keys'])){
                $p_data['keys'] = explode(',',$p_data['keys']);
            }
            foreach ($p_data['keys'] as $v){
                $this_key_arr[] = $v.$kf_source_str;
            }
        }else{
            foreach (QcConfig::$config_other_arr as $v){
                if(in_array($v['type'],['source','score'])){
                    $this_key_arr[] = $v['key'].$kf_source_str;
                }
                if(in_array($v['type'],['question_type'])){
                    $this_key_arr[] = $v['key'].'_'.$kf_source_str;
                }
            }
        }

        if(count($this_key_arr) == 0){
            return [];
        }elseif (count($this_key_arr) == 0){
            $where['key'] = $this_key_arr[0];
        }else{
            $where[] = ['key','in',$this_key_arr];
        }

        $list = $model->where(setWhereSql($where,''))->select()->toArray();

        $info = [];

        foreach ($list as $k => $v){
//            $info[str_replace($kf_source_str,'',$v['key'])] = QcConfig::changeDataValue($v);
            $info[str_replace($kf_source_str,'',$v['key'])] = $v['val'];
        }

        return $info;
    }

    /**
     * 质检会话转化
     * @param $str 会话内容
     * @return string
     */
    public static function lxxJsonToHtml($str){

        $text_str = '';
        if(preg_match('/\[lxxjsondata\]/', $str)){

            $text_data = unserialize(str_replace('[lxxjsondata]', '', $str));

            if($text_data){
                foreach ($text_data as $key => $value) {
                    $thistype = isset($value['type'])?$value['type']:1;
                    $thisdata['name'] = isset($value['name'])?$value['name']:'未知用户';
                    $thisdata['time'] = isset($value['time'])?date('y-m-d H:i:s',$value['time']):'未知时间';
                    $thisdata['text'] = isset($value['text'])?htmlspecialchars_decode($value['text']):'未知内容';

                    $text_str.=self::setChatText($thistype,$thisdata);
                }
            }
        }else{
            $text_str = $str;
        }

        return $text_str;
    }

    /**
     * 会话内容-图片处理
     * @param $text
     * @return false
     */
    protected static function kfTestAddImg(&$text){

        $flag = preg_match_all('/<img(.*?)>/',$text,$img_arr);

        if(!$flag){
            return false;
        }

        foreach ($img_arr[0] as $k => $v){
            if(!$v){
                continue;
            }

            $text = str_replace($v,preg_replace('/\s+/',' ',$v),$text);
        }

        $crawler = new Crawler($text);

        $crawler = $crawler->filter('img')->each(function (Crawler $node, $i) {

            $this_src = $node->attr('src');
            $this_onclick = $node->attr('onclick');
            $this_html = $node->outerHtml();

            if(empty($this_src) || $this_src == '\\'){
                return false;
            }

            if($this_onclick!=null){
                return false;
            }

            $new_img = str_replace('<img','<img onclick="window.parent.show_img(this)"',$this_html);
            return ['old'=>$this_html,'new'=>$new_img];
        });

        if($crawler){
            $new_test = htmlspecialchars($text);
            foreach ($crawler as $item){
                if(!$item){
                    continue;
                }

                $old = htmlspecialchars($item['old']);
                if(self::testImg($old,$new_test)){
                    $new_test = str_replace($old,htmlspecialchars($item['new']),$new_test);
                }else{
                    $old = str_replace(htmlspecialchars('>'),htmlspecialchars('/>'),$old);
                    if(self::testImg($old,$new_test)){
                        $new_test = str_replace($old,htmlspecialchars($item['new']),$new_test);
                    }
                }

            }

            $text = htmlspecialchars_decode($new_test);
        }
    }
    /**
     * 质检池-列表-会话更新
     * @param $p_data
     * @return array
     */
    public static function setConversationNeedUpdate($p_data){

        $code = [0=>"success",1=>"已设置，请等待",2=>"设置失败",3=>"参数不足",4=>"不可设置",5=>'会话数据不存在'];

        if(!$p_data['text_id']){
            return ['code'=>3,"msg"=>$code[3]];
        }

        if(in_array($p_data['kf_source'],[QcConfig::KF_SOURCE_SOBOT,QcConfig::KF_SOURCE_SOBOT_ZW])){//智齿

            $SobotConversation = new SobotConversation();

            $text_id = $p_data['text_id'];

            $where = [];
            $where['cid'] = $text_id;

            $conversation_info = $SobotConversation->where($where)->find();

            if(!$conversation_info){
                return ["code"=>5,"msg"=>$code[5]];
            }
            if($conversation_info->need_update == 1){
                return ["code"=>1,"msg"=>$code[1]];
            }

            $res = $conversation_info->save(["need_update"=>1]);

            if(!$res){
                return ["code"=>2,"msg"=>$code[2]];
            }
        }else{
            return ["code"=>4,"msg"=>$code[4]];
        }

        return ["code"=>0,"msg"=>$code[0]];
    }
    /**
     * 会话数据转html
     * @param  type 1 客户 2 客服
     * @param data.name 名称
     * @param data.text 内容
     * @param data.time 时间
     * @return html
     */
    public static function setChatText($type,$data){
        // <div class="chat" data-type="1">
        // <p class="ct1">
        // <span class="name">name</span>
        // <span style="margin-left: 5px">12:12:12</span>:
        // </p>
        // <p class="ct2" style="padding: 6px 10px;padding-left: 15px;background: #0000001a">text</p>
        // </div>
        // <div class="chat" data-type="2" style="text-align: right;">
        // <p class="ct1">
        // <span style="margin-right: 5px">12:12:12</span>
        // <span class="name">name</span>
        // </p>
        // <p class="ct2" style="padding: 6px 10px;padding-right: 15px;background: #0000001a">text</p>
        // </div>
        if(!isset($data['time'])){
            $data['time']='';
        }
        if(!isset($data['text'])){
            $data['text']='undefined';
        }

        $data['text'] = str_replace('<p','<span',$data['text']);
        $data['text'] = str_replace('</p>','</span><br>',$data['text']);

        if($type == 1){
            if(!isset($data['name'])){
                $data['name']='未知玩家';
            }
            $str = '<span class="chat" data-type="1">';
            $str .= '<p class="ct1" style="line-height: 40px;">';
            $str .= '<span class="name">'.$data['name'].'</span>';
            $str .= '<span style="margin-left: 20px;color: #858282">'.$data['time'].'</span>';
            $str .= '</p>';
            $str .= '<p class="ct2 msg_cnt1"><span style="padding: 10px;max-width: 60%;background: #f0f0f0;display: inline-block">'.$data['text'].'</span></p>';
            $str .= '</span>';
        }else{
            if(!isset($data['name'])){
                $data['name']='未知客服';
            }
            $str = '<span class="chat" data-type="2" style="text-align: right;">';
            $str .= '<p class="ct1" style="line-height: 40px;">';
            $str .= '<span style="margin-right: 20px;color: #858282">'.$data['time'].'</span>';
            $str .= '<span class="name">'.$data['name'].'</span>';
            $str .= '</p>';
            $str .= '<p class="ct2 msg_cnt2"><span style="padding: 10px;max-width: 60%;background: #BCF2EB;display: inline-block">'.$data['text'].'</span></p>';//background: #0000001a
            $str .= '</span>';
        }

        return $str;
    }

    /**
     * 质检记录-列表
     * @param $p_data
     * @return array
     */
    public static function logList($p_data){
        $page = getArrVal($p_data,'page',1);
        $limit = getArrVal($p_data,'limit',20);

        list($list,$count) = self::getQcQuestionLogCommonList($p_data,$page,$limit);

        if($list){
            $admin_list = SysServer::getAdminListCache();
            $base_config = self::logListConfig();
            $platform_arr = SysServer::getPlatformList();

            foreach ($list as $k => $v) {

                $list[$k]['create_time_str'] = $v['create_time']?date('y-m-d H:i',$v['create_time']):'未知';
                $list[$k]['server_start_str'] = $v['server_start']?date('y-m-d',$v['server_start']):'未知';
                $this_admin_id_info = getArrVal($admin_list,$v['kf_id'],[]);
                $list[$k]['group_id_str'] = $list[$k]['kf_name'] = '';
                if($this_admin_id_info){
                    $list[$k]['kf_name'] = $this_admin_id_info?$this_admin_id_info['name']:'未知';
                    if($this_admin_id_info['group_id']){
                        $this_kf_group_list = explode(',',$this_admin_id_info['group_id']);
                        $this_kf_group_list_name = [];
                        foreach ($this_kf_group_list as $tkgl_v){
                            $this_group_id_info = getArrVal($base_config['user_group_list'],$tkgl_v,[]);

                            if($this_group_id_info){
                                $this_kf_group_list_name[] =$this_group_id_info['name'];
                            }
                        }
                        if($this_kf_group_list_name){
                            $list[$k]['group_id_str'] = implode('/',$this_kf_group_list_name);
                        }
                    }
                }

                $list[$k]['admin_id_str'] = isset($admin_list[$v['admin_id']])?$admin_list[$v['admin_id']]['name']:'未知';
                $list[$k]['status_str'] = getArrVal($base_config['status_arr'],$v['status'],'');
                $list[$k]['kf_source_str'] = getArrVal($base_config['kf_source_arr'],$v['kf_source'],'');

                $this_platform_id_info = getArrVal($platform_arr,$v['platform_id'],[]);

                $list[$k]['platform_id_str'] =$this_platform_id_info?$this_platform_id_info['name']:'';


                $list[$k]['kf_is_solve_str'] = $v['kf_is_solve']?'是':'否';
                $list[$k]['question_is_solve_str'] = $v['question_is_solve']?'是':'否';
                $list[$k]['is_example_str'] = $v['is_example']?'是':'否';


                $list[$k]['action'] = ListActionServer::checkQcQuestionLogAction($v);
            }
        }

        return ['data'=>$list,'count'=>$count];
    }

    /**
     * 质检记录-列表-公共查询方法
     * @param $p_data
     * @param $page
     * @param $limit
     * @return array
     */
    public static function getQcQuestionLogCommonList($p_data,$page,$limit){
        $model = new QcQuestionLog();
        $QcQuestion = new QcQuestion();

        $int_field = [
            'kf_id','admin_id'
        ];

        $list_type = getArrVal($p_data,'list_type','');

        $where = getDataByField($p_data,$int_field);

        if($list_type == 'appeal') $where[] = ['status','!=',0];

        clearZero($where,$int_field);
        //权限条件
        if(!in_array(QcConfig::USER_GROUP_QC,self::$user_data['user_group_type_arr']) && self::$user_data['is_admin'] == 0){
            //非管理&&非质检（客服）=>只能看所属平台、同分组类型数据

            if(self::$user_data['platform_id'] && self::$user_data['user_group_type_arr']){
                $kf_id_arr = [];
                $admin_list = SysServer::getAdminListCache();

                foreach ($admin_list as $v){

                    $this_arrMID = arrMID($v['platform_id'],self::$user_data['platform_id']);//同平台
                    $this_arrMID2 = arrMID($v['user_group_type_arr'],self::$user_data['user_group_type_arr']);//同分组类型
                    if($this_arrMID['ai_com'] && $this_arrMID2['ai_com']){
                        $kf_id_arr[] = $v['id'];
                    }
                }
                if($kf_id_arr){
                    $where[] = ['kf_id','in',$kf_id_arr];
                }else{
                    $where[] = ['kf_id','=',-1];
                }
            }else{
                $where[] = ['kf_id','=',-1];
            }
        }

        //普通条件
        if($p_data['kf_source']){
            $where[] = getWhereDataArr($p_data['kf_source'],'kf_source');
        }

        if($p_data['platform_id']){
            $where[] = getWhereDataArr($p_data['platform_id'],'platform_id');
        }

        if(!empty($p_data['group_id'])){
            $ids = AdminServer::getAdminIdsByGroupId($p_data['group_id']);
            if(!$ids){
                $ids = [0];
            }
            $where[] = ['kf_id','in',$ids];
        }
        if(!empty($p_data['start'])){
            $where[] = ['create_time','>=',strtotime($p_data['start'])];
        }
        if(!empty($p_data['end'])){
            $where[] = ['create_time','<',strtotime($p_data['end'])];
        }
        if(!empty($p_data['qc_score_start'])){
            $where[] = ['qc_score','>=',$p_data['qc_score_start']];
        }
        if(!empty($p_data['qc_score_end'])){
            $where[] = ['qc_score','<=',$p_data['qc_score_end']];
        }

        if(isset($p_data['status']) && $p_data['status']!=''){
            $where[] = getWhereDataArr($p_data['status'],'status');
        }

        if(isset($p_data['kf_is_solve']) && $p_data['kf_is_solve'] >= 0){
            $where['kf_is_solve'] = $p_data['kf_is_solve'];
        }
        if( isset($p_data['is_example']) && $p_data['is_example'] >= 0){
            $where['is_example'] = $p_data['is_example'];
        }
        if(isset($p_data['question_is_solve']) && $p_data['question_is_solve'] >= 0){
            $where['question_is_solve'] = $p_data['question_is_solve'];
        }
        if(!empty($p_data['question_type'])){
            $where[] = ['question_type','=',$p_data['question_type']];
        }
        if(!empty($p_data['qc_type'])){
            $where[] = ['qc_type','=',$p_data['qc_type']];
        }
        if(!empty($p_data['game_type'])){
            $where[] = ['game_type','=',$p_data['game_type']];
        }
        if(!empty($p_data['qc_num'])){
            $question_info = $QcQuestion->where(['qc_num'=>$p_data['qc_num']])->find();
            if($question_info){
                $where[] = ['qc_question_id','=',$question_info->id];
            }else{
                $where[] = ['qc_question_id','=',-1];
            }
        }
        if(!empty($p_data['qc_level'])){
            $where[] = ['qc_level','=',$p_data['qc_level']];
        }
        if(!empty($p_data['server_date'])){
            $this_server_date = strtotime($p_data['server_date']);
            $where[] = ['server_start','>=',$this_server_date];
            $where[] = ['server_start','<',$this_server_date+3600*24];
        }
        if(!empty($p_data['text'])){
            $where['_string'] = "(text LIKE '%{$p_data[text]}%' OR qc_comments LIKE '%{$p_data[text]}%')";
        }

        $list = [];

        $count = $model->where(setWhereSql($where,''))->count();

        if($count>0){
            $list = $model->where(setWhereSql($where,''))->page($page,$limit)->order('id desc')->select()->toArray();
        }

        return [$list,$count];
    }

    /**
     * 质检记录-配置
     * @return array
     */
    public static function logListConfig(){

        $config = self::config();

        $config['status_arr'] = QcQuestionLog::$status_arr;

        $user_group_list = SysServer::getAdminGroupList(QcConfig::USER_GROUP_ONLINE_KF);

        $user_group_list = array_merge($user_group_list,SysServer::getAdminGroupList(QcConfig::USER_GROUP_VIP));

        $config['user_group_list'] =arrReSet($user_group_list,'id');

        $config['kf_source_arr'] = QcConfig::$kf_source_arr;

        return $config;
    }

    /**
     * 质检记录-详情
     * @param int $id
     * @param int $qc_question_id
     * @return array
     * @throws \think\Exception
     */
    public static function logDetail($id=0,$qc_question_id=0){
        $code = [
            0=>'success',1=>'数据不存在'
        ];

        $info = [];
        $question_info = [];
        $model = new QcQuestionLog();

        if($id){
            // dd($id);
            $where = [];
            $where['id'] = $id;

            self::getAdminId($where);

            $info = $model->where(setWhereSql($where,''))->find();

            if(!$info) return ['code'=>1,'msg'=>$code[1]];

            $info = $info->toArray();
            $qc_question_id = $info['qc_question_id'];
        }

        if($qc_question_id){
            $QcQuestionModel = new QcQuestion();
            $question_info_obj = $QcQuestionModel->where('id',$qc_question_id)->find();
            if($question_info_obj){
                $question_info = $question_info_obj->toArray();
                unset($question_info['id']);
                $question_info['qc_question_id'] = $qc_question_id;
            }
        }

        if($info){
            $info = array_merge($question_info,$info);
        }elseif($question_info){

            $question_info['text'] = self::lxxJsonToHtml($question_info['text']);
            $info = $question_info;
        }

        if(!empty($info['text'])){
            self::kfTestAddImg($info['text']);
        }

        //质检配置
        $config = SysServer::getAllConfigByCache();
        //会话来源
        $kf_source_arr = QcConfig::$kf_source_arr;
        //会话评分
        $kf_score_arr = QcConfig::$kf_score_arr;
        //扣分数据
        $dec_score_arr = [
            ['reason'=>'','score'=>0],
            ['reason'=>'','score'=>0],
            ['reason'=>'','score'=>0],
            ['reason'=>'','score'=>0],
            ['reason'=>'','score'=>0],
        ];
        //加分数据
        $inc_score_arr = [
            ['reason'=>'','score'=>0],
            ['reason'=>'','score'=>0],
        ];

        if($id){
            $dec_score_flag = 0;
            $inc_score_flag = 0;

            $QcQuestionScoreLogModel = new QcQuestionScoreLog();

            $qc_score_list = $QcQuestionScoreLogModel->getListByQlId($id)->toArray();

            if($qc_score_list){
                foreach ($qc_score_list as $k => $v) {
                    if($v['score']>=0){
                        if(isset($inc_score_arr[$inc_score_flag])){
                            $inc_score_arr[$inc_score_flag] = $v;
                        }

                        $inc_score_flag++;
                    }else{
                        if(isset($dec_score_arr[$dec_score_flag])){
                            $dec_score_arr[$dec_score_flag] = $v;
                        }
                        $dec_score_flag++;
                    }
                }
            }
        }

        $admin_list = SysServer::getUserListByAdminInfo(self::$user_data,['group_type'=>[QcConfig::USER_GROUP_ONLINE_KF,QcConfig::USER_GROUP_VIP,QcConfig::USER_GROUP_ACCOUNT_SAFE]]);

        $action = ListActionServer::checkQcQuestionLogAction($info);

        if($action){
            foreach ($action as $k => $v){
                $action[$k] = "Qc-".$v;
            }
        }

        $config_list = compact(
            'admin_list',
            'dec_score_arr',
            'inc_score_arr',
            'kf_source_arr',
            'kf_score_arr',
            'action',
            'config'
        );

        return ['code'=>0,'msg'=>$code[0],'data'=>$info,'config'=>$config_list];
    }

    /**
     * 质检记录-保存
     * @param $p_data
     * @return bool
     * @throws \think\Exception
     */
    public static function logSave($p_data){

        $id = getArrVal($p_data,'id',0);

        $model = new QcQuestionLog();
        $QcQuestionModel = new QcQuestion();
        $QcQuestionScoreLogModel = new QcQuestionScoreLog();

        /*处理数据*/
        $qc_score_data = [];//原因分数记录

        if(isset($p_data['qc_score_inc'])){
            foreach ($p_data['qc_score_inc'] as $k => $v) {
                if(isset($v['reason']) && $v['reason'] ){
                    $qc_score_data[] = $v;
                }
            }
        }

        if(isset($p_data['qc_score_dec'])){
            foreach ($p_data['qc_score_dec'] as $k => $v) {
                if(isset($v['reason']) && $v['reason'] ){
                    $qc_score_data[] = $v;
                }
            }
        }

        $log_field_arr = [
            'kf_id',
            'question_type',
            'game_type',
            'text',
            'qc_type',
            'qc_level',
            'qc_score',
            'qc_comments',
            'kf_adventage',
            'kf_is_solve',
            'kf_nosolve_res',
            'question_is_solve',
            'question_nosolve_res',
            'is_example',
            'kf_source',
        ];
        $log_save_data =getDataByField($p_data,$log_field_arr);//质检记录数据
        $log_save_data['platform_id'] = $p_data['kf_platform'];
        $log_save_data['admin_id'] = self::$user_data['id'];
        /*数据保存*/
        if($id){//修改
            /*修改质检记录*/
            $log_save_data['status'] = 0;

            $res = $model->where('id',$id)->update($log_save_data);

            if(!$res){
                return false;
            }
            /*修改分数记录*/
            $res1 = $QcQuestionScoreLogModel->saveInfoByQLId($id,$qc_score_data);

            if(!$res1){
                return false;
            }
        }else{

            $qc_question_id = getArrVal($p_data,'qc_question_id',0);

            if(!$qc_question_id)
            {
                /*添加质检池记录*/
                $question_field_arr = [
                    'kf_id',
                    'kf_source',
                    'kf_score',
                    'question_type',
                    'game_type',
                    'text',
                    'kf_platform',
                ];
                $question_save_data = getDataByField($p_data,$question_field_arr);
                $question_save_data['admin_id'] = self::$user_data['id'];

                try {//尝试把会话内容识别成数据（名称、时间、内容...）
                    $text_data = self::getTextData($p_data['text']);
                    if($text_data){
                        $question_save_data['text'] = '[lxxjsondata]'.serialize($text_data);
                    }
                } catch (Exception $e) {
                    $question_save_data['text'] = $p_data['text'];
                }

                $qc_question_id = $QcQuestionModel->insertGetId($question_save_data);

                if(!$qc_question_id){
                    return false;
                }

                $res = $QcQuestionModel->where('id',$qc_question_id)->find()->saveQcNum(1);//更新qc_num

            } else {
                $qc_question_info = $QcQuestionModel->where('id',$qc_question_id)->find();

                if($qc_question_info){
                    $qc_question_info = $qc_question_info->toArray();//冗余字段
                    $log_save_data['server_start'] = $qc_question_info['server_start'];
                    $log_save_data['server_end'] = $qc_question_info['server_end'];
                }
            }

            /*添加质检记录*/
            $log_save_data['qc_question_id'] = $qc_question_id;
            $log_save_data['admin_id'] = self::$user_data['id'];
            $log_save_data['create_time'] = $log_save_data['update_time']= time();
            $log_save_data['update_user'] = self::$user_data['id'];

            $id = $model->insert($log_save_data);
            if(!$id){
                return false;
            }

            /*添加质检分数记录*/
            $res1 = 1;
            if($qc_score_data){
                $res1 = $QcQuestionScoreLogModel->saveInfoByQLId($id,$qc_score_data);
            }

            if(!$res1){
                return false;
            }
            /*更新 质检池记录 质检状态*/
            $res2 = $QcQuestionModel->where('id',$qc_question_id)->update(['qc_question_log_id'=>$id]);
        }


        return true;
    }

    public static function logDel($id){

        $model = new QcQuestionLog();
        $QcQuestionModel = new QcQuestion();
        $info = $model->where('id',$id)->find();

        if(!$info){
            return false;
        }

        Db::startTrans();//开启事务
        try{
            //修改质检记录 软删除
            $info->is_del = 1;
            $info->update_user = self::$user_data['id'];
            $info->update_time = time();

            $res = $info->save();

            if(!$res){
                Db::rollback();
                return false;
            }

            if($info->qc_question_id>0){
                $qc_question = [];
                $qc_question['id'] = $info->qc_question_id;
                $qc_question['qc_question_log_id'] = 0;
                $res = $QcQuestionModel->where('id',$info->qc_question_id)->update(['qc_question_log_id'=>0]);
                if(!$res){
                    Db::rollback();
                    return false;
                }
            }
            // 提交事务
            Db::commit();
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            return false;
        }

        return true;
    }

    /**
     * 质检记录-详情-关联客服信息
     * @param $p_data
     * @return array
     * @throws \think\Exception
     */
    public static function checkKfInfo($p_data){

        $where = [];

        if(isset($p_data['kf_id']) && $p_data['kf_id']){
            $where['id'] = $p_data['kf_id'];
        }

        if(isset($p_data['kf_platform']) && $p_data['kf_platform']){
            $where['platform'] = $p_data['kf_platform'];
        }

        if(isset($p_data['kf_group']) && $p_data['kf_group']){
            $where['group_id'] = $p_data['kf_group'];
        }


        $field = '
            id
            ,realname AS name
            ,platform
            ,group_id
        ';

        $platform_arr = SysServer::getPlatformList();

        $model = new Admin();

        $info = $model->field($field)->where(setWhereSql($where,''))->order('id desc')->find();

        if($info){
            $info = $info->toArray();
            $info['platform_arr'] = [];
            $info['user_group_arr'] = [];
            if($info['platform']){
                $platform = explode(',', $info['platform']);

                foreach ($platform_arr as $k => $v) {
                    if(in_array($v['suffix'], $platform)){
                        $info['platform_arr'][] = $v;
                    }
                }
            }
            if($info['group_id']){

                $where = [];
                $where['status'] = 1;
                $where[] = ['id','in',$info['group_id']];
                $UserGroup = new UserGroup();
                $res = $UserGroup->where(setWhereSql($where,''))->select()->toArray();
                $info['user_group_arr'] = $res;
            }
            return $info;
        }else{
            return [];
        }

    }

    /**
     * 批量添加会话数据
     * @param $p_data array 会话数据
     * @param $source int 来源
     * @return array
     */
    public static function addQuestion(array $p_data,int $source){

        $QcQuestion  = new QcSellQuestion();
        $QdConversation = new QdConversation();
        $test_data = [];
        $add_data = [];
        $qd_add_data = [];
        $text_id_arr = [];
        if($source == QcConfig::KF_SOURCE_VIP_QD){
            $data_key = [
                '客服'=>'kf_name','所在组织架构'=>'group_name','客户'=>'user_name','会话开始时间'=>'start_time','会话通路'=>'','会话状态'=>'','会话主题'=>'',
                '接入方式'=>'','结束方式'=>'','排队时长(秒)'=>'','首次响应时长(秒)'=>'','会话时长(秒)'=>'server_long','满意度评分'=>'score',
                '满意度标签'=>'','是否已解决'=>'','会话发起方'=>'','会话消息数'=>'','会话关键词'=>'',
                '客户线索'=>'','客户标签'=>'user_tag','客户地区'=>'','会话有效性'=>'','质检结果'=>'','搜索引擎'=>'','搜索引擎关键字'=>'',
                '访问来源'=>'','着陆页'=>'','咨询页'=>'','分配时接待分组'=>'','客户首次发送消息时间'=>'user_send_time_first','客服首次发送消息时间'=>'','会话记录'=>'text',
            ];

            $qd_key_arr = [
                'group_name',
                'user_tag',
                'user_send_time_first',
                'cid',
                'add_time',
            ];
            $title_key = [];
            foreach ($p_data[0] as $k => $v){
                $v = trim($v);
                if(isset($data_key[$v]) && $data_key[$v]){
                    $title_key[$data_key[$v]] = $k;
                }
            }

            unset($p_data[0]);
            if(!$title_key){
                return ['code'=>1];
            }

            $ConversationServer = new QcConversation();

            foreach ($p_data as $k => $v){

                $this_user_tag = getArrVal($v,$title_key['user_tag']);

                $this_user_name = getArrVal($v,$title_key['user_name']);
                $this_kf_name = getArrVal($v,$title_key['kf_name']);
                $this_server_long = getArrVal($v,$title_key['server_long'],0);

                if(!$this_user_name && !$this_kf_name){
                    continue;
                }

                $this_data = [];
                $this_qd_data = [];
                foreach ($qd_key_arr as $qka_k => $qka_v){

                    if(!isset($title_key[$qka_v])){
                        $this_qd_data[$qka_v] = '';
                        continue;
                    }

                    if($qka_v == 'user_send_time_first'){

                        $this_user_send_time_first = trim(getArrVal($v,$title_key[$qka_v],0));

                        if($this_user_send_time_first){
                            $this_qd_data[$qka_v] = strtotime( $this_user_send_time_first );
                        }
                        if(!$this_qd_data[$qka_v]){
                            $this_qd_data[$qka_v] = 0;
                        }

                    }else{
                        $this_qd_data[$qka_v] = getArrVal($v,$title_key[$qka_v]);
                    }
                }
                $this_data['user_send_time_first'] = strtotime(getArrVal($v,$title_key['user_send_time_first']));

                $this_data['server_start'] = strtotime(getArrVal($v,$title_key['start_time']));
                if(!$this_data['server_start']){
                    $this_data['server_start'] = 0;
                }
                $this_data['server_end'] = $this_data['server_start']+$this_server_long;
                $this_data['server_long'] = ceil($this_server_long/60);

                $this_qd_data['cid'] = $this_data['text_id'] = md5($this_kf_name.$this_user_name.$this_data['server_start'].$this_data['server_long']);

                $where = [];
                $where['kf_source'] = QcConfig::KF_SOURCE_VIP_QD;
                $where['text_id'] =$this_data['text_id'];
                $this_info = $QcQuestion->where($where)->count();

                if($this_info){
                    continue;
                }
                $text_id_arr[] = $this_data['text_id'];
                $this_data['kf_source'] = QcConfig::KF_SOURCE_VIP_QD;
                $this_data['text'] = getArrVal($v,$title_key['text']);
                $this_text_info = $ConversationServer->getQDText(getArrVal($v,$title_key['text']),$this_user_name,$this_kf_name);
                if($this_text_info['text']){
                    $this_data['text'] = '[lxxjsondata]'.serialize($this_text_info['text']);
                    $test_data[]=$this_data['text'];
                }

                if($this_text_info['play_id']){
                    $this_data['partnerid'] = $this_text_info['play_id'];
                }else{
                    $this_data['partnerid'] = '';
                }

                if($this_user_tag){
                    $this_info = self::getQDUserTag($this_user_tag);

                    if($this_info){
                        if(isset($this_info['产品'])){
                            $this_data['game_type'] = $this_info['产品'];
                        }
                        if(isset($this_info['平台'])){
                            $this_data['kf_platform'] = self::pregPlatform($this_info['平台']);
                        }
                    }else{
                        $this_data['game_type'] = '';
                        $this_data['kf_platform'] = 0;
                    }
                }
                $this_data['kf_id'] = 0;
                if($this_data['kf_platform']){

                    $this_admin_info = self::searchAdminByName($this_data['kf_name'],$this_data['kf_platform']);

                    if($this_admin_info){
                        $this_data['kf_id'] = $this_admin_info['id'];
                    }
                }

                $this_qd_data['add_time'] = $this_data['create_time'] = time();
                $this_data['kf_score'] = -1;

                $add_data[] = $this_data;
                $qd_add_data[] = $this_qd_data;

            }
        }

        if(!$add_data){
            return ['code'=>2];
        }


        $add_key = [
            'kf_source',
            'kf_score',
            'server_start',
            'server_end',
            'server_long',
//            'question_type',
            'game_type',
            'text',
            'text_id',
            'create_time',
            'kf_platform',
            'kf_id',
            'partnerid',
        ];

        $sql = setIntoSql('qc_sell_question',$add_key,$add_data);

        $res = $QcQuestion->query($sql);

        $where = [];
        $where[] = ['text_id','in',$text_id_arr];
        $where[] = ['kf_source','=',$source];

        $sql = setUpdateSql('qc_sell_question',$where,['qc_num'=>['CONCAT('.date('Ymd').',id)']],0);

        $res = $QcQuestion->query($sql);

        $sql = setIntoSql('qd_conversation',$qd_key_arr,$qd_add_data);

        $res = $QdConversation->query($sql);

        return ['code'=>0,'msg'=>'成功操作：'.count($text_id_arr).'条'];
    }

    /**
     * @param $str
     * @return array
     */
    protected static function getQDUserTag($str){
        $data = [];
        $info = explode(';',trim($str));

        if($info){
            foreach ($info as $k => $v){
                $this_v = trim($v);
                $this_info = explode('/',$this_v);

                if(count($this_info) == 2){
                    $data[trim($this_info[0])] = trim($this_info[1]);
                }
            }
        }

        return $data;
    }

    /**
     * 平台匹配(会话信息，根据来源平台信息匹配游戏平台)
     * @param $str
     * @param int $source
     */
    public static function pregPlatform($str,$source=0){
        $platform_id = 0;

        if($source == QcConfig::KF_SOURCE_SOBOT){

        }

        $platform_list = SysServer::getPlatformList();

        foreach ($platform_list as $k => $v){
            if(preg_match('/'.$v['name'].'/',$str)){
                $platform_id = $v['platform_id'];
                break;
            }
        }

        return $platform_id;
    }

    /**
     * 真实名称查询后台用户账号
     * @param $name
     * @param $platform_id all 返回第一个
     * @return array
     */
    public static function searchAdminByName($name,$platform_id){

        $admin_list = SysServer::getAdminListCache();//id=>[id,name,platform,group_id]

        $platform_arr = SysServer::getPlatformList();//platform_id=>[platform_id,name,suffix]

        $info = [];
        foreach ($admin_list as $k => $v){
            if($v['name'] == $name){
                $this_platform = explode(',',$v['platform']);
                if($this_platform){
                    if($platform_id == 'all'){
                        $info = $v;
                        $new_platform_arr = arrReSet($platform_arr,'suffix');
                        if(isset($new_platform_arr[$this_platform[0]])){
                            $info['platform_id'] = $new_platform_arr[$this_platform[0]]['platform_id'];
                        }else{
                            $info['platform_id'] = 0;
                        }
                        break;
                    }
                    if(isset($platform_arr[$platform_id])){
                        $this_suffix = $platform_arr[$platform_id]['suffix'];
                        if(in_array($this_suffix,$this_platform)){
                            $info = $v;
                            break;
                        }
                    }
                }
            }
        }

        return $info;
    }

    /**
     * 质检配置-列表
     * @return array
     */
    public static function qcConfigList(){


        $kf_source_arr = QcConfig::$kf_source_arr;

        $config_other_arr = QcConfig::$config_other_arr;

        $config_arr = QcConfig::$config_arr;

        $system_config = SysServer::getAllConfigByCache();

        $config = [];

        foreach ($config_arr as $k => $v) {
            $config[$v['key']] = getArrVal($system_config,$v['key'],[]);
        }

        return ['data'=>compact('kf_source_arr','config_other_arr','config_arr','config')];
    }

    /**
     * 获取加/扣分配置数据
     * @param $p_data
     * @return array|\fix|string
     */
    public static function getScoreConfig($p_data){

        if(!isset($p_data['kf_source'])
            || !$p_data['kf_source']
            || !isset($p_data['key'])
            || !$p_data['key']
        ){
            return [];
        }

        $key = $p_data['key'].'_'.$p_data['kf_source'];
        $systemConfig = SysServer::getAllConfigByCache();
        $info = getArrVal($systemConfig,$key,[]);

        return $info;
    }

    /**
     * 质检配置-保存
     * @param $p_data
     */
    public static function configSave($p_data){

        $model = new QcConfig();

        $config_arr = QcConfig::$config_arr;

        $config_other_arr = QcConfig::$config_other_arr;

        foreach ($config_arr as $k => $v) {
            if(!isset($p_data[$v['key']])){
                continue;
            }

            $this_val = $p_data[$v['key']];

            if($this_val){
                if($v['type'] == 'score'){//重排数组键
                    $this_val = array_values($this_val);
                }
            }else{
                $this_val = [];
            }

            $this_info = $model->where('key',$v['key'])->find();

            if($this_info){
                $this_info->save(['val'=>$this_val]);
            }else{
                $model->create(['key'=>$v['key'],'val'=>$this_val]);
            }

        }

        foreach ($config_other_arr as $k => $v) {

            if(!isset($p_data[$v['key']])){
                continue;
            }

            $this_val = $p_data[$v['key']];
            $thiskey = $v['key'];

            if(in_array($v['type'],['score','source'])){//重排数组键
                $this_kf_source = isset($p_data['kf_source_'.$thiskey])?$p_data['kf_source_'.$thiskey]:0;
                if(!$this_kf_source){
                    continue;
                }
                $thiskey .= '_'.$this_kf_source;
            }

            if($this_val){
                if(in_array($v['type'],['score','source'])){//重排数组键
                    $this_val = array_values($this_val);
                }
            }else{
                $this_val = [];
            }

            $this_info = $model->where('key',$thiskey)->find();

            if($this_info){
                $this_info->save(['val'=>$this_val]);
            }else{
                $model->create(['key'=>$thiskey,'val'=>$this_val]);
            }
        }

        SysServer::getAllConfigByCache(1);
    }

    /**
     * 质检申诉记录-列表
     * @param $p_data
     * @return array
     */
    public static function appealLogList($p_data){

        $page = getArrVal($p_data,'page',1);
        $limit = getArrVal($p_data,'limit',20);
        $QcAppealLog = new QcAppealLog();

        list($list,$count) = self::getQcQuestionLogCommonList($p_data,$page,$limit);

        if($list){
            $admin_list = SysServer::getAdminListCache();

            $base_config = self::appealLogConfig();

            $platform_arr = SysServer::getPlatformList();

            foreach ($list as $k => $v) {
                $list[$k]['do_appeal_time'] = '';
                $list[$k]['appeal_time'] = '';
                $list[$k]['do_appeal_reason'] = '';

                $list[$k]['create_time_str'] = $v['create_time']?date('y-m-d H:i',$v['create_time']):'未知';
                $this_admin_id_info = getArrVal($admin_list,$v['kf_id'],[]);
                $list[$k]['group_id_str'] = $list[$k]['kf_name'] = '';
                if($this_admin_id_info){
                    $list[$k]['kf_name'] = $this_admin_id_info['name'];
                    if($this_admin_id_info['group_id']){
                        $this_kf_group_list = explode(',',$this_admin_id_info['group_id']);
                        $this_kf_group_list_name = [];
                        foreach ($this_kf_group_list as $tkgl_v){
                            $this_group_id_info = getArrVal($base_config['user_group_list'],$tkgl_v,[]);

                            if($this_group_id_info){
                                $this_kf_group_list_name[] =$this_group_id_info['name'];
                            }
                        }
                        if($this_kf_group_list_name){
                            $list[$k]['group_id_str'] = implode('/',$this_kf_group_list_name);
                        }
                    }
                }

                $list[$k]['admin_id_str'] = isset($admin_list[$v['admin_id']])?$admin_list[$v['admin_id']]['name']:'未知';
                $list[$k]['status_str'] = getArrVal($base_config['status_arr'],$v['status'],'');

                $this_platform_id_info = getArrVal($platform_arr,$v['platform_id'],[]);

                $list[$k]['platform_id_str'] =$this_platform_id_info?$this_platform_id_info['name']:'';

                $where = [];
                $where['log_id'] = $v['id'];
                $where['status'] = 1;
                $this_info = $QcAppealLog->where($where)->order('add_time desc')->find();
                if($this_info){
                    $list[$k]['appeal_time'] = $this_info->add_time;
                }

                $where = [];
                $where['log_id'] = $v['id'];
                $where['status'] = 3;
                $this_info = $QcAppealLog->where($where)->order('add_time desc')->find();
                if($this_info){
                    $list[$k]['do_appeal_time'] = $this_info->add_time;
                    $list[$k]['do_appeal_reason'] = $this_info->reason;
                }

                $list[$k]['action'] = ListActionServer::checkQcQuestionLogAction($v);
            }
        }

        return [$list,$count];
    }

    /**
     * 质检申述记录-配置
     * @return array
     */
    public static function appealLogConfig(){
        $config = [];

        $user_group_list = SysServer::getAdminGroupList(QcConfig::USER_GROUP_ONLINE_KF);

        $user_group_list = array_merge($user_group_list,SysServer::getAdminGroupList(QcConfig::USER_GROUP_VIP));

        $config['user_group_list'] =arrReSet($user_group_list,'id');

        $config['kf_source_arr'] = QcConfig::$kf_source_arr;

        $config['status_arr'] = QcQuestionLog::$status_arr;

        $config['admin_list_manager'] = SysServer::getUserListByAdminInfo(self::$user_data,['group_type'=>QcConfig::USER_GROUP_QC]);

        return $config;
    }

    protected static function getAdminId(&$where){

        if(!in_array(QcConfig::USER_GROUP_QC,self::$user_data['user_group_type_arr']) && self::$user_data['is_admin'] == 0){//非管理&&非质检（客服）=>只能看所属平台数据

            if(self::$user_data['platform_id']){
                $kf_id_arr = [];
                $admin_list = SysServer::getAdminListCache();
                foreach ($admin_list as $v){
                    $this_arrMID = arrMID($v['platform_id'],self::$user_data['platform_id']);
                    $this_arrMID1 = arrMID($v['user_group_type_arr'],self::$user_data['user_group_type_arr']);
                    if($this_arrMID['ai_com'] && $this_arrMID1['ai_com'] ){
                        $kf_id_arr[] = $v['id'];
                    }
                }
                if($kf_id_arr){
                    $where[] = ['kf_id','in',$kf_id_arr];
                }else{
                    $where[] = ['kf_id','=',-1];
                }
            }else{
                $where[] = ['kf_id','=',-1];
            }
        }
    }

    protected static function testImg($old,$text){

        $old = str_replace('\\','\\\\',$old);
        $old = str_replace('/','\/',$old);

        $flag = preg_match('/'.$old.'/', $text,$res);

        return $flag;
    }
}
