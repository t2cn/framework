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

namespace App\View\Twig;

use T2\Contract\ViewInterface;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Loader\FilesystemLoader;
use function app_path;
use function array_merge;
use function base_path;
use function config;
use function is_array;
use function request;

class Twig implements ViewInterface
{
    /**
     * 用于存储共享视图数据的静态数组。
     */
    private static array $sharedData = [];

    /**
     * 分配视图变量（支持键值对和数组形式）。
     *
     * @param string|array $name  变量名称或键值对数组。
     * @param mixed        $value 变量的值（当 $name 为字符串时生效）。
     */
    public static function assign(string|array $name, mixed $value = null): void
    {
        $request = request();
        // 将新分配的变量合并到 Request 的 `_view_vars` 中
        $request->_view_vars = array_merge($request->_view_vars, is_array($name) ? $name : [$name => $value]);
        // 同时将变量存储到共享的静态属性中，便于全局访问
        self::$sharedData = array_merge(self::$sharedData, is_array($name) ? $name : [$name => $value]);
    }

    /**
     * 渲染模板。
     *
     * @param string      $template 模板路径。
     * @param array       $vars     模板变量。
     * @param string|null $app      应用名称，默认为当前应用。
     *
     * @return string 渲染后的 HTML 字符串。
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public static function render(string $template, array $vars, ?string $app = null): string
    {
        // 缓存已加载的视图环境，避免重复实例化
        static $views = [];
        $request = request();
        // 获取当前应用名称
        $app = $app === null ? ($request->app ?? '') : $app;
        // 获取模板文件后缀，默认为 'html'
        $viewSuffix = config("view.options.view_suffix", 'html');
        // 应用目录路径
        $baseViewPath = app_path();
        // 解析模板路径
        if ($template[0] === '/') {
            // 如果模板路径以 '/' 开头，表示绝对路径
            $template = ltrim($template, '/');
            if (str_contains($template, '/view/')) {
                // 处理带有 'view' 的绝对路径
                [$viewPath, $template] = explode('/view/', $template, 2);
                $viewPath = base_path("$viewPath/view");
            } else {
                $viewPath = base_path();
            }
        } else {
            // 处理相对路径（基于应用目录）
            $viewPath = "$baseViewPath/$app/view/";
        }
        // 如果该路径的视图环境未加载，则初始化
        if (!isset($views[$viewPath])) {
            $views[$viewPath] = new Environment(new FilesystemLoader($viewPath), config("view.options", []));
            // 如果定义了扩展函数，则注册到环境中
            $extension = config("view.extension");
            if ($extension) {
                $extension($views[$viewPath]);
            }
        }
        // 合并全局分配变量与传递的变量
        if (isset($request->_view_vars)) {
            $vars = array_merge($request->_view_vars, $vars);
        }
        // 渲染模板并返回结果
        return $views[$viewPath]->render("$template.$viewSuffix", $vars);
    }

    /**
     * 获取分配的共享视图变量。
     *
     * @return array 返回共享数据的键值对数组。
     */
    public static function getAssignedData(): array
    {
        return self::$sharedData;
    }
}