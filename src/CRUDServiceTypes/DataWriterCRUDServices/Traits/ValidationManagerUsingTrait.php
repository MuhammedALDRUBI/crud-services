<?php

namespace CRUDServices\CRUDServiceTypes\DataWriterCRUDServices\Traits;

use CRUDServices\CRUDServiceTypes\DataWriterCRUDServices\DataWriterCRUDService;
use Exception;

trait ValidationManagerUsingTrait
{
    protected array $data = [];

    /**
     * @return DataWriterCRUDService|ValidationManagerUsingTrait
     * @throws Exception
     */
    protected function setRequestValidData() : self
    {
        $this->data = $this->validationManager->getRequestValidData();
        return $this;
    }

    /**
     * @param array $row
     * @return DataWriterCRUDService
     * @throws Exception
     */
    protected function validateModelRowKeys(array $row): DataWriterCRUDService
    {
        $this->validationManager->validateSingleModelRowKeys($row);
        return $this;
    }
    /**
     * @return DataWriterCRUDService|ValidationManagerUsingTrait
     * @throws Exception
     */
    protected function startGeneralValidation() : self
    {
        $this->validationManager->startGeneralValidation();
        return $this;
    }

}
