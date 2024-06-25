<?php

namespace CRUDServices\CRUDServiceTypes\DeletingServices;

use CRUDServices\CRUDService;
use CRUDServices\CRUDServiceTypes\DeletingServices\Traits\DeletingServiceCustomHooks;
use CRUDServices\CRUDServiceTypes\DeletingServices\Traits\RelationshipDeletingMethods;
use CRUDServices\FilesOperationsHandlers\OldFilesDeletingHandler\OldFilesDeletingHandler;
use CRUDServices\Helpers\Helpers;
use CRUDServices\Traits\CRUDCustomisationGeneralHooks;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Database\Eloquent\Model;

abstract class DeletingService extends CRUDService
{
    use CRUDCustomisationGeneralHooks , RelationshipDeletingMethods , DeletingServiceCustomHooks;
    protected Collection  $modelsToDelete  ;

    protected array $modelSetDeletingMap = [];
    protected array $modelDeletedAtColumns = [];

    abstract protected function getModelDeletingSuccessMessage() : string;

    protected function getModelDeletingFailingErrorMessage() : string
    {
        return  "Can't delete this record ... It is used in the system or it is in the progress !" ;
    }

    public function __construct(Collection | Model $modelsToDelete)
    {
        parent::__construct();
        $this->setModelsToDelete($modelsToDelete);
    }

    protected function convertToCollection( Collection|Model|null $modelOrCollection = null ) : Collection
    {
        if($modelOrCollection instanceof Model)
        {
            return collect()->add($modelOrCollection);
        }

        if($modelOrCollection instanceof Collection)
        {
            return $modelOrCollection->filter(function($object)
            {
                return $object instanceof Model;
            });
        }
        return collect();
    }
    /**
     * @param Model|Collection $modelsToDelete
     * @return $this
     */
    public function setModelsToDelete(Model|Collection $modelsToDelete): self
    {
        $this->modelsToDelete = $this->convertToCollection( $modelsToDelete );
        return $this;
    }

    protected function mapModelKeysToDelete(Model $model) : void
    {
        $mappingKey = get_class($model);
        $modelKey =  $model->getKey()  ;

        if(array_key_exists($mappingKey , $this->modelSetDeletingMap))
        {
            $this->modelSetDeletingMap[ $mappingKey ]["keys"][] = $modelKey;
            return;
        }

        $this->modelSetDeletingMap[ $mappingKey ] = ["keyName" => $model->getKeyName() , "keys" => [ $modelKey ] ];
    }
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

    protected function errorRespondingHandling(Exception | QueryException $exception) : JsonResponse
    {
        if($exception instanceof QueryException)
        {
            /** To avoid returning the default sql error message */
            return Response::error( [ $this->getModelDeletingFailingErrorMessage() ]);
        }

        return Response::error( [ $exception->getMessage()]);
    }

    protected function deleteMappedModelsSoftly() : bool
    {
        foreach ($this->modelSetDeletingMap as $modelClass => $keyInfo)
        {
            $modelDeletedAtColumn = $this->modelDeletedAtColumns[ $modelClass ] ?? null;
            if(! $modelDeletedAtColumn) { return false; }

            $softDeletingResult = $modelClass::whereIn( $keyInfo["keyName"] , $keyInfo["keys"] )->update( [ $modelDeletedAtColumn => now()  ] );
            if( !$softDeletingResult ) { return  false;}
        }

        return true;
    }
    protected function getModelDeletedAtColumn(Model $model) : string
    {
        return $model->getDeletedAtColumn();
    }

    protected function DoesItApplySoftDeleting(Model $model) : bool
    {
        return method_exists( $model , 'getDeletedAtColumn' );
    }
    protected function mapDeletedAtColumnName(Model $model) : void
    {
        if($this->DoesItApplySoftDeleting($model))
        {
            $this->modelDeletedAtColumns[ get_class($model) ] = $this->getModelDeletedAtColumn( $model );
        }
    }

    /**
     * @return $this
     * @throws Exception
     */
    protected function deleteSoftly() : self
    {
        foreach ($this->modelsToDelete as $model)
        {
            $this->mapModelKeysToDelete($model);
            $this->mapDeletedAtColumnName($model);
        }

        if(!$this->deleteMappedModelsSoftly())
        {
            Helpers::throwException( $this->getModelDeletingFailingErrorMessage());
        }
        return $this;
    }


    protected function forceDeleteMappedModels() : bool
    {
        foreach ($this->modelSetDeletingMap as $modelClass => $keyInfo)
        {
            if(! $modelClass::whereIn( $keyInfo["keyName"] , $keyInfo["keys"] )->delete() )
            {
                return false;
            }
        }
        return true;
    }
    /**
     * @return void
     * @throws Exception
     */
    protected function forceDelete() : void
    {
        foreach ($this->modelsToDelete as $model)
        {
            $this->prepareModelFilesToDelete($model);
            $this->prepareOwnedRelationshipFilesToDelete($model);
            $this->mapModelKeysToDelete($model);
        }

        if( !$this->forceDeleteMappedModels() )
        {
            Helpers::throwException($this->getModelDeletingFailingErrorMessage());
        }
    }

    /**
     * @param bool $forcedDeleting
     * @return $this
     * @throws Exception
     */
    protected function DeleteConveniently(bool $forcedDeleting = false) : self
    {
        if($forcedDeleting)
        {
            $this->forceDelete();
            return $this;
        }
        $this->deleteSoftly();
        return $this;
    }

    /**
     * @return void
     * @throws Exception
     */
    protected function checkDeletingConditionOrFail() : void
    {
        if( !$this->checkDeletingAdditionalConditions() )
        {
            Helpers::throwException( $this->getModelDeletingFailingErrorMessage() );
        }
    }

    /**
     * @param bool $forcedDeleting
     * @return JsonResponse
     */
    public function delete(bool $forcedDeleting = true) : JsonResponse
    {
        try {
               $this->checkDeletingConditionOrFail();

                DB::beginTransaction();
                $this->doBeforeOperationStart();

                $this->DeleteConveniently($forcedDeleting);

                $this->deleteFiles();

                //If No Exception Is Thrown From Previous Operations ... All Thing Is OK
                //So Database Transaction Will Be Commit
                DB::commit();
                $this->doBeforeSuccessResponding();

                //Response After getting Success
                return Response::success($this->getSuccessResponseData() , [$this->getModelDeletingSuccessMessage()]);

        }catch (Exception | QueryException $e)
        {
                //When An Exception Is Thrown ....  Database Transaction Will Be Rollback
                DB::rollBack();

                $this->doBeforeErrorResponding();
                return $this->errorRespondingHandling($e);

        }
    }

}
