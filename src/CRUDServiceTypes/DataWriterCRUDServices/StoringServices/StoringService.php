<?php

namespace CRUDServices\CRUDServiceTypes\DataWriterCRUDServices\StoringServices;

use CRUDServices\CRUDServiceTypes\DataWriterCRUDServices\DataWriterCRUDService;
use CRUDServices\CRUDServiceTypes\DataWriterCRUDServices\StoringServices\Traits\RelationshipsStoringMethods;
use CRUDServices\CRUDServiceTypes\DataWriterCRUDServices\StoringServices\Traits\StoringServiceAbstractMethods;
use CRUDServices\Helpers\Helpers;
use CRUDServices\ValidationManagers\ManagerTypes\StoringValidationManager;
use CRUDServices\ValidationManagers\ValidationManager;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Database\Eloquent\Model;

abstract class StoringService extends DataWriterCRUDService
{
    use  RelationshipsStoringMethods , StoringServiceAbstractMethods ;

    protected string $ModelClass;
    protected function getValidationManager(): ValidationManager
    {
        return StoringValidationManager::Singleton();
    }

    /**
     * @return $this
     * @throws Exception
     */
    public function setModelClass(): self
    {
        $ModelClass = $this->getModelClass();
        if (!class_exists($ModelClass))
        {
            Helpers::throwException("The Given Model Class " . $ModelClass . " Is Not defined !");
        }

        $this->ModelClass = $ModelClass;
        return $this;
    }

    /**
     * @throws Exception
     */
    public function __construct()
    {
        parent::__construct();
        $this->setModelClass();
    }

    protected function initModelInstance(): Model | null
    {
        return $this->ModelClass::make();
    }

    /**
     * @param array $dataRow -  its value always passed by child during overriding
     * @return $this
     * @throws Exception
     */
    protected function createConveniently(array $dataRow = []): StoringService
    {
        $modelInstance = $this->validateModelRowKeys($dataRow)->initModelInstance();
        /**
         * Make Files Ready To Upload And Setting Files Names Into Model's File path's props
         */
        $this->Model = $this->prepareModelFilesToUpload($dataRow ,  $modelInstance);

        $this->doBeforeSavingCurrentModelProps($dataRow);
        /**Saving Model Instance To Database After Setting All Fillables Values And Changing Files 's UploadedFile Object's value To The New Path Of File*/
        if($this->Model->save())
        {
            $this->HandleModelRelationships($this->data , $this->Model);
        }
        return $this;
    }

    /**
     * @return JsonResponse
     */
    public function create(): JsonResponse
    {
        try {
            $this->doBeforeValidation();
            $this->startGeneralValidation()->setRequestValidData();


            /** If No Exception Is Thrown From Validation Methods .... Database Transaction Will Start */
            DB::beginTransaction();
            $this->doBeforeOperationStart();
            $this->createConveniently();

            /**
             * Files Handling Before DB transaction's committing
             * to avoid committing if files uploading or deleting failed
             */
            $this->uploadFiles();
            $this->deleteOldFiles();

            /**  If No Exception Is Thrown From Previous Operations ... All Thing Is OK
             *   So Database Transaction Will Be Commit
             */
            DB::commit();

            $this->doBeforeSuccessResponding();

            /** Response After getting Success */
            return Response::success($this->getSuccessResponseData(), [$this->getModelCreatingSuccessMessage()]);
        } catch (Exception $e)
        {
            /** When An Exception Is Thrown ....  Database Transaction Will Be Rollback */

            DB::rollBack();

            $this->doBeforeErrorResponding();
            /** Response The Error Messages By Exception Messages */
            return Response::error([$e->getMessage()]);
        }
    }

}
