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

use App\Middleware;
use App\Route;
use T2\Config;
use T2\Contract\BootstrapInterface;
use T2\Log;
use T2\Env;
use T2\Util;
use Workerman\Events\Select;
use Workerman\Worker;

// 初始化 Worker 的事件循环机制
initializeEventLoop();

// 注册全局错误处理器
setGlobalErrorHandler();

// 注册脚本关闭时的回调
registerShutdownCallback($worker ?? null);

// 加载 .env 环境变量文件
loadEnvironmentVariables(base_path() . DIRECTORY_SEPARATOR . '.env');

// 清空配置缓存并加载应用配置
reloadAppConfig();

// 设置默认时区
setDefaultTimezone(config('app.default_timezone'));

// 自动加载配置中定义的文件
autoloadFiles(config('autoload.files', []));

// 加载全局中间件
Middleware::load(config('middleware', []));

// 加载多应用模式的配置与中间件
$directory = base_path() . '/app';
$paths     = loadAppConfigs($directory);

// 加载收集到的所有路由配置
Route::load($paths);

/**
 * 初始化 Worker 的事件循环机制
 * @return void
 */
function initializeEventLoop(): void
{
    if (empty(Worker::$eventLoopClass)) {
        Worker::$eventLoopClass = Select::class;
    }
}

/**
 * 注册全局错误处理器
 * @return void
 * @throws ErrorException
 */
function setGlobalErrorHandler(): void
{
    set_error_handler(function ($level, $message, $file = '', $line = 0) {
        if (error_reporting() & $level) {
            throw new ErrorException($message, 0, $level, $file, $line);
        }
    });
}

/**
 * 注册脚本关闭时的回调
 * @param Worker|null $worker
 * @return void
 */
function registerShutdownCallback(?Worker $worker): void
{
    if ($worker) {
        register_shutdown_function(function ($startTime) {
            if (time() - $startTime <= 0.1) {
                sleep(1);
            }
        }, time());
    }
}

/**
 * 加载 .env 环境变量文件
 * @param string $envPath
 * @return void
 */
function loadEnvironmentVariables(string $envPath): void
{
    if (class_exists(Env::class) && file_exists($envPath) && method_exists(Env::class, 'load')) {
        try {
            Env::load($envPath);
        } catch (Throwable $e) {
            error_log("Failed to load .env file: " . $e->getMessage());
        }
    }
}

/**
 * 清空配置缓存并加载应用配置
 * @return void
 */
function reloadAppConfig(): void
{
    Config::clear();
    T2\App::loadAllConfig(['route']);
}

/**
 * 设置默认时区
 * @param string|null $timezone
 * @return void
 */
function setDefaultTimezone(?string $timezone): void
{
    if ($timezone) {
        date_default_timezone_set($timezone);
    }
}

/**
 * 自动加载指定文件
 * @param array $files
 * @return void
 */
function autoloadFiles(array $files): void
{
    foreach ($files as $file) {
        include_once $file;
    }
}

/**
 * 加载多应用模式的配置与中间件
 * @param string $directory 应用目录路径
 * @return array 收集的配置路径
 */
function loadAppConfigs(string $directory): array
{
    $paths          = [config_path()];
    $appDirectories = Util::scanDir($directory, false);

    // 遍历应用目录
    foreach ($appDirectories as $appName) {
        $appPath = "$directory/$appName";

        // 检查 config 子目录是否存在
        $configDir = "$appPath/config";
        if (is_dir($configDir)) {
            $paths[] = $configDir;
            // 自动加载配置中定义的文件
            autoloadFiles(config("$appName.autoload.files", []));

            // 加载与应用相关的中间件
            if (function_exists('loadAppMiddlewares')) {
                loadAppMiddlewares($appName);
            }
        }

        // 检查 route 子目录是否存在
        $routeDir = "$appPath/route";
        if (is_dir($routeDir)) {
            $paths[] = $routeDir;
        }
    }

    // 加载 bootstrap
    foreach (config('bootstrap', []) as $className) {
        if (!class_exists($className)) {
            $log = "Warning: Class $className setting in config/bootstrap.php not found\r\n";
            echo $log;
            Log::error($log);
            continue;
        }
        /**
         * @var BootstrapInterface $className
         */
        $className::start($worker ?? null);
    }

    // 加载静态资源的全局中间件
    Middleware::load(['__static__' => config('static.middleware', [])]);

    // 添加全局路由目录
    $paths[] = base_path('route');
    return $paths;
}