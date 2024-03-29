<?php
namespace common\libraries;
use Elasticsearch\ClientBuilder;


final class ElasticSearch
{
    protected  $host = ['172.30.27.138:9200'];
//    private  $host = ['127.0.0.1:9200'];
    private  $client = '';

    public  $index_name = 'kefu';
//    protected  $body = [];
    private  $scroll_time = '1m';

    public function __construct($params=[])
    {
//        logger::init();
        if(isset($params['host']) && !empty($params['host'])){
            $this->host = $params['host'];
        }

        if(isset($params['host']) && !empty($params['index'])){
            $this->index_name = $params['index'];
        }else{
            $this->index_name = $this->index_name.'-'.date('Ym');
        }

        $this->client = ClientBuilder::create()->setHosts($this->host)->build();
    }


    /**
     * elasticSearch切换当前索引
     * @param $index_name string 索引名
     */
    public function changeIndex($index_name)
    {
        $this->index_name = $index_name;
        return $this;
    }



    //$params = [
    //'index' => isset($index_name) ? $index_name : self::INDEX_NAME,
    //
    //'body'=>[
    //'analyzer'=>'ik_smart',
    //'text'=>"大家来玩"
    //]
    public function analyze($body)
    {
        $params = [
            'index' => $this->index_name,
            'body'=>$body
        ];

        $a = $this->client->indices()->analyze($params);
        return $a;
    }


    /**
     * elasticSearch搜索
     * @param $index_name string 索引名
     * query=>['query_string'=>['default_field'=>查询字段],['query']=>*查询内容*] 用于包含查询
     */
    public function search($index_name_arr = [],$query = [],$field='',$order=['id'=>['order'=>'desc']],$page = 1,$limit = 20,$is_scroll = false)
    {
        $index_name_arr = !empty($index_name_arr)&&is_array($index_name_arr) ? $index_name_arr : [$this->index_name];

        foreach ($index_name_arr as $k=>$v){
            $res = $this->indexExists($v);
            if(!$res){
                unset($index_name_arr[$k]);
            }
        }

        $params = [
            'index' => $index_name_arr,
            '_source' =>$field,
            'body' => [
                'query' =>
                    $query,
                'sort'=>$order,
                'from'=>($page-1)*$limit,
                'size'=>$limit,
                'track_total_hits'=>true
            ]
        ];

        //是否返回scroll_id ,缓存1分钟
        if($is_scroll) $params['scroll'] = $this->scroll_time;

        $res = $this->client->search($params);

        $total = $res['hits']['total']['value'];
        $results = $res['hits']['hits'][0]['_source'];
        $tmp_arr = [];
        foreach($results = $res['hits']['hits'] as $k=>$v){
            array_push($tmp_arr,$v['_source']);
        }


        $tmp_params = ['total'=>$total,'data'=>$tmp_arr];
        if($is_scroll) $tmp_params['scroll_id'] = $res['_scroll_id'];

        return $tmp_params;

    }

    public function scroll($scroll_id){

        $params = [
            'scroll_id'=>$scroll_id,
            'scroll'=> $this->scroll_time
        ];
        $res = $this->client->scroll($params);

        $total = $res['hits']['total']['value'];

        $tmp_arr = [];
        foreach($results = $res['hits']['hits'] as $k=>$v){
            array_push($tmp_arr,$v['_source']);
        }

        $tmp_params = ['total'=>$total,'data'=>$tmp_arr];

        if($res['_scroll_id']) $tmp_params['scroll_id'] = $res['_scroll_id'];

        return $tmp_params;
    }

    /**
     * elasticSearch搜索单个文档

     * @param $ids int
     * @param $sort array 排序 例子 ['sort'=>['uid'=>"desc"]]
     */
    public function searchDocById($ids){
        $params = [
            'index' => $this->index_name,
            'body' => ['ids' => $ids]
        ];

        $res = $this->client->mget($params);
        return $res;

    }

    /**
     * elasticSearch创建索引
     */
    public function create(){

        $params = [
            'index' => $this->index_name
            ,'body'=>[
                'settings'=>[
                    'number_of_shards'=>5 //默认设置5个分片
                ]
            ]


        ];
        $this->client->indices()->create($params);

        $res = $this->indexExists($this->index_name);

        if($res){
            return true;
        }else{
            return false;
        }

    }

