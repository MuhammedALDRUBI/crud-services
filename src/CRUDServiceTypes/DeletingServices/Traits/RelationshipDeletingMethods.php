<?php

namespace CRUDServices\CRUDServiceTypes\DeletingServices\Traits;

use CRUDServices\CRUDComponents\CRUDRelationshipComponents\OwnedRelationshipComponent;
use CRUDServices\RelationshipsHandlers\RelationshipsHandler;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;


trait RelationshipDeletingMethods
{

    protected function getModelRelationshipModels(Model $model , OwnedRelationshipComponent $relationship ) : Collection
    {
        $relationshipModels = $model->{$relationship->getRelationshipName()};
        return $this->convertToCollection( $relationshipModels );
    }

    protected function prepareModelRelationshipFilesToDelete(Model $model , OwnedRelationshipComponent $relationship) : void
    {
        foreach ($this->getModelRelationshipModels($model , $relationship) as $relationshipModel)
        {
            $this->prepareModelFilesToDelete($relationshipModel);

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
                    $this->prepareModelRelationshipFilesToDelete($model , $relationship);
                }
            }
        }
    }


}
