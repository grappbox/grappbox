<?php

namespace SQLBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Ressources
 */
class Ressources
{
    /**
     * @var integer
     */
    private $id;
    
    /**
     * @var integer
     */
    private $resource;

    /**
     * @var \SQLBundle\Entity\Task
     */
    private $task;

    /**
     * @var \SQLBundle\Entity\User
     */
    private $user;


    /**
     * Set resource
     *
     * @param integer $resource
     * @return Ressources
     */
    public function setResource($resource)
    {
        $this->resource = $resource;

        return $this;
    }

    /**
     * Get resource
     *
     * @return integer 
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * Set task
     *
     * @param \SQLBundle\Entity\Task $task
     * @return Ressources
     */
    public function setTask(\SQLBundle\Entity\Task $task = null)
    {
        $this->task = $task;

        return $this;
    }

    /**
     * Get task
     *
     * @return \SQLBundle\Entity\Task 
     */
    public function getTask()
    {
        return $this->task;
    }

    /**
     * Set user
     *
     * @param \SQLBundle\Entity\User $user
     * @return Ressources
     */
    public function setUser(\SQLBundle\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \SQLBundle\Entity\User 
     */
    public function getUser()
    {
        return $this->user;
    }


    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }
}
