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

interface ViewInterface
{
    /**
     * 渲染视图模板.
     *
     * 此方法用于渲染指定的模板文件，并将传入的变量注入到模板中以生成最终的 HTML 内容。
     *
     * @param string $template 模板名称或路径.
     *                         - 通常为模板的相对路径，例如 'index/home'。
     *                         - 若配置了应用级模板路径，则会基于该路径查找模板文件。
     *
     * @param array $vars 渲染模板时使用的变量数组.
     *                    - 键名为模板中使用的变量名，键值为对应的值。
     *                    - 示例：['name' => '张三', 'age' => 25]
     *
     * @param string|null $app 指定的应用名称 (可选).
     *                         - 若项目中支持多应用模式，此参数用于指定模板所属的应用。
     *                         - 传入 null 时默认使用当前应用的模板路径。
     *                         - 示例：'admin' 表示渲染 admin 应用的模板。
     *
     * @return string 渲染后的 HTML 内容.
     *                - 返回经过变量替换的完整 HTML 字符串。
     *                - 示例：若模板内容为 `<h1>{{ name }}</h1>`，传入 `['name' => '张三']`，返回结果为 `<h1>张三</h1>`。
     */
    public static function render(string $template, array $vars, ?string $app = null): string;
}