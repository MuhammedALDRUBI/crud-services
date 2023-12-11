<?php

namespace CRUDServices\RelationshipsHandlers\Traits;

use CRUDServices\CRUDComponents\CRUDRelationshipComponents\ParticipatingRelationshipComponent;
use CRUDServices\Interfaces\ParticipatesToRelationships;
use CRUDServices\RelationshipsHandlers\RelationshipsHandler;
use Illuminate\Database\Eloquent\Model;
use Exception;

trait ParticipatingRelationshipMethods
{
    abstract protected function ParticipatingRelationshipRowsChildClassHandling(Model $model , ParticipatingRelationshipComponent $relationship ,array $ParticipatingRelationshipMultipleRows ) : bool;


    protected function getParticipatingRelationshipRow(array $dataRow , string $foreignColumnName , array $pivotColumns , array $arrayToOverride = []) : array
    {
        if(!array_key_exists($foreignColumnName , $dataRow)){return $arrayToOverride;}

        $foreignColumnValue = $dataRow[$foreignColumnName];
        $pivotColumnsValues = [];

        foreach ($pivotColumns as $column)
        {
            if(array_key_exists( $column , $dataRow))
            {
                $pivotColumnsValues[$column] = $dataRow[$column];
            }
        }
        $arrayToOverride[$foreignColumnValue] = $pivotColumnsValues;
        return  $arrayToOverride ;
    }

    /**
     * @throws Exception
     */
    protected function getParticipatingRelationshipRows(array $dataRow , ParticipatingRelationshipComponent $relationship , Model $model) : array | null
    {
        $rows = [];

        $RelationshipDataRows = $this->getRelationshipRequestData( $dataRow ,    $relationship->getRelationshipName());

        foreach ($RelationshipDataRows as $dataRow)
        {
            $this->validateRelationshipSingleRowKeys($dataRow , $relationship);
            $rows = $this->getParticipatingRelationshipRow($dataRow , $relationship->getForeignKeyName() , $relationship->getPivotColumns() , $rows);
        }
        return $rows;
    }

    /**
     * @param Model $model
     * @param ParticipatingRelationshipComponent $relationship
     * @param array $dataRow
     * @return RelationshipsHandler|ParticipatingRelationshipMethods
     * @throws Exception
     */
    protected function HandleParticipatingRelationshipRows( Model $model , ParticipatingRelationshipComponent $relationship , array $dataRow ) : self
    {
        if($this->checkIfRelationshipDataSent($dataRow , $relationship->getRelationshipName()))
        {
            /**
             * It will be handled if its data sent with request only
             */
            $ParticipatingRelationshipMultipleRows = $this->getParticipatingRelationshipRows($dataRow , $relationship , $model);
            $this->ParticipatingRelationshipRowsChildClassHandling($model , $relationship ,$ParticipatingRelationshipMultipleRows );
        }
        return $this;
    }
    protected function IsParticipatingRelationshipComponent($relationship) : bool
    {
        return $relationship instanceof ParticipatingRelationshipComponent;
    }
    /**
     * @param array $dataRow
     * @param Model $model
     * @return RelationshipsHandler|ParticipatingRelationshipMethods
     * @throws Exception
     */
    protected function HandleModelParticipatingRelationships(array $dataRow , Model $model) : self
    {
        if(!$this::DoesItParticipateToRelationships($model) ) { return $this;}

        /**@var Model | ParticipatesToRelationships $model*/
        foreach ($model->getParticipatingRelationships() as $relationship)
        {
            if($this->IsParticipatingRelationshipComponent($relationship) )
            {
                $this->HandleParticipatingRelationshipRows($model, $relationship, $dataRow);
            }
        }
        return $this;
    }


}
