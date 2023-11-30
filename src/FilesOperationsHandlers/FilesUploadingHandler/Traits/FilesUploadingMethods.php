<?php

namespace CRUDServices\FilesOperationsHandlers\FilesUploadingHandler\Traits;

use CustomFileSystem\CustomFileUploader;
use CustomFileSystem\S3CustomFileSystem\CustomFileUploader\S3CustomFileUploader;
use Exception;

trait FilesUploadingMethods
{

    protected function initCustomFileUploader() : CustomFileUploader
    {
        if(!$this->customFileUploader){$this->customFileUploader = new S3CustomFileUploader();}
        return $this->customFileUploader;
    }

    /**
     * @param string $RequestKeyName
     * @param string $FolderName
     * @param string|array $filePaths
     * @param bool $multipleUploading
     * @return array
     * @throws Exception
     */
    protected function addFileToUploadingQueue( string  $RequestKeyName , string $FolderName ,  string | array $filePaths  = "" , bool $multipleUploading = false) : array
    {
        if($multipleUploading)
        {
            return $this->customFileUploader->processMultiUploadedFile( $this->dataRow , $RequestKeyName, $FolderName , $filePaths ,true );
        }
        return  $this->customFileUploader->processFile($this->dataRow , $RequestKeyName, $FolderName , $filePaths);
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function uploadFiles() : bool
    {
        /** If No CustomFileHandler Is Set .... No File Added To Be Ready To Upload*/
        if(!$this->customFileUploader){return false;}

        if(!$this->customFileUploader->uploadFiles()){return false;}

        $this->restartFilesUploadingHandler();
        return true;
    }
}
