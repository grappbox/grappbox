<?php

namespace MongoBundle\Document;


/**
 * MongoBundle\Document\Dependencies
 */
class Dependencies
{
    /**
     * @var id $id
     */
    protected $id;

    /**
     * @var string $name
     */
    protected $name;

    /**
     * @var MongoBundle\Document\Task
     */
    protected $dependence_task;

    /**
     * @var MongoBundle\Document\Task
     */
    protected $task;


    /**
     * Get id
     *
     * @return id $id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return self
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set dependence_task
     *
     * @param MongoBundle\Document\Task $dependenceTask
     * @return self
     */
    public function setDependenceTask( $dependenceTask)
    {
        $this->dependence_task = $dependenceTask;

        return $this;
    }

    /**
     * Get dependence_task
     *
     * @return \MongoBundle\Document\Task
     */
    public function getDependenceTask()
    {
        return $this->dependence_task;
    }

    /**
     * Set task
     *
     * @param MongoBundle\Document\Task $task
     * @return self
     */
    public function setTask( $task)
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
}
