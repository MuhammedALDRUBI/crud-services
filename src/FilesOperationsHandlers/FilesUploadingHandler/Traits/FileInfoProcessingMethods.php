<?php

namespace CRUDServices\FilesOperationsHandlers\FilesUploadingHandler\Traits;

use Illuminate\Http\UploadedFile;

trait FileInfoProcessingMethods
{

    protected function setMultiFilesPathsForUploading(array $fileInfo ) : array
    {
        $paths = [];
        $fileNames = json_decode($fileInfo["fileName"]);
        foreach ( $fileNames as $fileName)
        {
            $paths[] = $fileInfo["FolderName"] . "/" . $fileName;
        }
        $fileInfo["filePath"]  = $paths;
        return $fileInfo;
    }

    protected function setSingleFilePathForUploading(array $fileInfo) : array
    {
        /** $fileName Is A Single String*/
        $fileInfo["filePath"] = $fileInfo["FolderName"] . "/" . $fileInfo["fileName"];
        return $fileInfo;
    }

    protected function oldFilesHandling(array $fileInfo) : bool
    {
        if($fileInfo["oldFilesDeleting"])
        {
            $this->initOldFilesDeletingHandler();
            $FolderName = $fileInfo["FolderName"];
            $fileOldName = $this->getFileOldName( $fileInfo["ModelPathPropName"] );
            if(!$fileOldName){return false;}

            foreach ($this->getFileNameRelevantPathArray($fileOldName , $FolderName) as $fileName => $fileRelevantPath)
            {
                $this->oldFilesDeletingHandler->addOldFileToDeletingQueue($fileName , $fileRelevantPath);
            }
        }
        return true;
    }

    /**
     * @param UploadedFile $file
     * @return string
     */
    protected function getUploadedFileHashName(UploadedFile $file) : string
    {
        return $this->customFileUploader->getFileHashName($file);
    }

    /**
     * @param UploadedFile $file
     * @return string
     */
    protected function getUploadedFileMimeType(UploadedFile $file) : string
    {
        return $this->customFileUploader->getUploadedFileMimeType($file);
    }

    protected function setUploadedFileMimeType(UploadedFile $file , array $fileInfo = []) : array
    {
        $fileInfo["fileName_mimetype"] = $this->getUploadedFileMimeType($file);
        return $fileInfo;
    }
    /**
     * @param UploadedFile $file
     * @return string
     */
    protected function getUploadedFileSize(UploadedFile $file) : string
    {
        return $this->customFileUploader->getUploadedFileSize($file);
    }
    protected function setUploadedFileSize(UploadedFile $file , array $fileInfo = []) : array
    {
        $fileInfo["fileName_size"] = $this->getUploadedFileSize($file);
        return $fileInfo;
    }

    /**
     * @param UploadedFile $file
     * @return string
     */
    protected function getUploadedFileOriginalName(UploadedFile $file) : string
    {
        return $this->customFileUploader->getUploadedFileOriginalName($file);
    }
    protected function getSingleUploadingFileName(UploadedFile $uploadedFile , array $fileInfo )  : array
    {
        $fileOldName = $this->getFileOldName( $fileInfo["ModelPathPropName"] );
        $fileInfo["fileName_original"] = $this->getUploadedFileOriginalName($uploadedFile);

        if(!$fileOldName ||  is_array($fileOldName))
        {
            $fileInfo["fileName"] = $this->getUploadedFileHashName($uploadedFile);
            return $fileInfo;
        }

        if($this->customFileUploader->HasExtensionChanged($uploadedFile , $fileOldName))
        {
            $fileInfo["oldFilesDeleting"] = true;
            $fileInfo["fileName"] = $this->getUploadedFileHashName($uploadedFile);
            return $fileInfo;
        }

        $fileInfo["fileName"] = $fileOldName;
        return $fileInfo;
    }

    protected function getMultipleUploadingFileNames(array $uploadedFilesArray , array $fileInfo ) :  array
    {
        $fileNames = [];
        foreach ($uploadedFilesArray as $file)
        {
            $fileNames[$this->getUploadedFileOriginalName($file)] = $this->getUploadedFileHashName($file);
        }
        $fileInfo["oldFilesDeleting"] = true;
        $fileInfo["fileName"] = json_encode($fileNames , JSON_PRETTY_PRINT);

        return $fileInfo;
    }

    protected function setFileNameAndUploadingPath( array $fileInfo ) : array
    {
        $RequestKeyName = $fileInfo["RequestKeyName"];
        $uploadedFile = $this->dataRow[ $RequestKeyName ];

        /** If It Is Multiple uploading operation ... A JSON Object Contains ( New ) File Names Always Will Be Returned */
        if ($fileInfo["multipleUploading"])
        {
            $fileInfo = $this->getMultipleUploadingFileNames($uploadedFile , $fileInfo );
            return $this->setMultiFilesPathsForUploading($fileInfo);
        }

        $fileInfo = $this->getSingleUploadingFileName($uploadedFile , $fileInfo);
        $fileInfo = $this->setUploadedFileSize($uploadedFile , $fileInfo);
        $fileInfo = $this->setUploadedFileMimeType($uploadedFile , $fileInfo);
        return $this->setSingleFilePathForUploading($fileInfo);
    }

    protected function setOldFilesDeletingProp(array $fileInfo) : array
    {
        if(!array_key_exists("oldFilesDeleting" , $fileInfo) || !is_bool($fileInfo["oldFilesDeleting"]) )
        {
            $fileInfo["oldFilesDeleting"] = false;
        }
        return $fileInfo;
    }

    protected function processFileInfoArrayRequiredValues(array $fileInfo) : array | null
    {
        $fileInfo = $this->setMultiUploadingValue($fileInfo);
        $fileInfo = $this->setModelPathPropNameValue($fileInfo);
        $fileInfo = $this->setOldFilesDeletingProp($fileInfo);
        $fileInfo = $this->setFolderName($fileInfo );

        /** If There Is No Folder Name In fileInfo Array , It Will Be Ignored To Avoid Random File Uploading (Without Specific Folder Name) */
        if(!$fileInfo){return null;}

        $fileInfo = $this->setFileNameAndUploadingPath($fileInfo );
        $this->oldFilesHandling($fileInfo);
        return $fileInfo;
    }
}
