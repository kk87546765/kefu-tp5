<?php


namespace common\server\UpdateBlockStatus;

use common\base\BasicServer;
use common\libraries\Common;
use common\server\Game\BlockServer;
use common\server\RoleNameBlock\RoleNameBlockServer;

class UpdateBlockStatusServer extends BasicServer
{

    public $return = ['code'=>-1,'data'=>['msg'=>'err']];

    public function updateBlockStatus()
    {
        $var = BlockServer::getBlockInfo();
        $ids = array_column($var,'id');
        $flag = BlockServer::unblockAndChat($ids);

        if($flag){
            $this->return['code'] = $flag;
            $this->return['data']['msg'] = 'success';
        }
        return $this->return;
    }

    public function updateRoleNameBlockStatus()
    {

        $var = RoleNameBlockServer::getBlockInfo();

        $ids = array_column($var,'id');
        $flag = BlockServer::roleNameUnblockAndChat($ids);

        if($flag){
            $this->return['code'] = $flag;
            $this->return['data']['msg'] = 'success';
        }
        return $this->return;
    }
}