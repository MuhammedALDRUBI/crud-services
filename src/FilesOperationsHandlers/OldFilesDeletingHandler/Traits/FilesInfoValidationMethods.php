<?php

namespace CRUDServices\FilesOperationsHandlers\OldFilesDeletingHandler\Traits;


trait FilesInfoValidationMethods
{
    /**
     * @param array $fileInfoArray
     * @return bool
     */
    protected function checkValidityConditions(array $fileInfoArray) : bool
    {
        return $this->checkFileInfoRequestKey($fileInfoArray);
    }

    protected function getFilesInfoValidArray(array $arrayToCheck = []) : array
    {
        if(empty($arrayToCheck)){$arrayToCheck = $this->model->getModelFileInfoArray();}
        return parent::getFilesInfoValidArray($arrayToCheck);
    }
}
