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
     * Assign.
     * @param string|array $name
     * @param mixed $value
     */
    public static function assign(string|array $name, mixed $value = null): void
    {
        $request             = request();
        $request->_view_vars = array_merge((array)$request->_view_vars, is_array($name) ? $name : [$name => $value]);
    }

    /**
     * Render.
     * @param string $template
     * @param array $vars
     * @param string|null $app
     * @return string
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public static function render(string $template, array $vars, ?string $app = null): string
    {
        static $views = [];
        $request      = request();
        $app          = $app === null ? ($request->app ?? '') : $app;
        $viewSuffix   = config("view.options.view_suffix", 'html');
        $baseViewPath = app_path();

        // 如果指定的模板以 ‘/’ 开头
        if ($template[0] === '/') {
            $template = ltrim($template, '/');
            if (str_contains($template, '/view/')) {
                [$viewPath, $template] = explode('/view/', $template, 2);
                $viewPath = base_path("$viewPath/view");
            } else {
                $viewPath = base_path();
            }
        } else {
            $viewPath = "$baseViewPath/$app/view/";
        }

        if (!isset($views[$viewPath])) {
            $views[$viewPath] = new Environment(new FilesystemLoader($viewPath), config("view.options", []));
            $extension        = config("view.extension");
            if ($extension) {
                $extension($views[$viewPath]);
            }
        }

        if (isset($request->_view_vars)) {
            $vars = array_merge((array)$request->_view_vars, $vars);
        }

        return $views[$viewPath]->render("$template.$viewSuffix", $vars);
    }
}