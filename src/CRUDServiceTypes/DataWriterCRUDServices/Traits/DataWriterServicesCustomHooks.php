<?php

namespace  CRUDServices\CRUDServiceTypes\DataWriterCRUDServices\Traits;

trait DataWriterServicesCustomHooks
{
    /**
     * @param array $currentDataRow
     * @return void
     * To do affect on the model after filling its fillable data and before saving them
     * ( newly created model , or the model wanted to be updated)
     */
    protected function doBeforeSavingCurrentModelProps( array $currentDataRow = []) : void
    {
        return;
    }
    protected function doBeforeValidation(): void
    {
        return ;
    }
}