    /**
     * elasticSearch删除索引
     */
    public function delete(){

        $params = [
            'index' => $this->index_name

        ];
        $this->client->indices()->delete($params);

        $res = $this->indexExists($params);

        if($res){
            return false;
        }else{
            return true;
        }

    }


    /**
     * elasticSearch判断索引是否存在
     */
    public function indexExists($index_name='')
    {
        $params = [
            'index' => empty($index_name) ? $this->index_name : $index_name

        ];
        $res = $this->client->indices()-> exists($params);
        if($res){
            return true;
        }else{
            return false;
        }

    }


    /**
     * elasticSearch 建立字段
     * @param $field array 表名 例子 ['uid'=>1]
     */
    public function getField()
    {
        $client = ClientBuilder::create()->setHosts($this->host)->build();
        $res = $client->indices()->get(['index'=>$this->index_name]);
        return $res;
    }

    /**
     * elasticSearch 建立字段
     * @param $field array 表名 例子 ['uid'=>1]
     */
    public function buildField($field = [])
    {
//        example field
//        'content' => [
//            'type'=>'text',
//            'analyzer'=>'ik_smart',
//            'search_analyzer'=>'ik_smart',
//        ],

        $tmp_params = [
            'index' => $this->index_name,
            'body'=>[
                'properties'=>$field
            ]
        ];

        $tmp_params['body'] = [] ;
        $tmp_params['body']['properties'] = $field;
        $client = ClientBuilder::create()->setHosts($this->host)->build();

        $client->indices()->putMapping($tmp_params);

        $res = $client->indices()->get(['index'=>$this->index_name]);
        return $res;
    }

    /**
     * elasticSearch批量新增数据
     * @param $data array [['id'=>1,'uid'=>2]]
     */
    public function addDoc($data)
    {

        $tmp_params = [];
        foreach($data as $k=>$v){

            $tmp_params['body'][$k] = [
                'index' => [
                    '_index' => $this->index_name

                ]
            ];
            foreach($v as $k1=>$v1){
                $tmp_params['body'][$k+1][$k1] = $v1;
            }
        }

        $client = ClientBuilder::create()->setHosts($this->host)->build();
        $res = $client->bulk($tmp_params);

        return $res;

    }

    /**
     * elasticSearch导出数据到目标索引
     * @param $source string 索引源
     * @param $dest string 目标索引
     */
    public function reindex($source,$dest){
        $client = ClientBuilder::create()->setHosts($this->host)->build();
        $tmp_params = [];
        $tmp_params['body']['source']['index'] = $source;
        $tmp_params['body']['dest']['index'] = $dest;
        $res = $client->reindex($tmp_params);
        return $res;
    }


    public function addMapping($data)
    {
        $tmp_params = [
            'index' => $this->index_name,
            'body'=>[
                'properties'=>$data
            ]
        ];
        $res = $this->client->indices()->putMapping($tmp_params);
    }

    public function initQuery($query=[]){
//        $bool['filter']['bool']['must'][]['term']

        $filter = [];
//        $query = [
//            'must'=>[
//                'terms' => [
//                    'id'=>1,
//                    'uid'=>2
//                ],
//
//            ],
//            'should'=>[
//                'terms'=>[
//                    'id'=>3,
//                    'uid'=>4
//                ],
//            ],
//            'filter'=>[
//                'terms' => [
//                    'role_id'=>1
//                ],
//                'range'=> [
//                    'time'=>[
//                        'gte'=>3,
//                        'lte'=>4
//                        ]
//                ]
//            ]
//
//        ];

        foreach($query as $k=>$v){

            foreach($v as $k1=>$v1){
                foreach($v1 as $k2=>$v2){
                    $filter[$k][][$k1][$k2] = $v2;
                }
            }
        }


        return ['bool'=>$filter];
    }


    public function getLikeWords($field,$keywords)
    {

        $last_month = Common::GetMonth(1);
        $now_month = date('Ym');
        $next_month = Common::GetMonth(0);

        $query['query_string']['default_field'] = $field;
        $query['query_string']['query'] = "*{$keywords}*";
        $a = $this->search([
            $this->index_name.'-'.$last_month,
            $this->index_name.'-'.$now_month,
            $this->index_name.'-'.$next_month
        ],$query,[]);
        return $a;
    }







}