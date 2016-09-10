<?php

namespace MongoBundle\Document;



/**
 * MongoBundle\Document\Ressources
 */
class Ressources
{
    /**
     * @var id
     */
    private $id;

    /**
     * @var int
     */
    private $resource;

    /**
     * @var MongoBundle\Document\Task
     */
    private $task;

    /**
     * @var MongoBundle\Document\User
     */
    private $user;


    /**
     * Set resource
     *
     * @param int $resource
     * @return self
     */
    public function setResource($resource)
    {
        $this->resource = $resource;

        return $this;
    }

    /**
     * Get resource
     *
     * @return int
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * Set task
     *
     * @param MongoBundle\Document\Task $task
     * @return self
     */
    public function setTask(\MongoBundle\Document\Task $task = null)
    {
        $this->task = $task;

        return $this;
    }

    /**
     * Get task
     *
     * @return MongoBundle\Document\Task
     */
    public function getTask()
    {
        return $this->task;
    }

    /**
     * Set user
     *
     * @param MongoBundle\Document\User $user
     * @return self
     */
    public function setUser(\MongoBundle\Document\User $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return MongoBundle\Document\User
     */
    public function getUser()
    {
        return $this->user;
    }


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
}
