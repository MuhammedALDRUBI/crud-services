<?php

namespace CRUDServices\CRUDServiceTypes\DeletingServices\DeletingStrategies;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

abstract class DeletingStrategy
{
    protected Collection  $modelsToDelete  ;
    protected array $notDeleted = [];
    protected bool $modelMultiTypeDeleting = false;
    public function __construct(Collection $modelsToDelete)
    {
        $this->setModelsToDelete($modelsToDelete);
    }
    protected function setModelMultiTypeDeletingStatus() : void
    {
        $modelTypes = array_keys( $this->notDeleted );
        if(count($modelTypes) > 1)
        {
            $this->modelMultiTypeDeleting = true;
        }
    }
    protected function initNotDeletedArray() : void
    {
        foreach ($this->modelsToDelete as $model)
        {
            if($model instanceof  Model)
            {
                $modelClass = get_class($model) ;
                $modelKey = $model->getKey();

                if(array_key_exists($modelClass , $this->notDeleted))
                {
                    $this->notDeleted[$modelClass][] = $modelKey;
                    continue;
                }

                $this->notDeleted[ $modelClass ] = [ $modelKey ];
            }
        }

        $this->setModelMultiTypeDeletingStatus();

    }

    /**
     * @param Collection $modelsToDelete
     */
    public function setModelsToDelete(Collection $modelsToDelete): void
    {
        $this->modelsToDelete = $modelsToDelete;
        $this->initNotDeletedArray();
    }

    protected function markModelClassAsDeleted(string $modelClass) : void
    {
        unset($this->notDeleted[$modelClass]);
    }
    protected function overrideModelClassNotDeletedValues(string $modelClass , array $values = []) : void
    {
        if (empty($values))
        {
            $this->markModelClassAsDeleted($modelClass);
            return;
        }
        $this->notDeleted[$modelClass] = $values;
    }
    protected function markNumericKeyAsDeleted(string $modelClass , int $keyToMark) : void
    {
        $keys = $this->notDeleted[$modelClass] ;
        if (($index = array_search($keyToMark, $keys)) !== false) // if key is not deleted ... we will mark it as deleted by deleting it from NotDeleted array
        {
            unset( $keys[$index] );
        }
        $this->overrideModelClassNotDeletedValues($modelClass , $keys);

    }
    protected function markKeyArrayAsDeleted(string $modelClass , array $keysToMark) : void
    {
        $modelKeys = $this->notDeleted[$modelClass];
        $stillNotDeletedKeys = array_diff($modelKeys, $keysToMark);
        $this->overrideModelClassNotDeletedValues($modelClass , $stillNotDeletedKeys);
    }
    protected function markAsDeleted(string | Model $modelClass  , int | array |Model  $keyToMark = [] ) : void
    {
        if($modelClass instanceof Model)
        {
            $keyToMark = $modelClass->getKey();
            $modelClass = get_class( $modelClass );
        }

        if(array_key_exists($modelClass , $this->notDeleted))
        {
            if(is_array($keyToMark))
            {
                $this->markKeyArrayAsDeleted($modelClass , $keyToMark);
                return;
            }

            if($keyToMark instanceof Model)
            {
                $keyToMark = $keyToMark->getKey(); // $key is absolutely integer at this point
            }
            $this->markNumericKeyAsDeleted($modelClass , $keyToMark);
        }
    }


    abstract public function delete() : bool;

    protected function getNotDeletedSingleTypeArray() : array
    {
        return Arr::first( $this->notDeleted );
    }
    /**
     * @return array
     */
    public function getNotDeleted(): array
    {
        if(!$this->modelMultiTypeDeleting)
        {
            return  $this->getNotDeletedSingleTypeArray();
        }
        return $this->notDeleted;
    }

}