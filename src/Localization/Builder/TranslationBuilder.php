<?php
/**
 * Created by PhpStorm.
 * User: etovladislav
 * Date: 29.08.18
 * Time: 15:37
 */

namespace Booking\Stdlib\Localization\Builder;


use Booking\Stdlib\Localization\Service\LanguageService;
use Booking\Stdlib\Localization\Utils\TranslationUtil;
use Structure\Base\Model\Builder;

class TranslationBuilder extends Builder
{
    protected $model;


    public function update(array $values)
    {
        $this->model->fill($values);
        $result = parent::update($this->model->getAttributes());

        $rootFKVal = $values[$this->model->getKeyName()] ?? null;
        if (isset($values['language_id']) && isset($rootFKVal)) {
            $this->model->setAttribute($this->model->getKeyName(), $rootFKVal);

            $translationInstance = TranslationUtil::createTranslationModelInstance($values, $this->model);
            $builder = $translationInstance->whereLanguageId($values['language_id'])
                ->where(
                    $this->model->getForeignKeyForTable(),
                    $rootFKVal
                );

            if (!$builder->exists()) {
                $translationInstance->save();
            } else {
                $builder->update($translationInstance->getAttributes());
            }

        }
        return $result;
    }

    public function withTranslation(int $languageId = null)
    {
        if ($languageId == null) {
            $languageId = $this->getCurrentLocale();
        }
        $translationTableName = $this->model->getTranslationTableName();
        $foreignKeyForTable = $this->model->getForeignKeyForTable();
        $baseTableName = $this->model->getTable();

        return $this->leftJoin(
            $this->model->getTranslationTableName(),
            function ($join) use ($translationTableName, $foreignKeyForTable, $baseTableName, $languageId) {
                $join->on(
                    $baseTableName . '.id',
                    '=',
                    $translationTableName . '.' . $foreignKeyForTable
                )->whereRaw(
                    "$translationTableName.language_id = 
                        (CASE WHEN EXISTS(select * from $translationTableName where language_id = $languageId)
                         THEN $languageId
                        ELSE 2
                        END)
                    "
                );
            }
        )->select(
            $translationTableName . '.*',
            $baseTableName . '.*'
        );
    }

    protected function getCurrentLocale(): int
    {
        /** @var LanguageService $languageService */
        $languageService = app()->get(LanguageService::class);
        return $languageService->getCurrentAppLanguage();
    }
}