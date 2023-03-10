<?php


namespace common\server\Game;


use common\base\BasicServer;
use common\libraries\{Logger,Curl,Common};


class ChangeCpUidServer extends BasicServer
{
    const GAME_SIGN_PATH = EXTEND_PATH."/GamekeySign";

    public static function change($data)
    {

        if(empty($data['product']) || empty($data['in_uid'])){
            return false;
        }

        include_once( self::GAME_SIGN_PATH."/{$data['product']}.php");

        if(class_exists($data['product'])){

            $game_model = new $data['product'];

            $id = '';
            //cpid换平台id
            if($data['type'] == 1){

                if(method_exists($game_model,'sdkid_to_uid_url')){

                    $id = $game_model->sdkid_to_uid_url($data['in_uid']);

                    if(method_exists($game_model,'filterUid')){

                        $id = $game_model->filterUid($id);

                    }
                }
            }else{

                //平台id转cpid

                if(method_exists($game_model,'uid_to_sdkid_url')){

                    $id = $game_model->uid_to_sdkid_url($data['in_uid']);

                    if(method_exists($game_model,'filterUid')){

                        $id = $game_model->filterUid($id);

                    }

                }

            }


        }

        return $id;
    }
}