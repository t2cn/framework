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
        // 计算主项目根目录（基于当前目录）
        $basePath = realpath(__DIR__ . '/../../../'); // 定位到主项目根目录
        echo "Base path calculated as: $basePath\r\n";

        foreach (static::$pathRelation as $source => $dest) {
            // 源文件的绝对路径
            $sourceFile = realpath(__DIR__ . DIRECTORY_SEPARATOR . $source);
            // 目标文件的绝对路径
            $destPath = $basePath . DIRECTORY_SEPARATOR . ltrim($dest, DIRECTORY_SEPARATOR);

            // 输出调试信息
            echo "Source file: $sourceFile\r\n";
            echo "Destination path: $destPath\r\n";

            if (!$sourceFile || !file_exists($sourceFile)) {
                echo "Source file $sourceFile does not exist.\r\n";
                continue;
            }

            // 确保目标目录存在 1
            $destDir = dirname($destPath);
            if (!is_dir($destDir)) {
                mkdir($destDir, 0777, true);
            }

            // 复制文件
            if (copy($sourceFile, $destPath)) {
                echo "File $source copied to $destPath\r\n";
            } else {
                echo "Failed to copy $source to $destPath\r\n";
            }
        }
    }
}