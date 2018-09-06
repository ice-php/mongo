<?php
declare(strict_types=1);

namespace icePHP;
/**
 * 对MongoDB的封装
 * 尚不完善, 随着使用进度, 逐步添加功能
 * @author Ice
 *
 */
final class Mongo
{

    /**
     * 连接句柄的缓存
     * @var \MongoClient[]
     */
    private static $clients = [];

    /**
     * 构造连接句柄
     *
     * @param string $server
     * @return \MongoClient
     * @throws \Exception
     */
    private static function client(string $server): \MongoClient
    {
        // 如果缓存中没有
        if (!isset(self::$clients[$server])) {
            // 创建一个连接
            self::$clients[$server] = new \MongoClient($server);
        }
        return self::$clients[$server];
    }

    /**
     * 创建一个集合对象
     *
     * @param string $name
     * @return \MongoCollection
     * @throws \Exception
     */
    private static function collection(string $name): \MongoCollection
    {
        // 记录开始时间
        $start = timeLog();

        // 取此集合的配置
        $config = config('mongo', $name);

        // 智能识别配置信息
        if (is_array($config)) {
            $server = $config['server'];
            if (isset($config['collection'])) {
                $collection = $config['collection'];
            } else {
                $collection = $name;
            }
        } else {
            $server = $config;
            $collection = $name;
        }

        // 连接指定数据库
        try {
            $db = self::client($server);
        } catch (\Exception $e) {
            dump($e->getMessage());
            exit();
        }

        // 分解库名
        $arr = explode('/', $server);
        $dbname = end($arr);

        // 记录调试信息
        debug("connect to mongo $name ,persist:" . timeLog($start) . 'ms');

        // 返回集合对象
        return $db->selectCollection($dbname, $collection);
    }

    /**
     * 当前集合对象
     * @var \MongoCollection
     */
    private $collection;

    // 当前集合名称
    private $collectionName;

    /**
     * 构造方法
     *
     * @param string $name 集合名称
     * @throws \Exception
     */
    public function __construct(string $name)
    {
        $this->collectionName = $name;
        $this->collection = self::collection($name);
    }

    /**
     * 返回当前集合对象
     */
    public function getCollection(): \MongoCollection
    {
        return $this->collection;
    }

    /**
     * 转接请求到集合对象上
     *
     * @param string $name
     * @param array $arguments
     * @return mixed
     */
    public function __call(string $name, array $arguments)
    {
        //请求转接
        return call_user_func_array([
            $this->collection,
            $name
        ], $arguments);
    }

    /**
     * 此操作对findAndModify的返回结果进行了封装
     * @param $args array
     * @return array
     */
    public function update(...$args): ?array
    {
        //请求转接 
        return call_user_func_array([
            $this->collection,
            'findAndModify'
        ], $args);
    }

    /**
     * 此操作对find方法进行了封装
     * @return array
     */
    public function select(): array
    {
        //生成请求游标
        $cursor = call_user_func_array([
            $this->collection,
            'find'
        ], func_get_args());

        //逐条取出结果,构造数组
        $result = [];
        while ($cursor->hasNext()) {
            $result[] = $cursor->getNext();
        }

        return $result;
    }

    /**
     * 此操作对findOne方法进行了封装
     */
    public function row(): ?array
    {
        //转接请求
        return call_user_func_array([
            $this->collection,
            'findOne'
        ], func_get_args());
    }
}