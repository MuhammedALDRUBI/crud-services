<?php

namespace CRUDServices\CRUDServiceTypes\DataWriterCRUDServices\Traits;

use CRUDServices\FilesOperationsHandlers\FilesUploadingHandler\FilesUploadingHandler;
use Illuminate\Database\Eloquent\Model;
use Exception;

trait FilesUploadingMethods
{

    protected function initFilesUploadingHandler() : FilesUploadingHandler
    {
        if(!$this->filesHandler){$this->filesHandler = FilesUploadingHandler::singleton();}
        return $this->filesHandler;
    }

    /**
     * @param array $dataRow
     * @param Model $model
     * @return Model
     * @throws Exception
     */
    public function prepareModelFilesToUpload(array $dataRow ,  Model $model ) : Model
    {
        if(FilesUploadingHandler::MustUploadModelFiles($model))
        {
            return $this->initFilesUploadingHandler()->prepareModelFilesToUpload($dataRow, $model);
        }
        return $model->fill($dataRow);
    }

    /**
     * @throws Exception
     */
    protected function uploadFiles() : bool
    {
        if($this->filesHandler)
        {
            return $this->filesHandler->uploadFiles();
        }
        if($this->relationshipsHandler)
        {
            return $this->relationshipsHandler->uploadRelationshipsFiles();
        }
        return true;
    }

    /**
     * @return bool
     */
    protected function deleteOldFiles() : bool
    {
        if($this->filesHandler)
        {
            return $this->filesHandler->deleteOldFiles();
        }
        if($this->relationshipsHandler)
        {
            return $this->relationshipsHandler->deleteOldFiles();
        }
        return true;
    }

}
