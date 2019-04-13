<?php
/**
 * 百度指数接口
 * Author: syCai
 * Date: 2019/02/20
 * Time: 9:50
 */
namespace App\Http\Controllers\Api\V1\Baidu;
use App\Http\Controllers\Api\CommonAuthController;
use Illuminate\Http\Request;

class BaiduController extends CommonAuthController
{
    private $model = null;

    public function __construct()
    {
        parent::__construct();
    }
    
	/**
     * @todo：获取百度指数
     * @return jsonAlert
     */
    public function getIndex(Request $request){
    	$keyword = trim($request->input('keyword'));
    	$actual = $request->input('actual');
    	if(empty($keyword))
    		$this->jsonAlert(400, '参数错误');
    	
		$baiduIndexModel = new \App\Model\Baidu\BaiduIndexModel();
		//查询当天记录
		if(empty($actual)){
			$oldIndex = $baiduIndexModel->getIndex($keyword);
			if($oldIndex)
				$this->jsonAlert(200, 'ok', $oldIndex);
		}
    	
    	//接口查询
    	$baiduCookiesModel = new \App\Model\Baidu\BaiduCookiesModel();
    	$baiduCookie = $baiduCookiesModel->getRndCookie();
    	if(!$baiduCookie)
    		$this->jsonAlert(500, '没有百度cookie');
    	
    	$url = "https://index.baidu.com/api/SearchApi/index?word=" . urlencode($keyword) . "&area=0&days=30";
    	$header = [
    		'Accept'=>'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8',
    		'Accept-Language'=>'zh-CN,zh;q=0.9',
    		'User-Agent'=>'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.0; Acoo Browser; SLCC1; .NET CLR 2.0.50727; Media Center PC 5.0; .NET CLR 3.0.04506)',
    		'Cookie'=>'BDUSS='.$baiduCookie->cookie
    		];
    	try{
    		$request = \Requests::get($url, $header);
    	}catch(Requests_Exception $e){
    		$this->jsonAlert(105, '网络异常 ', $e->message());
    	}
    	
    	if($request->status_code != 200)
    		$this->jsonAlert(500, '百度接口访问异常', 'status_code:'.$request->status_code);
    	
    	$data = json_decode($request->body);
    	if($data->status == 0){
    		$allIndex = $data->data->generalRatio[0]->all->avg;
    		$mIndex = $data->data->generalRatio[0]->wise->avg;
    		$pcIndex = $allIndex - $mIndex;
    		$baiduIndexModel->updateIndex($keyword, $pcIndex, $mIndex);
    		$this->jsonAlert(200, 'ok', ['pc_index'=>$pcIndex, 'm_index'=>$mIndex]);
    		
    	}elseif($data->status == 10000){
    		$baiduCookiesModel->setStatus($baiduCookie->id, -1);
    		$this->jsonAlert(500, '百度cookie失效');
    	
    	}elseif($data->status == 10001){
    		//request block
    		$baiduCookiesModel->setStatus($baiduCookie->id, 2);
    		$this->jsonAlert(500, 'cookie访问达到上限');
    		
    	}elseif($data->status == 10002){
    		//无百度指数
    		$baiduIndexModel->updateIndex($keyword, 0, 0);
    		$this->jsonAlert(200, 'ok', ['pc_index'=>0, 'm_index'=>0]);
    		
    	}else{
    		$this->jsonAlert(500, '未处理异常', $data->status.' '.$data->message);
    	}
    }
}