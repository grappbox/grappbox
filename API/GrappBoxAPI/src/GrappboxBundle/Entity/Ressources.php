<?php

namespace GrappboxBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Ressources
 */
class Ressources
{
    /**
     * @var integer
     */
    private $resource;

    /**
     * @var \GrappboxBundle\Entity\Task
     */
    private $task;

    /**
     * @var \GrappboxBundle\Entity\User
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
     * @param \GrappboxBundle\Entity\Task $task
     * @return Ressources
     */
    public function setTask(\GrappboxBundle\Entity\Task $task = null)
    {
        $this->task = $task;

        return $this;
    }

    /**
     * Get task
     *
     * @return \GrappboxBundle\Entity\Task 
     */
    public function getTask()
    {
        return $this->task;
    }

    /**
     * Set user
     *
     * @param \GrappboxBundle\Entity\User $user
     * @return Ressources
     */
    public function setUser(\GrappboxBundle\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \GrappboxBundle\Entity\User 
     */
    public function getUser()
    {
        return $this->user;
    }
    /**
     * @var integer
     */
    private $id;


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
