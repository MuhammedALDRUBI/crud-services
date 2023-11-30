<?php

namespace CRUDServices\FilesOperationsHandlers\Traits;

trait FileInfoProcessingGeneralMethods
{
    protected function getJSONArray(string $jsonOb) : array | null
    {
        return json_decode($jsonOb , JSON_PRETTY_PRINT);
    }

    protected function getFileOldName(string $ModelPathPropName) : string | array
    {
        $fileOldName = $this->model->{ $ModelPathPropName };
        if(!$fileOldName){return "";}

        return $this->getJSONArray($fileOldName) ?? $fileOldName;
    }

    protected function getFileNameRelevantPathArray(string | array $fileNames , string $FolderName) : array
    {
        $fileName_RelevantPath_Pairs_array = [];
        if(!is_array($fileNames)){$fileNames = [$fileNames];}

        foreach ($fileNames as $fileName)
        {
            $fileName_RelevantPath_Pairs_array[$fileName] = $FolderName . "/" . $fileName;
        }
        return $fileName_RelevantPath_Pairs_array;
    }

    protected function setFolderName(array $fileInfo )  :array | null
    {
        if( static::MustUploadModelFiles($this->model) )
        {
            $fileInfo["FolderName"] = $this->model->getDocumentsStorageFolderName();
        }
        return (array_key_exists("FolderName" , $fileInfo) && $fileInfo["FolderName"] !== "") ? $fileInfo : null;
    }

    protected function setMultiUploadingValue(array $fileInfo) : array
    {
        if(!array_key_exists("multipleUploading" , $fileInfo) || !is_bool($fileInfo["multipleUploading"]) )
        {
            $fileInfo["multipleUploading"] = false;
        }
        return $fileInfo;
    }

    protected function setModelPathPropNameValue(array $fileInfo) : array | null
    {
        if(!array_key_exists("ModelPathPropName" , $fileInfo) || $fileInfo["ModelPathPropName"] == "")
        {
            if(!$this->checkFileInfoRequestKey($fileInfo)){return null;}
            $fileInfo["ModelPathPropName"] = $fileInfo["RequestKeyName"];
        }
        return $fileInfo;
    }

}
