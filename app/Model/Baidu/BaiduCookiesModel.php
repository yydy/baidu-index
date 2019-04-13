<?php
/**
 * 任务
 * @author syCai
 * @version 2019-02-20
 */
namespace App\Model\Baidu;
use App\Model\DBModel;
use App\Model\BaseModel;
use Illuminate\Support\Facades\DB;

class BaiduCookiesModel extends DBModel
{
    use BaseModel;

    protected $table = 'seo_baidu_cookies';

    public function __construct()
    {
        parent::__construct();
    }
	
	/**
	 * @todo:获取随机一个百度cookie
	 * @return array
	 */
	public function getRndCookie(){
		$total = $this->getTotal(['type'=>4, 'status'=>0]);
		if($total == 0)
			return null;
		$rnd = mt_rand(0,$total-1);
		$items = DB::table($this->table)->where([
			['type', '=', 4],
			['status', '=', 0]
		])->offset($rnd)->limit(1)->select(DB::raw('id, cookie'))->get();
		return $items[0];
	}
	
	/**
	 * @todo:更新状态
	 * @param int $id
	 * @param int $status
	 * @return int
	 */
	public function setStatus($id, $status){
		return $this->update(['id'=>$id],['status'=>$status]);
	}
}