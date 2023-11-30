<?php

namespace CRUDServices\CRUDServiceTypes\DataWriterCRUDServices\UpdatingServices\Traits;

use CRUDServices\RelationshipsHandlers\RelationshipsHandler;
use CRUDServices\RelationshipsHandlers\RelationshipsHandlerTypes\RelationshipsUpdatingHandler;

trait RelationshipsUpdatingMethods
{

    protected function initRelationshipsHandler(): RelationshipsHandler
    {
        if(!$this->relationshipsHandler){$this->relationshipsHandler = new RelationshipsUpdatingHandler();}
        return $this->relationshipsHandler;
    }

}
