<?php
namespace App\Libraries;
class Cache
{
    private \Redis $redis;

    public function __construct()
    {
        date_default_timezone_set(getenv('locale.timezone'));
        $this->redis = new \Redis();
        $this->redis->connect('127.0.0.1', 6379);
    }
    public function set($key,$data,$expire=30)
    {
        $this->redis->set($key,$data,$expire);
        return true;
    }
    public function get($key)
    {
        return $this->redis->get($key);
    }
}
?>
