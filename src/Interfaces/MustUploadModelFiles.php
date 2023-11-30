<?php

namespace CRUDServices\Interfaces;

interface MustUploadModelFiles
{

    /**
     * Must Return An Array Like :
     * [
     *   [ "RequestKeyName" => "" , "ModelPathPropName" => "" , "multipleUploading" => false]
     * ]
     * Model Must Implement HasStorageFolder interface to getting Folder' Name Value
     * @return array
     */
    public function getModelFileInfoArray() : array;


    /**
     * @return string
     *
     * Important Note :
     * When You Change The Folder Name From A Model Implementing This Interface :
     *   You Must Change The Folder Name Manually From Storage (Local Storage or S3)
     */
    public function getDocumentsStorageFolderName() : string;

}
