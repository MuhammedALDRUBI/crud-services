<?php

namespace CRUDServices\FilesOperationsHandlers\FilePathsRetrievingHandler;

use CRUDServices\FilesOperationsHandlers\FilesHandler;
use Illuminate\Support\Facades\File;

class FileShortPathsHandler extends FilePathsHandler
{
    static protected ?FileShortPathsHandler $instance = null ;
    static public function singleton() : FilesHandler
    {
        if(!static::$instance){static::$instance = new static();}
        return static::$instance;
    }

    protected function processSingleFileName(  string $fileName) : string
    {
        return File::basename($fileName);
    }

    protected function processMultipleFileName( array $fileNamesArray) : array
    {
        return  array_map(function($fileName)
        {
            if($fileNewName = $this->processSingleFileName($fileName))
            {
                return $fileNewName;
            }
        } , $fileNamesArray);
    }

}
