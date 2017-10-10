<?php

namespace lkovace18\EntityFactoryBundle\Factory\EntityBuilder\Associations;

class OneToOne extends AbstractRelation
{
    protected function selfReferential()
    {
        $data = $this->params->get($this->association);
        $mapping = $this->meta->getAssociationMapping($this->association);
        $assocClass = $this->meta->getAssociationTargetClass($this->association);

        // Check if data is already a concrete entity
        if ( ! $data instanceof $assocClass) {
            $data[$mapping['fieldName']] = $this->instance;
            $entity = $this->entityBuilder->createEntity($assocClass, $data);
        } else {
            $entity = $data;
        }

        // Create a new entity
        $this->accessor->setValue(
            $this->instance,
            $this->association,
            $entity
        );
    }

    protected function uniDirectional()
    {
        $data = $this->params->get($this->association);
        $assocClass = $this->meta->getAssociationTargetClass($this->association);

        if ( ! $data instanceof $assocClass) {
            $entity = $this->entityBuilder->createEntity($assocClass, $data);
        } else {
            $entity = $data;
        }

        $this->accessor->setValue(
            $this->instance,
            $this->association,
            $entity
        );
    }

    protected function biDirectional()
    {
        $data = $this->params->get($this->association);
        $mapping = $this->meta->getAssociationMapping($this->association);
        $assocClass = $this->meta->getAssociationTargetClass($this->association);

        if ( ! $data instanceof $assocClass) {
            $data[$mapping['mappedBy']] = $this->instance;
            $entity = $this->entityBuilder->createEntity($assocClass, $data);
        } else {
            $entity = $data;
        }

        $this->accessor->setValue(
            $this->instance,
            $this->association,
            $entity
        );
    }
}
