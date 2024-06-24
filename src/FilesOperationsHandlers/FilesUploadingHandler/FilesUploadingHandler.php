<?php

namespace CRUDServices\FilesOperationsHandlers\FilesUploadingHandler;

use CRUDServices\FilesOperationsHandlers\FilesHandler;
use CRUDServices\FilesOperationsHandlers\FilesUploadingHandler\Traits\FilesInfoValidationMethods;
use CRUDServices\FilesOperationsHandlers\FilesUploadingHandler\Traits\FilesUploadingMethods;
use CRUDServices\FilesOperationsHandlers\OldFilesDeletingHandler\OldFilesDeletingHandler;
use CRUDServices\Interfaces\MustUploadModelFiles;
use CustomFileSystem\CustomFileUploader;
use Illuminate\Database\Eloquent\Model;
use Exception;

class FilesUploadingHandler extends FilesHandler
{
    use FilesInfoValidationMethods , FilesUploadingMethods;

    protected ?CustomFileUploader $customFileUploader = null;

    protected OldFilesDeletingHandler | FilesHandler | null $oldFilesDeletingHandler = null;

    static protected ?FilesUploadingHandler $instance = null ;
    static public function singleton() : FilesHandler
    {
        if(!static::$instance){static::$instance = new static();}
        return static::$instance;
    }

    protected array $dataRow = [];

    /**
     * @param array $dataRow
     * @return $this
     */
    protected function setDataRow(array $dataRow): FilesUploadingHandler
    {
        $this->dataRow = $dataRow;
        return $this;
    }


    protected function setDataRowFileSize(string $RequestKeyName , $value) : void
    {
        $this->dataRow[$RequestKeyName . "_size"] = $value;
    }
    protected function setDataRowFileMimeType(string $RequestKeyName , $value) : void
    {
        $this->dataRow[$RequestKeyName . "_mimetype"] = $value;
    }
    protected function setDataRowFileOriginalName(string $RequestKeyName , $value) : void
    {
        $this->dataRow[$RequestKeyName . "_original"] = $value;
    }

    protected function resetDeletedFileDataRowMetaData(string $RequestKeyName  ) : void
    {
        $this->setDataRowFileOriginalName($RequestKeyName , null);
        $this->setDataRowFileMimeType($RequestKeyName , null);
        $this->setDataRowFileSize($RequestKeyName , null);
    }
    protected function setUploadedFileDataRowMetaData(string $RequestKeyName , array $fileInfo ) : void
    {
        /**
         * We Need To Change (( FileName )) in dataRow Manually Because CustomFileUploader updates dataRow Array with the (( fileRelevantPath ))
         * Which Is Not Desired To Be Stored In DB
         */
        $this->dataRow[$RequestKeyName] = $fileInfo["fileName"];
        if(!$fileInfo["multipleUploading"])
        {
            /**
             * When Uploading is Multiple Uploading .... Files Original Names Will set As The Key Of Each File Value Found in Path JSON Object
             * So We Need To Set File's Original name As A new Data Key Only In Single File Uploading Situation
             */
            $this->setDataRowFileOriginalName($RequestKeyName , $fileInfo["fileName_original"]);
            $this->setDataRowFileMimeType($RequestKeyName , $fileInfo["fileName_mimetype"]);
            $this->setDataRowFileSize($RequestKeyName , $fileInfo["fileName_size"]);
        }
    }
    protected function initOldFilesDeletingHandler() : OldFilesDeletingHandler
    {
        if(!$this->oldFilesDeletingHandler){$this->oldFilesDeletingHandler = OldFilesDeletingHandler::singleton();}
        return $this->oldFilesDeletingHandler;
    }

    /**
     * @throws Exception
     */
    protected function prepareFileToUpload(  array $fileInfo  ) : void
    {
        $RequestKeyName = $fileInfo["RequestKeyName"];

        /** Setting File To CustomFileUploader Uploading Queue To Upload it later when all data operation is done */
        $this->addFileToUploadingQueue( $RequestKeyName , $fileInfo["FolderName"] , $fileInfo["filePath"] , $fileInfo["multipleUploading"]);

        $this->setUploadedFileDataRowMetaData($RequestKeyName , $fileInfo);
    }


    protected function getModelFileInfoArray(Model $model , array $dataRow) : array
    {
        if(!$this::MustUploadModelFiles($model)){return [];}
        /** If Model Is Implementing The Required Interface ... It Can Provide Us The Needed File Info's array
         * Then We Can Set dataRow And Model props To Using Them at entire the Object's access level
         */
        $this->setModel($model)->setDataRow($dataRow);

        $this->initCustomFileUploader();

        /** @var MustUploadModelFiles $model */
        return $this->getFilesInfoValidArray( $model->getModelFileInfoArray() );
    }

    /**
     * @throws Exception
     */
    public function prepareModelFilesToUpload(array $dataRow ,  Model $model ) : Model
    {
        /**  Model And dataRow Will Be A global Scope Variables If The Given Model Implements The Required Interface */

        foreach ( $this->getModelFileInfoArray($model , $dataRow ) as $fileInfo)
        {
            $this->prepareFileToUpload($fileInfo);
        }

        /** dataRow Is Updated At This Point ... it Contains The Required File Names To Store In DB by Model's Save Method */
        return $model->fill($this->dataRow);
    }

    public function deleteOldFiles() : bool
    {
        return $this->initOldFilesDeletingHandler()->setOldFilesToDeletingQueue();
    }
    protected function restartFilesUploadingHandler() : void
    {
        $this->model = null;
        $this->dataRow = [];
    }
}
