<?php
/**
 * Created by PhpStorm.
 * User: etovladislav
 * Date: 31.08.18
 * Time: 15:00
 */

namespace Booking\Stdlib\Localization\Service;


interface LanguageService
{

    /**
     * Получение текущего языка
     * @return int
     */
    public function getCurrentAppLanguage(): int;
}