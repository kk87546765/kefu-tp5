<?php
/**
 * 系统
 */
namespace common\server\Sensitive;



use common\base\BasicServer;
use common\sql_server\SensitiveSqlServer;



class SensitiveServer extends BasicServer
{

    public static $sensitive_keyword_key = 'sensitive';

    public static $return = ['code'=>-1,''];

    public static function getList($data)
    {
        $where = self::dealData($data);

        $serach_data = [
            'where'=>$where ?? '',
            'order'=>$data['order'] ?? 'id desc',
            'offset'=>($data['page']-1)*$data['limit'],
            'limit'=>$data['limit'],
        ];

        $keywords = SensitiveSqlServer::getList($serach_data);

        foreach ($keywords as &$keyword) {

            $keyword['game_name'] = $data['gamelist'][$keyword['game']]['name'];
            $keyword['addtime']   = date("Y-m-d H:i:s",$keyword['addtime']);
            $keyword['status']    = $keyword['status']?'开启':'关闭';
//            $keyword['total']     = $es->getLikeWords('sensitive_keyword',$keyword['keyword']);
        }

        return $keywords;
    }

    public static function getCount($data)
    {
        $where = self::dealData($data);

        $count = SensitiveSqlServer::getCount($where);

        return $count;
    }


    public static function dealData($data)
    {

        $where = '1=1 ';
        if ( $data['game'] ) {
            $where .= " and game = '{$data['game']}'";
        }

        if ( $data['keyword'] ) {
            $where .= " and keyword like '{$data['keyword']}%'";
        }


        return $where;
    }

    public static function add($data)
    {
        if ($data['keywords']) {
            $keywords = explode("\n", $data['keywords']);

            foreach ($keywords as $keyword) {

                $where =  "keyword = '{$keyword}' and game = '{$data['game']}'";
                $info = sensitiveServer::getOne($where);

                if (isset($info) && $info != false) {
                    return self::$return['msg'] = '操作失败，敏感词已存在';
                }

                $add_data = [
                    'game'=>$data['game'],
                    'add_user'=>$data['admin_user'],
                    'keyword' =>$keyword,
                    'level_min'=>$data['level_min'],
                    'level_max'=>$data['level_max'],
                    'money_min'=>$data['money_min'],
                    'money_max'=>$data['money_max'],
                    'addtime'=>time(),
//                    'num'=>$data['num'],
                    'status'=>$data['status'] == 1 ? 1 : 0,
                ];


                $res = sensitiveSqlServer::add($add_data);

                if ($res) {
                    $redis = get_redis();
                    $t_keyword = str_replace(array(".", "+"), array("", ""), $keyword);
                    $redis->sadd(self::$sensitive_keyword_key . '_' . $data['game'], $t_keyword);

                }
            }
        }
        self::$return['code'] = 0;
        self::$return['msg'] = '添加成功';
        return self::$return;
    }

    public static function edit($data)
    {

        $resemble_word_arr = [];
        if ($data['keyword']) {
            $redis = get_redis();
            $info = SensitiveServer::getOne(['id' => $data['id']]);

            if (!isset($info) || $info == false) {
                return self::$return['msg'] = '操作失败，敏感词不存在';
            }

            $old_keyword = $info['keyword'];
            $old_game = $info['game'];

            $update_data['id'] = $data['id'];
            $update_data['game'] = $data['game'];
            $update_data['keyword'] = trim($data['keyword']);
            $update_data['level_min'] = $data['level_min'];
            $update_data['level_max'] = $data['level_max'];
            $update_data['money_min'] = $data['money_min'];
            $update_data['money_max'] = $data['money_max'];
            $update_data['status'] = $data['status'] == 1 ? 1 : 0;

            $res = SensitiveSqlServer::edit($update_data);

            if ($res) {

                $t_keyword = str_replace(array(".", "+"), array("", ""), $old_keyword);
                $redis->srem(self::$sensitive_keyword_key . '_' . $old_game, $t_keyword);
                $t_keyword = str_replace(array(".", "+"), array("", ""), $data['keyword']);
                $redis->sadd(self::$sensitive_keyword_key . '_' . $data['game'], $t_keyword);


                self::$return['code'] = 0;
                self::$return['msg'] = '修改成功';

            }
        }

        return self::$return;
    }


    public static function getOne($where)
    {
        $res = SensitiveSqlServer::getOne($where);
        return $res;
    }

    public static function del($ids)
    {
        if(is_array($ids)){
            $ids = implode(',',$ids);
        }elseif(is_string($ids)){
            $ids = $ids;
        }
        $data['where'] = "id in ({$ids})";
        $data['offset'] = 0;
        $data['limit'] = 200000;
        $data['order'] = 'id desc';
        $keywords = SensitiveSqlServer::getList($data);
        $redis = get_redis();
        foreach( $keywords as $v ){
            $t_keyword = str_replace(array(".", "+"), array("", ""), $v['keyword']);
            $redis->srem(self::$sensitive_keyword_key . '_' . $v['game'], $t_keyword);
        }

        $res = SensitiveSqlServer::del($ids);
        self::$return['code'] = $res ? 0 : 1;
        self::$return['msg'] = '删除成功';
        return self::$return;
    }


}