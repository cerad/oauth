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
    public function persist($entity) { return $this->_em->persist($entity); }
    public function refresh($entity) { return $this->_em->refresh($entity); }
    public function detach ($entity) { return $this->_em->detach ($entity); }
    public function merge  ($entity) { return $this->_em->merge  ($entity); }
    public function remove ($entity) { return $this->_em->remove ($entity); }
    public function flush()          { return $this->_em->flush();          }
    public function clear()          { return $this->_em->clear();          }
 
    public function getReference($id) { return $this->_em->getReference($this->getEntityName(),$id); }
    
    public function getConnection()
    {
        return $this->_em->getConnection();
    }
}
