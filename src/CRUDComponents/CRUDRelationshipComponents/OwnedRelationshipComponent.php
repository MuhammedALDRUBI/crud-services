<?php

namespace CRUDServices\CRUDComponents\CRUDRelationshipComponents;

class OwnedRelationshipComponent extends RelationshipComponent
{

    protected array $updatingConditionColumns = [];
    protected bool $parentDeletingCascading = true;

    public function IsAllowedToCascadeParentDeleting() : bool
    {
        return $this->parentDeletingCascading;
    }
    public function disableParentDeletingCascading()  :OwnedRelationshipComponent
    {
        $this->parentDeletingCascading = true;
        return $this;
    }
    public function enableParentDeletingCascading() : OwnedRelationshipComponent
    {
        $this->parentDeletingCascading = false;
        return $this;
    }


    public static function create(string $relationshipName , string $foreignKeyName) : OwnedRelationshipComponent
    {
        return new static($relationshipName , $foreignKeyName);
    }

    /**
     * @param array $updatingConditionColumns
     * @return $this
     */
    public function setUpdatingConditionColumns(array $updatingConditionColumns): OwnedRelationshipComponent
    {
        $this->updatingConditionColumns = $updatingConditionColumns;
        return $this;
    }


    /**
     * @return array
     */
    public function getUpdatingConditionColumns(): array
    {
        if(empty($this->updatingConditionColumns))
        {
            return ["id"];
        }
        return $this->updatingConditionColumns;
    }
}
