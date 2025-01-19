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

namespace T2\Contract;

use Workerman\Worker;

interface BootstrapInterface
{
    /**
     * Worker 进程启动事件.
     *
     * 当 Worker 进程启动时会触发此方法，通常用于初始化相关资源或执行启动逻辑。
     *
     * @param Worker|null $worker Worker 实例 (可选).
     *                            - Worker 是一个 Workerman 的核心类，表示一个具体的工作进程。
     *                            - 如果传入 null，表示未绑定到特定的 Worker 实例。
     *                            - 常用于访问当前 Worker 的属性或执行与进程相关的操作。
     *                            - 示例：可以使用 `$worker->id` 获取当前进程的 ID。
     *
     * @return void 方法返回值
     */
    public static function start(?Worker $worker): void;
}