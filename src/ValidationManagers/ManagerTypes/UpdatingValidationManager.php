<?php

namespace CRUDServices\ValidationManagers\ManagerTypes;

use CRUDServices\CRUDComponents\CRUDRelationshipComponents\RelationshipComponent;
use CRUDServices\ValidationManagers\ValidationManager;
use Illuminate\Database\Eloquent\Model;


class UpdatingValidationManager extends ValidationManager
{
    protected Model $model ;

    /**
     * @param Model $model
     * @return $this
     */
    public function setModel(Model $model): UpdatingValidationManager
    {
        $this->model = $model;
        return $this;
    }

    protected function primaryKeyHandling(array $singleDataRow = [] , ?Model $model = null) : array
    {
        if($model)
        {
            $singleDataRow[$model->getKeyName()] = $model->getKey();
        }
        return $singleDataRow;
    }
    public function validateSingleModelRowKeys(array $singleDataRow = []): ValidationManager
    {
        $singleDataRow = $this->primaryKeyHandling($singleDataRow , $this->model);
        return parent::validateSingleModelRowKeys($singleDataRow);
    }
    public function validateRelationshipSingleRowKeys(RelationshipComponent $relationship, array $singleDataRow = [] , ?Model $relationshipModel = null): ValidationManager
    {
        $singleDataRow = $this->primaryKeyHandling($singleDataRow , $relationshipModel);
        return parent::validateRelationshipSingleRowKeys($relationship, $singleDataRow);
    }
}
