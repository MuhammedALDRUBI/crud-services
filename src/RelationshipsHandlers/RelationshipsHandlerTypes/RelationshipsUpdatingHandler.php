<?php

namespace CRUDServices\RelationshipsHandlers\RelationshipsHandlerTypes;

use CRUDServices\CRUDComponents\CRUDRelationshipComponents\OwnedRelationshipComponent;
use CRUDServices\CRUDComponents\CRUDRelationshipComponents\ParticipatingRelationshipComponent;
use CRUDServices\FilesOperationsHandlers\FilesHandler;
use CRUDServices\Helpers\Helpers;
use CRUDServices\RelationshipsHandlers\RelationshipsHandler;
use Exception;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class RelationshipsUpdatingHandler extends RelationshipsHandler
{
    protected string $primaryColumnName = "";
    protected string $ModelClass = "";

    /**
     * @var array
     * This Array Will Be Like : ["model" => $model  , "data" => ModelNewDataArray]
     */
    protected array $UpdatableModelDataMap = [];

    /**
     * @var array
     * This Array Will Be Like : [ ModelId => $model ]
     * To Use Array Keys In One Deleting Query Statement , And Use Model To Check If There Is Need To Delete Its Files
     */
    protected array $DeletableModelsMap = [];
    protected array $updatableModelsData = [];
    protected array $NewModelsData = [];
    protected array $updatingConditionColumns = [];
    protected  Collection | EloquentCollection | null $modelsCollection = null;

    /**
     * @return bool
     */
    protected function prepareModelOldFilesToDelete() : bool
    {
        /** If Model Doesn't Have Any File .... Nothing To Be Deleted After Deleting The Model*/
        if(!FilesHandler::MustUploadModelFiles( Arr::first($this->DeletableModelsMap) )){return true;}
        $this->initOldFilesDeletingHandler();

        foreach ($this->DeletableModelsMap as $model)
        {
            $this->oldFilesDeletingHandler->prepareModelOldFilesToDelete($model);
        }
        return true;
    }

    /**
     * @param Model $model
     * @return void
     */
    public function setPrimaryColumnName(Model $model): void
    {
        $this->primaryColumnName = $model->getKeyName();
    }

    /**
     * @return bool
     */
    public function setModelClass(): bool
    {
        if(!$this->modelsCollection){return false;}

        $model = $this->modelsCollection->first();
        $this->ModelClass = get_class($model);
        $this->setPrimaryColumnName($model);
        return true;
    }

    /**
     * @return bool
     * @throws Exception
     */
    protected function deletedModelsHandling() : bool
    {
        $deletedModelKeyValues = array_keys($this->DeletableModelsMap);
        if(!empty($deletedModelKeyValues))
        {
            if(!$this->setModelClass()){return false;}

            /** We Need To Get Old File's paths from the models before deleting them */
            $this->prepareModelOldFilesToDelete();

            if(
                $this->ModelClass::whereIn($this->primaryColumnName , $deletedModelKeyValues)->delete()
            ){return true;}
            $exceptionClass = Helpers::getExceptionClass();
            throw new $exceptionClass("Failed To Delete old Models");
        }
        return true;
    }

    /**
     * @param Model $model
     * @param array $data
     * @param OwnedRelationshipComponent $relationship
     * @return bool
     * @throws Exception
     */
    protected function updateSingleModel(Model $model ,  array $data , OwnedRelationshipComponent $relationship ) : bool
    {
        $this->validateRelationshipSingleRowKeys($data ,$relationship , $model);

        $model = $this->ModelFilesHandling($model , $data);
        if(!$model->save())
        {
            $exceptionClass = Helpers::getExceptionClass();
            throw new $exceptionClass("Failed To Update Relationship Entry !");
        }
        $this->HandleModelRelationships( $data ,  $model);
        return true;
    }

    /**
     * @param array $UpdatableModelDataMap
     * $UpdatableModelDataMap : is A Static Array Because There is A Recursion When There Is Need To Update The Model's Relationships
     * And All Model Maps And Data 's Arrays Will Be Empty Every "OwnedRelationshipRowsChildClassHandling" 's method Calling
     * @param OwnedRelationshipComponent $relationship
     * @return bool
     * @throws Exception
     */
    protected function startToUpdateModels(array $UpdatableModelDataMap , OwnedRelationshipComponent $relationship): bool
    {
        foreach ($UpdatableModelDataMap as $ModelInfoArray)
        {
            if(!empty($ModelInfoArray["data"]))
            {
                $this->updateSingleModel( $ModelInfoArray["model"] ,  $ModelInfoArray["data"] , $relationship);
            }
        }
        return true;
    }

    protected function addToDeletableModelsMap( Model $model)
    {
        $this->DeletableModelsMap[ $model->getKey() ] = $model;
    }

    protected function addToUpdatableModelDataMap(Model $model , array $dataRow)
    {
        $this->UpdatableModelDataMap[] = ["model" => $model , "data" => $dataRow];
    }

    protected function prepareNewModelsToCreation(Model $parentModel , string $relationship) : self
    {
        foreach ($this->NewModelsData as $row)
        {
            $this->addToUpdatableModelDataMap($this->getRelationshipModelInstance($parentModel , $relationship) , $row);
        }
        $this->NewModelsData = [];
        return $this;
    }

    protected function checkModelColumnValue(Model $model , array $dataRow , string $column) : bool
    {
        return $model->{$column} == $dataRow[$column];
    }

    protected function addToModelMaps( Model $model) : void
    {
        foreach ($this->updatableModelsData as $row)
        {
            $modelStatus = true;
            foreach ($this->updatingConditionColumns as $column)
            {
                $modelStatus &= $this->checkModelColumnValue($model , $row ,$column);
            }
            /**
             * If Model Status Is True ... That Means The Model Has Data To Be Updated
             */
            if($modelStatus)
            {
                $this->addToUpdatableModelDataMap(  $model , $row);
                return;
            }
        }
        /** Otherwise , The Model Is Requested To Be Deleted */
        $this->addToDeletableModelsMap( $model );
    }

    /**
     * @return $this
     *
     * In This Method : All Of Collection's Models Will Be Checked :
     * If Any Model Has Data With Its updatingConditionColumns column's values ... Model Will Be Updated Later
     * ELSE : Model Will Be Deleted Later
     *
     */
    protected function CollectionModelsClassification() : self
    {
        if(!$this->modelsCollection){return $this;}

        foreach ($this->modelsCollection as $model)
        {
            $this->addToModelMaps($model);
        }
        return $this;
    }

    protected function setDataCollection( Model $parentModel , string $relationship ) : self
    {
        $modelOrCollection = $parentModel->{$relationship};
        if(!$modelOrCollection){return $this;}

        if($modelOrCollection instanceof Model)
        {
            $modelOrCollection  =  collect()->add($modelOrCollection);
        }
        $this->modelsCollection = $modelOrCollection->count() > 0 ? $modelOrCollection : null;
        return $this;
    }

    protected function DataRowsArrayClassification(array $relationshipMultipleRows , array $updatingConditionColumns )  : self
    {
        foreach ($relationshipMultipleRows as  $row)
        {
            if(Arr::has($row , $updatingConditionColumns))
            {
                $this->updatableModelsData[] = $row;
                continue;
            }

            if(!empty($row))
            {
                $this->NewModelsData[] = $row;
            }
        }

        $this->updatingConditionColumns = $updatingConditionColumns;

        return $this;
    }

    /**
     * @param Model $model
     * @param OwnedRelationshipComponent $relationship
     * @param array $relationshipMultipleRows
     * @return bool
     * @throws Exception
     */
    protected function OwnedRelationshipRowsChildClassHandling(Model $model, OwnedRelationshipComponent $relationship , array $relationshipMultipleRows ) : bool
    {
        $this->restartUpdater();
        $this->DataRowsArrayClassification($relationshipMultipleRows , $relationship->getUpdatingConditionColumns() );
        $this->setDataCollection($model , $relationship->getRelationshipName() )
             ->CollectionModelsClassification()
             ->prepareNewModelsToCreation($model, $relationship->getRelationshipName() );
        $this->deletedModelsHandling();
        return $this->startToUpdateModels($this->UpdatableModelDataMap , $relationship);
    }
    protected function restartUpdater() : void
    {
        $this->UpdatableModelDataMap = [];
        $this->updatableModelsData = [];
        $this->DeletableModelsMap = [];
    }

    /**
     * @param Model $model
     * @param ParticipatingRelationshipComponent $relationship
     * @param array $ParticipatingRelationshipFinalData
     * @return bool
     * @throws Exception
     */
    protected function ParticipatingRelationshipRowsChildClassHandling(Model $model, ParticipatingRelationshipComponent $relationship , array $ParticipatingRelationshipFinalData): bool
    {
        $model->{$relationship->getRelationshipName()}()->sync( $ParticipatingRelationshipFinalData );
        return true;
    }
}
