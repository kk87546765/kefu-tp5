<?php

namespace common\base;



class BasicServer
{
    protected static $user_data;
    protected static $role_data;
    protected static $common_data;
    protected static $mix_game;
    
    public static function setRole($data){
        self::$role_data = $data;
    }

    public static function setData($data){
        foreach ($data as $k=>$v){
            self::$$k = $v;
        }
    }

    public static function getMixGame()
    {
        self::$mix_game = getenv();
    }


}