<?php

namespace CRUDServices\FilesOperationsHandlers\FilePathsRetrievingHandler;

use CRUDServices\FilesOperationsHandlers\FilesHandler;
use CustomFileSystem\CustomFileHandler;

class FileFullPathsHandler extends FilePathsHandler
{
    static protected ?FileFullPathsHandler $instance = null ;
    static public function singleton() : FilesHandler
    {
        if(!static::$instance){static::$instance = new static();}
        return static::$instance;
    }

    protected function processSingleFileName(  string $fileName) : string
    {
        return $fileName ?
                         CustomFileHandler::getFileAssetDownloadingLink( $this->model->getDocumentsStorageFolderName() . "/" . $fileName )
                         : "";
    }

    protected function processMultipleFileName(  array $fileNamesArray) : array
    {
        return  array_map(function($fileName)
                        {
                            if($fileName)
                            {
                                return  $this->processSingleFileName($fileName);
                            }
                        } , $fileNamesArray);
    }
}
