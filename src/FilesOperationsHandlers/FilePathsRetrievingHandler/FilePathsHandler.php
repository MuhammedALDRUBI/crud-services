<?php

namespace CRUDServices\FilesOperationsHandlers\FilePathsRetrievingHandler;

use CRUDServices\FilesOperationsHandlers\FilesHandler;
use Illuminate\Database\Eloquent\Model;

abstract class FilePathsHandler extends FilesHandler
{

    abstract protected function processMultipleFileName(  array $fileNamesArray) : array ;

    abstract protected function processSingleFileName(  string $fileName) : string ;



    protected function checkValidityConditions(array $fileInfoArray): bool
    {
        return $this->checkFileInfoRequestKey($fileInfoArray);
    }

    protected function processFileInfoArrayRequiredValues(array $fileInfo): array|null
    {
        return $this->setModelPathPropNameValue($fileInfo);
    }

    protected function setModelFileProp(string $fileProp ,  string $filePropValue) : void
    {
        $this->model->{$fileProp} = $filePropValue;
    }


    protected function setMultipleFileName(string $fileProp ,  array $fileNamesArray) : void
    {
        $fileNamesNewArray = $this->processMultipleFileName($fileNamesArray);
        if(!empty($fileNamesNewArray))
        {
            $this->setModelFileProp($fileProp , json_encode($fileNamesNewArray , JSON_PRETTY_PRINT) );
        }
    }

    protected function setSingleFileName(string $fileProp ,  string $fileName) : void
    {
        if($fileNewName = $this->processSingleFileName($fileName))
        {
            $this->setModelFileProp($fileProp , $fileNewName);
        }
    }

    protected function ModelFilePathHandling( string $fileProp) : void
    {
        $ModelFileName = $this->getFileOldName($fileProp);
        if(!$ModelFileName){return;}

        /**
         * If $ModelFileName != "" ... Its Value Will Be a String Or an Array
         * - If It Is Array ... We Have Multi File Names Combined in JSON
         * - If It Is A string We Can Process It Immediately
         */
        if(!is_array($ModelFileName))
        {
            $this->setSingleFileName($fileProp , $ModelFileName);
            return ;
        }
        $this->setMultipleFileName($fileProp , $ModelFileName);
    }

    protected function getModelFilePathPropsArray( ) : array
    {
        $ModelFilesInfo = $this->getFilesInfoValidArray( $this->model->getModelFileInfoArray()  );

        $fileProps = [];
        foreach ( $ModelFilesInfo as $fileInfo)
        {
            $fileProps[] = $fileInfo["ModelPathPropName"];
        }
        return $fileProps;
    }

    public function ModelFilesPathHandling(Model $model , array $fileProps = []) : void
    {
        if(!$this::MustUploadModelFiles($model)){return ;}
        $this->setModel($model);
        if(empty($fileProps)){$fileProps = $this->getModelFilePathPropsArray();}
        foreach ($fileProps as $fileProp)
        {
            $this->ModelFilePathHandling($fileProp);
        }
    }

}
