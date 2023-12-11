<?php

namespace CRUDServices\FilesOperationsHandlers\FilesUploadingHandler\Traits;

use Exception;
use Illuminate\Http\UploadedFile;

trait FilesInfoValidationMethods
{
    use FileInfoProcessingMethods;

    protected function checkNewFileUploadingStatus(string $RequestKeyName) : bool
    {
        $file = $this->dataRow[$RequestKeyName];
        return $file instanceof UploadedFile || (is_array($file) &&  $file[0] instanceof UploadedFile );
    }

    /**
     * @param string $RequestKeyName
     * @param array $fileInfoArray
     * @return bool
     */
    protected function prepareModelOldFileForDeleting(string $RequestKeyName , array $fileInfoArray) : bool
    {
        if(is_null($this->dataRow[$RequestKeyName] ))
        {
            $this->initOldFilesDeletingHandler()->prepareModelOldFilesToDelete($this->model , [$fileInfoArray]);
            $this->setDataRowFileOriginalName($RequestKeyName , null);
            return false;
        }
        return true;
    }

    protected function avoidFileChanging(string $RequestKeyName) : bool
    {
        if(is_string($this->dataRow[$RequestKeyName]))
        {
            unset($this->dataRow[$RequestKeyName]);
            return false;
        }
        return true;
    }

    /**
     * @param array $fileInfoArray
     * @return bool
     */
    protected function checkRequestKey(array $fileInfoArray) : bool
    {
        if(!$this->checkFileInfoRequestKey($fileInfoArray)) { return false; }

        /** If File Is Not Found In Data array => Nothing Can Be uploaded and This FileInfo array Will Be Ignored
         * ( To Avoid Getting An Exception in Updating Operation Or When Handling Nullable Files)
         *
         * And Will Be Considered as An Old File Deleting 's Request To Delete The model's file described by this fileInfoArray
         */
        $RequestKeyName = $fileInfoArray["RequestKeyName"];
        if(!array_key_exists($RequestKeyName , $this->dataRow)) { return false; }
        if(!$this->prepareModelOldFileForDeleting($RequestKeyName , $fileInfoArray)
            ||
            !$this->avoidFileChanging($RequestKeyName ) ){return false;}

        return $this->checkNewFileUploadingStatus($RequestKeyName);
    }

    /**
     * @param array $fileInfoArray
     * @return bool
     * @throws Exception
     */
    protected function checkValidityConditions(array $fileInfoArray) : bool
    {
        return $this->checkRequestKey($fileInfoArray);
    }

}
