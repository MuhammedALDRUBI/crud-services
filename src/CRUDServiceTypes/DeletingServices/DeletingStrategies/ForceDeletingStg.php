<?php

namespace CRUDServices\CRUDServiceTypes\DeletingServices\DeletingStrategies;

use CRUDServices\CRUDServiceTypes\DeletingServices\Traits\HelperTrait;
use CRUDServices\CRUDServiceTypes\DeletingServices\Traits\RelationshipDeletingMethods;
use CRUDServices\FilesOperationsHandlers\FilesHandler;
use CRUDServices\FilesOperationsHandlers\OldFilesDeletingHandler\OldFilesDeletingHandler;
use CRUDServices\Helpers\Helpers;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class ForceDeletingStg extends  DeletingStrategy
{
    use RelationshipDeletingMethods , HelperTrait;

    protected OldFilesDeletingHandler |FilesHandler|null $filesHandler = null;
    protected function initFilesDeleter() : OldFilesDeletingHandler
    {
        if(!$this->filesHandler){$this->filesHandler = OldFilesDeletingHandler::singleton();}
        return $this->filesHandler;
    }
    protected function prepareModelFilesToDelete(Model $model) : void
    {
        $this->initFilesDeleter()->prepareModelOldFilesToDelete($model);
    }
    protected function deleteFiles() : bool
    {
        return $this->initFilesDeleter()->setOldFilesToDeletingQueue();
    }
    protected function restartFilesDeleter() : void
    {
        $this->initFilesDeleter()->restartOldFilesHandler();
    }

    protected function forceDeleteModel(Model $model) : bool
    {
        try {
            DB::beginTransaction();
            $this->prepareModelFilesToDelete($model);
            $this->prepareOwnedRelationshipFilesToDelete($model);

            if(!$model->forceDelete())
            {
                throw new Exception("Failed to delete model"); // need to exit function with exception to rollback database transaction then returning false
            }

            $this->markAsDeleted($model );
            $this->deleteFiles();

            //If No Exception Is Thrown From Previous Operations ... All Thing Is OK
            //So Database Transaction Will Be Commit
            DB::commit();

            return true;

        }catch (Exception | QueryException $e)
        {
            //When An Exception Is Thrown ....  Database Transaction Will Be Rollback
            DB::rollBack();
            $this->restartFilesDeleter();
            return false;
        }
    }

    /**
     * @return bool
     */
    public function delete(): bool
    {
        foreach ($this->modelsToDelete as $model)
        {
             $this->forceDeleteModel($model);
        }

        return empty($this->notDeleted);
    }

}