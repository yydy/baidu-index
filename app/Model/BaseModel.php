<?php
/**
 * Model底层基本方法封装
 * Created by PhpStorm.
 * User: ypliang
 * Date: 2018/12/29
 * Time: 15:09
 */
namespace App\Model;

use DB;

trait BaseModel
{
    protected static $compares = [
        '_gt'=>'>',
        '_gte'=>'>=',
        '_lt'=>'<',
        '_lte'=>'<=',
        '_ne'=>'<>',
        '_neq'=>'<>',
        '_like'=>'like',
    ];

    public $connection_tab;

    //无需过滤字段
    public $unfilter_fields = [
        'is_delete',
        'status',
    ];

    //主键id
    public $primary_key = 'id';

    public $query;

    public function getList($where = [], $page = 1, $per_page = 20, $order_by = 'created_at:desc', $field = ['*'])
    {
        if ($page > 0) {
            $offset = ($page - 1) * $per_page;
        } else {
            $offset = 0;
        }
        $limit = $per_page;

        if ($this->query) {
            $query = $this->query;
        } else {
            $query = $this->getConnectionTable($this->table);
        }
        $this->whereSearchProcess($query, $where);

        //获取符合条件的总数
        $total = $query->count();

        if($offset >=0 && $limit >0)
        {
            $query->skip($offset)->take($limit);
        }

        if(is_string($order_by) && $order_by != '') {
            $order_params = explode(':',$order_by);
            $query->orderBy($order_params[0], $order_params[1]);
        } else if (is_array($order_by)) {
            foreach ($order_by as $key=>$direction) {
                $query->orderBy($key, $direction);
            }
        }

        //获取列表
        $list = $query->get($field);
        $this->query = null;
        return ['total' => $total, 'list' => obj_to_array($list)];
    }


    public function getListAll($where = [], $order_by = 'created_at:desc', $field = ['*'], $group_bys = [])
    {
        if ($this->query) {
            $query = $this->query;
        } else {
            $query = $this->getConnectionTable($this->table);
        }

        $this->whereSearchProcess($query, $where);
        if(is_string($order_by) && $order_by != '') {
            $order_params = explode(':',$order_by);
            $query->orderBy($order_params[0], $order_params[1]);
        } else if (is_array($order_by)){
            foreach ($order_by as $key=>$direction) {
                $query->orderBy($key, $direction);
            }
        }
        if (!empty($group_bys)) {
            foreach ($group_bys as $group_by) {
                $query->groupBy($group_by);
            }
            $total = $this->getConnectionTable( DB::raw("({$query->toSql()}) as sub") )
                ->mergeBindings($query)
                ->count();
        } else {
            $total = $query->count();
        }

        //获取列表
        $list = $query->get($field);
        $this->query = null;

        return ['total' => $total, 'list' => obj_to_array($list)];
    }

    public function getOne($where = array(), $field = ['*'], $order_by = "")
    {
        if ($this->query) {
            $query = $this->query;
        } else {
            $query = $this->getConnectionTable($this->table);
        }
        $this->whereSearchProcess($query, $where);

        if(is_string($order_by) && $order_by != '') {
            $order_params = explode(':',$order_by);
            $query->orderBy($order_params[0], $order_params[1]);
        } else if (is_array($order_by)){
            foreach ($order_by as $key=>$direction) {
                $query->orderBy($key, $direction);
            }
        }
        $rs = $query->first($field);
        $this->query = null;
        return obj_to_array($rs);
    }

    public function insert($data){
        if(empty($data) || !is_array($data)){
            return false;
        }
        $id = $this->getConnectionTable($this->table)->insertGetId($data);
        return $id;
    }

    public function multiInsert($data){
        if(empty($data) || !is_array($data)){
            return false;
        }
        DB::connection()->enableQueryLog(); // 开启查询日志
        $rs = $this->getConnectionTable($this->table)->insert($data);
        return $rs;
    }

    public function update($where, $data){
        $query = $this->getConnectionTable($this->table);
        if(empty($data) || !is_array($data)){
            return false;
        }
        if (is_numeric($where)) {
            $query->where($this->primary_key,$where);
        } else if (is_array($where)) {
            $this->whereSearchProcess($query, $where);
        }
        $rs = $query->update($data);
        return $rs;
    }

    public function delete($where){
        $query = $this->getConnectionTable($this->table);
        if(empty($where)){
            return false;
        }
        if (is_numeric($where)) {
            $query->where($this->primary_key,$where);
        } else if (is_array($where)) {
            $this->whereSearchProcess($query, $where);
        }
        $rs = $query->delete();
        return $rs;
    }


    public function whereSearchProcess(&$query, $where){
        $unfilter_pattern = '/'.implode('|',$this->unfilter_fields).'/';
        $where = array_filter($where,function ($value,$key) use ($unfilter_pattern){
            $value = true;
            return $value;
        },ARRAY_FILTER_USE_BOTH);
        foreach ($where as $key => $value) {
            if(is_array($value)){
                $query->whereIn($key,$value);
            }else if(preg_match('/_gt$|_gte$|_lt$|_lte$|_ne$|_neq$|_like$/',$key,$match)){
                //获取查询字段
                $field = preg_replace("/{$match[0]}/",'',$key);
                //获取比较符
                $compare = self::$compares[$match[0]];
                $query->where($field,$compare,$value);
            }else{
                $query->where($key,'=',$value);
            }
        }
    }

    public function getTotal($where)
    {
        if ($this->query) {
            $query = $this->query;
        } else {
            $query = $this->getConnectionTable($this->table);
        }
        $this->whereSearchProcess($query, $where);
        //获取符合条件的总数
        $total = $query->count();
        $this->query = null;
        return $total;
    }


    public function getSum($where, $cloum)
    {
        if ($this->query) {
            $query = $this->query;
        } else {
            $query = $this->getConnectionTable($this->table);
        }
        $this->whereSearchProcess($query, $where);
        $sum = $query->sum($cloum);
        $this->query = null;
        return $sum;
    }


    /**
     * 递增
     * @param $field
     * @param $where
     * @param int $num
     * @return mixed
     */
    public function increment($field, $where, $num=1)
    {
        if ($this->query) {
            $query = $this->query;
        } else {
            $query = $this->getConnectionTable($this->table);
        }
        $this->whereSearchProcess($query, $where);
        $rs = $query->increment($field,$num);
        $this->query = null;
        return $rs;
    }

    /**
     * 递减
     * @param $field
     * @param $where
     * @param int $num
     * @return mixed
     */
    public function decrement($field, $where, $num=1)
    {
        if ($this->query) {
            $query = $this->query;
        } else {
            $query = $this->getConnectionTable($this->table);
        }
        $this->whereSearchProcess($query, $where);
        $rs = $query->decrement($field,$num);
        $this->query = null;
        return $rs;
    }


    /**
     * 原生sql查询
     * @param $sql
     * @return array
     */
    public function query($sql)
    {
        $query = $this->connection;
        $rs = $query->select($sql);
        return obj_to_array($rs);
    }
}