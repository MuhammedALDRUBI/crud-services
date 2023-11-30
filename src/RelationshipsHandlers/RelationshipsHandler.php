<?php

namespace CRUDServices\RelationshipsHandlers;


use CRUDServices\CRUDComponents\CRUDRelationshipComponents\RelationshipComponent;
use CRUDServices\Interfaces\OwnsRelationships;
use CRUDServices\Interfaces\ParticipatesToRelationships;
use CRUDServices\RelationshipsHandlers\Traits\OwnedRelationshipMethods;
use CRUDServices\RelationshipsHandlers\Traits\ParticipatingRelationshipMethods;
use CRUDServices\ValidationManagers\ManagerTypes\StoringValidationManager;
use CRUDServices\ValidationManagers\ValidationManager;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

abstract class RelationshipsHandler
{
    use OwnedRelationshipMethods , ParticipatingRelationshipMethods;

    protected ?ValidationManager $validationManager = null;

    /**
     * @param array $dataRow
     * @param RelationshipComponent $relationship
     * @param ?Model $relationshipModel
     * @return void
     * @throws Exception
     */
    protected function validateRelationshipSingleRowKeys(array $dataRow , RelationshipComponent $relationship , ?Model $relationshipModel = null) : void
    {
        $this->initValidationManager()->validateRelationshipSingleRowKeys( $relationship , $dataRow , $relationshipModel);
    }
    /**
     * @return ValidationManager
     * override it from child class if it is needed
     */
    protected function getDefaultValidationManager() : ValidationManager
    {
        return StoringValidationManager::Singleton();
    }

    /**
     * @param ValidationManager|null $validationManager
     * @return $this
     */
    public function setValidationManager(?ValidationManager $validationManager = null): RelationshipsHandler
    {
        if(!$validationManager){$validationManager = $this->getDefaultValidationManager();}
        $this->validationManager = $validationManager;
        return $this;
    }
    protected function initValidationManager() : ValidationManager
    {
        if(!$this->validationManager){$this->setValidationManager();}
        return $this->validationManager;
    }

    protected function isItMultiRowedArray(mixed $array): bool
    {
        return Arr::isList($array) && is_array(Arr::first($array));
    }

    protected function getRelationshipRequestDataArray(array $dataRow ,string $relationship ) : array | null
    {
        if(array_key_exists($relationship , $dataRow) && is_array($dataRow[$relationship]) )
        {
            return $dataRow[$relationship];
        }
        return null;
    }

    protected function getRelationshipModelInstance(Model $model , string $relationship , array $dataArrayToSet = []) : Model
    {
        return $model->{$relationship}()->make($dataArrayToSet);
    }

    protected function getRelationshipRequestData(array $dataRow, string $relationship) : array | null
    {
        $RelationshipRequestDataArray = $this->getRelationshipRequestDataArray($dataRow, $relationship);
        if(!$RelationshipRequestDataArray){ return [];}
        return $this->isItMultiRowedArray($RelationshipRequestDataArray) ? $RelationshipRequestDataArray : [$RelationshipRequestDataArray];
    }

    static public function DoesItOwnRelationships( Model $model ): bool
    {
        return $model instanceof OwnsRelationships;
    }
    static public function DoesItParticipateToRelationships(Model $model  ): bool
    {
        return $model instanceof ParticipatesToRelationships;
    }

    /**
     * @param array $dataRow
     * @param Model $model
     * @return RelationshipsHandler
     * @throws Exception
     */
    public function HandleModelRelationships(array $dataRow , Model $model ): RelationshipsHandler
    {
        return $this->HandleModelOwnedRelationships( $dataRow ,  $model)
                    ->HandleModelParticipatingRelationships( $dataRow ,  $model);
    }
}
