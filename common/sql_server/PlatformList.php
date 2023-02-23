<?php


namespace common\sql_server;

use common\model\db_customer\PlatformList as PlatformListModel;

class PlatformList
{
    const PLATFORM_SUFFIX_INFO_KEY = 'platform_suffix_info';
    const PLATFORM_SUFFIX_FIELD = 'suffix_';
    const PLATFORM_ID_FIELD = 'id_';
    const STATUS_OPEN = 1;

    protected $_tablename = 'platform_list';



    public static $static_arr = [
        1=>'开启',
        2=>'关闭',
    ];


    /**
     * @param array $data
     * @return array
     */
    public static function getPlatformList($data=array())
    {
        $conditions = '1 ';
        $bind = $result = [];

        foreach ($data as $k=>$v) {
            $conditions .= "and $k = :$k: ";
            $bind[$k] = $v;
        }


        if (empty($bind)) {

            $res = PlatformListModel::all();
        }else {
            $res = PlatformListModel::all($data);
        }

        if (!empty($res)) $result = $res->toArray();

        return $result;
    }

    public static function add($data)
    {
        $model = new self;

        foreach ($data as $key => $item) {
            $model->$key = $item;
        }
        $model->create_time = time();
        $res = $model->create();

        if(!$res){
            return false;
        }
        return $model;
    }

    public static function del($id)
    {
        $model = new self;
        $res = $model->findFirst([
            'conditions' => "id = {$id}",
        ]);
        return $res->delete();
    }

    public static function edit($data)
    {
        if (
        !$role = self::findFirst([
            'conditions' => "id = ?0",
            'bind'       => [
                $data['id']
            ]
        ])
        ) {
            throw new \Quan\Common\Libraries\Exception\ApiException(4044001);
        }
        unset($data['id']);
        foreach ($data as $key => $item) {
            $role->$key = $item;
        }
        return $role->save();
    }

    public static function getOneById($id){

        $data = self::findFirst([
            'conditions'=> 'id = '.$id
        ]);

        return $data;
    }

    public function getAllByWhere($where,$field='*'){

        $conditions = '';
        $bind = [];

        setWhere($conditions,$bind,$where);

        $list = $this->find([
            'conditions'=>$conditions,
            'bind'=>$bind,
            'order'=>'id DESC',
            'columns'=>$field,
        ]);

        return $list;

    }


    public static function getOneBySuffix($suffix){

        $data = self::findFirst([
            'conditions'=> "platform_suffix = '{$suffix}'"
        ]);
        $data = isset($data) ? $data->toArray() : [];

        return $data['platform_id'];
    }
}