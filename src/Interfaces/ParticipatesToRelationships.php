<?php

namespace CRUDServices\Interfaces;

use CRUDServices\CRUDComponents\CRUDRelationshipComponents\ParticipatingRelationshipComponent;

interface ParticipatesToRelationships
{

    /**
     * @return ParticipatingRelationshipComponent[]
     */
    public function getParticipatingRelationships() : array;
}

