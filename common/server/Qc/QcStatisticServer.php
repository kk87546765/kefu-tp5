<?php
/**
 * 系统
 */
namespace common\server\Qc;


use common\base\BasicServer;
use common\model\db_customer\QcConfig;
use common\model\db_customer\QcQuestionLog;
use common\server\AdminServer;
use common\server\SysServer;

class QcStatisticServer extends BasicServer
{

    public static $qc_level_field_arr = [
        '满分'=>'full_score',
        '非致命错误'=>'no_error',
        '一级致命'=>'error_1',
        '二级致命'=>'error_2',
        '三级致命'=>'error_3',
        '无效质检'=>'no_active',
    ];
    /**
     * 会话统计-列表
     * @param $param
     * @return array
     */
    public static function getConversationTypeList($param){

        $page = getArrVal($param,'page',1);

        $limit = getArrVal($param,'limit',20);

        $model = new QcQuestionLog();

        if(!isset($param['kf_source']) || !$param['kf_source']){
            return [[],0];
        }
        if(!is_array($param['kf_source'])){
            $this_kf_source = explode(',',$param['kf_source']);
        }else{
            $this_kf_source = $param['kf_source'];
        }

        $qc_config = SysServer::getAllConfigByCache();

        $this_question_type_key = 'question_type_'.$param['kf_source'];

        $question_type_arr = getArrVal($qc_config,$this_question_type_key,[]);

        if(!$question_type_arr){
            return [[],0];
        }

        $question_type_arr_new = [];
        foreach ($question_type_arr as $k => $v){
            $question_type_arr_new[$k]['field'] = 'question_type'.$k;
            $question_type_arr_new[$k]['title'] = $v;
        }

        $where = getDataByField($param,['kf_id','question_type','game_type','qc_type',],1);

        $where['status'] = 0;

        if(count($this_kf_source) == 1){
            $where['kf_source'] = $this_kf_source[0];
        }elseif(count($this_kf_source) > 1){
            $where[] = ['kf_source','in',$this_kf_source];
        }


        if(isset($param['platform_id']) && $param['platform_id']){

            $this_data = explode(',',$param['platform_id']);

            if(count($this_data) == 1){
                $where['platform_id'] = $this_data[0];
            }elseif(count($this_data) > 1){
                $where[] = ['platform_id','in',$this_data];
            }
        }

        if(isset($param['kf_is_solve']) && $param['kf_is_solve']>-1){
            $where['kf_is_solve'] = $param['kf_is_solve'];
        }
        if(isset($param['question_is_solve']) && $param['question_is_solve']>-1){
            $where['question_is_solve'] = $param['question_is_solve'];
        }

        if(isset($param['create_time_start']) && $param['create_time_start']){
            $where[] = ['create_time','>=',strtotime($param['create_time_start'])];
        }
        if(isset($param['create_time_end']) && $param['create_time_end']){
            $where[] = ['create_time','<',strtotime($param['create_time_end'])];
        }

        if(isset($param['server_start_start']) && $param['server_start_start']){
            $where[] = ['server_start','>=',strtotime($param['server_start_start'])];
        }
        if(isset($param['server_start_end']) && $param['server_start_end']){
            $where[] = ['server_start','<',strtotime($param['server_start_end'])];
        }

        $column = 'distinct kf_id';

        $count = $model->where(setWhereSql($where,''))->count($column);

        if(!$count){
            return [[],0];
        }


        $columns = 'kf_id';

        foreach ($question_type_arr_new as $k => $v){
            $columns .=",SUM(CASE question_type WHEN '".$v['title']."' THEN 1 ELSE 0 END) AS '$v[title]'";
        }

        $sql = "SELECT $columns FROM qc_question_log".setWhereSql($where);

        $sql.=' GROUP BY kf_id';
        $sql.=' ORDER BY kf_id asc';

        if($limit && $page){
            $sql.=' limit '.(($page - 1) * $limit).','.$limit;
        }

        $list = $model->query($sql)->toArray();

        $admin_list = SysServer::getAdminListCache();

        $all_count = ['kf_id_str'=>'总'];

        foreach ($list as $k => &$v){
            $this_admin_info = getArrVal($admin_list,$v['kf_id'],[]);
            $v['kf_id_str'] = $this_admin_info?$this_admin_info['name']:'';
            foreach ($v as $v_k => $v_v){
                if(!in_array($v_k,['kf_id','kf_id_str'])){
                    if(isset($all_count[$v_k])){
                        $all_count[$v_k] += $v_v;
                    }else{
                        $all_count[$v_k] = $v_v;
                    }
                }
            }
        }

        $list[] =$all_count;

        return [$list,$count];


    }
    /**
     * 会话统计-配置数据
     * @return array
     */
    public static function getConversationTypeConfig(){
        $config = [];

        $qc_config = SysServer::getAllConfigByCache();

        $config['question_type'] = $qc_config['question_type'];

        $config['qc_type'] = $qc_config['qc_type'];

        $config['game_type'] = $qc_config['game_type'];

        $config['status_arr'] = QcQuestionLog::$status_arr;

        $config['kf_source_arr'] = QcConfig::$kf_source_arr;

        return $config;
    }

