<?php

namespace CRUDServices\CRUDComponents\CRUDRelationshipComponents;

class   ParticipatingRelationshipComponent extends RelationshipComponent
{
    protected array $pivotColumns = [];


    public static function create(string $relationshipName , string $foreignKeyName = "" ) : ParticipatingRelationshipComponent
    {
        return new static($relationshipName  , $foreignKeyName);
    }

    /**
     * @param array $pivotColumns
     * @return ParticipatingRelationshipComponent
     */
    public function setPivotColumns(array $pivotColumns): ParticipatingRelationshipComponent
    {
        $this->pivotColumns = $pivotColumns;
        return $this;
    }

    /**
     * @return array
     */
    public function getPivotColumns(): array
    {
        return $this->pivotColumns;
    }
}
