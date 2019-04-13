<?php
/**
 * 任务
 * @author ypliang
 * @version 2019-01-02
 */
namespace App\Model\Task;
use App\Model\DBModel;
use App\Model\BaseModel;
class TaskModel extends DBModel
{
    use BaseModel;

    protected $table = 'bd_task';
    protected $kw_table = 'bd_key_word';
    protected $platform_table = 'bd_task_platform';
    protected $time_table = 'bd_task_time';

    public function __construct()
    {
        parent::__construct();
    }


    /**
     * @todo：创建任务
     * @param $data
     * @return bool|int
     * @throws \Exception
     */
    public function toInsert($data){
        $common_part = array_intersect(array_keys($data), ['task', 'platform', 'time']);
        if (count($common_part) <= 0) {
            return false;
        }
        if(empty($data['task'])){
            return false;
        }

        $this->connection->beginTransaction();
        //1、插入任务表
        $t_id = $this->getConnectionTable($this->table)->insertGetId($data['task']);
        if (!$t_id) {
            $this->connection->rollBack();
            return false;
        }
        //2、插入任务平台表
        if(!empty($data['platform'])){
            foreach($data['platform'] as $val){
                $val['task_id'] = $t_id;
                $pt_id = $this->getConnectionTable($this->platform_table)->insertGetId($val);
                if (!$pt_id) {
                    $this->connection->rollBack();
                    return false;
                }
            }
        }
        //3、插入任务时间表
        if(!empty($data['time'])){
            foreach($data['time'] as $val){
                $val['task_id'] = $t_id;
                $tt_id = $this->getConnectionTable($this->time_table)->insertGetId($val);
                if (!$tt_id) {
                    $this->connection->rollBack();
                    return false;
                }
            }
        }
        $this->connection->commit();
        return $t_id;
    }


}