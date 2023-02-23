<?php
namespace app\admin\controller;


use common\base\BasicController;
class Common extends BasicController
{
    protected $no_oauth = ['a'];

    const UEDITOR =  ROOT_PATH.'common/libraries/ueditor';

    public function ueditor()
    {

        date_default_timezone_set("Asia/chongqing");
        error_reporting(E_ERROR);
        header("Content-Type: text/html; charset=utf-8");
        $CONFIG = json_decode(preg_replace("/\/\*[\s\S]+?\*\//", "", file_get_contents(self::UEDITOR."/config.json")), true);
        $action = $_GET['action'];

        switch ($action) {
            case 'config':
                $result =  json_encode($CONFIG);
                break;

            /* 上传图片 */
            case 'uploadimage':
                /* 上传涂鸦 */
            case 'uploadscrawl':
                /* 上传视频 */
            case 'uploadvideo':
                /* 上传文件 */
            case 'uploadfile':
                $result = require_once self::UEDITOR.'/action_upload.php';
                break;

            /* 列出图片 */
            case 'listimage':
                $result = require_once self::UEDITOR.'/action_list.php';
                break;
            /* 列出文件 */
            case 'listfile':
                $result = require_once self::UEDITOR.'/action_list.php';
                break;

            /* 抓取远程文件 */
            case 'catchimage':
                $result = require_once self::UEDITOR.'/action_crawler.php';
                break;

            default:
                $result = json_encode(array(
                    'state'=> '请求地址出错'
                ));
                break;
        }

        /* 输出结果 */
        if (isset($_GET["callback"])) {
            if (preg_match("/^[\w_]+$/", $_GET["callback"])) {
                echo htmlspecialchars($_GET["callback"]) . '(' . $result . ')';
            } else {
                echo json_encode(array(
                    'state'=> 'callback参数不合法'
                ));
            }
        } else {
            echo $result;
        }
    }

    //单图片上传
    public function fileUploadOne()
    {
        $allowExts = ['gif','jpg','jpeg','bmp','png','swf','zip','rar','arj','xls','xlsx','doc','docx','mp4','avi','mov','rm','rmvb'];

        $path = 'Uploads/worksheet/'.date('Ym').'/'.date('d').'/';
        $real_path = 'static/admin/'.$path;

        if (!file_exists($real_path) && !mkdir($real_path, 0777, true)) {
            $this->rs['code'] = 1;
            $this->rs['msg'] = 'ERROR_CREATE_DIR';
            return return_json($this->rs);
        } else if (!is_writeable($real_path)) {
            $this->rs['code'] = 2;
            $this->rs['msg'] = 'ERROR_DIR_NOT_WRITEABLE';
            return return_json($this->rs);
        }

        $file = $this->request->file('file');

        if (!$file) {
            $this->rs['code'] = 3;
            $this->rs['msg'] = '没有上传数文件';
            return return_json($this->rs);
        }

        $data = [];

        $this_path_info = pathinfo($file->getInfo('name'));

        if(!in_array($this_path_info['extension'],$allowExts)){
            $this->rs['code'] = 4;
            $this->rs['msg'] = '非法文件';
            return return_json($this->rs);
        }

        $new_name = time().createNonceStr(4).'.'.$this_path_info['extension'];
        // Move the file into the application
        $file->move(
            $real_path,$new_name
        );

        $data['url'] = $path.$new_name;
        $data['http_url'] = '/'.$path.$new_name;

        $this->rs['data'] = $data;

        return return_json($this->rs);
    }

}
