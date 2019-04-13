<?php
/**
 * 任务
 * @author syCai
 * @version 2019-02-20
 */
namespace App\Model\Baidu;
use App\Model\DBModel;
use App\Model\BaseModel;
class BaiduIndexModel extends DBModel
{
    use BaseModel;

    protected $table = 'seo_baidu_index';

    public function __construct()
    {
        parent::__construct();
    }
	
	/**
	 * @todo:获取关键词指数
	 * @param string $keyword
	 * @return string
	 */
	public function getIndex($keyword){
		$today = strtotime(date('Y-m-d 00:00:00'));
		return $this->getOne(['keyword'=>$keyword, 'updated_at_gt'=>$today], ['pc_index','m_index']);
	}
	
	/**
	 * @todo:更新关键词指数
	 * @param string $keyword
	 * @param int $pcIndex
	 * @param int $mIndex
	 * @return int
	 */
	public function updateIndex($keyword, $pcIndex=0, $mIndex=0){
		$exist = $this->getOne(['keyword'=>$keyword], ['id']);
		if($exist){
			return $this->update(['id'=>$exist['id']], ['pc_index'=>$pcIndex, 'm_index'=>$mIndex]);
		}else{
			return $this->insert(['keyword'=>$keyword, 'pc_index'=>$pcIndex, 'm_index'=>$mIndex, 'updated_at'=>time()]);
		}
	}
	
}