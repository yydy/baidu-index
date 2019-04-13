<?php
/**
 * Author: ypliang
 * Date: 2019/01/16
 * Time: 18:46
 */
namespace App\Model;
use Illuminate\Support\Facades\Redis;

class RedisModel
{

    public $prefix = null;
    public $connectionName = null;
    public $connection = null;
    public $expire = null;
    public $set_connection_name = false;

    public function __construct($connection = 'cache')
    {
        $this->prefix = 'BASEDATA:';
        $this->expire = 3600;
        $this->connectionName = $connection;
    }

    public function setConnectionName($connectionName)
    {
        $this->set_connection_name = true;
        return $this->connectionName = $connectionName;
    }
    
    private function setCacheConnection()
    {
        return Redis::connection($this->connectionName);
    }

    public function setWithExpire($key, $val, $expire)
    {
        $expire = is_numeric($expire) && $expire > 0 ? $expire : $this->expire;
        return $this->setCacheConnection()->setex($this->prefix . $key, $expire, $val);
    }

    public function get($key)
    {
        return $this->setCacheConnection()->get($this->prefix . $key);
    }

    public function tempSetWithExpire($key, $val, $expire)
    {
        $expire = is_numeric($expire) && $expire > 0 ? $expire : $this->expire;
        $res = $this->setCacheConnection()->setex($this->prefix . $key, $expire, $val);
        return [
            'key' => $key,
            'connectionName' => $this->connectionName,
            'res' => $res
        ];
    }

    public function tempGet($key)
    {
        $res = $this->setCacheConnection()->get($this->prefix . $key);
        return [
            'key' => $key,
            'connectionName' => $this->connectionName,
            'res' => $res
        ];
    }

    public function set($key, $value)
    {
        return $this->setCacheConnection()->set($this->prefix . $key, $value);
    }

    public function setnx($key, $value)
    {
        return $this->setCacheConnection()->setnx($this->prefix . $key, $value);
    }

    public function del($key)
    {
        return $this->setCacheConnection()->del($this->prefix . $key);
    }

    public function incr($key)
    {
        return $this->setCacheConnection()->incr($this->prefix . $key);
    }

    public function incrBy($key, $value)
    {
        return $this->setCacheConnection()->incrby($this->prefix . $key, $value);
    }

    public function decr($key)
    {
        return $this->setCacheConnection()->decr($this->prefix . $key);
    }

    public function sAdd($key, $val)
    {
        return $this->setCacheConnection()->sadd($this->prefix . $key, $val);
    }

    public function sRem($key, $val)
    {
        return $this->setCacheConnection()->srem($this->prefix . $key, $val);
    }

    public function sIsMember($key, $val)
    {
        return $this->setCacheConnection()->sismember($this->prefix . $key, $val);
    }

    public function sMembers($key)
    {
        return $this->setCacheConnection()->smembers($this->prefix . $key);
    }

    public function sCard($key)
    {
        return $this->setCacheConnection()->scard($this->prefix . $key);
    }

    public function zAdd($key, $score, $member)
    {
        return $this->setCacheConnection()->zadd($this->prefix . $key, $score, $member);
    }

    public function zAdds($key, $values)
    {
        return $this->setCacheConnection()->zadd($this->prefix . $key, $values);
    }

    public function zRem($key, $values)
    {
        return $this->setCacheConnection()->zrem($this->prefix . $key, $values);
    }

    public function zScore($key, $val)
    {
        return $this->setCacheConnection()->zScore($this->prefix . $key, $val);
    }

    public function zCard($key)
    {
        return $this->setCacheConnection()->zcard($this->prefix . $key);
    }

    public function zRevRange($key, $start, $end, $options = [])
    {
        return $this->setCacheConnection()->zrevrange($this->prefix . $key, $start, $end, $options);
    }

    public function hMSet($key, $values)
    {
        return $this->setCacheConnection()->hmset($this->prefix . $key, $values);
    }

    public function hMGet($outer_keys, $keys)
    {
        $result = $this->setCacheConnection()->hMGet($this->prefix . $outer_keys, $keys);
        return $result;
    }

    public function hSet($outer_keys, $key, $value)
    {
        $result = $this->setCacheConnection()->hSet($this->prefix . $outer_keys, $key, $value);
        if (!is_array($result)) {
            $result = json_decode($result, true);
        }
        return $result;
    }

    public function hGet($outer_keys, $key)
    {
        $result = false;
        if ($this->setCacheConnection()->hExists($this->prefix . $outer_keys, $key)) {
            $data = $this->setCacheConnection()->hGet($this->prefix . $outer_keys, $key);
            $result = json_decode($data, true);
        }
        return $result;
    }

    public function hDel($outer_keys, $key)
    {
        return $this->setCacheConnection()->hdel($this->prefix . $outer_keys, $key);
    }

    public function hLen($outer_keys)
    {
        return $this->setCacheConnection()->hLen($this->prefix . $outer_keys);
    }

    public function hGetAll($key)
    {
        return $this->setCacheConnection()->hgetall($this->prefix . $key);
    }

    public function hIncrBy($key, $field, $val)
    {
        return $this->setCacheConnection()->hincrby($this->prefix . $key, $field, $val);
    }

    public function expireAt($key, $timestamp)
    {
        return $this->setCacheConnection()->expireAt($this->prefix . $key, $timestamp);
    }

    public function expireSeconds($key, $seconds)
    {
        return $this->setCacheConnection()->expire($this->prefix . $key, $seconds);
    }

    public function keys($key_n)
    {
        return $this->setCacheConnection()->keys($key_n);
    }

}