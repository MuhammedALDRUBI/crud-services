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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Database\Eloquent\Model;

abstract class DeletingService extends CRUDService
{
    use CRUDCustomisationGeneralHooks , RelationshipDeletingMethods , DeletingServiceCustomHooks;

    abstract protected function getModelDeletingSuccessMessage() : string;

    protected function getModelDeletingFailingErrorMessage() : string
    {
        return  "Can't delete this record ... It is used in the system or it is in the progress !" ;
    }

    public function __construct($model)
    {
        parent::__construct();
        $this->setModel($model);
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
    /**
     * @return $this
     * @throws Exception
     */
    protected function deleteSoftly() : self
    {
        if(!$this->Model->delete())
        {
            $exceptionClass = Helpers::getExceptionClass();
            throw new $exceptionClass($this->getModelDeletingFailingErrorMessage());
        }
        return $this;
    }

    /**
     * @return void
     * @throws Exception
     */
    protected function forceDelete() : void
    {
        $this->prepareModelFilesToDelete($this->Model);
        $this->prepareOwnedRelationshipFilesToDelete($this->Model);
        if(!$this->Model->forceDelete())
        {
            $exceptionClass = Helpers::getExceptionClass();
            throw new $exceptionClass($this->getModelDeletingFailingErrorMessage());
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
            $exceptionClass = Helpers::getExceptionClass();
            throw new $exceptionClass($this->getModelDeletingFailingErrorMessage());
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
