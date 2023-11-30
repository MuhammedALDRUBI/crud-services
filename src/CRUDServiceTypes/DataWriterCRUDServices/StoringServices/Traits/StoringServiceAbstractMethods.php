<?php

namespace CRUDServices\CRUDServiceTypes\DataWriterCRUDServices\StoringServices\Traits;

use CRUDServices\CRUDServiceTypes\DataWriterCRUDServices\DataWriterCRUDService;

trait StoringServiceAbstractMethods
{
    /**
     * Model And Operation Method
     *
     */

    /**
     * @return string
     */
    abstract protected function getModelClass(): string;

    /**
     * @retur DataWriterCRUDService
     */
    abstract protected function createConveniently(): DataWriterCRUDService;


    /**
     * Responding Methods
     */

    /**
     * @return string
     */
    abstract protected function getModelCreatingFailingErrorMessage(): string;

    /**
     * @return string
     */
    abstract protected function getModelCreatingSuccessMessage(): string;

}
