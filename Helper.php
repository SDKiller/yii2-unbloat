<?php
/**
 * @author      Serge Postrash aka SDKiller <jexy.ru@gmail.com>
 * @copyright   Copyright (c) 2014 Serge Postrash aka SDKiller
 * @license     BSD-3-Clause
 */

namespace zyx\unbloat;

use Composer\Script\CommandEvent;
use yii\helpers\FileHelper;
use yii\helpers\StringHelper;


class Helper
{
    /**
     * Deletes files and directories listed in 'extra' section of root composer.json under 'ignore'
     * @param CommandEvent $event
     */
    public static function postCmd($event)
    {
        echo 'Running post-cmd' . PHP_EOL;

        $extra = $event->getComposer()->getPackage()->getExtra();

        if (array_key_exists('ignore', $extra) && !empty($extra['ignore']) && is_array($extra['ignore'])) {

            foreach ($extra['ignore'] as $extension => &$ignored) {

                if (empty ($ignored)) {
                    echo 'Ignored attribute is empty for package: "' . $extension .'"' . PHP_EOL;
                    continue;
                }

                $extension_path = static::getPackagePath($extension);
                if ($extension_path === false) {
                    echo 'Directory not found for package: "' . $extension .'"' . PHP_EOL;
                    continue;
                }

                if (is_string($ignored)) {
                    if ($ignored === 'type::bower') {
                        $bower_ignored = [];
                        //lookup for bower.json file of extension
                        $bower_path = FileHelper::normalizePath($extension_path . '/bower.json');
                        if (is_file($bower_path)) {
                            $bower_ignored = static::parseBower($bower_path);
                        } else {
                            echo 'File bower.json not found for package: "' . $extension .'"' . PHP_EOL;
                        }

                        if (empty($bower_ignored)) {
                            echo 'Could not parse bower.json or empty ignored attribute for package: "' . $extension . '"' . PHP_EOL;
                            continue;
                        }

                        //exclude composer.json from bower.json 'ignored'
                        $i = array_search('composer.json', $bower_ignored);
                        if ($i !== false) {
                            unset($bower_ignored[$i]);
                        }
                        $ignored = $bower_ignored;
                    }
                    if (!is_array($ignored)) {
                        $ignored = (array) $ignored;
                    }
                }

                echo 'Processing ignored list for package: "' . $extension .'" ...' . PHP_EOL;

                $dirs = [];
                $files = [];
                $excluded_files = [];

                foreach ($ignored as $item) {
                    if (strpos($item, '!') === 0) {
                        //excluded in 'ignore'
                        $pattern = StringHelper::byteSubstr($item, 1, StringHelper::byteLength($item));
                        if (strpos($pattern, '*') !== false) {
                            $fs = FileHelper::findFiles($extension_path, ['only' => [$pattern]]);
                            if (!empty($fs) && is_array($fs)) {
                                foreach ($fs as $f) {
                                    $excluded_files[] = $f;
                                }
                            }
                            //TODO - FileHelper has no method to find directories against wilcard
                        } else {
                            $path = FileHelper::normalizePath($extension_path . '/' . $pattern);
                            if (is_file($path) && !is_link($path)) {
                                $excluded_files[] = $path;
                            }
                        }
                    } else {
                        $pattern = $item;
                        if (strpos($pattern, '*') !== false) {
                            $fs = FileHelper::findFiles($extension_path, ['only' => [$pattern]]);
                            if (!empty($fs) && is_array($fs)) {
                                foreach ($fs as $f) {
                                    $files[] = $f;
                                }
                            }
                            //TODO - FileHelper has no method to find directories against wilcard
                        } else {
                            $path = FileHelper::normalizePath($extension_path . '/' . $pattern);
                            if (is_file($path) && !is_link($path)) {
                                $files[] = $path;
                            } elseif (is_dir($path)) {
                                $dirs[] = $path;
                            }
                        }
                    }
                }

                foreach ($dirs as $dir) {
                    FileHelper::removeDirectory($dir);
                    clearstatcache();
                    if (is_dir($dir)) {
                        echo 'Failed to remove directory: ' . $dir . PHP_EOL;
                    }
                }

                foreach ($files as $file) {
                    if (!in_array($file, $excluded_files, true)) {
                        if (!unlink($file)) {
                            echo 'Failed to remove file: ' . $file . PHP_EOL;
                        }
                    }
                }

                echo '... done' . PHP_EOL;
            }

        } else {

            echo 'Ignore section of composer.json is empty' . PHP_EOL;
        }
    }

    public static function getPackagePath($name)
    {
        $vendorDir = dirname(dirname(dirname(__FILE__)));

        $path = FileHelper::normalizePath($vendorDir . '/'. $name);

        if (is_dir($path)) {
            return $path;
        }

        return false;
    }

    public static function parseBower($path)
    {
        $result = [];
        $buff = @file_get_contents($path, FILE_TEXT);
        $arr = @json_decode($buff, true);
        if (!empty($arr) && is_array($arr)) {
            if (array_key_exists('ignore', $arr) && !empty($arr['ignore'])) {
                $result = $arr['ignore'];
                if (!is_array($result)) {
                    $result = (array) $result;
                }
            }
        }

        return $result;
    }

}
