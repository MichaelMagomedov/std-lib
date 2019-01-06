<?php
/**
 * Created by PhpStorm.
 * User: etovladislav
 * Date: 31.08.18
 * Time: 15:01
 */

namespace Booking\Stdlib\Localization\Service\Impl;


use Booking\Stdlib\Localization\Repository\LanguageRepository;
use Booking\Stdlib\Localization\Service\LanguageService;
use Illuminate\Http\Request;

class LanguageServiceImpl implements LanguageService
{
    /** @var  LanguageRepository */
    protected $languageRepository;

    /**
     * LanguageServiceImpl constructor.
     * @param LanguageRepository $languageRepository
     */
    public function __construct(LanguageRepository $languageRepository)
    {
        $this->languageRepository = $languageRepository;
    }


    /**
     * Получение текущего языка
     * @return int
     */
    public function getCurrentAppLanguage(): int
    {
        /** @var Request $request */
        $request = app()->get(Request::class);
        $languageId = $request->get('languageId');

        if (!isset($languageId)) {
            $routePrefix = $request->segment(1);
            if (isset($routePrefix)) {
                $language = $this->languageRepository->getLanguageByCode(mb_strtoupper($routePrefix));
                return (isset($language)) ? $language->getId() : config('localization.default.id');
            }
            return $languageId = config('localization.default.id');

        }

        return $languageId;
    }
}