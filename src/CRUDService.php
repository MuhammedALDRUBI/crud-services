<?php

namespace CRUDServices;

use AuthorizationManagement\PermissionExaminers\PermissionExaminer;
use CRUDServices\FilesOperationsHandlers\FilesHandler;
use CRUDServices\FilesOperationsHandlers\FilesUploadingHandler\FilesUploadingHandler;
use CRUDServices\FilesOperationsHandlers\OldFilesDeletingHandler\OldFilesDeletingHandler;
use CRUDServices\Traits\CRUDCustomisationGeneralHooks;
use Exception;
use Illuminate\Database\Eloquent\Model;

abstract class CRUDService
{
    use CRUDCustomisationGeneralHooks;

    protected FilesHandler | FilesUploadingHandler | OldFilesDeletingHandler | null $filesHandler = null;
    protected ?Model $Model ;

    /**
     * @param Model|null $Model
     * @return $this
     */
    public function setModel(?Model $Model): self
    {
        $this->Model = $Model;
        return $this;
    }

    protected function AuthorizeByPolicy() : bool
    {
        return true;
    }

    /**
     * @return void
     * @throws Exception
     */
    protected function checkActionPolicy() : void
    {
        if(!$this->AuthorizeByPolicy())
        {
            throw PermissionExaminer::getUnAuthenticatingException();
        }
    }

    /**
     * @throws Exception
     */
    public function __construct()
    {
        $this->checkActionPolicy();
    }

}
