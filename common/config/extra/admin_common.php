<?php
return [
    'cache_bloc_node'=>'bloc_node',//缓存后台全部方法以及菜单
    'cache_bloc_model'=>'bloc_model:',//后台根据组缓存全部方法以及菜单
    'cache_group_user'=>'bloc_group_user:',//登陆模块
    'cache_role_user'=>'bloc_role_user:',//组模块缓存
    'root'=>1,//超级管理员id
    'base_menu'=>[//必要菜单
        'layui-icon-set',
        'layui-icon-senior',
        'layui-icon-friends',
        'node/menu',
        'role/index',
        'node/model',
        'node_user/index',
        'node_user/operate',
        'node_user/get_all_user',
        'node_user/edit',
        'node_user/edit_status',
        'node_user/submit',
        'node_user/grant_list',
        'node_user/grant',
        'role/submit',
        'role/edit',
        'role/grant_list',
        'role/grant',
        'role/del',
        'node/submit',
        'node/del',
        'node/menu_list',
        'node/menu_sort',
    ],
];