<?php

namespace CRUDServices\FilesOperationsHandlers\OldFilesDeletingHandler;

use CRUDServices\FilesOperationsHandlers\FilesHandler;
use CRUDServices\FilesOperationsHandlers\OldFilesDeletingHandler\Traits\FileInfoProcessingMethods;
use CRUDServices\FilesOperationsHandlers\OldFilesDeletingHandler\Traits\FilesInfoValidationMethods;
use CRUDServices\FilesOperationsHandlers\OldFilesDeletingHandler\Traits\OldFilesInfoManagerMethods;
use Illuminate\Database\Eloquent\Model;

class OldFilesDeletingHandler extends FilesHandler
{
    use OldFilesInfoManagerMethods , FilesInfoValidationMethods , FileInfoProcessingMethods;


    static protected ?OldFilesDeletingHandler $instance = null ;
    static public function singleton() : FilesHandler
    {
        if(!static::$instance){static::$instance = new static();}
        return static::$instance;
    }

    protected function getFileNameRelevantPathPairs( array $fileInfo) : array
    {
        $fileNameString = $this->getFileOldName( $fileInfo["ModelPathPropName"] );
        if(!$fileNameString){$fileNameString = [];}

        return $this->getFileNameRelevantPathArray($fileNameString , $fileInfo["FolderName"]);
    }

    /**
     * @param array $fileInfo
     * @return void
     */
    protected function prepareModelOldFileToDelete(array $fileInfo) : void
    {
        foreach ($this->getFileNameRelevantPathPairs($fileInfo) as $fileName => $fileRelevantPath)
        {
            $this->addOldFileToDeletingQueue($fileName , $fileRelevantPath);
        }
    }

    /**
     * @param Model $model
     * @param array $specificFilesInfoArray
     * @return Model
     */
    public function prepareModelOldFilesToDelete(Model $model , array $specificFilesInfoArray = []) : Model
    {
        if(!$this::MustUploadModelFiles($model) ){return $model;}
        $this->setModel($model);

        $this->initOldFilesInfoManager();
        foreach ($this->getFilesInfoValidArray($specificFilesInfoArray) as $fileInfo)
        {
            $this->prepareModelOldFileToDelete($fileInfo);
        }
        return $model;
    }
}
