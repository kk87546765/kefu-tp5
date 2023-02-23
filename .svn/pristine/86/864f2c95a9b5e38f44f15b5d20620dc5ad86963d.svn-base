<?php
/**
 * Created by PhpStorm.
 * User: crosstime
 * Date: 2018/8/14
 * Time: 下午5:31
 */
namespace common\sql_server;

use common\model\gr_chat\Block;

class CountBlockSqlServer extends BaseSqlServer
{


    public static function getList($tmp_data)
    {
        $sql = "
         SELECT
              *
            FROM
                (
                    SELECT
                        op_admin_id,
                        sum(if(`type`='USER',1,0)) as type_user,
                        sum(if(`type`='CHAT',1,0)) as type_chat,
                        gkey
                    FROM
                        (select * from  gr_block where {$tmp_data['condition']}  GROUP BY
                       uid,type,{$tmp_data['group']} )c
                        
                    WHERE
                       {$tmp_data['condition']}
                and op_admin_id != ''
                    GROUP BY
                       {$tmp_data['group']}    
                ) b
                left join gr_admin c 
                on b.op_admin_id = c.username
            LIMIT {$tmp_data['offset']},
            {$tmp_data['limit']}";


        $model = new Block();
        $blocks = $model->query($sql);

        $blocks = isset($blocks) ? $blocks : [];
        return $blocks;
    }

    public static function getCount($tmp_data)
    {
        $sql = "
            SELECT
                count(*)as count
            FROM
                (
                    SELECT
                       *
                    FROM
                        gr_block
                    WHERE
                       {$tmp_data['condition']}
                and op_admin_id != ''
                    GROUP BY
                       {$tmp_data['group']}

                    
                ) b
           ";
        $model = new Block();
        $count = $model->query($sql);
        $count = isset($count) ? $count[0]['count'] : 0;
        return $count;

    }


}