<?php

namespace CRUDServices\OldFilesInfoManager;

use CRUDServices\ConfigManagers\ConfigManager;
use CustomFileSystem\CustomFileHandler;
use Exception;
use FilesInfoDataManagers\FilesInfoDataManager;

class OldFilesInfoManager extends FilesInfoDataManager
{

    /**
     * @return string
     * @throws Exception
     */
    protected function getDataFilesInfoPath(): string
    {
        return ConfigManager::Singleton()->getValue("old_files_info_json_file_path") ?? "";
    }

    public function getAllFiles() : array
    {
        return $this->InfoData;
    }

    public function addOldFileInfo(string $fileName , string $fileRelevantPath) : bool
    {
        if(!CustomFileHandler::IsFileExists($fileRelevantPath)){return false;}
        return $this->addFileInfo($fileRelevantPath , $fileName);
    }

}
