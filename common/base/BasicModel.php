<?php

namespace common\base;



use think\Db;
use think\Model;

class BasicModel extends Model
{
    /**
     * 批量插入或更新-表需要有唯一键
     * $param array  数据，二维数组
     * $update_field array 需要更新的字段, [key=>val,。。。]格式， 该参数为空时，不进行更新操作
     *      如果需要自定义更新字段，val按原生sql语法填写， 如： [ 'count'=> 'count+1' ] 即更新count字段时，按 count+1 处理
     *      如果无需自定义，则val 为 false , 则更新时 按 value($field) 处理
     * @param array $param
     * @param array $update_field
     * @return int|string
     */
    public static function multiSave( array $param, array $update_field=[]){
        try{
            $obj        = new static();
            $config     = $obj->getConfig();
            $table      = self::getTable();
            $pk         = $obj->getPk();
            $columns    = [];

            #字段-整理
            foreach( $param[0] as $k=>$v ){
                if( $k==$pk ) continue;
                $columns[] = $k;
            }

            $fields     = "`".implode("`,`",$columns)."`";
            $base_sql   = "INSERT INTO {$table}($fields) values";
            $update_sql = "";
            #更新字段处理
            if( !empty($update_field) ){
                $update_sql = " ON DUPLICATE KEY UPDATE ";
                foreach( $update_field as $k=>$v ){
                    if( false !== $v ){
                        $update_sql .= "`{$k}`='{$v}',";
                    }else{
                        $update_sql .= "`{$k}`=values(`{$k}`),";
                    }
                }
                $update_sql = trim($update_sql,",");
            }
            $count = $num = 0;
            $sql   = '';
            foreach( $param as $info ){
                ++$count;
                $t_sql = '';
                foreach( $columns as $field ){
                    $t_val = $info[$field];
                    $t_val  = is_string($t_val)? "'{$t_val}'":$t_val;
                    $t_sql .= "{$t_val},";
                }
                $sql .= "(". trim($t_sql,",") ."),";
                if( $count%1000==0 ){
                    $sql = trim($sql,",");
                    $res = Db::connect($config)->execute($base_sql.$sql.$update_sql);
                    if( $res ) $num+=1000;
                    $sql   = '';
                }
            }
            if( !empty($sql) ){
                $sql = trim($sql,",");
                $res = Db::connect($config)->execute($base_sql.$sql.$update_sql);
                if( $res ) $num+=($count%1000);
            }
            return $num;
        }catch(\Exception $e){
            return "error:".$e->getMessage();
        }
    }
}