<?php

namespace SQLBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Dependencies
 */
class Dependencies
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var \SQLBundle\Entity\Task
     */
    private $dependence_task;

    /**
     * @var \SQLBundle\Entity\Task
     */
    private $task;


    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Dependencies
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
     * @param \SQLBundle\Entity\Task $dependenceTask
     * @return Dependencies
     */
    public function setDependenceTask(\SQLBundle\Entity\Task $dependenceTask = null)
    {
        $this->dependence_task = $dependenceTask;

        return $this;
    }

    /**
     * Get dependence_task
     *
     * @return \SQLBundle\Entity\Task 
     */
    public function getDependenceTask()
    {
        return $this->dependence_task;
    }

    /**
     * Set task
     *
     * @param \SQLBundle\Entity\Task $task
     * @return Dependencies
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
}
