<?php
declare(strict_types=1);

namespace icePHP;
/**
 * 生成一个Mongo表对象
 * 这是一个SMongo的快捷入口
 * @param string $name Mongo表名
 * @return Mongo
 */
function mongo(string $name): Mongo
{
    return new Mongo($name);
}