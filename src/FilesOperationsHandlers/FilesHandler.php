<?php

namespace CRUDServices\FilesOperationsHandlers;

use CRUDServices\FilesOperationsHandlers\Traits\FileInfoProcessingGeneralMethods;
use CRUDServices\FilesOperationsHandlers\Traits\FilesInfoValidationGeneralMethods;
use CRUDServices\Interfaces\MustUploadModelFiles;
use Illuminate\Database\Eloquent\Model;

abstract class FilesHandler
{
    use FileInfoProcessingGeneralMethods , FilesInfoValidationGeneralMethods;

    protected null | MustUploadModelFiles | Model $model = null;

    protected function __construct(){ }
    abstract static public function singleton() : FilesHandler;

    /**
     * @param Model $model
     * @return $this
     */
    protected function setModel(Model $model) : FilesHandler
    {
        $this->model = $model ;
        return $this;
    }

    static public function MustUploadModelFiles(Model $model) : bool
    {
        return $model instanceof MustUploadModelFiles;
    }
}
