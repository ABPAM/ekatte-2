<?php

namespace ABPAM\Ekatte;

use ABPAM\Ekatte\Oblast;
use Utilities\EkatteArray;


/**
 * Клас за работа с общините
 */
class Obshtina extends Ekatte 
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

        // Изчистваме кметствата във всяка община
        array_walk($db, function(&$oblast) {
            array_walk($oblast['obshtina'], function(&$obshtina) {
                unset($obshtina['kmetstvo']);
            });
        });

        return $db;
    }

    /**
     * Списък на всички общини
     * 
     * @return array
     */
    public static function getList()
    {
        // Изкарваме общините от масива с областите, правейки нов масив, съдържащ САМО списък с общините
        $obshtiniList = array_map(function($oblast) {
            return $oblast['obshtina'];
        }, self::db());

        // Правим масива на едно ниво
        $result = [];
        foreach ($obshtiniList as $key => $value) {
            if (is_array($value)) {
                $result = array_merge($result, $value);
            } else {
                $result[$key] = $value;
            }
        }

        // Подреждаме общините по азбучен ред (според името)
        usort($result, function($a, $b) {
            return strcmp($a['name'], $b['name']);
        });

        return $result;
    }


    /**
     * Информация за община по име (Пр.: „Елена“, „Симитли“)
     * 
     * @param string $name
     * @return array
     */
    public static function getByName($name)
    {
        return self::getObshtina('name', $name);
    }


    /**
     * Информация за община по код (Пр.: „RSE08“, „PER02“)
     * 
     * @param string $code
     * @return array
     */
    public static function getByCode($code)
    {
        return self::getObshtina('code', $code);
    }

    /**
     * Информация за общината по зададен критерий
     * 
     * @param string $criteria
     * @param string $criteriaValue
     * @return array
     */
    protected static function getObshtina($criteria, $criteriaValue)
    {
        // Вземаме списък на областите с общините във всяка област
        $db = self::db();
        $result = [];

        // По код или по име на областта ще търсим
        switch($criteria) {
            case 'code': // По код
                array_walk($db, function($oblast) use ($criteriaValue, &$result) {
                    if($oblast['code'] == substr($criteriaValue, 0, -2)) {
                        array_walk($oblast['obshtina'], function ($obshtina) use ($oblast, $criteriaValue, &$result) {
                            if($obshtina['code'] == $criteriaValue) {
                                $obshtina['oblast_code'] = $oblast['code'];
                                $obshtina['oblast_name'] = $oblast['name'];
                                $result = $obshtina;
                            }
                        });
                    }
                });
                break;

            case 'name': // По име
                array_walk($db, function($oblast) use ($criteriaValue, &$result) {
                    array_walk($oblast['obshtina'], function ($obshtina) use ($oblast, $criteriaValue, &$result) {
                        if($obshtina['name'] == $criteriaValue) {
                            $obshtina['oblast_code'] = $oblast['code'];
                            $obshtina['oblast_name'] = $oblast['name'];
                            $result = $obshtina;
                        }
                    });
                });
                break;

            default: // Ако няма зададен критерий, връщаме празен резултат
                $result = null;
                break;
        }

        return $result;
    }

    /**
     * Списък на общините в област, търсена по код (Пр.: „VRC“, „KRZ“)
     * 
     * @param string $code
     * @return array
     */
    public static function getListByOblastCode($code)
    {
        return self::getListByOblast('code', $code);
    }

    /**
     * Списък на общините в област, търсена по име (Пр.: „Варна“, „Смолян“)
     * 
     * @param string $name
     * @return array
     */
    public static function getListByOblastName($name)
    {
        return self::getListByOblast('name', $name);
    }

    /**
     * Списък на общините в дадена област по зададен критерий
     * 
     * @param string $criteria
     * @param string $criteriaValue
     * @return array
     */
    protected static function getListByOblast($criteria, $criteriaValue)
    {
        $db = self::db();
        $obshtiniList = array_filter($db, function($oblast) use ($criteria, $criteriaValue) {
            return ($oblast[$criteria] == $criteriaValue);
        });

        usort($obshtiniList, function($a, $b) {
            return strcmp($a['name'], $b['name']);
        });

        return $obshtiniList[0]['obshtina'];
    }
}