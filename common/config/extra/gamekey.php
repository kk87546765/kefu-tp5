  <?php
   # key 名一定要跟后台的CP对得上，如一刀传奇CP是王者传奇， 就填 wzcq ，type=1是模式一（sdk封禁+踢下线） type=2是模式二（sdk封禁+cp封禁）
   # need_change_uid用于判断cp传过来的uid是否是聚合平台的id，是的话为1否则为0
   # is_direct_game 是否直接对接平台，1直接 :0先对接阿斯加德
   # role_block_url 角色封禁地址
   # batch_block 是否支持角色封禁
   # need_cp_deal 是否对接cp的文档，走cp的统一接口，内容写在游戏类中
   use think\Env;
   return [
       'gamekey'=>
           [
               'jzxjz' =>
                   [
                       'id'=> 1,
                       'name'=> '九州仙剑传',
                       'type'=> 1,
                       'key'=> "xw1svfhll6gufdpjfoshx2yyz3f8253o",
                       'block_url'=> "",
                       'ip_url'=> "",
                       'chat_url'=> "https://nknedd.51pbnet.com/jthy/api/sdk/game.php/lltalk/playerShutUp",  #目前在使用的禁言接口
                       'loginout_url'=> "https://nknedd.51pbnet.com/jthy/api/sdk/game.php/lltalk/playerOut", #目前在使用的踢下线接口
                       'role_block_url'=> "",
                       'need_change_uid'=> 0,
                       'is_direct_game'=>1,
                       'batch_block'=>0
                   ],

               'mori' =>
                   [
                       'id'=> 2,
                       'name'=> '极限逃亡',
                       'type'=> 2,
                       'key'=> "htu7oijlrljpirr7ahcvqln18fotqo0r",
                       'block_url'=> "https://api-mrjqtwo.sh9130.com/?method=Ban.liulian_ban_role",
                       'ip_url'=> "",
                       'chat_url'=> "https://api-mrjqtwo.sh9130.com/?method=Ban.liulian_chat_control",  #目前在使用的禁言接口
                       'loginout_url'=> "https://api-mrjqtwo.sh9130.com/?method=Ban.liulian_kick_off", #目前在使用的踢下线接口
                       'role_block_url'=> "",
                       'need_change_uid'=> 0,
                       'is_direct_game' => 0,
                       'batch_block'=>0
                       ],

               'cyd' =>
                   [
                       'id'=> 3,
                       'name'=> '苍月岛',
                       'type'=> 1,
                       'key'=> "f8830330f3b627ee708e7822a7a5a98d",
                       'block_url'=> "https://data-jzcyd.9377.cn/index/api/ban_account",
                       'ban_url'=> "https://data-jzcyd.9377.cn/index/api/ban_account",
                       'ip_url'=> "https://data-jzcyd.9377.cn/index/api/ban_ip",
                       'chat_url'=> "https://data-jzcyd.9377.cn/index/api/gag",  #目前在使用的禁言接口
                       'loginout_url'=> "https://data-jzcyd.9377.cn/index/api/kick", #目前在使用的踢下线接口
                       'role_block_url'=> "",
                       'need_change_uid'=> 0,
                       'is_direct_game' => 0,
                       'batch_block'=>0
                       ],

               'qingyun' =>
                   [
                       'id'=> 4,
                       'name'=> '青云诀',
                       'type'=> 1,
                       'key'=> "JpSuuCM0p2QyWvtn8ZdilIJ3mAK37Kit",
                       'block_url'=> "https://qyj2platformapi.yyxxgame.com/youyu/game_lock_by_username",
                       'ip_url'=> "",
                       'chat_url'=> "https://qyj2platformapi.yyxxgame.com/youyu/game_ban_chat",  #目前在使用的禁言接口
                       'loginout_url'=> "https://qyj2platformapi.yyxxgame.com/youyu/game_force_offline", #目前在使用的踢下线接口
                       'role_block_url'=> "",
                       'need_change_uid'=> 0,
                       'is_direct_game' => 0,
                       'batch_block'=>0
                   ],


               '555' =>
                   [
                       'id'=> 5,
                       'name'=> '555',
                       'type'=> 2,
                       'key'=> "87214810fa97f84f5fb94cfd1084ac6c",
                       'block_url'=> "https://lcwslogpy.guyuncq.com/youyu/api?m=Player&fn=banUser",
                       'ip_url'=> "",
                       'chat_url'=> "https://lcwslogpy.guyuncq.com/youyu/api?m=Player&fn=banChat",  #目前在使用的禁言接口
                       'loginout_url'=> "https://lcwslogpy.guyuncq.com/youyu/api?m=Player&fn=banUser", #type=2时，该地址为封禁地址
                       'role_block_url'=> "",
                       'need_change_uid'=> 0,
                       'is_direct_game' => 0,
                       'batch_block'=>0
                   ],


               'shenqi' =>
                   [
                       'id'=> 6,
                       'name'=> '神器',
                       'type'=> 2,
                       'key'=> "QnagbQ9XO5sygh4QrY4TrhMIgE0yyWqj",
                       'block_url'=> "http://ht.srtt.nctian.com/extapi?action=sqbanUser",
                       'ip_url'=> "",
                       'chat_url'=> "http://ht.srtt.nctian.com/extapi?action=sqbanChat",  #目前在使用的禁言接口
                       'loginout_url'=> "http://ht.srtt.nctian.com/extapi?action=sqbanUser", #type=2时，该地址为封禁地址
                       'server_list'=> "http://ht.srtt.nctian.com/extapi?action=sqServerList", #区服列表
                       'role_block_url'=> "",
                       'need_change_uid'=> 0,
                       'is_direct_game'=>1,
                       'batch_block'=>0
                   ],


               'bxcq' =>
                   [
                       'id'=> 7,
                       'name'=> '冰雪传奇',
                       'type'=> 2,
                       'key'=> "9Au9NGWVm1VyOqSIxeMNzc3t4KaldYRk",
                       'block_url'=> "http://ht.cylc.app.d2ty.com/extapi?action=zwbanUser",
                       'ip_url'=> "http://ht.cylc.app.d2ty.com/extapi?action=zwbanIp",
                       'chat_url'=> "http://ht.cylc.app.d2ty.com/extapi?action=zwbanChat",  #目前在使用的禁言接口
                       'loginout_url'=> "http://ht.cylc.app.d2ty.com/extapi?action=zwbanUser", #type=2时，该地址为封禁地址
                       'role_block_url'=> "",
                       'need_change_uid'=> 0,
                       'is_direct_game'=>1,
                       'batch_block'=>0
                   ],


               'mxw' =>
                   [
                       'id'=> 8,
                       'name'=> '冒险王',
                       'type'=> 2,
                       'key'=> "gLswvWS3ppYXEu5tbBo4dn6bVePuIdAs",
                       'block_url'=> "http://ht.cylc.app.d2ty.com/extapi?action=zwbanUser",
                       'ip_url'=> "http://ht.cylc.app.d2ty.com/extapi?action=zwbanIp",
                       'chat_url'=> "http://ht.cylc.app.d2ty.com/extapi?action=zwbanChat",  #目前在使用的禁言接口
                       'loginout_url'=> "http://ht.cylc.app.d2ty.com/extapi?action=zwbanUser", #type=2时，该地址为封禁地址
                       'role_block_url'=> "",
                       'need_change_uid'=> 0,
                       'is_direct_game' => 0,
                       'batch_block'=>0
                   ],


               'sxj' =>
                   [
                       'id'=> 9,
                       'name'=> '幻灵修仙传(神仙劫)',
                       'type'=> 2,
                       'key'=> "07oYgI5g1wuHQQvSEBmOPsC0bkbHu4Gu",
                       'block_url'=> "https://sdk.tianzongyouxi.com/v1/sdk/ext/youyu/block/8/7080",
                       'ip_url'=> "",
                       'chat_url'=> "https://sdk.tianzongyouxi.com/v1/sdk/ext/youyu/ban/8/7080",  #目前在使用的禁言接口
                       'loginout_url'=> "https://sdk.tianzongyouxi.com/v1/sdk/ext/youyu/block/8/7080", #type=2时，该地址为封禁地址
                       'role_block_url'=> "",
                       'need_change_uid'=> 0,
                       'is_direct_game' => 0,
                       'batch_block'=>0
                       ],


               'cs' =>
                   [
                       'id'=> 10,
                       'name'=> '传世',
                       'type'=> 2,
                       'key'=> "j5pVn8YorxMLorOEDst8z3R0gIChSKzI",
                       'block_url'=> "http://pay.fgcs.jinzewl.com:9897/tw/ban/user/zw",
                       'ip_url'=> "http://pay.fgcs.jinzewl.com:9897/tw/ban/ip/zw",
                       'chat_url'=> "http://pay.fgcs.jinzewl.com:9897/tw/ban/chat/zw",
                       'loginout_url'=> "http://pay.fgcs.jinzewl.com:9897/tw/ban/off/zw",
                       'role_block_url'=> "",
                       'need_change_uid'=> 1,
                       'is_direct_game'=>1,
                       'batch_block'=>0
                   ],


               'y9cq' =>
                   [
                       'id'=> 11,
                       'name'=> 'y9传奇',
                       'type'=> 2,
                       'key'=> "v7iumyXmOjYc1yXML0xDLGFkIU4INTvy",
                       'block_url'=> "http://pay.cqly.app.9125flying.com:9897/ye/ban/user",
                       'ip_url'=> "http://pay.cqly.app.9125flying.com:9897/ye/ban/ip",
                       'chat_url'=> "http://pay.cqly.app.9125flying.com:9897/ye/ban/chat",  #目前在使用的禁言接口
                       'loginout_url'=> "http://pay.cqly.app.9125flying.com:9897/ye/ban/off",
                       'role_block_url'=> "http://pay.cqly.app.9125flying.com:9897/ye/ban/role", #主动角色封禁，用于手动封禁角色'
                       'need_change_uid'=> 0,
                       'is_direct_game'=>1,
                       'batch_block'=>1,
                       'sell_list'=>'ht.cqly.app.9125flying.com/extapi?action=get_logstall',
                       'insider_list'=>'ht.cqly.app.9125flying.com/extapi?action=own_resources'

                   ],


               '555fl' =>
                   [
                       'id'=> 12,
                       'name'=> '555返利版',
                       'type'=> 2,
                       'key'=> "XXi947Yyyw6cAGQxbeW3JhxpbyzCNMSO",
                       'block_url'=> "https://lcwslogpy.guyuncq.com/zhangwxfb/api?m=Player&fn=banUser",
                       'ip_url'=> "",
                       'chat_url'=> "https://lcwslogpy.guyuncq.com/zhangwxfb/api?m=Player&fn=banCh",
                       'loginout_url'=> "https://lcwslogpy.guyuncq.com/zhangwxfb/api?m=Player&fn=banUser",
                       'role_block_url'=> "",
                       'need_change_uid'=> 0,
                       'is_direct_game' => 0,
                       'batch_block'=>0
                   ],


               'shenqiios' =>
                   [
                       'id'=> 13,
                       'name'=> '神器ios版',
                       'type'=> 2,
                       'key'=> "Ky2ed8xzrz2vJ6I42TGjhpaN5C6aRFD0",
                       'block_url'=> "http://ht.srtt.nctian.com/extapi?action=sqbanUser",
                       'ip_url'=> "",
                       'chat_url'=> "http://ht.srtt.nctian.com/extapi?action=sqbanChat",
                       'loginout_url'=> "http://ht.srtt.nctian.com/extapi?action=sqbanUser",
                       'role_block_url'=> "",
                       'need_change_uid'=> 0,
                       'is_direct_game'=>1,
                       'batch_block'=>0
                   ],


               'lmzh' =>
                   [
                       'id'=> 14,
                       'name'=> '黎明召唤',
                       'type'=> 2,
                       'key'=> "ZOoBRqcBMTMbOO6l7qFgP567zWNVYoKo",
                       'block_url'=> "https://api-ttzg2.sh9130.com/?method=ban.youyu_ban_account",
                       'ip_url'=> "",
                       'chat_url'=> "https://api-ttzg2.sh9130.com/?method=ban.youyu_ban_chat",
                       'loginout_url'=> "https://api-ttzg2.sh9130.com/?method=ban.youyu_offline",
                       'role_block_url'=> "",
                       'need_change_uid'=> 0,
                       'is_direct_game' => 0,
                       'batch_block'=>0
               ],


               'hjgz' =>
                   [
                       'id'=> 15,
                       'name'=> '幻境公主',
                       'type'=> 2,
                       'key'=> "vdo7srxaGpTEjrpZtl1guT0LAmRUaW4b",
                       'block_url'=> "",
                       'ip_url'=> "",
                       'chat_url'=> "",
                       'loginout_url'=> "",
                       'role_block_url'=> "",
                       'need_change_uid'=> 0,
                       'is_direct_game' => 0,
                       'batch_block'=>0
                   ],


               '555fl-ll' =>
                   [
                       'id'=> 16,
                       'name'=> '555返利版-游娱',
                       'type'=> 2,
                       'key'=> "l0UodrBu0EWYXDwpPWOAJxyAsVulpJGY",
                       'block_url'=> "https://lcwslogpy.guyuncq.com/youyuxfb/api?m=Player&fn=banUser",
                       'ip_url'=> "",
                       'chat_url'=> "https://lcwslogpy.guyuncq.com/youyuxfb/api?m=Player&fn=banChat",
                       'loginout_url'=> "",
                       'role_block_url'=> "",
                       'need_change_uid'=> 0,
                       'is_direct_game' => 0,
                       'batch_block'=>0
                   ],


               'rxhj' =>
                   [
                       'id'=> 17,
                       'name'=> '热血合击',
                       'type'=> 2,
                       'key'=> "1z2OWORtIogSkOAWCmiKR145Vllxb1O5",
                       'block_url'=> "http://ht.yshj.lianhuke.com/extapi?action=zi4banUser",
                       'ip_url'=> "http://ht.yshj.lianhuke.com/extapi?action=zi4banIp",
                       'chat_url'=> "http://ht.yshj.lianhuke.com/extapi?action=zi4banChat",
                       'loginout_url'=> "http://ht.yshj.lianhuke.com/extapi?action=zi4banChat",
                       'role_block_url'=> "",
                       'need_change_uid'=> 1,
                       'is_direct_game'=>1,
                       'batch_block'=>0
                   ],


               'y8cl' =>
                   [
                       'id'=> 18,
                       'name'=> 'y8苍龙',
                       'type'=> 2,
                       'key'=> "4jtPU96Ug68voB6trr9QUanzcduMN8SB",
                       'block_url'=> "http://api-clcq.hnzwwlw.com/yoy/game/ban",
                       'ip_url'=> "",
                       'chat_url'=> "http://api-clcq.hnzwwlw.com/yoy/game/unspeakable",
                       'loginout_url'=> "http://api-clcq.hnzwwlw.com/yoy/game/offline",
                       'role_block_url'=> "",
                       'need_change_uid'=> 0,
                       'is_direct_game'=>1,
                       'batch_block'=>0
                   ],

               'csios' =>
                   [
                       'id'=> 19,
                       'name'=> '传世ios',
                       'type'=> 2,
                       'key'=> "k40b5ku27TZG6qe7m38xels0prgtLIie",
                       'block_url'=> "http://pay.fgcs.jinzewl.com:9897/ye/ban/user/ios",
                       'ip_url'=> "http://pay.fgcs.jinzewl.com:9897/ye/ban/ip/ios",
                       'chat_url'=> "http://pay.fgcs.jinzewl.com:9897/ye/ban/chat/ios",
                       'loginout_url'=> "http://pay.fgcs.jinzewl.com:9897/ye/ban/off/ios",
                       'role_block_url'=> "",
                       'need_change_uid'=> 1,
                       'is_direct_game'=>1,
                       'batch_block'=>0
                   ],

               'tjqy' =>
                   [
                       'id'=> 20,
                       'name'=> '天剑奇缘',
                       'type'=> 2,
                       'key'=> "u75KCfQJ1sQzYTIxVBwewRM1MMwGDVa0",
                       'block_url'=> "https://api-tjqy.shzbkj.com/?method=platform.Youyu.ban_role.platforms",
                       'ip_url'=> "",
                       'chat_url'=> "https://api-tjqy.shzbkj.com/?method=platform.Youyu.ban_chat.platforms",
                       'loginout_url'=> "",
                       'role_block_url'=> "",
                       'need_change_uid'=> 0,
                       'is_direct_game'=>1,
                       'batch_block'=>0
                   ],


               'y9cqjh' =>
                   [
                       'id'=> 21,
                       'name'=> 'y9传奇-聚合',
                       'type'=> 2,
                       'key'=> "v7iumyXmOjYc1yXML0xDLGFkIU4INTvy",
                       'block_url'=> "http://pay.cqly.app.9125flying.com:9897/ye/ban/user",
                       'ip_url'=> "http://pay.cqly.app.9125flying.com:9897/ye/ban/ip",
                       'chat_url'=> "http://pay.cqly.app.9125flying.com:9897/ye/ban/chat",  #目前在使用的禁言接口
                       'loginout_url'=> "http://pay.cqly.app.9125flying.com:9897/ye/ban/off",
                       'role_block_url'=> "http://pay.cqly.app.9125flying.com:9897/ye/ban/role", #主动角色封禁，用于手动封禁角色'
                       'need_change_uid'=> 0,
                       'is_direct_game'=>1,
                       'batch_block'=>1,
                   ],

               'slsm' =>
                   [
                       'id'=> 22,
                       'name'=> '狩猎使命',
                       'type'=> 2,
                       'key'=> "bRtoKiZBM0Df6J9CXnVTbjBSNNQ14fa1",
                       'block_url'=> "",
                       'ip_url'=> "",
                       'chat_url'=> "",
                       'loginout_url'=> "",
                       'role_block_url'=> "",
                       'need_change_uid'=> 0,
                       'is_direct_game'=>0,
                       'batch_block'=>0
                   ],

               'y9cqios' =>
                   [
                       'id'=> 23,
                       'name'=> 'y9传奇IOS',
                       'type'=> 2,
                       'key'=> "v7iumyXmOjYc1yXML0xDLGFkIU4INTvy",
                       'block_url'=> "http://pay.cqly.app.9125flying.com:9897/ye/ban/user",
                       'ip_url'=> "http://pay.cqly.app.9125flying.com:9897/ye/ban/ip",
                       'chat_url'=> "http://pay.cqly.app.9125flying.com:9897/ye/ban/chat",
                       'loginout_url'=> "http://pay.cqly.app.9125flying.com:9897/ye/ban/off",
                       'role_block_url'=> "http://pay.cqly.app.9125flying.com:9897/ye/ban/role",
                       'need_change_uid'=> 0,
                       'is_direct_game'=>1,
                       'batch_block'=>1
                   ],

               'wyxx' =>
                   [
                       'id'=> 24,
                       'name'=> '我要修仙',
                       'type'=> 2,
                       'key'=> "zkPPk13diLLim08zP2cQQTs12Ql3Vvvb",
                       'block_url'=> "https://isp.hhycdk.com/toSn/extend/forbidTalk_gr6_uid/107/h0091/frx_gr_6",
                       'ip_url'=> "https://isp.hhycdk.com/toSn/extend/forbidTalk_gr6_ip/107/h0091/frx_gr_6",
                       'chat_url'=> "https://isp.hhycdk.com/toSn/extend/forbidTalk_gr6/107/h0091/frx_gr_6",
                       'loginout_url'=> "https://isp.hhycdk.com/toSn/extend/forbidTalk_gr6_uid/107/h0091/frx_gr_6",
//                       'role_block_url'=> "https://isp-dev.hhycdk.com/toSn/extend/forbidTalk_gr6_role/107/h0091/frx_gr_6",//测试服
                       'role_block_url'=> "https://isp.hhycdk.com/toSn/extend/forbidTalk_gr6_role/107/h0091/frx_gr_6",

                       'need_change_uid'=> 1,
                       'is_direct_game'=>1,
                       'batch_block'=>1
                   ],

               'xycq' =>
                   [
                       'id'=> 25,
                       'name'=> '西游传奇',
                       'type'=> 2,
                       'key'=> "FX6pArxtZVhe1elBjXLr92bjCAM4Jvec",
                       'block_url'=> "https://ht-api.hlxy.db9x.com/oper/zwband.php?queryAction=banUser",
                       'ip_url'=> "https://ht-api.hlxy.db9x.com/oper/zwband.php?queryAction=banIp",
                       'chat_url'=> "https://ht-api.hlxy.db9x.com/oper/zwband.php?queryAction=banChat",
                       'loginout_url'=> "https://ht-api.hlxy.db9x.com/oper/zwband.php?queryAction=banUser",
                       'role_block_url'=> "",
                       'server_list'=> "https://ht-api.hlxy.db9x.com/oper/zwband.php?queryAction=serverList",
                       'need_change_uid'=> 0,
                       'is_direct_game'=>1,
                       'batch_block'=>0
                   ],

               'qj' =>
                   [
                       'id'=> 26,
                       'name'=> '奇迹',
                       'type'=> 2,
                       'key'=> "2xAWaEingwY07zNEOrBdQF0dxlQ6xiQY",
                       'block_url'=> "https://fa.xy.com/fasdk/banuser/tanwan/14/1",
                       'ip_url'=> "",
                       'chat_url'=> "https://fa.xy.com/fasdk/banuser/tanwan/14/3",
                       'loginout_url'=> "",
                       'role_block_url'=> "https://fa.xy.com/fasdk/banuser/tanwan/14/1",
                       'server_list'=> "",
                       'need_change_uid'=> 1,
                       'is_direct_game'=>1,
                       'batch_block'=>1
                   ],

               'yscqios' =>
                   [
                       'id'=> 27,
                       'name'=> '原始传奇ios',
                       'type'=> 2,
                       'key'=> "YM0uDyIQ6OGRmsbmb92JP0xj6RVlDQDv",
                       'block_url'=> "http://ht.yssy.baimaniu.com/extapi?action=sqbanUser",
                       'ip_url'=> "",
                       'chat_url'=> "http://ht.yssy.baimaniu.com//extapi?action=sqbanChat",
                       'loginout_url'=> "",
                       'role_block_url'=> "",
                       'server_list'=> "",
                       'need_change_uid'=> 1,
                       'is_direct_game'=>1,
                       'batch_block'=>0
                   ],

               'jzezf' =>
                   [
                       'id'=> 28,
                       'name'=> '九州e专服',
                       'type'=> 2,
                       'key'=> "EbMRjB62wmC7Tlt1vNefcXieQBHOa8Rd",
                       'block_url'=> "https://nknedd.51pbnet.com/jthy/api/sdk/game.php/llE/playerBan",
                       'ip_url'=> "https://nknedd.51pbnet.com/jthy/api/sdk/game.php/llE/playerIpBan",
                       'chat_url'=> "https://nknedd.51pbnet.com/jthy/api/sdk/game.php/llE/playerShutUp",
                       'loginout_url'=> "https://nknedd.51pbnet.com/jthy/api/sdk/game.php/llE/roleOutLine",
                       'role_block_url'=> "https://nknedd.51pbnet.com/jthy/api/sdk/game.php/llE/playerBan",
                       'server_list'=> "https://nknedd.51pbnet.com/jthy/api/sdk/game.php/llE/getServerListInfo",
                       'need_change_uid'=> 1,
                       'is_direct_game'=>1,
                       'batch_block'=>1
                   ],

               'nbcq' =>
                   [
                       'id'=> 29,
                       'name'=> '牛逼传奇',
                       'type'=> 2,
                       'key'=> "ExEQePlw2cvCeBeWpXzeN55dr7tMTJqV",
                       'block_url'=> "",
                       'ip_url'=> "http://ht.xwcq.douquy.com/extapi?action=BanLoginApi&distribute=p93D6qlNQNH0iRIA4FqGBQ==",
                       'chat_url'=> "http://ht.xwcq.douquy.com/extapi?action=BanLoginApi&distribute=6UwKBL9+WpMYzfxROD0x+Q==",
                       'loginout_url'=> "",
                       'role_block_url'=> "http://ht.xwcq.douquy.com/extapi?action=BanLoginApi&distribute=TpVmLnAE4994IxLV7Eqr3w==",
                       'server_list'=> "",
                       'need_change_uid'=> 1,
                       'is_direct_game'=>1,
                       'batch_block'=>1
                   ],

               'yscqyy' =>
                   [
                       'id'=> 30,
                       'name'=> '原始传奇-游娱',
                       'type'=> 2,
                       'key'=> "ysjsnjlXSgRlHgHlwLyzQ0KqSjSFEBze",
                       'block_url'=> "http://ht.yssy.baimaniu.com/extapi?action=sqbanUser",
                       'ip_url'=> "",
                       'chat_url'=> "http://ht.yssy.baimaniu.com//extapi?action=sqbanChat",
                       'loginout_url'=> "",
                       'role_block_url'=> "",
                       'server_list'=> "",
                       'need_change_uid'=> 1,
                       'is_direct_game'=>1,
                       'batch_block'=>0
                   ],
               'dxcq' =>
                   [
                       'id'=> 31,
                       'name'=> '大侠传奇',
                       'type'=> 2,
                       'key'=> "aynFd4NFnBRotvZDUjmZ3ykkw5Vqiu1a",
                       'block_url'=> "http://ht.fyws.itunesapplestore.net/channel/mlbanuser",
                       'ip_url'=> "",
                       'chat_url'=> "http://ht.fyws.itunesapplestore.net/channel/mlbanchat",
                       'loginout_url'=> "",
                       'role_block_url'=> "http://ht.fyws.itunesapplestore.net/channel/Mlbanjuese",
                       'server_list'=> "",
                       'need_change_uid'=> 1,
                       'is_direct_game'=>1,
                       'batch_block'=>1
                   ],

               '555xyx' =>
                   [
                       'id'=> 32,
                       'name'=> '555小游戏',
                       'type'=> 2,
                       'key'=> "OM5UqpzfDcUZ5YJ2FFDELvOl6EaZZxXF",
                       'block_url'=> "https://lcwslogpy.guyuncq.com/zwxfbwx/api?m=Player&fn=banUser",
                       'ip_url'=> "",
                       'chat_url'=> "https://lcwslogpy.guyuncq.com/zwxfbwx/api?m=Player&fn=banChat",
                       'loginout_url'=> "",
                       'role_block_url'=> "https://lcwslogpy.guyuncq.com/zwxfbwx/api?m=Player&fn=banUser",
                       'server_list'=> "",
                       'need_change_uid'=> 0,
                       'is_direct_game'=>1,
                       'batch_block'=>1
                   ],

               'xycqyy' =>
                   [
                       'id'=> 33,
                       'name'=> '西游传奇-游娱',
                       'type'=> 2,
                       'key'=> "FX6pArxtZVhe1elBjXLr92bjCAM4Jvec",
                       'block_url'=> "https://ht-api.hlxy.db9x.com/oper/llinfo.php?queryAction=banUser",
                       'ip_url'=> "https://ht-api.hlxy.db9x.com/oper/llinfo.php?queryAction=banIp",
                       'chat_url'=> "https://ht-api.hlxy.db9x.com/oper/llinfo.php?queryAction=banChat",
                       'loginout_url'=> "",
                       'role_block_url'=> "https://ht-api.hlxy.db9x.com/oper/llinfo.php?queryAction=banRole",
                       'server_list'=> "https://ht-api.hlxy.db9x.com/oper/zwband.php?queryAction=serverList",
                       'need_change_uid'=> 0,
                       'is_direct_game'=>1,
                       'batch_block'=>0
                   ],

               'jmxy' =>
                   [
                       'id'=> 33,
                       'name'=> '九梦仙域',
                       'type'=> 2,
                       'key'=> "wLovREBpwLBa3MP7MSfKk2PPnBJaLZP3",
                       'block_url'=> "http://cl3.uc01.huanyuantech.com/agy/lock_user.php",
                       'ip_url'=> "https://ht-api.hlxy.db9x.com/oper/llinfo.php?queryAction=banIp",
                       'chat_url'=> "http://cl3.uc01.huanyuantech.com/agy/mute_role.php",
                       'loginout_url'=> "",
                       'role_block_url'=> "http://cl3.uc01.huanyuantech.com/agy/lock_role.php",
                       'server_list'=> "",
                       'need_change_uid'=> 1,
                       'is_direct_game'=>1,
                       'batch_block'=>1
                   ],
               'ts' =>
                   [
                       'id'=> 34,
                       'name'=> '通神',
                       'type'=> 2,
                       'key'=> "Uf3gRj8y3yI9hp77VTxJpKgjWfCRIRge",
                       'block_url'=> "https://tongshenht.xinshenghudong.com/index/ban-account",
                       'ip_url'=> "",
                       'chat_url'=> "https://tongshenht.xinshenghudong.com/index/shutup",
                       'loginout_url'=> "",
                       'role_block_url'=> "https://tongshenht.xinshenghudong.com/index/ban-role",
                       'server_list'=> "",
                       'need_change_uid'=> 0,
                       'is_direct_game'=>1,
                       'batch_block'=>1
                   ],
               'nbcqios' =>
                   [
                       'id'=> 35,
                       'name'=> '牛逼传奇IOS',
                       'type'=> 2,
                       'key'=> "kneOHVMTo65DLd2Zb2MPtoTHopsD9HVZ",
                       'block_url'=> "http://ht.xwcq.douquy.com/extapi?action=BanLoginApi&distribute=+3hr2AevAL8AyQw7QhheTQ==",
                       'ip_url'=> "http://ht.xwcq.douquy.com/extapi?action=BanLoginApi&distribute=pDsnvUpWl2N96QxbChhuiA==",
                       'chat_url'=> "http://ht.xwcq.douquy.com/extapi?action=BanLoginApi&distribute=FBwlXsJvnbv6qKELCkpffA==",
                       'loginout_url'=> "",
                       'role_block_url'=> "http://ht.xwcq.douquy.com/extapi?action=BanLoginApi&distribute=+3hr2AevAL8AyQw7QhheTQ==",
                       'server_list'=> "",
                       'need_change_uid'=> 0,
                       'is_direct_game'=>1,
                       'batch_block'=>1
                   ],

               'nbcq2zw' =>
                   [
                       'id'=> 36,
                       'name'=> '牛逼传奇2-掌玩',
                       'type'=> 2,
                       'key'=> "Py3DEFFW1EhaZNSXLln5UhxAtDSc2bdZ",
                       'block_url'=> "http://ht.nbc2.douquy.com/channel/BanInterfaceApiAdzwRole",
                       'ip_url'=> "http://ht.nbc2.douquy.com/channel/BanInterfaceApiAdzwIp",
                       'chat_url'=> "http://ht.nbc2.douquy.com/channel/BanInterfaceApiAdzwChat",
                       'loginout_url'=> "",
                       'role_block_url'=> "http://ht.nbc2.douquy.com/channel/BanInterfaceApiAdzwRole",
                       'server_list'=> "",
                       'need_change_uid'=> 0,
                       'is_direct_game'=>1,
                       'batch_block'=>1
                   ],


               'nbcq2youyu' =>
                   [
                       'id'=> 37,
                       'name'=> '牛逼传奇2-游娱',
                       'type'=> 2,
                       'key'=> "gPD0Ghfskd4O4KBEdpeGo8ic0vWlZgQo",
                       'block_url'=> "http://ht.nbc2.douquy.com/channel/BanInterfaceApiAdyouyRole",
                       'ip_url'=> "http://ht.nbc2.douquy.com/channel/BanInterfaceApiAdyouyIp",
                       'chat_url'=> "http://ht.nbc2.douquy.com/channel/BanInterfaceApiAdyouyChat",
                       'loginout_url'=> "",
                       'role_block_url'=> "http://ht.nbc2.douquy.com/channel/BanInterfaceApiAdyouyRole",
                       'server_list'=> "",
                       'need_change_uid'=> 0,
                       'is_direct_game'=>1,
                       'batch_block'=>1
                   ],

               'xgz' =>
                   [
                       'id'=> 38,
                       'name'=> '新国战',
                       'type'=> 2,
                       'key'=> "FVBQNwgbLUtmDJKIp1N5t7zm35cNNBWm",
                       'block_url'=> "http://pay.lycb.hnzwaa.com/ye/ban/user",
                       'ip_url'=> "http://pay.lycb.hnzwaa.com/ye/ban/ip",
                       'chat_url'=> "http://pay.lycb.hnzwaa.com/ye/ban/chat",
                       'loginout_url'=> "http://pay.lycb.hnzwaa.com/ye/ban/off ",
                       'role_block_url'=> "http://pay.lycb.hnzwaa.com/ye/ban/role",
                       'server_list'=> "",
                       'need_change_uid'=> 1,
                       'is_direct_game'=>1,
                       'batch_block'=>1
                   ],


               'gz3' =>
                   [
                       'id'=> 39,
                       'name'=> '国战3.0',
                       'type'=> 2,
                       'key'=> "vrXCzGqqiFwK6dSlRSSc8yl6EkMWqS3U",
                       'block_url'=> "http://pay.lyhj.hnzwaa.com/ye/ban/user",
                       'ip_url'=> "http://pay.lyhj.hnzwaa.com/ye/ban/ip",
                       'chat_url'=> "http://pay.lyhj.hnzwaa.com/ye/ban/chat",
                       'loginout_url'=> "http://pay.lyhj.hnzwaa.com/ye/ban/off",
                       'role_block_url'=> "http://pay.lyhj.hnzwaa.com/ye/ban/role",
                       'server_list'=> "",
                       'need_change_uid'=> 0,
                       'is_direct_game'=>1,
                       'batch_block'=>1
                   ],

               'rxxcx' =>
                   [
                       'id'=> 40,
                       'name'=> '热血小程序',
                       'type'=> 2,
                       'key'=> "vrXCzGqqiFwK6dSlRSSc8yl6EkMWqS3U",
                       'block_url'=> "http://api.4399data.com/?r=banType/BanAct",
                       'ip_url'=> "",
                       'chat_url'=> "http://api.4399data.com/?r=banType/BanRoleIdChat",
                       'loginout_url'=> "",
                       'role_block_url'=> "http://api.4399data.com/?r=banType/BanRoleId",
                       'server_list'=> "",
                       'need_change_uid'=> 0,
                       'is_direct_game'=>1,
                       'batch_block'=>1,
                       'need_cp_deal'=>1
                   ],
               'bx555' =>
                   [
                       'id'=> 41,
                       'name'=> '冰雪-555',
                       'type'=> 2,
                       'key'=> "Jwex4CaryHf4MBjJdMBHS3EbSdhmN2hX",
                       'block_url'=> "https://lcwslogpy.guyuncq.com/bingxue2hf/api?m=Player&fn=banUser",
                       'ip_url'=> "",
                       'chat_url'=> "https://lcwslogpy.guyuncq.com/bingxue2hf/api?m=Player&fn=banChat",
                       'loginout_url'=> "",
                       'role_block_url'=> "https://lcwslogpy.guyuncq.com/bingxue2hf/api?m=Player&fn=banUser",
                       'server_list'=> "",
                       'need_change_uid'=> 0,
                       'is_direct_game'=>1,
                       'batch_block'=>1,

                   ],
               'dxcq2' =>
                   [
                       'id'=> 42,
                       'name'=> '大侠传奇2',
                       'type'=> 2,
                       'key'=> "rR8fG3RK8c25UT4pnetUVEwqvBFBNsRq",
                       'block_url'=> "http://ht.dxdzy.shengjuewl.net/channel/Mlbanuser",
                       'ip_url'=> "",
                       'chat_url'=> "http://ht.dxdzy.shengjuewl.net/channel/Mlbanchat",
                       'loginout_url'=> "",
                       'role_block_url'=> "http://ht.dxdzy.shengjuewl.net/channel/Mlbanjuese",
                       'server_list'=> "",
                       'need_change_uid'=> 1,
                       'is_direct_game'=>1,
                       'batch_block'=>1,

                   ],







       ]
   ];
