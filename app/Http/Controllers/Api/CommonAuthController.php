<?php
// +----------------------------------------------------------------------
// | 接口采用RESTful风格架构，请按RESTful风格书写接口文档
// +----------------------------------------------------------------------
// | 请求方式：GET、POST、PUT、DELETE、PATCH
// +----------------------------------------------------------------------
// | Code码：RESTful code码
// +----------------------------------------------------------------------

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;

class CommonAuthController extends Controller
{

    public function __construct()
    {

    }


    /**
     * 转utf-8
     * @param $data
     * @return mixed|string
     */
    function characet($data)
    {
        if (!empty($data)) {
            $fileType = mb_detect_encoding($data, array('UTF-8', 'GBK', 'LATIN1', 'BIG5'));
            if ($fileType != 'UTF-8') {
                $data = mb_convert_encoding($data, 'utf-8', $fileType);
            }
        }
        return $data;
    }

    /**
     * 信息输出
     *
     * @param int $code 错误码
     * @param string $msg
     * @param array $data
     */
    public function jsonAlert($code, $msg = '', $data = [])
    {
        $code = (int)$code;
        $msg = trim($msg);
        if(is_object($data)){
            $data = obj_to_array($data);
        }
        $json_data = ['code' => $code, 'msg' => $msg, 'data' => $data];
        echo json_encode($json_data);
        die;
    }

}