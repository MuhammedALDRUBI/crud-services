<?php

namespace CRUDServices\FilesOperationsHandlers\Traits;


trait FilesInfoValidationGeneralMethods
{

    /**
     * @param array $fileInfoArray
     * @return bool
     */
    abstract protected function checkValidityConditions(array $fileInfoArray) : bool;

    /**
     * @param array $fileInfo
     * @return array|null
     */
    abstract protected function processFileInfoArrayRequiredValues(array $fileInfo) : array | null;


    protected function checkFileInfoRequestKey(array $fileInfoArray) : bool
    {
        /**
         * If File info Array Doesn't Have RequestKeyName No File Can Be got from dataRow ( data array )
         * Then : This FileInfo array Will Be Ignored
         */
        return array_key_exists("RequestKeyName" , $fileInfoArray) && $fileInfoArray["RequestKeyName"] !== "";
    }

    /**
     * @param array $arrayToCheck
     * @return array
     */
    protected function getFilesInfoValidArray(array $arrayToCheck) : array
    {
        $filesInfoArray = [];
        foreach( $arrayToCheck as $fileInfoArray)
        {
            if($this->checkValidityConditions($fileInfoArray))
            {
                $validArray = $this->processFileInfoArrayRequiredValues($fileInfoArray);
                if($validArray)
                {
                    $filesInfoArray[] = $validArray;
                }
            }
        }
        return $filesInfoArray;
    }

}
