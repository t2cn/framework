<?php
/**
 * This file is part of T2-Engine.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the MIT-LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @author    Tony<dev@t2engine.cn>
 * @copyright Tony<dev@t2engine.cn>
 * @link      https://www.t2engine.cn/
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */
declare(strict_types=1);

namespace T2;

use Illuminate\Events\Dispatcher;
use Illuminate\Redis\Connections\Connection;
use Illuminate\Redis\RedisManager;
use Workerman\Timer;
use Workerman\Worker;
use function class_exists;
use function config;
use function in_array;

/**
 * Class Redis
 *
 * @package support
 *
 * Strings methods
 * @method static int append($key, $value) 在指定的键 $key 的值后追加字符串 $value。如果键不存在，则创建一个新键并设置其值为 $value。
 * @method static int bitCount($key) 统计指定键 $key 对应的值中，二进制位为 1 的数量。
 * @method static int decr($key, $value = 1) 将指定键 $key 的值减 1。如果键不存在，则初始化为 0 再执行减操作。
 * @method static int decrBy($key, $value) 将指定键 $key 的值减少 $value。如果键不存在，则初始化为 0 再执行减操作。
 * @method static string|bool get($key) 获取指定键 $key 的值。如果键不存在，则返回 false。
 * @method static int getBit($key, $offset) 获取键 $key 的值的二进制数据中指定偏移量 $offset 的位。
 * @method static string getRange($key, $start, $end) 获取键 $key 的值的子字符串，从偏移 $start 到 $end。
 * @method static string getSet($key, $value) 将键 $key 的值设置为 $value，并返回键原来的值。
 * @method static int incr($key, $value = 1) 将指定键 $key 的值加 1。如果键不存在，则初始化为 0 再执行加操作。
 * @method static int incrBy($key, $value) 将指定键 $key 的值增加 $value。如果键不存在，则初始化为 0 再执行加操作。
 * @method static float incrByFloat($key, $value) 将指定键 $key 的值增加浮点数 $value。如果键不存在，则初始化为 0 再执行加操作。
 * @method static array mGet(array $keys) 批量获取多个键 $keys 的值，返回值为一个数组。
 * @method static array getMultiple(array $keys) 与 `mGet` 功能相同，批量获取多个键的值，返回数组。
 * @method static bool mSet($pairs) 批量设置键值对，$pairs 是一个关联数组，键为键名，值为对应的值。
 * @method static bool mSetNx($pairs) 批量设置键值对，只有所有键都不存在时才会成功设置。$pairs 是一个关联数组。
 * @method static bool set($key, $value, $expireResolution = null, $expireTTL = null, $flag = null) 设置键 $key 的值为 $value，可以选择设置过期时间 $expireTTL 和过期方式。
 * @method static bool setBit($key, $offset, $value) 设置键 $key 的值的二进制数据中指定偏移量 $offset 的位为 $value（0 或 1）。
 * @method static bool setEx($key, $ttl, $value) 设置键 $key 的值为 $value，并指定过期时间 $ttl（以秒为单位）。
 * @method static bool pSetEx($key, $ttl, $value) 设置键 $key 的值为 $value，并指定过期时间 $ttl（以毫秒为单位）。
 * @method static bool setNx($key, $value) 仅当键 $key 不存在时，设置其值为 $value。
 * @method static string setRange($key, $offset, $value) 使用 $value 替换键 $key 的值，从偏移量 $offset 开始覆盖。
 * @method static int strLen($key) 获取键 $key 的值的长度（以字符串长度计算）
 *
 * Keys methods
 * @method static int del(...$keys) 删除指定的键 $keys，可以一次删除多个。
 * @method static int unlink(...$keys) 异步删除一个或多个键。与 `del` 不同，`unlink` 是非阻塞的，适合处理大量数据的键删除操作。
 * @method static false|string dump($key) 序列化键 $key 的值，并返回其序列化表示。返回的值可以通过 `restore` 方法还原。
 * @method static int exists(...$keys) 检查指定的键 $keys 是否存在，返回存在的键的数量。
 * @method static bool expire($key, $ttl) 设置键 $key 的过期时间，$ttl 为从现在起的秒数。
 * @method static bool pexpire($key, $ttl) 设置键 $key 的过期时间为指定的 $ttl 毫秒。
 * @method static bool expireAt($key, $timestamp) 设置键 $key 的过期时间，$timestamp 为 Unix 时间戳。
 * @method static bool pexpireAt($key, $timestamp) 设置键 $key 的过期时间为指定的 UNIX 时间戳 $timestamp，单位是毫秒。当指定的时间到达后，键将自动被删除。返回 true 表示设置成功，false 表示失败或键不存在。
 * @method static array keys($pattern) 返回与模式 $pattern 匹配的所有键的列表。
 * @method static bool|array scan($it) 以增量迭代的方式扫描当前数据库中的键，类似于 `keys`，但适合大数据量操作。
 * @method static void migrate($host, $port, $keys, $dbIndex, $timeout, $copy = false, $replace = false)
 * @method static bool select($dbIndex) 切换到指定的 Redis 数据库 $dbIndex（索引从 0 开始）。
 * @method static bool move($key, $dbIndex) 将指定的键 $key 移动到另一个 Redis 数据库 $dbIndex（索引从 0 开始）。
 * @method static string|int|bool object($information, $key) 返回关于键 $key 的底层 Redis 信息，例如引用计数、空闲时间等。
 * @method static bool persist($key) 移除指定键 $key 的过期时间，使其永久存在。
 * @method static string randomKey() 返回当前数据库中的一个随机键。
 * @method static bool rename($srcKey, $dstKey) 将键 $srcKey 重命名为 $dstKey。如果 $dstKey 已存在，则覆盖。
 * @method static bool renameNx($srcKey, $dstKey) 将键 $srcKey 重命名为 $dstKey，只有当 $dstKey 不存在时才执行。
 * @method static string type($key) 返回键 $key 的数据类型。
 * @method static int|array sort($key, $options = []) 对键 $key 的列表、集合或有序集合进行排序。
 * @method static int ttl($key) 返回键 $key 的剩余生存时间（TTL），单位是秒。
 * @method static int pttl($key) 返回键 $key 的剩余生存时间（TTL），单位是毫秒。
 * @method static void restore($key, $ttl, $value) 反序列化给定的值 $value 并将其存储到键 $key 中，同时设置过期时间 $ttl（单位毫秒）
 *
 * Hashes methods
 * @method static false|int hSet($key, $hashKey, $value) 将哈希表 $key 中的字段 $hashKey 的值设为 $value。如果字段不存在，会创建该字段。
 * @method static bool hSetNx($key, $hashKey, $value) 为哈希表 $key 的字段 $hashKey 设置值，仅当该字段不存在时有效。
 * @method static false|string hGet($key, $hashKey) 获取哈希表 $key 中字段 $hashKey 的值。
 * @method static false|int hLen($key) 获取哈希表 $key 中字段的数量。
 * @method static false|int hDel($key, ...$hashKeys) 删除哈希表 $key 中的一个或多个字段。
 * @method static array hKeys($key) 获取哈希表 $key 中的所有字段名。
 * @method static array hVals($key) 获取哈希表 $key 中的所有字段值。
 * @method static array hGetAll($key) 获取哈希表 $key 中的所有字段和值。
 * @method static bool hExists($key, $hashKey) 检查哈希表 $key 中是否存在字段 $hashKey。
 * @method static int hIncrBy($key, $hashKey, $value) 为哈希表 $key 中字段 $hashKey 的值增加 $value。
 * @method static float hIncrByFloat($key, $hashKey, $value) 为哈希表 $key 中字段 $hashKey 的值增加浮点数 $value。
 * @method static bool hMSet($key, $members) 为哈希表 $key 中的多个字段设置值。
 * @method static array hMGet($key, $memberKeys) 获取哈希表 $key 中多个字段的值。
 * @method static array hScan($key, $iterator, $pattern = '', $count = 0) 迭代哈希表 $key 中的字段和值。
 * @method static int hStrLen($key, $hashKey) 获取哈希表 $key 中字段 $hashKey 的值的字符串长度。
 *
 * Lists methods
 * @method static array blPop($keys, $timeout) 从左侧弹出 $keys 列表中第一个非空列表的元素，如果所有列表为空，则阻塞直到超时或有元素可弹出。
 * @method static array brPop($keys, $timeout) 从右侧弹出 $keys 列表中第一个非空列表的元素，如果所有列表为空，则阻塞直到超时或有元素可弹出。
 * @method static false|string bRPopLPush($srcKey, $dstKey, $timeout) 从 $srcKey 列表右侧弹出一个元素，并将其推入 $dstKey 列表的左侧。如果 $srcKey 为空，则阻塞直到超时。
 * @method static false|string lIndex($key, $index) 获取 $key 列表中指定索引的元素。
 * @method static int lInsert($key, $position, $pivot, $value) 在 $key 列表中 $pivot 元素之前或之后插入 $value。
 * @method static false|string lPop($key) 从 $key 列表的左侧弹出一个元素。
 * @method static false|int lPush($key, ...$entries) 将一个或多个值插入到 $key 列表的左侧。
 * @method static false|int lPushx($key, $value) 将 $value 插入到 $key 列表的左侧，仅当列表已存在时有效。
 * @method static array lRange($key, $start, $end) 获取 $key 列表中指定范围内的元素。
 * @method static false|int lRem($key, $count, $value) 从 $key 列表中删除最多 $count 个值等于 $value 的元素。
 * @method static bool lSet($key, $index, $value) 将 $key 列表中索引为 $index 的元素设置为 $value。
 * @method static false|array lTrim($key, $start, $end) 对 $key 列表进行修剪，仅保留从 $start 到 $end 范围内的元素。
 * @method static false|string rPop($key) 从 $key 列表的右侧弹出一个元素。
 * @method static false|string rPopLPush($srcKey, $dstKey) 从 $srcKey 列表的右侧弹出一个元素，并将其推入 $dstKey 列表的左侧。
 * @method static false|int rPush($key, ...$entries) 将一个或多个值插入到 $key 列表的右侧。
 * @method static false|int rPushX($key, $value) 将 $value 插入到 $key 列表的右侧，仅当列表已存在时有效。
 * @method static false|int lLen($key) 获取 $key 列表的长度。
 *
 * Sets methods
 * @method static int sAdd($key, $value) 向集合中添加一个或多个元素
 * @method static int sCard($key) 获取集合中元素的数量
 * @method static array sDiff($keys) 返回多个集合的差集
 * @method static false|int sDiffStore($dst, $keys) 计算多个集合的差集并将结果存储到目标集合中
 * @method static false|array sInter($keys) 返回多个集合的交集
 * @method static false|int sInterStore($dst, $keys) 计算多个集合的交集并将结果存储到目标集合中
 * @method static bool sIsMember($key, $member) 判断元素是否是集合的成员
 * @method static array sMembers($key) 获取集合中的所有成员
 * @method static bool sMove($src, $dst, $member) 将集合中的一个元素移动到另一个集合
 * @method static false|string|array sPop($key, $count = 0) 从集合中移除并返回一个或多个随机元素
 * @method static false|string|array sRandMember($key, $count = 0) 从集合中随机返回一个或多个元素，但不移除
 * @method static int sRem($key, ...$members) 从集合中移除一个或多个元素
 * @method static array sUnion(...$keys) 返回多个集合的并集
 * @method static false|int sUnionStore($dst, ...$keys) 计算多个集合的并集并将结果存储到目标集合中
 * @method static false|array sScan($key, $iterator, $pattern = '', $count = 0) 迭代集合中的元素（支持模式匹配）
 *
 * @method static array bzPopMin($keys, $timeout) 从多个有序集合中弹出最小值，阻塞直到超时或找到元素。
 * @method static array bzPopMax($keys, $timeout) 从多个有序集合中弹出最大值，阻塞直到超时或找到元素。
 * @method static int zAdd($key, $score, $value) 向有序集合添加一个元素。
 * @method static int zCard($key) 获取有序集合的元素数量。
 * @method static int zCount($key, $start, $end) 获取指定分数范围内的元素数量。
 * @method static double zIncrBy($key, $value, $member) 增加有序集合中成员的分数。
 * @method static int zinterstore($keyOutput, $arrayZSetKeys, $arrayWeights = [], $aggregateFunction = '') 计算多个有序集合的交集并存储结果。
 * @method static array zPopMin($key, $count) 弹出有序集合中的 $count 个最小值。
 * @method static array zPopMax($key, $count) 弹出有序集合中的 $count 个最大值。
 * @method static array zRange($key, $start, $end, $withScores = false) 返回有序集合中指定区间的元素。
 * @method static array zRangeByScore($key, $start, $end, $options = []) 返回指定分数范围内的元素。
 * @method static array zRevRangeByScore($key, $start, $end, $options = []) 逆序返回指定分数范围内的元素。
 * @method static array zRangeByLex($key, $min, $max, $offset = 0, $limit = 0) 根据字典序返回指定范围内的元素。
 * @method static int zRank($key, $member) 返回有序集合中指定成员的排名（从小到大）。
 * @method static int zRevRank($key, $member) 返回有序集合中指定成员的排名（从大到小）。
 * @method static int zRem($key, ...$members) 从有序集合中删除一个或多个成员。
 * @method static int zRemRangeByRank($key, $start, $end) 删除指定排名范围内的元素。
 * @method static int zRemRangeByScore($key, $start, $end) 删除指定分数范围内的元素。
 * @method static array zRevRange($key, $start, $end, $withScores = false) 逆序返回有序集合中指定区间的元素。
 * @method static double zScore($key, $member) 返回有序集合中指定成员的分数。
 * @method static int zunionstore($keyOutput, $arrayZSetKeys, $arrayWeights = [], $aggregateFunction = '') 计算多个有序集合的并集并存储结果。
 * @method static false|array zScan($key, $iterator, $pattern = '', $count = 0) 迭代有序集合中的元素。
 *
 * HyperLogLogs methods
 * @method static int pfAdd($key, $values) 添加元素到 HyperLogLog 数据结构。
 * @method static int pfCount($keys) 返回 HyperLogLog 的基数估计值。
 * @method static bool pfMerge($dstKey, $srcKeys) 合并多个 HyperLogLog。
 *
 * Geocoding methods
 * @method static int geoAdd($key, $longitude, $latitude, $member, ...$items) 添加地理位置信息。
 * @method static array geoHash($key, ...$members) 获取成员的 Geohash 值。
 * @method static array geoPos($key, ...$members) 获取成员的经纬度。
 * @method static double geoDist($key, $members, $unit = '') 计算两成员之间的距离。
 * @method static int|array geoRadius($key, $longitude, $latitude, $radius, $unit, $options = []) 返回指定范围内的成员。
 * @method static array geoRadiusByMember($key, $member, $radius, $units, $options = []) 返回指定成员附近的成员。
 *
 * Streams methods
 * @method static int xAck($stream, $group, $arrMessages) 确认消息已处理。
 * @method static string xAdd($strKey, $strId, $arrMessage, $iMaxLen = 0, $booApproximate = false) 向流中添加消息。
 * @method static array xClaim($strKey, $strGroup, $strConsumer, $minIdleTime, $arrIds, $arrOptions = []) 重新分配消息给消费者。
 * @method static int xDel($strKey, $arrIds) 删除流中的消息。
 * @method static mixed xGroup($command, $strKey, $strGroup, $strMsgId, $booMKStream = null) 管理消费者组。
 * @method static mixed xInfo($command, $strStream, $strGroup = null) 获取流或消费者组的信息。
 * @method static int xLen($stream) 获取流中的消息数量。
 * @method static array xPending($strStream, $strGroup, $strStart = 0, $strEnd = 0, $iCount = 0, $strConsumer = null) 获取待处理消息。
 * @method static array xRange($strStream, $strStart, $strEnd, $iCount = 0) 获取指定范围内的消息。
 * @method static array xRead($arrStreams, $iCount = 0, $iBlock = null) 读取消息。
 * @method static array xReadGroup($strGroup, $strConsumer, $arrStreams, $iCount = 0, $iBlock = null) 从消费者组读取消息。
 * @method static array xRevRange($strStream, $strEnd, $strStart, $iCount = 0) 逆序获取指定范围内的消息。
 * @method static int xTrim($strStream, $iMaxLen, $booApproximate = null) 修剪流以限制其长度。
 *
 * Pub/sub methods
 * @method static mixed pSubscribe($patterns, $callback) 订阅模式匹配的频道。
 * @method static mixed publish($channel, $message) 向频道发布消息。
 * @method static mixed subscribe($channels, $callback) 订阅指定的频道。
 * @method static mixed pubSub($keyword, $argument = null) 获取 Pub/Sub 信息。
 *
 * Generic methods
 * @method static mixed rawCommand(...$commandAndArgs) 发送原始 Redis 命令。
 *
 * Transactions methods
 * @method static Redis multi() 开始事务。
 * @method static mixed exec() 执行事务。
 * @method static mixed discard() 丢弃事务。
 * @method static mixed watch($keys) 监视一个或多个键。
 * @method static mixed unwatch($keys) 取消监视。
 *
 * Scripting methods
 * @method static mixed eval($script, $numkeys, $keyOrArg1 = null, $keyOrArgN = null) 执行 Lua 脚本。
 * @method static mixed evalSha($scriptSha, $numkeys, ...$arguments) 执行已缓存的 Lua 脚本。
 * @method static mixed script($command, ...$scripts) 管理脚本缓存。
 * @method static mixed client(...$args) 管理 Redis 客户端连接。
 * @method static null|string getLastError() 获取最后的错误信息。
 * @method static bool clearLastError() 清除最后的错误信息。
 * @method static mixed _prefix($value) 获取带前缀的键。
 * @method static mixed _serialize($value) 序列化值。
 * @method static mixed _unserialize($value) 反序列化值。
 *
 * Introspection methods
 * @method static bool isConnected() 检查是否已连接。
 * @method static mixed getHost() 获取主机地址。
 * @method static mixed getPort() 获取端口号。
 * @method static false|int getDbNum() 获取当前数据库编号。
 * @method static false|double getTimeout() 获取超时时间。
 * @method static mixed getReadTimeout() 获取读取超时时间。
 * @method static mixed getPersistentID() 获取持久连接 ID。
 * @method static mixed getAuth() 获取认证信息。
 */
