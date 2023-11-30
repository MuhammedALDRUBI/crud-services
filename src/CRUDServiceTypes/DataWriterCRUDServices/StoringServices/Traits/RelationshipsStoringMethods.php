<?php

namespace CRUDServices\CRUDServiceTypes\DataWriterCRUDServices\StoringServices\Traits;

use CRUDServices\RelationshipsHandlers\RelationshipsHandler;
use CRUDServices\RelationshipsHandlers\RelationshipsHandlerTypes\RelationshipsCreationHandler;

trait RelationshipsStoringMethods
{

    protected function initRelationshipsHandler(): RelationshipsHandler
    {
        if(!$this->relationshipsHandler){$this->relationshipsHandler = new RelationshipsCreationHandler();}
        return $this->relationshipsHandler;
    }

}
