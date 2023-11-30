<?php

namespace CRUDServices\Interfaces\ValidationManagerInterfaces;

interface NeedsModelKeyAdvancedValidation
{
    public function getModelKeyAdvancedValidationRules(array $data = []) : array;
}
