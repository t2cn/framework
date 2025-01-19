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

use Exception;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class ReplaceString extends AbstractExtension
{
    /**
     * 注册自定义 Twig 函数
     * @return array
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('static', [$this, 'getStaticPath']),
            new TwigFunction('res', [$this, 'getResPath']),
            new TwigFunction('common', [$this, 'getCommonPath']),
            new TwigFunction('url', [$this, 'generateUrl']),
            new TwigFunction('csrf_token', [$this, 'csrf_token']),
        ];
    }

    /**
     * 获取静态资源路径
     * @param string $path
     * @return string
     */
    public function getStaticPath(string $path = ''): string
    {
        return '/static/' . ltrim($path, '/');
    }

    /**
     * 获取资源路径
     * @param string $path
     * @return string
     */
    public function getResPath(string $path = ''): string
    {
        return '/res/' . ltrim($path, '/');
    }

    /**
     * 获取公共路径
     * @param string $path
     * @return string
     */
    public function getCommonPath(string $path = ''): string
    {
        return '/common/' . ltrim($path, '/');
    }

    /**
     * URL
     * @param string $path 路径，例如 'admin/index/index'
     * @param array $params 参数，例如 ['id' => 1]
     * @return string
     */
    public function generateUrl(string $path, array $params = []): string
    {
        return url($path, $params);
    }

    /**
     * 生成csrf_token
     * @return string
     * @throws Exception
     */
    public function csrf_token(): string
    {
        return request()->buildToken();
    }
}