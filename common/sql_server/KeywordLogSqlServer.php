<?php

namespace common\sql_server;


use common\model\gr_chat\KeywordLog;
class KeywordLogSqlServer extends BaseSqlServer
{


    public static function add($data)
    {
        $model = new KeywordLog();

        $data = $model->insert($data);

        return $data;
    }

    public static function getCount($where)
    {
        $model = new KeywordLog();

        $data = $model->where($where)->count();

        return $data;
    }

    public static function insertData($data){
        $sql = "INSERT INTO `gr_keyword_log` (
                `uid`,
                `uname`,
                `gkey`,
                `sid`,
                `keyword`,
                `content`,
                `roleid`,
                `rolename`,
                `role_level`,
                `count_money`,
                `addtime`,
                `type`,
                `tkey`
            )
            VALUES
                ('{$data['uid']}',
                    '{$data['uname']}',
                    '{$data['gkey']}',
                    '{$data['sid']}',
                    '{$data['keyword']}',
                    '{$data['content']}',
                    '{$data['roleid']}',
                    '{$data['rolename']}',
                    '{$data['role_level']}',
                    '{$data['count_money']}',
                    '{$data['addtime']}',
                     {$data['type']},
                     '{$data['tkey']}'
                     
                ); ";
        $model = new KeywordLog();

        $ret = $model->execute($sql);
        return $ret;
    }


}