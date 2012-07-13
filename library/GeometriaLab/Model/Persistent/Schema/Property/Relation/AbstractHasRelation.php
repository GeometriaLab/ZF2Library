<?php

namespace GeometriaLab\Model\Persistent\Schema\Property\Relation;

use GeometriaLab\Model\Persistent\ModelInterface;

abstract class AbstractHasRelation extends AbstractRelation
{
    CONST DELETE_NONE     = 'none';
    CONST DELETE_SET_NULL = 'setNull';
    CONST DELETE_CASCADE  = 'cascade';

    protected $onDelete = 'setNull';

    public function setOnDelete($deleteMode)
    {
        $this->onDelete = $deleteMode;

        return $this;
    }

    public function getOnDelete()
    {
        return $this->onDelete;
    }

    public function removeForeignRelations(ModelInterface $referencedModel)
    {
        $onDelete = $this->getOnDelete();

        if ($onDelete == static::DELETE_NONE) {
            return 0;
        }

        $foreignMapper = call_user_func(array($this->getModelClass(), 'getMapper'));

        $referencedPropertyValue = $referencedModel->get($this->getReferencedProperty());

        $query = $foreignMapper->createQuery();
        $query->where(array($this->getForeignProperty() => $referencedPropertyValue));

        $foreignModels = $foreignMapper->getAll($query);

        foreach($foreignModels as $foreignModel) {
            if ($onDelete === static::DELETE_CASCADE) {
                $foreignModel->delete();
            } else if ($onDelete === static::DELETE_SET_NULL) {
                $foreignModel->set($this->getForeignProperty(), null);
                $foreignModel->save();
            } else {
                throw new \RuntimeException("Invalid relation '{$this->getName()}' delete mode");
            }
        }

        return count($foreignModels);
    }
}