    /**
     * 扣分原因统计-列表
     * @param $param
     * @return array
     */
    public static function getQcScoreDecList($param){

        $page = getArrVal($param,'page',1);

        $limit = getArrVal($param,'limit',20);

        $model = new QcQuestionLog();

        $qc_config = SysServer::getAllConfigByCache();

        $kf_source = getArrVal($param,'kf_source',0);

        $qc_score_dec_arr = getArrVal($qc_config,'qc_score_dec_'.$kf_source,[]);

        if(!$qc_score_dec_arr){
            return [[],0];
        }

        $qc_score_dec_arr_new = [];
        foreach ($qc_score_dec_arr as $k => $v){
            $qc_score_dec_arr_new[$k]['field'] = 'qc_score_dec'.$k;
            $qc_score_dec_arr_new[$k]['title'] = $v['reason'];
            $qc_score_dec_arr_new[$k]['score'] = $v['score'];
        }

        $where = getDataByField($param,['kf_id','question_type','game_type','qc_type',],1);
        $where['status'] = 0;
        if(isset($param['group_id']) && $param['group_id'] ){
            $ids = AdminServer::getAdminIdsByGroupId($param['group_id']);
            if($ids){
                $where[] = ['kf_id','in',$ids];
            }else{
                $where[] = ['kf_id','=',0];
            }
        }

        if(isset($param['kf_source']) && $param['kf_source']){

            $this_data = explode(',',$param['kf_source']);

            if(count($this_data) == 1){
                $where['kf_source'] = $this_data[0];
            }elseif(count($this_data) > 1){
                $where[] = ['kf_source','in',$this_data];
            }
        }

        if(isset($param['platform_id']) && $param['platform_id']){

            $this_data = explode(',',$param['platform_id']);

            if(count($this_data) == 1){
                $where['platform_id'] = $this_data[0];
            }elseif(count($this_data) > 1){
                $where[] = ['platform_id','in',$this_data];
            }
        }

        if(isset($param['kf_is_solve']) && $param['kf_is_solve']>-1){
            $where['kf_is_solve'] = $param['kf_is_solve'];
        }
        if(isset($param['question_is_solve']) && $param['question_is_solve']>-1){
            $where['question_is_solve'] = $param['question_is_solve'];
        }

        if(isset($param['create_time_start']) && $param['create_time_start']){
            $where[] = ['create_time','>=',strtotime($param['create_time_start'])];
        }
        if(isset($param['create_time_end']) && $param['create_time_end']){
            $where[] = ['create_time','<',strtotime($param['create_time_end'])];
        }

        if(isset($param['server_start_start']) && $param['server_start_start']){
            $where[] = ['server_start','>=',strtotime($param['server_start_start'])];
        }
        if(isset($param['server_start_end']) && $param['server_start_end']){
            $where[] = ['server_start','<',strtotime($param['server_start_end'])];
        }

        $column = 'distinct kf_id';

        $count = $model->where(setWhereSql($where,''))->count($column);

        if(!$count){
            return [[],0];
        }


        $columns = 'kf_id';

        foreach ($qc_score_dec_arr_new as $k => $v){
            $columns .=",SUM(CASE qc_question_score_log.reason WHEN '".$v['title']."' THEN 1 ELSE 0 END) AS '$v[title]'";//$v['field']
        }

        $sql = "SELECT $columns FROM qc_question_log LEFT JOIN qc_question_score_log ON qc_question_score_log.qc_question_log_id = qc_question_log.id".setWhereSql($where);

        $sql.=' GROUP BY kf_id';
        $sql.=' ORDER BY kf_id desc';

        if($limit && $page){
            $sql.=' limit '.(($page - 1) * $limit).','.$limit;
        }

        $list = $model->query($sql)->toArray();

        $admin_list = SysServer::getAdminListCache();
        $all_count = ['kf_id_str'=>'总'];
        foreach ($list as $k => &$v){
            $this_admin_info = getArrVal($admin_list,$v['kf_id'],[]);
            $v['kf_id_str'] = $this_admin_info?$this_admin_info['name']:'';

            foreach ($v as $v_k => $v_v){
                if(!in_array($v_k,['kf_id','kf_id_str'])){
                    if(isset($all_count[$v_k])){
                        $all_count[$v_k] += $v_v;
                    }else{
                        $all_count[$v_k] = $v_v;
                    }
                }
            }
        }

        $list[] =$all_count;

        return [$list,$count];


    }
    /**
     * 扣分原因统计-配置
     * @return array
     */
    public static function getQcScoreDecConfig(){
        $config = [];

        $qc_config = SysServer::getAllConfigByCache();

        $config['question_type'] = $qc_config['question_type'];

        $config['qc_type'] = $qc_config['qc_type'];

        $config['game_type'] = $qc_config['game_type'];

        $config['status_arr'] = QcQuestionLog::$status_arr;

        $config['kf_source_arr'] = QcConfig::$kf_source_arr;

        $config['group_list'] = array_merge(
            SysServer::getAdminGroupListShow(QcConfig::USER_GROUP_VIP),
            SysServer::getAdminGroupListShow(QcConfig::USER_GROUP_ONLINE_KF)
        );

        return $config;
    }

