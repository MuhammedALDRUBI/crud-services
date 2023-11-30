<?php

namespace CRUDServices\Interfaces\ValidationManagerInterfaces;

interface NeedsRelationshipsKeyAdvancedValidation
{
    /**
     * @return array
     * ["relationshipKey used in CRUDRelationship object" => rules array ]
     */
    public function getRelationshipsKeyAdvancedValidationRules(array $data = []) : array;
}
