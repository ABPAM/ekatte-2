<?php

namespace ABPAM\Ekatte;

use Utilities\Installer;
use Utilities\File;


/**
 * Основния клас на пакета, който бива extend-нат от 
 * другите класове - Oblast, Obshtina и Kmetstvo
 */
class Ekatte
{

    /**
     * Съдържанието на JSON архива
     * 
     * @var string
     */
    protected static $ekatteDB;

    /**
     * Конструктор
     */
    public function __construct()
    {
        // 
    }

    /**
     * Инсталационна функция
     */
    public static function setup()
    {
        // Проверяваме дали средата на изпълнение е „конзолна“ (CLI)
        if (PHP_SAPI != 'cli') {
            echo "[EN]\n";
            echo "Ekatte::setup() is a command line interface (cli) utility and can ONLY\n";
            echo "be run through the command line. It seems this is not the case.\n\n";
            echo "Aborting...\n\n";

            echo "[BG]\n";
            echo "Ekatte::setup() е инструмент, който може да бъде използван САМО „под конзола“.\n";
            echo "Изглежда, в случая, това не е така. Уверете се, че стартирате скрипта от командния ред.\n\n";
            echo "Прекратяване...\n\n";

            exit(1);
        }

        /**
         * Изчистваме от къвичките Environment променливата, съдържаща URL на ZIP файла от НСИ, 
         * защото поради необясними за мен причини, Composer я set-ва със все къвички
         */
        $url = trim(getenv('EKATTE_URL'), '"');
        
        // Стартираме същинската setup функция в Installer.php
        $installer = new Installer($url);
        $installer->setup();

        echo "ЕКАТТЕ беше успешно инсталиран." . PHP_EOL;
    }

    /**
     * Прочита JSON архива
     * 
     * @return object
     */
    protected static function getDB()
    {
        self::$ekatteDB = File::readJSONasArray(__DIR__.'/ekatte.json');
        return self::$ekatteDB['oblast'];
    }
}
