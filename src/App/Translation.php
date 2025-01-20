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

namespace App;

use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;
use Symfony\Component\Translation\Translator;
use App\Exception\NotFoundException;
use function basename;
use function config;
use function get_realpath;
use function pathinfo;
use function request;
use function substr;

/**
 * Class Translation
 * 翻译管理类，用于加载和管理多语言翻译资源。
 * 支持通过插件的方式扩展多语言翻译，同时支持动态调用翻译方法。
 *
 * @package support
 *
 * @method static string trans(?string $id, array $parameters = [], string $domain = null, string $locale = null)
 *         翻译方法，根据翻译 ID 和参数获取翻译内容。
 * @method static void setLocale(string $locale)
 *         设置当前语言环境。
 * @method static string getLocale()
 *         获取当前语言环境。
 */
class Translation
{
    /**
     * @var Translator[] 存储翻译器实例的数组，根据插件名称进行分组。
     */
    protected static array $instance = [];

    /**
     * 获取翻译器实例。
     *
     * @param string $app 应用插件名称，默认为空字符串表示全局翻译配置。
     * @return Translator 返回对应插件或全局的翻译器实例。
     * @throws NotFoundException 如果翻译文件路径不存在，则抛出异常。
     */
    public static function instance(string $app = ''): Translator
    {
        // 检查是否已创建该插件的翻译器实例
        if (!isset(static::$instance[$app])) {
            // 加载翻译配置
            $config = config($app ? "$app.translation" : 'translation', []);
            $paths  = (array)($config['path'] ?? []); // 翻译文件路径数组

            // 创建翻译器实例，并设置语言环境和后备语言
            static::$instance[$app] = $translator = new Translator($config['locale']);
            $translator->setFallbackLocales($config['fallback_locale']);

            // 定义支持的翻译文件类型及其加载器
            $classes = [
                'Symfony\Component\Translation\Loader\PhpFileLoader' => [
                    'extension' => '.php',
                    'format'    => 'phpfile'
                ],
                'Symfony\Component\Translation\Loader\PoFileLoader'  => [
                    'extension' => '.po',
                    'format'    => 'pofile'
                ]
            ];

            // 遍历翻译路径
            foreach ($paths as $path) {
                // 获取实际文件路径（支持 phar 文件）
                if (!$translationsPath = get_realpath($path)) {
                    throw new NotFoundException("File $path not found");
                }

                // 遍历支持的加载器
                foreach ($classes as $class => $opts) {
                    // 为翻译器添加文件加载器
                    $translator->addLoader($opts['format'], new $class);

                    // 遍历翻译文件目录，查找匹配的翻译文件
                    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($translationsPath, FilesystemIterator::SKIP_DOTS));
                    $files    = new RegexIterator($iterator, '/^.+' . preg_quote($opts['extension']) . '$/i', RegexIterator::GET_MATCH);

                    // 遍历翻译文件并加载资源
                    foreach ($files as $file) {
                        $file    = $file[0];
                        $domain  = basename($file, $opts['extension']); // 文件名作为 domain
                        $dirName = pathinfo($file, PATHINFO_DIRNAME); // 获取文件路径
                        $locale  = substr(strrchr($dirName, DIRECTORY_SEPARATOR), 1); // 获取语言环境

                        // 如果域名和语言环境有效，添加翻译资源
                        if ($domain && $locale) {
                            $translator->addResource($opts['format'], $file, $locale, $domain);
                        }
                    }
                }
            }
        }

        // 返回翻译器实例
        return static::$instance[$app];
    }

    /**
     * 静态调用方法，通过调用翻译器实例的方法实现动态调用。
     *
     * @param string $name 调用的方法名称。
     * @param array $arguments 方法的参数。
     * @return mixed 返回调用的结果。
     * @throws NotFoundException 如果翻译器实例不存在，则抛出异常。
     */
    public static function __callStatic(string $name, array $arguments)
    {
        // 获取当前请求中的插件名称
        $request = request();
        $app     = $request->app ?? '';

        // 调用对应插件的翻译器实例的方法
        return static::instance($app)->{$name}(...$arguments);
    }
}