<?php
/**
 * Created by PhpStorm.
 * User: etovladislav
 * Date: 30.08.18
 * Time: 15:45
 */

namespace Booking\Stdlib\Localization\Repository;


use App\Module\Option\Entity\LanguageEntity;
use Illuminate\Support\Collection;
use Structure\Base\Entity\Entity;

interface LanguageRepository
{

    /**
     * Поиск языка по коду (RU)
     * @param string $code
     * @return LanguageEntity
     */
    public function getLanguageByCode(string $code): ?LanguageEntity;

}