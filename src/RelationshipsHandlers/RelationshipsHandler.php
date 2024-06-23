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

    protected function getRelationshipModelInstance(Model $model , string $relationship , array $dataArrayToSet = []) : Model
    {
        return $model->{$relationship}()->make($dataArrayToSet);
    }

    protected function checkIfRelationshipDataSent(array $dataRow, string $relationshipName) : bool
    {
        return array_key_exists($relationshipName , $dataRow) ;
    }

    protected function isItMultiRowedArray(mixed $array): bool
    {
        return Arr::isList($array) && is_array(Arr::first($array));
    }
    protected function convertToMultipleArray(array $array) : array
    {
        return $this->isItMultiRowedArray($array) ? $array : [$array];
    }

    protected function getRelationshipRequestDataArray(array $dataRow ,string $relationshipName ) : array
    {
        if($this->checkIfRelationshipDataSent($dataRow , $relationshipName) && is_array($dataRow[$relationshipName]) )
        {
            return $dataRow[$relationshipName] ;
        }
        return [];
    }
    protected function getRelationshipRequestData(array $dataRow, string $relationshipName) : array | null
    {
        $RelationshipRequestDataArray = $this->getRelationshipRequestDataArray($dataRow, $relationshipName);
        return $this->convertToMultipleArray($RelationshipRequestDataArray);
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
