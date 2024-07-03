<?php

namespace CRUDServices\CRUDServiceTypes\DeletingServices\DeletingStrategies;

use CRUDServices\Helpers\Helpers;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;

class SoftDeletingStg extends DeletingStrategy
{
    protected array $modelKeyNames = [];
    protected array $modelDeletedAtColumns = [];

    /**
     * @throws Exception
     */
    protected function deleteModelClassRowsSoftly(string $modelClass , array $keys  ) : bool
    {
        if(! $modelDeletedAtColumn = $this->modelDeletedAtColumns[ $modelClass ] ?? null )
        {
            /**
             * We don't want to catch this exception ... it is development env exception ,
             * and must be solved before pushing to product env
             */
            throw new Exception("Failed to delete the model softly ... It doesn't have a deleted_at database column !");
        }


        if(!$modelKeyName = $this->modelKeyNames[$modelClass] ?? null)
        {
            throw new Exception("Failed to delete the model softly ... No Model key name is set!");
        }

        try {
            if( $modelClass::whereIn($modelKeyName , $keys )->update([ $modelDeletedAtColumn => now()  ]))
            {
                $this->markAsDeleted($modelClass , $keys);
                return true;
            }
            return false;

        }catch ( QueryException $exception)
        {
           return false;
        }
    }

    /**
     * @throws Exception
     */
    protected function deleteMappedModelsSoftly() : bool
    {
        foreach ($this->notDeleted as $modelClass => $keys)
        {
            $this->deleteModelClassRowsSoftly($modelClass , $keys);
        }

        return empty($this->notDeleted);
    }
    protected function getModelDeletedAtColumn(Model $model) : string
    {
        return $model->getDeletedAtColumn();
    }

    protected function DoesItApplySoftDeleting(Model $model) : bool
    {
        return method_exists( $model , 'getDeletedAtColumn' );
    }
    protected function mapDeletedAtColumnName(Model $model) : void
    {
        if($this->DoesItApplySoftDeleting($model))
        {
            $this->modelDeletedAtColumns[ get_class($model) ] = $this->getModelDeletedAtColumn( $model );
        }
    }

    protected function mapModelKeyNames(Model $model) : void
    {
        $modelClass = get_class($model);

        if(!array_key_exists($modelClass , $this->modelKeyNames))
        {
            $this->modelKeyNames[ $modelClass ] = $model->getKeyName();
        }
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function delete(): bool
    {
        foreach ($this->modelsToDelete as $model)
        {
            $this->mapModelKeyNames($model);
            $this->mapDeletedAtColumnName($model);
        }
        return $this->deleteMappedModelsSoftly();
    }
}