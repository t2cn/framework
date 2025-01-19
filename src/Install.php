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
        // 获取项目根目录
        $baseDir = base_path();

        // 输出调试信息
        echo "Base path calculated as: $baseDir\r\n";

        foreach (static::$pathRelation as $source => $dest) {
            // 获取源文件的绝对路径
            $sourceFile = realpath(__DIR__ . '/../src/' . $source);
            // 目标文件的绝对路径
            $destPath = $baseDir . DIRECTORY_SEPARATOR . $dest;

            // 输出调试信息，确保路径正确
            echo "Source file: $sourceFile\r\n";
            echo "Destination path: $destPath\r\n";

            // 检查源文件是否存在
            if (!$sourceFile || !file_exists($sourceFile)) {
                echo "Source file $sourceFile does not exist.\r\n";
                continue;
            }

            // 确保目标目录存在
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