    /**
     * 质检统计-列表
     * @param $param
     * @return array
     */
    public static function getQcKfList($param){

        $page = getArrVal($param,'page',1);

        $limit = getArrVal($param,'limit',20);

        $model = new QcQuestionLog();

        $qc_config = SysServer::getAllConfigByCache();

        $qc_level_arr = getArrVal($qc_config,'qc_level',[]);

        if(!$qc_level_arr){
            return [[],0];
        }

        $qc_level_arr_new = [];

        foreach ($qc_level_arr as $k => $v){
            $this_field = getArrVal(self::$qc_level_field_arr,$v,'');
            if($this_field){
                $qc_level_arr_new[$k]['field'] =$this_field;
                $qc_level_arr_new[$k]['title'] = $v;
            }

        }

        $search_type = getArrVal($param,'search_type','kf');

        $where = getDataByField($param,['kf_id','admin_id','qc_type'],1);
        $where['status'] = 0;

        if(isset($param['kf_source']) && $param['kf_source']){

            $this_data = explode(',',$param['kf_source']);

            if(count($this_data) == 1){
                $where['kf_source'] = $this_data[0];
            }elseif(count($this_data) > 1){
                $where[] = ['kf_source','in',$this_data];
            }
        }

        if(isset($param['platform_id']) && $param['platform_id']){

            $this_data = explode(',',$param['platform_id']);

            if(count($this_data) == 1){
                $where['platform_id'] = $this_data[0];
            }elseif(count($this_data) > 1){
                $where[] = ['platform_id','in',$this_data];
            }
        }


        if(isset($param['create_time_start']) && $param['create_time_start']){
            $where[] = ['create_time','>=',strtotime($param['create_time_start'])];
        }
        if(isset($param['create_time_end']) && $param['create_time_end']){
            $where[] = ['create_time','<',strtotime($param['create_time_end'])];
        }

        if(isset($param['server_start_start']) && $param['server_start_start']){
            $where[] = ['server_start','>=',strtotime($param['server_start_start'])];
        }
        if(isset($param['server_start_end']) && $param['server_start_end']){
            $where[] = ['server_start','<',strtotime($param['server_start_end'])];
        }


        if($search_type == 'admin'){
            $group_field = 'admin_id';
        }else{
            $group_field = 'kf_id';
        }

        $column = 'distinct '.$group_field;

        $count = $model->where(setWhereSql($where,''))->count($column);

        if(!$count){
            return [[],0];
        }


        $columns = $group_field.',kf_source,platform_id';


        foreach ($qc_level_arr_new as $k => $v){
            $columns .=",SUM(CASE qc_level WHEN '$v[title]' THEN 1 ELSE 0 END) AS $v[field]";
            $columns .=",SUM(CASE qc_level WHEN '$v[title]' THEN qc_score ELSE 0 END) AS $v[field]_score";
            $columns .=",SUM(CASE WHEN qc_level='$v[title]' AND qc_score = 100 THEN 1 ELSE 0 END) AS $v[field]_score_100";
            $columns .=",SUM(CASE WHEN qc_level='$v[title]' AND qc_score = 0 THEN 1 ELSE 0 END) AS $v[field]_score_0";
            $columns .=",SUM(CASE WHEN qc_level='$v[title]' AND kf_is_solve = 1 THEN 1 ELSE 0 END) AS $v[field]_kf_is_solve";
            $columns .=",SUM(CASE WHEN qc_level='$v[title]' AND question_is_solve = 1 THEN 1 ELSE 0 END) AS $v[field]_question_is_solve";

        }
//        dd($columns);
//        $sql = "SELECT $columns FROM qc_question_log LEFT JOIN qc_question_score_log ON qc_question_score_log.qc_question_log_id = qc_question_log.id".setWhereSql($where);
        $sql = "SELECT $columns FROM qc_question_log ".setWhereSql($where);
        $sql.=' GROUP BY '.$group_field;
        $sql.=' ORDER BY '.$group_field.' desc';
//        dd($sql);
        if($limit && $page){
            $sql.=' limit '.(($page - 1) * $limit).','.$limit;
        }

        $list = $model->query($sql);

        $admin_list = SysServer::getAdminListCache();
        $platform_list = SysServer::getPlatformList();
        $kf_source_arr = QcConfig::$kf_source_arr;

        $count_all = [
            'kf_id_str'=>'总',
            'platform_id_str'=>'',
            'admin_id_str'=>'总',
        ];
        self::initQcKf($count_all);


        foreach ($list as $k => &$v){
            $this_admin_info = getArrVal($admin_list,$v[$group_field],[]);
            $v[$group_field.'_str'] = $this_admin_info?$this_admin_info['name']:'';
            $this_platform_info = getArrVal($platform_list,$v['platform_id'],[]);
            $v['platform_id_str'] = $this_platform_info?$this_platform_info['name']:'';
            $v['kf_source_str'] = getArrVal($kf_source_arr,$v['kf_source'],'');

            self::initQcKf($v);

            foreach (self::$qc_level_field_arr as $k1 => $v1){
                $v['c_all'] += $v[$v1];
                if(in_array($v1,['full_score','no_error','error_1','error_2','error_3'])){
                    $v['c_all_active'] += $v[$v1];
                    $v['c_all_active_score'] += $v[$v1.'_score'];
                    $v['zero_count'] += $v[$v1.'_score_0'];
                    $v['full_count'] += $v[$v1.'_score_100'];
                    $v['kf_is_solve_count'] += $v[$v1.'_kf_is_solve'];
                    $v['question_is_solve_count'] += $v[$v1.'_question_is_solve'];
                    if($v1!='full_score'){
                        $v['not_full_count'] += $v[$v1];
                    }
                    if(in_array($v1,['error_1','error_2','error_3'])){
                        $v['error_count'] += $v[$v1];
                    }
                }
            }
            foreach ($v as $v_k => $v_v){
                if(preg_match('/str/',$v_k)){
                    continue;
                }
                if(!isset($count_all[$v_k])){
                    $count_all[$v_k] = 0;
                }
                $count_all[$v_k]+=$v_v;
            }
            //有效平均分
            self::countPresentQCKf($v);
        }
        self::countPresentQCKf($count_all);
        $list[] = $count_all;
        return [$list,$count];


    }
    /**
     * 质检统计-初始化行参数
     * @param $v
     */
    public static function initQcKf(&$v){
        $v['c_all'] = 0;//总数
        $v['c_all_active'] = 0;//有效总数
        $v['c_all_active_score'] = 0;//有效总分数
        $v['avg_score'] = 0;//平均
        $v['full_count'] = 0;
        $v['not_full_count'] = 0;//出错量
        $v['not_full_present_str'] = '';//出错率

        $v['full_present_str'] = '';//满分率
        $v['error_count'] = 0;//致命量
        $v['error_present_str'] = '';//致命率
        $v['zero_count'] = 0;//零分量
        $v['zero_present_str'] = '';//零分率
        $v['kf_is_solve_count'] = 0;//客服解决量
        $v['kf_is_solve_present_str'] = '';//客服解决率
        $v['question_is_solve_count'] = 0;//问题解决量
        $v['question_is_solve_present_str'] = '';//问题解决率
    }
    /**
     * 质检统计-计算总参数
     * @param $v
     */
    public static function countPresentQCKf(&$v){
        if($v['c_all_active']){
            $v['avg_score'] = countPresent($v['c_all_active_score']/100,$v['c_all_active']);
        }
        if($v['c_all_active']){
            $v['not_full_present_str'] = countPresent($v['not_full_count'],$v['c_all_active']).'%';
            $v['full_present_str'] = countPresent($v['full_count'],$v['c_all_active']).'%';
            $v['error_present_str'] = countPresent($v['error_count'],$v['c_all_active']).'%';
            $v['zero_present_str'] = countPresent($v['zero_count'],$v['c_all_active']).'%';
            $v['kf_is_solve_present_str'] = countPresent($v['kf_is_solve_count'],$v['c_all_active']).'%';
            $v['question_is_solve_present_str'] = countPresent($v['question_is_solve_count'],$v['c_all_active']).'%';
        }
    }
    /**
     * 质检统计-配置
     * @return array
     */
    public static function getQcKfConfig(){

        $config = [];
        $data = [];

        $qc_config = SysServer::getAllConfigByCache();

        $config['qc_level'] = $qc_config['qc_level'];
        $config['qc_type'] = $qc_config['qc_type'];

        $field_list = [];

        $field_list[] = ['title'=>'抽检总量', 'field'=>'c_all', 'minWidth'=>100];

        foreach ($qc_config['qc_level'] as $k => $v){
            $this_field = getArrVal(self::$qc_level_field_arr,$v,'');
            if($this_field){
                $field_list[] = [
                    'title'=>$v,
                    'field'=>$this_field,
                    'minWidth'=>120,
                ];
            }
        }

        $field_list[] = ['title'=>'平均分', 'field'=>'avg_score', 'minWidth'=>100];
        $field_list[] = ['title'=>'出错量', 'field'=>'not_full_count', 'minWidth'=>100];
        $field_list[] = ['title'=>'出错率', 'field'=>'not_full_present_str', 'minWidth'=>100];
        $field_list[] = ['title'=>'满分量', 'field'=>'full_count', 'minWidth'=>100];
        $field_list[] = ['title'=>'满分率', 'field'=>'full_present_str', 'minWidth'=>100];
        $field_list[] = ['title'=>'致命率', 'field'=>'error_present_str', 'minWidth'=>100];
        $field_list[] = ['title'=>'零分量', 'field'=>'zero_count', 'minWidth'=>100];
        $field_list[] = ['title'=>'零分率', 'field'=>'zero_present_str', 'minWidth'=>100];
        $field_list[] = ['title'=>'客服解决量', 'field'=>'kf_is_solve_count', 'minWidth'=>100];
        $field_list[] = ['title'=>'客服解决率', 'field'=>'kf_is_solve_present_str', 'minWidth'=>100];
        $field_list[] = ['title'=>'问题解决量', 'field'=>'question_is_solve_count', 'minWidth'=>100];
        $field_list[] = ['title'=>'问题解决率', 'field'=>'question_is_solve_present_str', 'minWidth'=>100];

        $config['kf_source_arr'] = QcConfig::$kf_source_arr;

        $time_arr = timeCondition('month');
        $data['create_time_start'] = date('Y-m-d H:i:s',$time_arr['starttime']);

        return compact('config','field_list','data');
    }
}
