<?php
//公共函数库

/**
 * @todo:静态资源路径
 * @param $path
 * @param bool $force_secure
 * @return string
 */
function asset_path($path, $force_secure = false)
{
	$static_file_path = config('sys.static_file_path');
	if (!empty($static_file_path)) {
		return $static_file_path . $path;
	} else {
		if ($force_secure === true) {
			return asset($path, true);
		} else {
			return asset($path);
		}
	}
}


/**
 * @todo:上传资源路径
 * @param string $path
 * @param bool $force_secure
 * @return string
 */
function attached_path($path = '', $force_secure = false)
{
	$upload_path = config('sys.upload_path');
	if (!empty($upload_path)) {
		return $upload_path . '/' . $path;
	} else {
		$path = 'attached/' . $path;
		if (file_exists($path)) {
			return asset($path, $force_secure);
		} else {
			$default_image = 'static/img/default_s.gif';
			return asset($default_image, $force_secure);
		}
	}
}

/**
 * @todo:获取请求IP
 * @return string
 */
function ip()
{
    $ip = '';
    if (getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown')) {
        $ip = getenv('HTTP_CLIENT_IP');
    } elseif (getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown')) {
        $ip = getenv('HTTP_X_FORWARDED_FOR');
    } elseif (getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown')) {
        $ip = getenv('REMOTE_ADDR');
    } elseif (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')) {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    return preg_match('/[\d\.]{7,15}/', $ip, $matches) ? $matches[0] : '';
}

/**
 * 对象转换为数组
 * @param $obj
 * @return array
 */
function obj_to_array($obj)
{
    if (!is_object($obj) && !is_array($obj)) {
        return [];
    }
    $arr = [];
    foreach ($obj as $k => $item) {
        if (is_object($item) || is_array($item)) {
            $arr[$k] = obj_to_array($item);
        } else {
            $arr[$k] = $item;
        }
    }
    return $arr;
}

/**
 * 数组转对象
 * @param $arr
 * @return object|void
 */
function array_to_obj($arr)
{
    if (gettype($arr) != 'array') {
        return false;
    };
    foreach ($arr as $k => $v) {
        if (gettype($v) == 'array' || gettype($v) == 'object')
            $arr[$k] = (object)array_to_obj($v);
    }
    return (object)$arr;
}


/**
 * 信息返回
 *
 * @param int $code 错误码 0成功，其他失败
 * @param string $msg 返回消息
 * @param array $data 返回数据
 * @return array
 */
function alert_info($code, $msg = '', $data = [])
{
	$code = (int)$code;
	$msg = trim($msg);
	return ['code' => $code, 'msg' => $msg, 'data' => $data];
}


/**
 * @todo:打印函数
 * @param string $str
 */
function P($str = '')
{
    echo "<pre>";
    print_r($str);
    echo "</pre>";
}


/**
 * @todo：CURL请求
 * @param $url
 * @param $type
 * @param $params
 * @param int $timeout
 * @param bool $is_json
 * @return array
 */
function curl_gather($url, $type, $params, $timeout = 20, $is_json = false)
{
    if (!empty($type)) {
        $type = strtoupper($type);
    }
    $ch = curl_init();
    $header = [
        'ka_core_version: 1'
    ];
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_FAILONERROR, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_DNS_CACHE_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    switch ($type) {
        case "GET" :
            if (is_array($params) && 0 < count($params)) {
                $getBodyString = "";
                foreach ($params as $k => $v) {
                    $getBodyString .= "$k=" . urlencode($v) . "&";
                }
                unset($k, $v);
                $url .= '?' . substr($getBodyString, 0, -1);
            }
            curl_setopt($ch, CURLOPT_HTTPGET, true);
            break;
        case "POST":
            if (is_array($params) && 0 < count($params)) {
                $postBodyString = "";
                $postMultipart = false;
                foreach ($params as $k => $v) {
                    if ("@" != substr($v, 0, 1)) // 判断是不是文件上传
                    {
                        $postBodyString .= "$k=" . urlencode($v) . "&";
                    } else {
                        $postMultipart = true;
                    }
                }
                unset($k, $v);
                curl_setopt($ch, CURLOPT_POST, true);
                if ($postMultipart) {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
                } else {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, substr($postBodyString, 0, -1));
                }
            } elseif (is_string($params)) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
            } elseif (count($params == 0)) {
                curl_setopt($ch, CURLOPT_POST, true);
            }
            break;
        case "PUT" :
            if (is_array($params) && 0 < count($params)) {
                $postBodyString = "";
                $postMultipart = false;
                foreach ($params as $k => $v) {
                    if ("@" != substr($v, 0, 1)) // 判断是不是文件上传
                    {
                        $postBodyString .= "$k=" . urlencode($v) . "&";
                    } else {
                        $postMultipart = true;
                    }
                }
                unset($k, $v);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
                if ($postMultipart) {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
                } else {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, substr($postBodyString, 0, -1));
                }
            } elseif (is_string($params)) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
            } elseif (count($params == 0)) {
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
            }
            break;
        case "DELETE":
            if (is_array($params) && 0 < count($params)) {
                $postBodyString = "";
                foreach ($params as $k => $v) {
                    $postBodyString .= "$k=" . urlencode($v) . "&";
                }
                unset($k, $v);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
                curl_setopt($ch, CURLOPT_POSTFIELDS, substr($postBodyString, 0, -1));
            } elseif (is_string($params)) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
            } elseif (count($params == 0)) {
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
            }
            break;
        default:
            break;
    }
    if ($is_json) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Content-Length: ' . strlen($params)]
        );
    }
    curl_setopt($ch, CURLOPT_URL, $url);
    $response = curl_exec($ch);
    $response_time = curl_getinfo($ch, CURLINFO_TOTAL_TIME);
    $curl_errno = curl_errno($ch);
    if ($curl_errno) {
        curl_close($ch);
        return alert_info(1, 'CURL操作失败，错误号：' . $curl_errno, ['response_time' => $response_time]);
    }
    $http_status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if (200 !== $http_status_code) {
        curl_close($ch);
        return alert_info(1, '接口请求失败，HTTP代码：' . $http_status_code, ['response_time' => $response_time]);
    }
    curl_close($ch);
    return alert_info(0, 'OK', ['response_time' => $response_time, 'response' => $response]);
}


/**
 * @todo：对url的中文内容进行urlencode
 * @param $url
 * @return mixed
 */
function urlencode_ch($url)
{
    $pregstr = "/[\x{4e00}-\x{9fa5}]+/u";//中文正则
    if (preg_match_all($pregstr, $url, $matchArray)) {
        foreach ($matchArray[0] as $key => $val) {
            $url = str_replace($val, urlencode($val), $url);//将转译替换中文
        }
        if (strpos($url, ' ')) {//若存在空格
            $url = str_replace(' ', '%20', $url);
        }
    }
    return $url;
}