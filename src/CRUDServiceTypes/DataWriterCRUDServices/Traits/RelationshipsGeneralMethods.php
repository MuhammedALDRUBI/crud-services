<?php

namespace CRUDServices\CRUDServiceTypes\DataWriterCRUDServices\Traits;

use CRUDServices\CRUDServiceTypes\DataWriterCRUDServices\DataWriterCRUDService;
use CRUDServices\RelationshipsHandlers\RelationshipsHandler;
use Exception;
use Illuminate\Database\Eloquent\Model;

trait RelationshipsGeneralMethods
{
    abstract protected function initRelationshipsHandler() : RelationshipsHandler;

    /**
     * @param array $dataRow
     * @param Model $model
     * @return DataWriterCRUDService
     * @throws Exception
     */
    protected function HandleModelRelationships(array $dataRow ,  Model $model  ): DataWriterCRUDService
    {
        if ( RelationshipsHandler::DoesItOwnRelationships($model)
            ||
            RelationshipsHandler::DoesItParticipateToRelationships($model) )
        {
            $this->initRelationshipsHandler()->setValidationManager($this->validationManager)
                                             ->HandleModelRelationships($dataRow , $model  );
        }
        return $this;
    }

}
