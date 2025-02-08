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

use stdClass;
use Workerman\Coroutine\Context as WorkermanContext;
use Workerman\Coroutine\Utils\DestructionWatcher;
use Closure;

class Context extends WorkermanContext
{
    public static function onDestroy(Closure $closure): void
    {
        $obj = static::get('context.onDestroy');
        if (!$obj) {
            $obj = new stdClass();
            static::set('context.onDestroy', $obj);
        }
        DestructionWatcher::watch($obj, $closure);
    }
}