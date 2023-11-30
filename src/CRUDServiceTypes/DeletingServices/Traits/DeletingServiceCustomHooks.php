<?php

namespace  CRUDServices\CRUDServiceTypes\DeletingServices\Traits;

trait DeletingServiceCustomHooks
{

    protected function checkDeletingAdditionalConditions()  : bool
    {
        return true;
    }

}
