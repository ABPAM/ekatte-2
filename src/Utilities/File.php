<?php

namespace Utilities;

/**
 * Клас за файлови операции
 */
class File
{

    /**
     * Създава файл
     * 
     * @param string $file
     * @return boolean
     */
    public static function create($file)
    {
        return touch($file);
    }

    /**
     * Прочита файл
     * 
     * @param string $file
     * @return string
     */
    public static function read($file)
    {
        $content = '';
        $handle = fopen($file, 'r');
        while(!feof($handle)) {
            $content .= fread($handle, 1);
        }
        fclose($handle);

        return $content;
    }

    /**
     * Прочита файл като масив
     * 
     * @param string $file
     * @return array
     */
    public static function readAsArray($file)
    {
        return file($file);
    }

    /**
     * Прочита JSON файл като обект
     * 
     * @param string $jsonFile
     * @return object
     */
    public static function readJSONasObject($jsonFile)
    {
        $content = self::read($jsonFile);
        $obj = (object) $content;

        if (property_exists($obj, 'scalar')) {
            unset($obj->scalar);
        }

        return $obj;
    }

    public static function readJSONasArray($jsonFile)
    {
        $content = self::read($jsonFile);
        return json_decode($content, true);
    }

    /**
     * 
     */
    public static function readObj($jsonFile)
    {
        $content = self::read($jsonFile);
        $obj = (object) json_decode($content, false);

        return $obj;
    }

    /**
     * Записва съдържание във файл
     * 
     * @param string $file
     * @param string $data
     * @param string $mode - по подразбиране изтрива съдържанието на файла и записва новото
     */
    public static function write($file, $data, $mode = 'w')
    {
        $handle = fopen($file, $mode);
        $write = fwrite($handle, $data);
        fclose($handle);

        return $write;
    }

    /**
     * Изтрива файл
     * 
     * @param string $file
     * @return boolean
     */
    public static function remove($file)
    {
        return unlink($file);
    }

    /**
     * Изтрива папка, барабар с подпапките
     * 
     * @param string $dir
     */
    public static function dirRemove($dir)
    {
        if (!file_exists($dir)) {
            return true;
        }
        
        if (!is_dir($dir)) {
            return unlink($dir);
        }
        
        foreach (scandir($dir) as $item) {
            if ($item == '.' || $item == '..') {
                continue;
            }
        
            if (!self::dirRemove($dir . DIRECTORY_SEPARATOR . $item)) {
                return false;
            }
        
        }
        
        return rmdir($dir);
    }
}