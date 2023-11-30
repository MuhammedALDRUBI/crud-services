<?php

namespace CRUDServices\CRUDServiceTypes\DeletingServices\Traits;

use CRUDServices\CRUDComponents\CRUDRelationshipComponents\OwnedRelationshipComponent;
use CRUDServices\RelationshipsHandlers\RelationshipsHandler;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

trait RelationshipDeletingMethods
{
    protected function getModelRelationshipModels(OwnedRelationshipComponent $relationship ) : Collection
    {
        $relationshipRows = $this->Model->{$relationship->getRelationshipName()};
        if($relationshipRows instanceof Model)
        {
            return collect()->add($relationshipRows);
        }

        if($relationshipRows instanceof Collection)
        {
            return $relationshipRows->filter(function($row)
            {
                return $row instanceof Model;
            });
        }
        return collect();
    }

    protected function prepareModelRelationshipFilesToDelete(OwnedRelationshipComponent $relationship) : void
    {
        foreach ($this->getModelRelationshipModels($relationship) as $relationshipModel)
        {
            $this->initFilesDeleter()->prepareModelOldFilesToDelete($relationshipModel);

            /**  Recall the method to handle the sub relationship models */
            $this->prepareOwnedRelationshipFilesToDelete($relationshipModel);
        }
    }

    protected function prepareOwnedRelationshipFilesToDelete(Model $model) : void
    {
        if(RelationshipsHandler::DoesItOwnRelationships($model))
        {
            foreach ($model->getOwnedRelationships() as $relationship )
            {
                /** @var OwnedRelationshipComponent $relationship */
                if( $relationship->IsAllowedToCascadeParentDeleting() )
                {
                    $this->prepareModelRelationshipFilesToDelete($relationship);
                }
            }
        }
    }


}
