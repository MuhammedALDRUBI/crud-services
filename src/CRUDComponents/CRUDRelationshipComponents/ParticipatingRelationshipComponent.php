<?php

namespace CRUDServices\CRUDComponents\CRUDRelationshipComponents;

class   ParticipatingRelationshipComponent extends RelationshipComponent
{
    protected array $pivotColumns = [];
    protected bool $hasPivotColumns = false;


    public static function create(string $relationshipName , string $foreignKeyName = "" ) : ParticipatingRelationshipComponent
    {
        return new static($relationshipName  , $foreignKeyName);
    }

    public function hasPivotColumns() : bool
    {
        return $this->hasPivotColumns;
    }
    /**
     * @param array $pivotColumns
     * @return ParticipatingRelationshipComponent
     */
    public function setPivotColumns(array $pivotColumns): ParticipatingRelationshipComponent
    {
        $this->pivotColumns = $pivotColumns;
        $this->hasPivotColumns = true;
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
