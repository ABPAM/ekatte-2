<?php

namespace Utilities;

use Utilities\PclZip;
use Utilities\SimpleXLSX;
use Utilities\File;
use Utilities\EkatteArray;

/**
 * Клас на инсталационния скрипт
 */
class Installer
{

    /**
     * URL адресът на ZIP файла от НСИ
     * 
     * @var string
     */
    protected $url;

    /**
     * Root папката на пакета
     * 
     * @var string
     */
    protected $root;

    /**
     * Път до JSON архива (своего рода БД)
     * 
     * @var string
     */
    protected $json;

    /**
     * Конструктор
     * 
     * @param string $url
     */
    public function __construct($url)
    {
        $this->url = $url;
        $this->root = realpath(__DIR__.'/..');
        $this->json = $this->root.'/ekatte.json';
    }

    /**
     * Започваме инсталацията.
     * 
     * @return void
     */
    public function setup()
    {
        // Да определим какъв ще е метода, използван за изтегляне на ZIP-а от НСИ.
        $downloadMethod = $this->getDownloadMethod();

        switch ($downloadMethod) {
            case 'fopen':
                    $this->downloadFopen();
                break;

            case 'curl':
                    $this->downloadCurl();
                break;

            default:
                //
        }

        $this->extractEkatteZip();
    }

    /**
     * Определя метода на download на ZIP файла от НСИ
     * 
     * @return string
     * @throws Exception Няма подходящ метод.
     */
    protected function getDownloadMethod()
    {
        // Проверяваме дали е позволено отваряне на отдалечени файлове като файлов обект
        if (ini_get('allow_url_fopen') == 1) { // Използваме fopen
            return 'fopen';
        }

        if (function_exists('curl_version')) { // Използваме cURL
            return 'curl';
        }

        // Ако нито fopen, нито cURL могат да бъдат използвани
        throw new Exception('Не е намерен подходящ метод за изтегляне на файла от НСИ. Моля, провепете php.ini, дали cURL Extension е включен или allow_url_fopen параметъра е равен на "1"!');
    }

    /**
     * Изтегля ZIP файла от НСИ чрез fopen()
     * 
     * @return void
     */
    protected function downloadFopen()
    {
        $nsiZip = File::read($this->url);
        File::write($this->root.'/Ekatte.zip', $nsiZip);
    }

    /**
     * Изтегля ZIP файла от НСИ чрез cURL
     * 
     * @return void
     */
    protected function downloadCurl()
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        $fileContents = curl_exec($ch);
        curl_close($ch);

