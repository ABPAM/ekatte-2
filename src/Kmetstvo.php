<?php

namespace ABPAM\Ekatte;

use ABPAM\Ekatte\Oblast;
use ABPAM\Ekatte\Obshtina;
use Utilities\EkatteArray;

class Kmetstvo extends Ekatte 
{
    /**
     * Конструктор
     */
    public function __construct()
    {
        //
    }

    /**
     * Взема JSON архива
     * 
     * @return array
     */
    protected static function db()
    {
        $db = self::getDB();

        return $db;
    }

    /**
     * Списък на всички кметства
     * 
     * @return array
     */
    public static function getList()
    {
        // Изкарваме кметствата от масива с областите и общините, правейки нов масив, съдържащ САМО списък с кметствата
        $kmetstvaList = array_map(function($oblast) {
            return array_map(function ($obshtina) {
                return $obshtina['kmetstvo'];
            }, $oblast['obshtina']);
        }, self::db());

        // „Изравняваме“ масива
        $result = array_merge([], ...$kmetstvaList);
        $result = array_merge([], ...$result);

        // Подреждаме кметствата по азбучен ред (според името)
        usort($result, function($a, $b) {
            $first = explode('. ', $a['name']);
            $second = explode('. ', $b['name']);
            return strcmp($first[1], $second[1]);
        });

        return $result;
    }


    /**
     * Списък на всички кметства в дадена област по код (Пр.: „VAR“, „MON“)
     * 
     * @param string $code
     * @return array
     */
    public static function getListByOblastCode($code)
    {
        return self::getListByOblast('code', $code);
    }

    /**
     * Списък на всички кметства в дадена област по име (Пр.: „Търговище“, „Пазарджик“)
     * 
     * @param string $name
     * @return array
     */
    public static function getListByOblastName($name)
    {
        return self::getListByOblast('name', $name);
    }

    /**
     * Списък на кметствата в дадена област по зададен критерий
     * 
     * @param string $criteria
     * @param string $criteriaValue
     * @return array
     */
    protected static function getListByOblast($criteria, $criteriaValue)
    {
        $db = self::db();

        // Вземаме цялата област
        $kmetstvaList = array_filter($db, function ($oblast) use ($criteria, $criteriaValue) {
            return ($oblast[$criteria] == $criteriaValue);
        });

        // Сортираме масива, за да започва от „0“
        sort($kmetstvaList);

        /**
         * Вземаме всички кметства от всички общини, след което сливаме
         * „разгънатия“ масив с празен такъв, с цел „изравняване“ (всички 
         * кметства да дойдат на 1-во ниво в масива)
         */
        $result = array_column($kmetstvaList[0]['obshtina'], 'kmetstvo');
        $result = array_merge([], ...$result);

        // Сортираме списъка по азбучен ред
        usort($result, function($a, $b) {
            $first = explode('. ', $a['name']);
            $second = explode('. ', $b['name']);
            return strcmp($first[1], $second[1]);
        });

        return $result;
    }

    /**
     * Списък на кметствата в дадена община по код (Пр.: „BGS02“, „RAZ05“)
     * 
     * @param string $code
     * @return array
     */
    public static function getListByObshtinaCode($code)
    {
        return self::getListByObshtina('code', $code);
    }

    /**
     * Списък на кметствата в дадена община по име (Пр.: „Опака“, „Свищов“)
     * 
     * @param string $name
     * @return array
     */
    public static function getListByObshtinaName($name)
    {
        return self::getListByObshtina('name', $name);
    }

