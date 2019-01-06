<?php
/**
 * Created by PhpStorm.
 * User: etovladislav
 * Date: 29.08.18
 * Time: 15:53
 */

namespace Booking\Stdlib\Localization\Utils;


use Structure\Base\Model\Model;

class TranslationUtil
{

    /**
     * Создает новый экземпляр модели перевода
     * @param array $attributes
     * @param Model $model
     * @return Model
     * @internal param $parentId
     */
    public static function createTranslationModelInstance(array $attributes = [], Model $model): Model
    {
        $translationModel = new Model();
        $translationModel->setTable($model->getTranslationTableName());
        $translationModel->fillable($model->getTranslatableFields());
        $translationModel->fill($attributes);
        $translationModel->setAttribute('language_id', $attributes['language_id']);
        $translationModel->setAttribute($model->getForeignKeyForTable(), $model->id);
        return $translationModel;
    }
}