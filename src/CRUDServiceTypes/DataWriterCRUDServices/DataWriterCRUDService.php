<?php

namespace CRUDServices\CRUDServiceTypes\DataWriterCRUDServices;

use CRUDServices\CRUDService;
use CRUDServices\CRUDServiceTypes\DataWriterCRUDServices\Traits\DataWriterServicesCustomHooks;
use CRUDServices\CRUDServiceTypes\DataWriterCRUDServices\Traits\FilesUploadingMethods;
use CRUDServices\CRUDServiceTypes\DataWriterCRUDServices\Traits\RelationshipsGeneralMethods;
use CRUDServices\CRUDServiceTypes\DataWriterCRUDServices\Traits\ValidationManagerUsingTrait;
use CRUDServices\RelationshipsHandlers\RelationshipsHandler;
use CRUDServices\ValidationManagers\ValidationManager;

abstract class DataWriterCRUDService extends CRUDService
{
    protected ?RelationshipsHandler $relationshipsHandler = null;
    protected ?ValidationManager $validationManager = null;

    use ValidationManagerUsingTrait, RelationshipsGeneralMethods , FilesUploadingMethods , DataWriterServicesCustomHooks;

    /**
     * @return string
     */
    abstract protected function getRequestClass(): string;

    abstract protected function getValidationManager() : ValidationManager;
    protected function initValidationManager() : void
    {
        $this->validationManager = $this->getValidationManager()
                                        ->setBaseRequestFormClass($this->getRequestClass());
    }

    public function __construct()
    {
        parent::__construct();
        $this->initValidationManager();
    }

}