    /**
     * Списък на кметствата в дадена община по зададен критерий
     * 
     * @param string $criteria
     * @param string $criteriaValue
     * @return array
     */
    protected static function getListByObshtina($criteria, $criteriaValue)
    {
        $db = self::db();

        foreach(self::db() as $oblast) {
            foreach($oblast['obshtina'] as $obshtina) {
                // Вземаме всички кметства от общината
                if($obshtina[$criteria] == $criteriaValue) {
                    $result = $obshtina['kmetstvo'];
                }
            }
        }

        // Сортираме списъка, за да започва от „0“
        sort($result);

        // Подреждаме списъка по азбучен ред
        usort($result, function($a, $b) {
            $first = explode('. ', $a['name']);
            $second = explode('. ', $b['name']);
            return strcmp($first[1], $second[1]);
        });

        return $result;
    }

    
    /**
     * Информация за кметство по име (Пр.: „с. Аврамово“, „Волуяк“)
     * Забележка: При търсене на кметство по име, не е задължително
     * изписването на „гр.“ или „с.“ пред името.
     * 
     * @param string $name
     * @return array
     */
    public static function getByName($name)
    {
        return self::getKmetstvo('name', $name);
    }

    /**
     * Информация за кметство по код (Пр.: „GAB03-018“, „PVN02-003“)
     * 
     * @param string $name
     * @return array
     */
    public static function getByCode($code)
    {
        return self::getKmetstvo('code', $code);
    }

    /**
     * Информация за кметството по зададен критерий
     * 
     * @param string $criteria
     * @param string $criteriaValue
     * @return array
     */
    public static function getKmetstvo($criteria, $criteriaValue)
    {
        $db = self::db();
        $result = [];

        /**
         * Ако някой намери по-елегантен начин за решаване на задачата,
         * много ще се радвам да направи Pull request ;)
         * 
         *                                      (ABPAM)
         */

        // Минаваме през всяка област
        foreach($db as $oblast) {
            // Минаваме през всяка община в съответната област
            foreach($oblast['obshtina'] as $obshtina) {
                // Минаваме през всяко кметство в съответната община
                foreach($obshtina['kmetstvo'] as $kmetstvo) {
                    
                    // По код или по име ще търсим
                    switch($criteria) {
                        // По име
                        case 'name':
                            
                            // Създаваме regex за търсене на префикс („гр.“, „с.“ и т.н.)
                            $pattern = "/^(.*\.\s?)/";
                            // Вземаме префикса, ако съществува
                            preg_match($pattern, $criteriaValue, $prefix);
                            // Разбиваме търсеното име на префикс и име
                            $name = preg_split($pattern, $criteriaValue);
                            
                            // Добавяме данни за областта и общината
                            $kmetstvo['oblast_name'] = $oblast['name'];
                            $kmetstvo['oblast_code'] = $oblast['code'];
                            $kmetstvo['obshtina_name'] = $obshtina['name'];
                            $kmetstvo['obshtina_code'] = $obshtina['code'];

                            /**
                             * Задал ли е потребителя префикс. Ако е задал, $name има поне два елемента.
                             * В противен случай, след preg_split() $name ще съдържа само $name[0]
                             */
                            if(isset($name[1])) {
                                // Изчистваме интервалите началото и края на префикса, без значение дали потребителя е сложил такъв, или не
                                $prefix[0] = trim($prefix[0]);
                                // Изчистваме интервалите и в началото и края на името
                                $name[1]   = trim($name[1]);

                                if($kmetstvo['name'] == $prefix[0] . " " . $name[1]) { // За да сме сигурни, че добавяме САМО 1 интервал
                                    $result[] = $kmetstvo;
                                }
                            } else {
                                // Ако няма префикс, от JSON архива вземаме само името на кметството, без префикса
                                $kmetstvoName = explode(". ", $kmetstvo['name'])[1];

                                if($criteriaValue == $kmetstvoName) {
                                    $result[] = $kmetstvo;
                                }
                            }
                            break;
                        
                        // По код
                        case 'code':
                            // Добавяме данни за областта и общината
                            $kmetstvo['oblast_name'] = $oblast['name'];
                            $kmetstvo['oblast_code'] = $oblast['code'];
                            $kmetstvo['obshtina_name'] = $obshtina['name'];
                            $kmetstvo['obshtina_code'] = $obshtina['code'];

                            // Ако търсения код съвпада с кода на кметството подред
                            if($kmetstvo['code'] == $criteriaValue) {
                                $result[] = $kmetstvo;
                            }
                            break;
                        
                        // Нямаме критерий, връщаме празен резултат
                        default:
                            $result = null;
                    }
                }
            }
        }

        return $result;
    }

}
