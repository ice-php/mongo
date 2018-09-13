<?php
declare(strict_types=1);

namespace icePHP;

class MongoException extends \Exception{
    //Mongo服务器连接失败
    const CONNECT_FAIL=1;

    //读取Mongo服务器配置失败
    const CONFIG_FAIL=2;

    //Mongo集合名称错误
    const COLLECTION_ERROR=3;
}