<?php

namespace ABPAM\Ekatte;

/**
 * Клас за работа с областите
 */
class Oblast extends Ekatte
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
     * @return object
     */
    protected static function db()
    {
        $db = self::getDB();

        array_walk($db, function (&$oblast) {
            unset($oblast['obshtina']);
        });

        return $db;
    }

    /**
     * Списък с областите
     * 
     * @return array
     */
    public static function getList()
    {
        return self::db();
    }

    /**
     * Информация за област по име (Пр. Бургас, Велико Търново)
     * 
     * @param string $name
     * @return array
     */
    public static function getByName($name) 
    {
        return self::getOblast('name', $name);
    }

    /**
     * Информация за област по трибуквен код (Пр. SHU, PDV)
     * 
     * @param string $code
     * @return array
     */
    public static function getByCode($code)
    {
        return self::getOblast('code', $code);
    }

    /**
     * Информация за областта по зададен критерий
     * 
     * @param string $criteria
     * @param string $criteriaValue
     * @return array
     */
    public static function getOblast($criteria, $criteriaValue)
    {
        $ekatte = new Ekatte();
        $db = self::db();

        $oblast = array_filter($db, function($oblast) use ($criteria, $criteriaValue) {
            return $oblast[$criteria] == $criteriaValue;
        });

        sort($oblast);

        return $oblast;
    }
}
