<?php

namespace CRUDServices\CRUDComponents\CRUDRelationshipComponents;

abstract class RelationshipComponent
{
    protected string $relationshipName ;
    protected string $foreignKeyName ;
    protected string | int $foreignKeyValue = "";

    /**
     * @param string $foreignKeyName
     * @return ParticipatingRelationshipComponent
     */
    public function setForeignKeyName(string $foreignKeyName): self
    {
        $this->foreignKeyName = $foreignKeyName;
        return $this;
    }

    public function __construct(string $relationshipName , string $foreignKeyName)
    {
        $this->setRelationshipName($relationshipName);
        $this->setForeignKeyName($foreignKeyName);
    }
    /**
     * @return string
     */
    public function getForeignKeyName(): string
    {
        return $this->foreignKeyName;
    }

    /**
     * @param int|string $foreignKeyValue
     */
    public function setForeignKeyValue(int|string $foreignKeyValue): self
    {
        $this->foreignKeyValue = $foreignKeyValue;
        return $this;
    }

    /**
     * @return int|string
     */
    public function getForeignKeyValue(): int|string
    {
        return $this->foreignKeyValue;
    }

    /**
     * @param string $relationshipName
     */
    public function setRelationshipName(string $relationshipName): void
    {
        $this->relationshipName = $relationshipName;
    }

    /**
     * @return string
     */
    public function getRelationshipName(): string
    {
        return $this->relationshipName;
    }
}
