  <?php
   use think\Env;
   return [
       'keyword_key'=>
           [
               'block_keyword_key'     => 'block_keyword_set', #关键词单集合
               'common_keyword_forbid' => 'common_keyword_set', #公共关键词集合
               'block_keyword_forbid'  => 'block_keyword_forbid', #封禁前缀
               'merge_chat_keyword'    => 'merge_chat_keyword' ,#上下文集合
               'block_resemble_key'    => 'block_resemble_key', #谐音关键词集合
               'white_keyword_key'     => 'white_keyword_key', #白名单集合
           ]
   ];
