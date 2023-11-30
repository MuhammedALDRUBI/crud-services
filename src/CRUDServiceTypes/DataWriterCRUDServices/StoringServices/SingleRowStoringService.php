<?php

namespace CRUDServices\CRUDServiceTypes\DataWriterCRUDServices\StoringServices;

abstract class SingleRowStoringService extends StoringService
{
    protected function createConveniently(array $dataRow = []): StoringService
    {
        return parent::createConveniently($this->data);
    }
}
