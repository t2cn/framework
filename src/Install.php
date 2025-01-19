<?php

namespace T2;

class Install
{
    const bool T2_INSTALL = true;

    /**
     * 文件与目标路径的映射
     * @var array
     */
    protected static array $pathRelation = [
        'start.php'   => 'start.php',
        'windows.php' => 'windows.php',
        'windows.bat' => 'windows.bat',
    ];

    /**
     * 安装方法
     * @return void
     */
    public static function install(): void
    {
        static::installByRelation();
    }

    /**
     * 根据文件关系安装
     * @return void
     */
    public static function installByRelation(): void
    {
        // 获取正确的目标基础目录
        $baseDir = dirname(__DIR__, 4); // 通过调整 dirname 回溯到 t2engine 目录

        foreach (static::$pathRelation as $source => $dest) {
            // 获取源文件的绝对路径
            $sourceFile = realpath(__DIR__ . '/../src/' . $source);

            // 拼接目标文件的绝对路径
            $destPath = $baseDir . DIRECTORY_SEPARATOR . $dest;

            // 检查源文件是否存在
            if (!$sourceFile || !file_exists($sourceFile)) {
                echo "Source file $sourceFile does not exist.\r\n";
                continue;
            }

            // 移动文件
            static::moveFile($sourceFile, $destPath, $dest);
        }
    }

    /**
     * 移动文件的功能
     * @param string $sourceFile 源文件路径
     * @param string $destPath 目标文件路径
     * @param string $dest 文件名称，用于输出
     * @return void
     */
    protected static function moveFile(string $sourceFile, string $destPath, string $dest): void
    {
        // 如果目标文件已存在，可以选择覆盖或重命名
        if (file_exists($destPath)) {
            return;
        }

        // 执行移动操作
        if (rename($sourceFile, $destPath)) {
            echo "Create $dest\r\n";  // 成功时输出 Create 文件名
        } else {
            echo "Create $dest fail\r\n";  // 成功时输出 Create 文件名
        }
    }
}