        File::write($this->root.'/Ekatte.zip', $fileContents);
    }

    /**
     * Разархивира ZIP файла от НСИ в root директорията на пакета,
     * създава JSON архива и изтрива разархивираните файлове и ZIP архива
     * 
     * @return void
     */
    protected function extractEkatteZip()
    {
        $rootArchive = new PclZip($this->root.'/Ekatte.zip');
        $rootArchive->extract($this->root.'/ekatte');

        $excelArchive = new PclZip($this->root.'/ekatte/Ekatte_xlsx.zip');
        $excelArchive->extract($this->root.'/XLSX');

        File::dirRemove($this->root.'/ekatte');
        File::remove($this->root.'/Ekatte.zip');

        // Създаваме JSON архива
        File::create($this->json);

        $this->addEntries();
        File::dirRemove($this->root.'/XLSX');
    }

    /**
     * Добавя нужните записи в JSON архива
     * 
     * @return void
     */
    protected function addEntries()
    {
        // Привеждаме данните от Ексел в удобен за PHP вид (парсваме).
        $oblastiArr  = SimpleXLSX::parse($this->root.'/XLSX/Ek_obl.xlsx')->rows();
        $obshtiniArr = SimpleXLSX::parse($this->root.'/XLSX/Ek_obst.xlsx')->rows();
        $kmetstvaArr = SimpleXLSX::parse($this->root.'/XLSX/Ek_atte.xlsx')->rows();

        // Премахваме антетките и други ненужни реквизити от екселските таблици
        array_shift($oblastiArr);
        array_shift($obshtiniArr);
        array_splice($kmetstvaArr, 0, 2);
        
        // Отваряме JSON файла (вж. $this->json) като обект. 
        $ekatteObj = File::readJSONasArray($this->json);

        /** 
         * Създаваме масив от области, взети от XLSX файла. Във всеки елемент (всяка област)
         * създаваме и празен масив, който да бъде попълнен с общини: 
         * [['code' => 'xxx', 'name' => 'xxxxxxxx', 'obshtina' => [] ]....]
         */
        $ekatteObj['oblast'] = array_map(function($row) {
            $oblast['code'] = $row[0];
            $oblast['name'] = $row[2];
            $oblast['obshtina'] = [];
            return $oblast;
        }, $oblastiArr);
        
        /**
         * Създаваме масив с всяка община:
         *['code' => 'xxxxx', 'tmp_code' => 'xxxxx', 'name' => 'xxxxxxxxx', 'kmetstvo' => []]
         * и добавяме и празен масив, който да бъде попълнен с кметствата.
         * Ключът tmp_code се взема от ЕКАТТЕ файла и служи като указател за 
         * разпределението на кметствата по общини. ЕКАТТЕ кодовете на общините не са последователни,
         * затова след разпределението на кметствата, ключът 'tmp_code' се изтрива.
         * Сортираме всяка община от XLSX файла във съответната област.
         */
        array_walk($obshtiniArr, function($obshtinaRow) use (&$ekatteObj) {
            $oblastCode = substr($obshtinaRow[0], 0, 3);
            $oblast = array_search($oblastCode, array_column($ekatteObj['oblast'], 'code'));
            
            // Взимаме последния елемент от подмасива 'obshtina' в масива 'oblast'...
            $lastElem = EkatteArray::getlastElem($ekatteObj['oblast'][$oblast]['obshtina']);

            /**
             * ... и определяме поредния номер на общината в областта (последен номер + 1).
             * Ако няма предходни елементи, поредният номер = 1.
             */
            $codeIndex = 
                (isset($lastElem['code'])) ? 
                    ((int) substr($lastElem['code'], -2)) + 1 : 
                    1;

            // Създаваме елемент $obshtina...
            $obshtina['code'] = $oblastCode . sprintf("%02d", $codeIndex);
            $obshtina['tmp_code'] = $obshtinaRow[0];
            $obshtina['name'] = $obshtinaRow[2];
            $obshtina['kmetstvo'] = [];
            
            // ... и го добавяме в съответната област.
            $ekatteObj['oblast'][$oblast]['obshtina'][] = $obshtina;
        });


        /**
         * Създаваме масив за всяко кметство:
         * ['code' => 'xxxxx', 'name' => 'xxxxxxxxx']
         * Сортираме всяко кметство от XLSX файла във съответната община.
         */
        array_walk($kmetstvaArr, function($kmetstvoRow) use (&$ekatteObj) {
            $oblastCode = $kmetstvoRow[3];
            $obshtinaCode = $kmetstvoRow[4]; // свързано с tmp_code, а не пореден номер, който слагаме ние.

            // Определяме областта и общината, в които се намира кметството.
            $oblast = array_search($oblastCode, array_column($ekatteObj['oblast'], 'code'));
            $obshtina = array_search($obshtinaCode, array_column($ekatteObj['oblast'][$oblast]['obshtina'], 'tmp_code'));

            // Взимаме последния елемент от подмасива 'kmetstvo' в масива 'obshtina'...
            $lastElem = EkatteArray::getlastElem($ekatteObj['oblast'][$oblast]['obshtina'][$obshtina]['kmetstvo']);

            /**
             * ... и определяме поредния номер на кметството в общината (последен номер + 1).
             * Ако няма предходни елементи, поредният номер = 1.
             */
            $codeIndex = 
                (isset($lastElem['code'])) ? 
                    ((int) substr($lastElem['code'], -3)) + 1 : 
                    1;
            
            // Създаваме елемент $kmetstvo...
            $kmetstvo['code'] = $ekatteObj['oblast'][$oblast]['obshtina'][$obshtina]['code'] . '-' . sprintf("%03d", $codeIndex);
            $kmetstvo['name'] = $kmetstvoRow[1] . ' ' . $kmetstvoRow[2];

            // ... и го добавяме в съответната община.
            $ekatteObj['oblast'][$oblast]['obshtina'][$obshtina]['kmetstvo'][] = $kmetstvo;
        });

        /**
         * Ако някой намери по-елегантен начин за решаване на задачата,
         * много ще се радвам да направи Pull request ;)
         * 
         *                                      (ABPAM)
         */

        // Премахваме полето tmp_code от общините
        foreach($ekatteObj['oblast'] as &$oblast) {
            foreach($oblast['obshtina'] as &$obshtina) {
                unset($obshtina['tmp_code']);
            }
        }


        // Записваме готовия масив в JSON формат, във файла, зададен в началото на този (вж. $this->json).
        File::write($this->json, json_encode($ekatteObj, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    }
}
