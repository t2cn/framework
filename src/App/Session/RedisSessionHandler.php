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

namespace App\Session;

use Redis;
use RedisException;
use RuntimeException;
use Workerman\Protocols\Http\Session\RedisSessionHandler as RedisHandler;

class RedisSessionHandler extends RedisHandler
{
    /**
     * @return void
     * @throws RedisException
     */
    public function connect(): void
    {
        $config      = $this->config;
        $this->redis = new Redis();
        if (false === $this->redis->connect($config['host'], (int)$config['port'], $config['timeout'])) {
            throw new RuntimeException("Redis connect {$config['host']}:{$config['port']} fail.");
        }
        if (!empty($config['auth'])) {
            $this->redis->auth($config['auth']);
        }
        if (!empty($config['database'])) {
            $this->redis->select((int)$config['database']);
        }
        if (empty($config['prefix'])) {
            $config['prefix'] = 'redis_session_';
        }
        $this->redis->setOption(Redis::OPT_PREFIX, $config['prefix']);
    }
}