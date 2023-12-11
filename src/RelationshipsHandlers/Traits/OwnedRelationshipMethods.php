<?php

namespace CRUDServices\RelationshipsHandlers\Traits;

use CRUDServices\CRUDComponents\CRUDRelationshipComponents\OwnedRelationshipComponent;
use CRUDServices\FilesOperationsHandlers\FilesHandler;
use CRUDServices\FilesOperationsHandlers\FilesUploadingHandler\FilesUploadingHandler;
use CRUDServices\FilesOperationsHandlers\OldFilesDeletingHandler\OldFilesDeletingHandler;
use CRUDServices\Interfaces\OwnsRelationships;
use CRUDServices\RelationshipsHandlers\RelationshipsHandler;
use Illuminate\Database\Eloquent\Model;
use Exception;

trait OwnedRelationshipMethods
{
    protected FilesUploadingHandler | FilesHandler | null $filesUploadingHandler = null;

    protected OldFilesDeletingHandler | FilesHandler | null $oldFilesDeletingHandler = null;

    protected function initOldFilesDeletingHandler() : OldFilesDeletingHandler
    {
        if(!$this->oldFilesDeletingHandler){$this->oldFilesDeletingHandler = OldFilesDeletingHandler::singleton();}
        return $this->oldFilesDeletingHandler;
    }

    public function deleteOldFiles() : bool
    {
        return $this->initOldFilesDeletingHandler()->setOldFilesToDeletingQueue();
    }

    /**
     * @param Model $model
     * @param OwnedRelationshipComponent $relationship
     * @param array $relationshipMultipleRows
     * @return bool
     */
    abstract protected function OwnedRelationshipRowsChildClassHandling(Model $model  , OwnedRelationshipComponent $relationship , array $relationshipMultipleRows) : bool;

    /**
     * @return FilesUploadingHandler
     */
    protected function initFilesUploadingHandler() : FilesUploadingHandler
    {
        if(!$this->filesUploadingHandler){$this->filesUploadingHandler = FilesUploadingHandler::singleton();}
        return $this->filesUploadingHandler;
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function uploadRelationshipsFiles() : bool
    {
        return $this->filesUploadingHandler?->uploadFiles() ?? true;
    }

    /**
     * @param Model $model
     * @param array $data
     * @return Model
     * @throws Exception
     */
    protected function ModelFilesHandling(Model $model , array $data ) : Model
    {
        if(FilesUploadingHandler::MustUploadModelFiles($model))
        {
            return $this->initFilesUploadingHandler()->prepareModelFilesToUpload($data , $model);
        }
        return $model->fill($data);
    }

    /**
     * @param Model $model
     * @param OwnedRelationshipComponent $relationship
     * @param array $dataRow
     * @return RelationshipsHandler|OwnedRelationshipMethods
     */
    protected function HandleOwnedRelationshipRows( Model $model , OwnedRelationshipComponent $relationship , array $dataRow ) : self
    {
        $relationshipName =  $relationship->getRelationshipName();
        if($this->checkIfRelationshipDataSent($dataRow , $relationshipName))
        {
            /**
             * It will be handled if its data sent with request only
             */
            $relationshipRows = $this->getRelationshipRequestData($dataRow ,$relationshipName);
            $this->OwnedRelationshipRowsChildClassHandling($model , $relationship , $relationshipRows );
        }
        return $this;
    }

    protected function IsOwnedRelationshipComponent($relationship) : bool
    {
        return $relationship instanceof OwnedRelationshipComponent;
    }
    protected function HandleModelOwnedRelationships(array $dataRow , Model $model) : self
    {
        if(!$this::DoesItOwnRelationships($model) ) { return $this;}

        /**
         * @var Model | OwnsRelationships $model
         * @var OwnedRelationshipComponent $relationship
         */
        foreach ($model->getOwnedRelationships() as $relationship)
        {
            if($this->IsOwnedRelationshipComponent($relationship))
            {
                $this->HandleOwnedRelationshipRows($model ,$relationship , $dataRow );
            }
        }
        return $this;
    }
}
