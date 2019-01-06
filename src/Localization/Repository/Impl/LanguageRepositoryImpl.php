<?php
/**
 * Created by PhpStorm.
 * User: etovladislav
 * Date: 30.08.18
 * Time: 15:47
 */

namespace Booking\Stdlib\Localization\Repository\Impl;


use App\Module\Option\Entity\LanguageEntity;
use App\Module\Option\Model\LanguageModel;
use Booking\Stdlib\Localization\Repository\LanguageRepository;
use Illuminate\Support\Collection;
use Structure\Base\Model\Model;
use Structure\Base\Repository\Repository;
use Structure\Base\Repository\Traits\Deleteable;
use Structure\Base\Repository\Traits\Saveable;
use Structure\Base\Repository\Traits\Updateable;

class LanguageRepositoryImpl extends Repository implements LanguageRepository
{
    use Updateable;
    use Deleteable;
    use Saveable;

    protected $modelClassName = LanguageModel::class;

    /** @var Model */
    protected $model;

    /**
     * Поиск языка по коду (RU) ISO
     * @param string $code
     * @return LanguageEntity
     */
    public function getLanguageByCode(string $code): ?LanguageEntity
    {
        //todo: bugfix for hydrator when eloquent use recursion for construct relationships
        $queryModel = $this->model->newInstance();
        $queryModel->setEntityClassName(null);
        $result = $queryModel->whereCode($code)->first();
        if ($result != null) {
            return new LanguageEntity($result->toArray());
        }
        return null;
    }

}