class Redis
{
    /**
     * @var ?RedisManager
     */
    protected static ?RedisManager $instance = null;

    /**
     * need to install phpredis extension
     */
    const string PHPREDIS_CLIENT = 'phpredis';

    /**
     * need to install the 'predis/predis' packgage.
     * cmd: composer install predis/predis
     */
    const string PREDIS_CLIENT = 'predis';

    /**
     * Support client collection
     */
    static array $allowClient = [
        self::PHPREDIS_CLIENT,
        self::PREDIS_CLIENT
    ];

    /**
     * The Redis server configurations.
     *
     * @var array
     */
    protected static array $config = [];

    /**
     * Static timers facilitate deletion during callbacks.
     *
     * @var array
     */
    protected static array $timers = [];

    /**
     * The number of seconds an idle connection will be terminated.
     *
     * @var int
     */
    protected static $idle_time = 0;

    /**
     * @return ?RedisManager
     */
    public static function instance(): ?RedisManager
    {
        if (!static::$instance) {
            $config = config('redis');
            $client = $config['client'] ?? self::PHPREDIS_CLIENT;

            if (!in_array($client, static::$allowClient)) {
                $client = self::PHPREDIS_CLIENT;
            }

            static::$config   = $config;
            static::$instance = new RedisManager('', $client, $config);
        }
        return static::$instance;
    }

    /**
     * Connection
     *
     * @param string $name
     *
     * @return Connection
     */
    public static function connection(string $name = 'default'): Connection
    {
        if (!empty(static::$config[$name]['idle_timeout'])) {
            static::$idle_time = time();
        }

        $connection = static::instance()->connection($name);
        if (!isset(static::$timers[$name])) {
            static::$timers[$name] = Worker::getAllWorkers() ? Timer::add(55, function () use ($connection, $name) {
                if (!empty(static::$config[$name]['idle_timeout'])
                    && time() - static::$idle_time > static::$config[$name]['idle_timeout']) {
                    Timer::del(static::$timers[$name]);
                    unset(static::$timers[$name]);
                    return $connection->client()->close();
                }

                $connection->get('ping');
            }) : 1;

            if (class_exists(Dispatcher::class)) {
                $connection->setEventDispatcher(new Dispatcher());
            }
        }
        return $connection;
    }

    /**
     * @param string $name
     * @param array  $arguments
     *
     * @return mixed
     */
    public static function __callStatic(string $name, array $arguments)
    {
        return static::connection()->{$name}(... $arguments);
    }
}