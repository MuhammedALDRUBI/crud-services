<?php

namespace CRUDServices\CRUDServiceTypes\DeletingServices\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

trait HelperTrait
{
    protected function convertToCollection( Collection|Model|null $modelOrCollection = null ) : Collection
    {
        if($modelOrCollection instanceof Model)
        {
            return collect()->add($modelOrCollection);
        }

        if($modelOrCollection instanceof Collection)
        {
            return $modelOrCollection->filter(function($object)
            {
                return $object instanceof Model;
            });
        }
        return collect();
    }
}