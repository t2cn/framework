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

namespace T2\Bootstrap;

use T2\Contract\BootstrapInterface;
use Workerman\Protocols\Http\Session as SessionBase;
use Workerman\Worker;
use function config;
use function property_exists;

class Session implements BootstrapInterface
{
    /**
     * @param Worker|null $worker
     * @return void
     */
    public static function start(?Worker $worker): void
    {
        $config = config('session');
        if (property_exists(SessionBase::class, 'name')) {
            SessionBase::$name = $config['session_name'];
        }
        SessionBase::handlerClass($config['handler'], $config['config'][$config['type']]);
        $map = [
            'auto_update_timestamp' => 'autoUpdateTimestamp',
            'cookie_lifetime'       => 'cookieLifetime',
            'gc_probability'        => 'gcProbability',
            'cookie_path'           => 'cookiePath',
            'http_only'             => 'httpOnly',
            'same_site'             => 'sameSite',
            'lifetime'              => 'lifetime',
            'domain'                => 'domain',
            'secure'                => 'secure',
        ];

        foreach ($map as $key => $name) {
            if (isset($config[$key]) && property_exists(SessionBase::class, $name)) {
                SessionBase::${$name} = $config[$key];
            }
        }
    }
}