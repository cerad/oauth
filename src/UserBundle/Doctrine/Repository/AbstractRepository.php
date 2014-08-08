<?php

namespace Cerad\Bundle\UserBundle\Doctrine\Repository;

use Doctrine\ORM\EntityRepository;

// TODO: Implement ObjectManager Interface

class AbstractRepository extends EntityRepository
{
    // Allow null for id
    public function find($id)
    {
        return $id ? parent::find($id) : null;
    }
    /* ==========================================================
     * Persistence
     */
    public function persist($entity) { return $this->getEntityManager()->persist($entity); }
    public function refresh($entity) { return $this->getEntityManager()->refresh($entity); }
    public function detach ($entity) { return $this->getEntityManager()->detach ($entity); }
    public function remove ($entity) { return $this->getEntityManager()->remove ($entity); }
    public function flush()          { return $this->getEntityManager()->flush();          }
    public function clear()          { return $this->getEntityManager()->clear();          }
 
    public function getReference($id) { return $this->getEntityManager()->getReference($this->getEntityName(),$id); }    
}
