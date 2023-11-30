<?php

namespace CRUDServices\Interfaces;

use CRUDServices\CRUDComponents\CRUDRelationshipComponents\OwnedRelationshipComponent;

interface OwnsRelationships
{
    /**
     * @return OwnedRelationshipComponent[]
     */
    public function getOwnedRelationships() : array;
}
