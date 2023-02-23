<?php
/**
 * Created by PhpStorm.
 * User: crosstime
 * Date: 2017/10/12
 * Time: 上午11:57
 */
namespace app\admin\controller;

use common\libraries\Common;

use common\server\BatchBlock\BatchBlockServer;
//use common\Logics\Platform\BanLogic;
//use common\Models\Block;
//use common\Models\BatchBlockModel;
//use common\Models\KefuCommonMember;
//use common\Models\UserImei;

use common\Logics\Game\BlockLogic;
use Phalcon\Di;
use Quan\System\Config;

class BatchBlock extends Oauth
{


//    public $blocktypes = [
//        Block::TYPE_CHAT => '禁言',
//        Block::TYPE_IP => '禁IP',
//        Block::TYPE_USER => '封用户',
//        Block::TYPE_IMEI => '封IMEI',
//        Block::TYPE_AUTO => '关键词自动封禁',
//        Block::TYPE_ACTION => '行为封禁',
//        Block::TYPE_AUTOCHAT => '关键词自动禁言',
//        Block::TYPE_ACTIONCHAT => '行为禁言',
//    ];




    public function batch_block(){

        $data['account'] = $this->request->post('account/s', '');
        $data['game'] = $this->request->post('game/s', '');
        $data['type'] = $this->request->post('type/s', '2');
        $data['ban_time'] = $this->request->post('ban_time/s', '31536000');
        $data['reason'] =  $this->request->post('reason/s', '');
        $data['op_admin'] = $this->user_data['username'];

        $res = BatchBlockServer::Block($data);
        $return = [];
        $return['code'] = $res['code'];
        $return['msg'] = $res['msg'];

//        ."，成功的有：".json_encode($res['succ'])."失败的有".json_encode($res['fail']);

        return return_json($return);



//        $this->gamelist = Common::getProductList(2);
//        $this->view->setVar('gamelist', $this->gamelist);
    }



    public function crateSign($type,$action_type,$time,$key){
        return md5($type.$action_type.$time.$key);
    }

    /**
     * 角色封禁列表
     */
    public function index()
    {

        $this->gamelist = Common::getProductList(2);

        $data['page']            = $this->request->post('page/d', 1);
        $data['limit']           = $this->request->post('limit/d',  20);
        $data['game']            = $this->request->post('game/s', 'common');
        $data['server_id']       = $this->request->post('server_id/s',  '');
        $data['role_id']         = $this->request->post('role_id/s',  '');
        $data['type']            = $this->request->post('type/s',  '');
        $data['op_admin']        = $this->request->post('op_admin/s',  '');
        $data['dateStart']       = $this->request->post('sdate/s',  date('Y-m-d 00:00:00'));
        $data['dateEnd']         = $this->request->post('edate/s', date('Y-m-d 23:59:59'));

        $list = BatchBlockServer::getList($data);
        $total = BatchBlockServer::getCount($data);

        $return = [];

        $return['code'] = 0;
        $return['msg'] = '获取成功';
        $return['data'] = $list;
        $return['count'] = $total;


        return return_json($return);



//        $this->view->setVar('gamelist', $this->gamelist);
//        $this->view->setVar('types', $this->blocktypes);
    }

}